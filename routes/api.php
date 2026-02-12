<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;



Route::post('/login', [AuthController::class, 'login']);

/**
 * se protege se requiere en token
 */
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // roles
    Route::middleware('role:admin')->get('/admin', fn () => response()->json(['ok' => 'admin']));
    Route::middleware('role:tecnico')->get('/tecnico', fn () => response()->json(['ok' => 'tecnico']));
    Route::middleware('role:sucursal')->get('/sucursal', fn () => response()->json(['ok' => 'sucursal']));

    // Tickets (todos autenticados)
    Route::get('/tickets', [TicketController::class, 'index']);

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
