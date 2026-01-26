<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    // Listar tickets (depende del rol)
    public function index(Request $request)
    {
        $user = $request->user();
        $rol = strtolower($user->rol?->nombre_rol ?? '');

        $q = DB::table('ticket')
            ->join('estado_ticket', 'ticket.id_estado', '=', 'estado_ticket.id_estado')
            ->join('prioridad', 'ticket.id_prioridad', '=', 'prioridad.id_prioridad')
            ->join('sucursal', 'ticket.id_sucursal', '=', 'sucursal.id_sucursal')
            ->leftJoin('tipo_problema', 'ticket.id_problema', '=', 'tipo_problema.id_problema')
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

    // 2) Crear ticket (solo sucursal)
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'id_prioridad' => 'required|integer',
            'id_problema' => 'required|integer',
        ]);

        $user = $request->user();

        $idTicket = DB::table('ticket')->insertGetId([
            'id_sucursal' => $user->id_sucursal,
            'id_estado' => 1, // Abierto (asegúrate que 1 sea "Abierto")
            'id_usuario' => $user->id_usuario,
            'id_prioridad' => $request->id_prioridad,
            'id_problema' => $request->id_problema,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha_creacion' => now(),
            'comentarios' => null,
        ], 'id_ticket');

        return response()->json([
            'message' => 'Ticket creado',
            'id_ticket' => $idTicket,
        ], 201);
    }

    // 3) Resolver ticket (solo técnico)
    public function resolver(Request $request, $id)
    {
        $request->validate([
            'solucion' => 'required|string',
            'observaciones' => 'nullable|string',
        ]);

        $tecnico = $request->user();

        $ticket = DB::table('ticket')->where('id_ticket', $id)->first();

        if (!$ticket) {
            return response()->json(['message' => 'Ticket no encontrado'], 404);
        }

        // tiempo en minutos desde creación
        $minutos = now()->diffInMinutes($ticket->fecha_creacion);

        DB::table('ticket_resuelto')->insert([
            'id_ticket' => (int)$id,
            'id_usuario' => $tecnico->id_usuario,
            'fecha_resolucion' => now(),
            'solucion' => $request->solucion,
            'observaciones' => $request->observaciones,
            'tiempo_resolucion_minutos' => $minutos,
        ]);

        // Cambiar estado a Cerrado
        DB::table('ticket')->where('id_ticket', $id)->update([
            'id_estado' => 3, // Cerrado (asegúrate que 3 sea Cerrado)
            'comentarios' => $request->observaciones,
        ]);

        return response()->json([
            'message' => 'Ticket resuelto',
            'id_ticket' => (int)$id,
            'tiempo_resolucion_minutos' => $minutos,
        ]);
    }
}
