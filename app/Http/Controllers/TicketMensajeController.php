<?php

namespace App\Http\Controllers;

use App\Models\TicketMensaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use Illuminate\Support\Facades\Log;

class TicketMensajeController extends Controller
{
    // ========================
    // LISTAR MENSAJES
    // ========================
    public function index($id)
    {
        return TicketMensaje::with('usuario') // 👈 ESTA ES LA CLAVE
            ->where('id_ticket', $id)
            ->orderBy('fecha_envio', 'asc')
            ->get();
    }

    // ========================
    // GUARDAR MENSAJE + ARCHIVO
    // ========================
    public function store(Request $request, $id)
    {
        try {

            $request->validate([
                'mensaje' => 'nullable|string',
                'archivo' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov|max:20480'
            ]);

            $path = null;

            if (!$request->hasFile('archivo')) {
                Log::info("NO LLEGA ARCHIVO");
            } else {
                Log::info("SI LLEGA ARCHIVO");
            }

            $file = $request->file('archivo');

            if ($file) {
                $path = $file->store('tickets', 'public');
            }

            // 🚨 VALIDACIÓN CLAVE
            if (!$request->mensaje && !$path) {
                return response()->json([
                    'error' => 'Debes enviar mensaje o archivo'
                ], 400);
            }

            return TicketMensaje::create([
                'id_ticket' => $id,
                'id_usuario' => Auth::user()->id_usuario,
                'mensaje' => $request->mensaje,
                'archivo' => $path,
                'fecha_envio' => now(),
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'error_real' => $e->getMessage(),
                'linea' => $e->getLine()
            ], 500);
        }
    }

    // ========================
    // DETALLE CON MENSAJES
    // ========================
    public function show($id)
    {
        $ticket = Ticket::with([
            'mensajes.usuario'
        ])->where('id_ticket', $id)->firstOrFail();

        return response()->json($ticket);
    }
}
