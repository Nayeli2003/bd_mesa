<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;

/**
 * Público
 */
Route::post('/login', [AuthController::class, 'login']);

/**
 * Protegido (requiere token Sanctum)
 */
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    /**
     * Test rápido por rol (útil para debug)
     */
    Route::middleware('role:admin')->get('/admin', fn () => response()->json(['ok' => 'admin']));
    Route::middleware('role:tecnico')->get('/tecnico', fn () => response()->json(['ok' => 'tecnico']));
    Route::middleware('role:sucursal')->get('/sucursal', fn () => response()->json(['ok' => 'sucursal']));

    /**
     * Tickets (todos autenticados)
     */
    Route::get('/tickets', [TicketController::class, 'index']);

    // Sucursal crea ticket
    Route::middleware('role:sucursal')
        ->post('/tickets', [TicketController::class, 'store']);

    // Técnico resuelve ticket
    Route::middleware('role:tecnico')
        ->post('/tickets/{id}/resolver', [TicketController::class, 'resolver']);

    // Técnico ve sus tickets
    Route::middleware('role:tecnico')
        ->get('/mis-tickets', [TicketController::class, 'misTickets']);
});
