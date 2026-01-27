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
            'id_prioridad' => 'required|integer',
            'id_tipo_problema' => 'required|integer',
        ]);

        // Buscar estado "Abierto"
        $estadoAbierto = DB::table('estado_ticket')->where('nombre', 'Abierto')->first();
        if (!$estadoAbierto) {
            return response()->json(['message' => 'Error: El estado "Abierto" no existe en la DB'], 500);
        }

        $idTicket = DB::table('ticket')->insertGetId([
            'id_sucursal' => $user->id_sucursal,
            'id_estado' => $estadoAbierto->id_estado,
            'id_usuario' => $user->id_usuario,
            'id_prioridad' => $request->id_prioridad,
            'id_tipo_problema' => $request->id_tipo_problema,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha_creacion' => now(),
        ], 'id_ticket');

        return response()->json([
            'message' => 'Ticket creado',
            'id_ticket' => $idTicket
        ], 201);
    }

    /**
     * 3) Resolver / actualizar estado ticket (solo técnico)
     * - Si el estado elegido es "Cerrado", crea/actualiza en ticket_resuelto con tiempo de resolución.
     */
    public function resolver(Request $request, $id)
    {
        $tecnico = $request->user();
        $rol = strtolower($tecnico->rol?->nombre_rol ?? '');

        if ($rol !== 'tecnico') {
            return response()->json(['message' => 'No autorizado (solo técnico)'], 403);
        }

        $request->validate([
            'id_estado' => 'required|integer',
            'solucion' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        $ticket = DB::table('ticket')->where('id_ticket', $id)->first();
        if (!$ticket) {
            return response()->json(['message' => 'Ticket no encontrado'], 404);
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
            // Opcional: si tú asignas el técnico aquí, descomenta:
            // 'id_tecnico' => $tecnico->id_usuario,
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
}
