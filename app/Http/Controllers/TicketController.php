<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TicketController extends Controller
{
    /**
     * 1) Listar tickets (depende del rol)
     * - Admin / Técnico: ve todos
     * - Sucursal: solo ve los suyos
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $rol = strtolower($user->rol?->nombre_rol ?? '');

        $q = DB::table('ticket')
            ->join('estado_ticket', 'ticket.id_estado', '=', 'estado_ticket.id_estado')
            ->join('prioridad', 'ticket.id_prioridad', '=', 'prioridad.id_prioridad')
            ->join('sucursal', 'ticket.id_sucursal', '=', 'sucursal.id_sucursal')
            ->leftJoin('tipo_problema', 'ticket.id_tipo_problema', '=', 'tipo_problema.id_tipo_problema')
            ->select(
                'ticket.*',
                'estado_ticket.nombre as estado',
                'prioridad.nombre as prioridad',
                'prioridad.color as prioridad_color',
                'sucursal.nombre as sucursal',
                'tipo_problema.nombre as tipo_problema'
            )
            ->orderByDesc('ticket.fecha_creacion');

        // Si es sucursal, solo ve sus tickets
        if ($rol === 'sucursal') {
            $q->where('ticket.id_sucursal', $user->id_sucursal);
        }

        return $q->get();
    }

    /**
     * 2) Crear ticket (solo sucursal)
     * - Asignación automática de técnico
     * - Agregar que se asigne la prioridad automáticamente 
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $rol = strtolower($user->rol?->nombre_rol ?? '');

        if ($rol !== 'sucursal') {
            return response()->json(['message' => 'No autorizado (solo sucursal)'], 403);
        }

        $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'id_prioridad' => 'required|integer|exists:prioridad,id_prioridad',
            'id_tipo_problema' => 'required|integer|exists:tipo_problema,id_tipo_problema',
        ]);

        // Buscar estado "Abierto"
        $estadoAbierto = DB::table('estado_ticket')->where('nombre', 'Abierto')->first();
        if (!$estadoAbierto) {
            return response()->json(['message' => 'Error: El estado "Abierto" no existe en la DB'], 500);
        }

        // Técnico automático (menos carga de tickets activos)
        $tecnico = $this->seleccionarTecnicoAutomatico();
        if (!$tecnico) {
            return response()->json(['message' => 'No hay técnicos disponibles para asignación automática'], 422);
        }

        // (Opcional) Si existe estado "En proceso", úsalo cuando se asigna
        $estadoProceso = DB::table('estado_ticket')
            ->whereRaw("LOWER(nombre) IN ('en proceso','proceso')")
            ->first();

        $idEstadoInicial = $estadoProceso ? $estadoProceso->id_estado : $estadoAbierto->id_estado;

        $idTicket = DB::table('ticket')->insertGetId([
            'id_sucursal' => $user->id_sucursal,
            'id_estado' => $idEstadoInicial,
            'id_usuario' => $user->id_usuario,
            'id_prioridad' => $request->id_prioridad,
            'id_tipo_problema' => $request->id_tipo_problema,
            'id_tecnico' => $tecnico->id_usuario, // asignación automática
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha_creacion' => now(),
        ], 'id_ticket');

        return response()->json([
            'message' => 'Ticket creado y asignado automáticamente',
            'id_ticket' => $idTicket,
            'id_tecnico' => (int)$tecnico->id_usuario
        ], 201);
    }

    /**
     * 3) Resolver / actualizar estado ticket (solo técnico)
     * - Si el estado elegido es "Cerrado", guarda en ticket_resuelto
     * - Agregar que se genere el pdf.
     */
    public function resolver(Request $request, $id)
    {
        $tecnico = $request->user();
        $rol = strtolower($tecnico->rol?->nombre_rol ?? '');

        if ($rol !== 'tecnico') {
            return response()->json(['message' => 'No autorizado (solo técnico)'], 403);
        }

        $request->validate([
            'id_estado' => 'required|integer|exists:estado_ticket,id_estado',
            'solucion' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        $ticket = DB::table('ticket')->where('id_ticket', $id)->first();
        if (!$ticket) {
            return response()->json(['message' => 'Ticket no encontrado'], 404);
        }

        // Seguridad: que solo resuelva los asignados
        if ((int)($ticket->id_tecnico ?? 0) !== (int)$tecnico->id_usuario) {
            return response()->json(['message' => 'No puedes resolver un ticket que no está asignado a ti'], 403);
        }

        // Buscar estado "Cerrado"
        $estadoCerrado = DB::table('estado_ticket')->where('nombre', 'Cerrado')->first();
        if (!$estadoCerrado) {
            return response()->json(['message' => 'Error: El estado "Cerrado" no existe en la DB'], 500);
        }

        // Si el técnico eligió "Cerrado", guardamos resolución
        if ((int)$request->id_estado === (int)$estadoCerrado->id_estado) {
            $minutos = now()->diffInMinutes(Carbon::parse($ticket->fecha_creacion));

            DB::table('ticket_resuelto')->updateOrInsert(
                ['id_ticket' => (int)$id],
                [
                    'id_usuario' => $tecnico->id_usuario,
                    'fecha_resolucion' => now(),
                    'solucion' => $request->solucion ?? 'Sin solución detallada',
                    'observaciones' => $request->observaciones,
                    'tiempo_resolucion_minutos' => $minutos,
                ]
            );
        }

        // Actualizar el ticket con el estado seleccionado
        DB::table('ticket')->where('id_ticket', $id)->update([
            'id_estado' => $request->id_estado,
            'comentarios' => $request->observaciones,
        ]);

        return response()->json(['message' => 'Estado del ticket actualizado']);
    }

    /**
     * 4) Mis tickets (solo técnico)
     * - Tickets asignados al técnico en ticket.id_tecnico
     */
    public function misTickets(Request $request)
    {
        $tecnico = $request->user();
        $rol = strtolower($tecnico->rol?->nombre_rol ?? '');

        if ($rol !== 'tecnico') {
            return response()->json(['message' => 'No autorizado (solo técnico)'], 403);
        }

        $tickets = DB::table('ticket')
            ->join('estado_ticket', 'ticket.id_estado', '=', 'estado_ticket.id_estado')
            ->join('prioridad', 'ticket.id_prioridad', '=', 'prioridad.id_prioridad')
            ->join('sucursal', 'ticket.id_sucursal', '=', 'sucursal.id_sucursal')
            ->leftJoin('tipo_problema', 'ticket.id_tipo_problema', '=', 'tipo_problema.id_tipo_problema')
            ->select(
                'ticket.*',
                'estado_ticket.nombre as estado',
                'prioridad.nombre as prioridad',
                'prioridad.color as prioridad_color',
                'sucursal.nombre as sucursal',
                'tipo_problema.nombre as tipo_problema'
            )
            ->where('ticket.id_tecnico', $tecnico->id_usuario)
            ->orderByDesc('ticket.fecha_creacion')
            ->get();

        return $tickets;
    }

    /**
     * 5) Asignación manual por admin (reasignación)
     * POST /tickets/{id}/asignar  body: { "id_tecnico": 2 } ejemplo
     * - que tambien pueda descargar pdf (urgente)
     */
    public function asignarTecnico(Request $request, $id)
    {
        $admin = $request->user();
        $rol = strtolower($admin->rol?->nombre_rol ?? '');

        if ($rol !== 'admin') {
            return response()->json(['message' => 'No autorizado (solo admin)'], 403);
        }

        $request->validate([
            'id_tecnico' => 'required|integer',
        ]);

        $ticket = DB::table('ticket')->where('id_ticket', $id)->first();
        if (!$ticket) {
            return response()->json(['message' => 'Ticket no encontrado'], 404);
        }

        // Validar que el usuario existe y es técnico
        $tecnico = DB::table('usuario as u')
            ->join('rol as r', 'u.id_rol', '=', 'r.id_rol')
            ->where('u.id_usuario', $request->id_tecnico)
            ->whereRaw("LOWER(r.nombre_rol) = 'tecnico'")
            ->select('u.id_usuario')
            ->first();

        if (!$tecnico) {
            return response()->json(['message' => 'El usuario no es técnico o no existe'], 422);
        }

        // (Opcional) si existe "En proceso", se pone al asignar
        $estadoProceso = DB::table('estado_ticket')
            ->whereRaw("LOWER(nombre) IN ('en proceso','proceso')")
            ->first();

        $dataUpdate = [
            'id_tecnico' => (int)$request->id_tecnico,
        ];

        if ($estadoProceso) {
            $dataUpdate['id_estado'] = $estadoProceso->id_estado;
        }

        DB::table('ticket')->where('id_ticket', $id)->update($dataUpdate);

        return response()->json([
            'message' => 'Técnico asignado correctamente',
            'id_ticket' => (int)$id,
            'id_tecnico' => (int)$request->id_tecnico
        ]);
    }

    /**
     * Selecciona técnico automático por menor carga de tickets activos
     */
    private function seleccionarTecnicoAutomatico(): ?object
    {
        $estadosActivos = DB::table('estado_ticket')
            ->whereIn(DB::raw('LOWER(nombre)'), ['abierto', 'en proceso', 'proceso'])
            ->pluck('id_estado')
            ->toArray();

        $tecnico = DB::table('usuario as u')
            ->join('rol as r', 'u.id_rol', '=', 'r.id_rol')
            ->leftJoin('ticket as t', function ($join) use ($estadosActivos) {
                $join->on('u.id_usuario', '=', 't.id_tecnico');
                if (!empty($estadosActivos)) {
                    $join->whereIn('t.id_estado', $estadosActivos);
                }
            })
            ->whereRaw("LOWER(r.nombre_rol) = 'tecnico'")
            ->select('u.id_usuario', DB::raw('COUNT(t.id_ticket) as carga'))
            ->groupBy('u.id_usuario')
            ->orderBy('carga', 'asc')
            ->orderBy('u.id_usuario', 'asc')
            ->first();

        return $tecnico; // { id_usuario, carga } o null depende
    }
}
