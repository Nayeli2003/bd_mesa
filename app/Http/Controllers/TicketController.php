<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

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


        // Solo sucursal puede crear ticket
        if ($rol !== 'sucursal') {
            return response()->json(['message' => 'No autorizado (solo sucursal)'], 403);
        }

        // Validación
        $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'id_tipo_problema' => 'required|integer|exists:tipo_problema,id_tipo_problema',
            'evidencias.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov|max:20480'
        ]);

        // Buscar estado "Abierto"
        $estadoAbierto = DB::table('estado_ticket')
            ->whereRaw("LOWER(nombre) = 'abierto'")
            ->first();

        if (!$estadoAbierto) {
            return response()->json(['message' => 'Error: El estado "Abierto" no existe'], 500);
        }

        // Selección automática de técnico
        $tecnico = $this->seleccionarTecnicoAutomatico();

        if (!$tecnico) {
            return response()->json(['message' => 'No hay técnicos disponibles'], 422);
        }

        // Estado inicial
        $estadoProceso = DB::table('estado_ticket')
            ->whereRaw("LOWER(nombre) IN ('en proceso','proceso')")
            ->first();

        $idEstadoInicial = $estadoProceso
            ? $estadoProceso->id_estado
            : $estadoAbierto->id_estado;

        // Prioridad automática
        $idPrioridad = $this->calcularPrioridadAutomatica(
            $request->id_tipo_problema
        );

        // Crear ticket
        $idTicket = DB::table('ticket')->insertGetId([
            'id_sucursal' => $user->id_sucursal,
            'id_estado' => $idEstadoInicial,
            'id_usuario' => $user->id_usuario,
            'id_prioridad' => $idPrioridad,
            'id_tipo_problema' => $request->id_tipo_problema,
            'id_tecnico' => $tecnico->id_usuario,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha_creacion' => now(),
        ], 'id_ticket');




        // Guardar evidencias
        $files = $request->file('evidencias');

        if ($files) {
            foreach ((array)$files as $file) {

                if (!$file instanceof \Illuminate\Http\UploadedFile) {
                    continue; // 👈 IGNORA basura
                }

                $path = $file->store('tickets', 'public');

                DB::table('ticket_evidencia')->insert([
                    'id_ticket' => $idTicket,
                    'id_usuario' => $user->id_usuario,
                    'tipo' => $file->getMimeType(),
                    'nombre' => $file->getClientOriginalName(),
                    'ruta' => $path,
                ]);
            }
        }

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
        $estadoCerrado = DB::table('estado_ticket')->whereRaw("LOWER(nombre) = 'cerrado'")->first();
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
                    'tiempo_resolucion' => gmdate("H:i:s", $minutos * 60),
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
     * cambiar el estado de los tickets
     */
    public function cambiarEstado(Request $request, $id)
    {
        $user = $request->user();

        DB::table('ticket')
            ->where('id_ticket', $id)
            ->update([
                'id_estado' => $request->id_estado
            ]);

        return response()->json(['message' => 'Estado actualizado']);
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

        $request->merge([
            'id_tecnico' => $request->id_tecnico
        ]);

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

    /**
     * Calcula prioridad automáticamente según el tipo de problema
     */
    private function calcularPrioridadAutomatica($idTipoProblema)
    {
        $tipo = DB::table('tipo_problema')
            ->where('id_tipo_problema', $idTipoProblema)
            ->first();

        if (!$tipo) {
            return 3; // Baja por defecto
        }

        $nombre = strtolower($tipo->nombre);

        // Alta prioridad
        if (str_contains($nombre, 'falla de luz')) {
            return 1;
        }

        // Media prioridad
        if (
            str_contains($nombre, 'error de conexión') ||
            str_contains($nombre, 'falla telmex')
        ) {
            return 2;
        }

        // Baja prioridad por defecto
        return 3;
    }


    /**
     * 6) Descargar memoria técnica (PDF)
     * - Solo admin y técnico
     * - Solo si el ticket está cerrado
     */
    public function descargarMemoria(Request $request, $id)
    {
        $user = $request->user();
        $rol = strtolower($user->rol?->nombre_rol ?? '');

        // Solo admin y técnico pueden descargar
        if (!in_array($rol, ['admin', 'tecnico'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Buscar ticket con toda la información necesaria
        $ticket = DB::table('ticket')
            ->join('estado_ticket', 'ticket.id_estado', '=', 'estado_ticket.id_estado')
            ->join('prioridad', 'ticket.id_prioridad', '=', 'prioridad.id_prioridad')
            ->join('sucursal', 'ticket.id_sucursal', '=', 'sucursal.id_sucursal')
            ->leftJoin('ticket_resuelto', 'ticket.id_ticket', '=', 'ticket_resuelto.id_ticket')
            ->select(
                'ticket.*',
                'estado_ticket.nombre as estado',
                'prioridad.nombre as prioridad',
                'sucursal.nombre as sucursal',
                'ticket_resuelto.solucion',
                'ticket_resuelto.observaciones',
                'ticket_resuelto.tiempo_resolucion_minutos'
            )
            ->where('ticket.id_ticket', $id)
            ->first();

        if (!$ticket) {
            return response()->json(['message' => 'Ticket no encontrado'], 404);
        }

        // Solo se puede generar si está cerrado
        if (strtolower($ticket->estado) !== 'cerrado') {
            return response()->json([
                'message' => 'La memoria técnica solo puede generarse cuando el ticket está cerrado'
            ], 422);
        }

        // Generar PDF
        $pdf = Pdf::loadView('pdf.memoria_tecnica', compact('ticket'));

        return $pdf->download('Memoria_Tecnica_Ticket_' . $ticket->id_ticket . '.pdf');
    }

    /**
     * 7) Mostrar detalle de un ticket específico
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $rol = strtolower($user->rol?->nombre_rol ?? '');

        // BUSCAR TICKET
        $ticket = DB::table('ticket')
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
            ->where('ticket.id_ticket', $id)
            ->first();

        // validar antes de usar
        if (!$ticket) {
            return response()->json([
                'message' => 'Ticket no encontrado'
            ], 404);
        }

        //  TRAER EVIDENCIAS
        $evidencias = DB::table('ticket_evidencia')
            ->where('id_ticket', $id)
            ->get();

        //  CONVERTIR A URL
        $ticket->evidencias = collect($evidencias)->map(function ($e) {
            return [
                'type' => str_contains($e->tipo, 'image') ? 'image' : 'video',
                'name' => $e->nombre,
                'path' => asset('storage/' . $e->ruta),
            ];
        });
        //  SEGURIDAD
        if (
            $rol === 'admin' ||
            ($rol === 'tecnico' && (int)$ticket->id_tecnico === (int)$user->id_usuario) ||
            ($rol === 'sucursal' && (int)$ticket->id_sucursal === (int)$user->id_sucursal)
        ) {
            return response()->json($ticket);
        }

        return response()->json([
            'message' => 'No autorizado'
        ], 403);
    }
}
