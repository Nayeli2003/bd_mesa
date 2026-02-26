<?php
namespace App\Http\Controllers;

use App\Models\TicketMensaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketMensajeController extends Controller
{
    public function index($id)
    {
        return TicketMensaje::where('id_ticket', $id)
            ->with('usuario')
            ->orderBy('fecha_envio', 'asc')
            ->get();
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'mensaje' => 'required|string'
        ]);

        return TicketMensaje::create([
            'id_ticket' => $id,
            'id_usuario' => Auth::user()->id_usuario,
            'mensaje' => $request->mensaje,
        ]);
    }
}
