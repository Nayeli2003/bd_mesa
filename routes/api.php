<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TicketMensajeController;

Route::post('/login', [AuthController::class, 'login']);

Route::options('/{any}', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With')
        ->header('Access-Control-Max-Age', '86400');
})->where('any', '.*');

/**
 * se protege se requiere en token
 */
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // roles
    Route::middleware('role:admin')->get('/admin', fn() => response()->json(['ok' => 'admin']));
    Route::middleware('role:tecnico')->get('/tecnico', fn() => response()->json(['ok' => 'tecnico']));
    Route::middleware('role:sucursal')->get('/sucursal', fn() => response()->json(['ok' => 'sucursal']));

    // Tickets (todos autenticados)
    Route::get('/tickets', [TicketController::class, 'index']);

    // ===== MENSAJES (chat del ticket) =====
    Route::get('/tickets/{id}/mensajes', [TicketMensajeController::class, 'index']);
    Route::post('/tickets/{id}/mensajes', [TicketMensajeController::class, 'store']);

    Route::get('/tickets/{id}', [TicketController::class, 'show']); //*probable error de ruta*


    // Descargar PDF
    Route::get('/tickets/{id}/memoria', [TicketController::class, 'descargarMemoria']);

    // Sucursal
    Route::middleware('role:sucursal')->group(function () {
        Route::post('/tickets', [TicketController::class, 'store']);
    });

    // Técnico
    Route::middleware('role:tecnico')->group(function () {
        Route::get('/mis-tickets', [TicketController::class, 'misTickets']);
        Route::post('/tickets/{id}/resolver', [TicketController::class, 'resolver']);
    });

    // Admin
    Route::middleware('role:admin')->group(function () {

        // Tickets
        Route::post('/tickets/{id}/asignar', [TicketController::class, 'asignarTecnico']);

        // ===== USUARIOS (panel admin) =====
        Route::get('/usuarios', [UserController::class, 'index']);

        Route::post('/usuarios/admin', [UserController::class, 'storeAdmin']);
        Route::post('/usuarios/tecnico', [UserController::class, 'storeTecnico']);
        Route::post('/usuarios/sucursal', [UserController::class, 'storeSucursal']);

        Route::put('/usuarios/{id_usuario}', [UserController::class, 'update']);
        Route::patch('/usuarios/{id}/estado', [UserController::class, 'cambiarEstado']);
        Route::delete('/usuarios/{id}', [UserController::class, 'destroy']);
    });
});
