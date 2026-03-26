<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TicketMensajeController;
use App\Http\Controllers\TipoProblemaController;
use App\Http\Controllers\TareaController;

/**
 * =========================
 * RUTA PÚBLICA
 * =========================
 */
Route::post('/login', [AuthController::class, 'login']);

// 🔥 ESTA RUTA DEBE SER PÚBLICA (IMPORTANTE PARA FLUTTER)
Route::get('/tipo-problema', [TipoProblemaController::class, 'index']);

/**
 * =========================
 * CORS (IMPORTANTE)
 * =========================
 */
Route::options('/{any}', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With')
        ->header('Access-Control-Max-Age', '86400');
})->where('any', '.*');

/**
 * =========================
 * TODO PROTEGIDO POR TOKEN
 * =========================
 */
Route::middleware('auth:sanctum')->group(function () {

    /**
     * =========================
     * AUTH
     * =========================
     */
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    /**
     * =========================
     * TEST DE ROLES (OPCIONAL)
     * =========================
     */
    Route::middleware('role:admin')->get('/admin', fn() => response()->json(['ok' => 'admin']));
    Route::middleware('role:tecnico')->get('/tecnico', fn() => response()->json(['ok' => 'tecnico']));
    Route::middleware('role:sucursal')->get('/sucursal', fn() => response()->json(['ok' => 'sucursal']));

    /**
     * =========================
     * TICKETS (COMPARTIDO)
     * =========================
     * TODOS los autenticados pueden ver detalle
     * PERO el control REAL va en el Controller
     */
    Route::get('/tickets/cerrados', [TicketController::class, 'ticketsCerrados']);
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::get('/tickets/{id}/memoria', [TicketController::class, 'descargarMemoria']);

    /**
     * =========================
     * MENSAJES (CHAT)
     * =========================
     * técnico y sucursal usan esto
     */
    Route::get('/tickets/{id}/mensajes', [TicketMensajeController::class, 'index']);
    Route::post('/tickets/{id}/mensajes', [TicketMensajeController::class, 'store']);

    /**
     * =========================
     * SUCURSAL
     * =========================
     */
    Route::middleware('role:sucursal')->group(function () {

        // Crear ticket (con imágenes)
        Route::post('/tickets', [TicketController::class, 'store']);
    });

    /**
     * =========================
     * TÉCNICO
     * =========================
     */
    Route::middleware('role:tecnico')->group(function () {

        // Solo ve los que le asignaron
        Route::get('/mis-tickets', [TicketController::class, 'misTickets']);

        // Resolver ticket
        Route::post('/tickets/{id}/resolver', [TicketController::class, 'resolver']);
<<<<<<< Updated upstream

        //Cerrar ticket
        // Route::get('/tickets/cerrados', [TicketController::class, 'ticketsCerrados']);
=======
>>>>>>> Stashed changes
    });

    /**
     * =========================
     * ADMIN (FULL CONTROL)
     * =========================
     */
    Route::middleware('role:admin')->group(function () {

        // Asignar técnico
        Route::post('/tickets/{id}/asignar', [TicketController::class, 'asignarTecnico']);

        Route::patch('/tickets/{id}/estado', [TicketController::class, 'cambiarEstado']);

        /**
         * USUARIOS
         */
        Route::get('/usuarios', [UserController::class, 'index']);

        Route::post('/usuarios/admin', [UserController::class, 'storeAdmin']);
        Route::post('/usuarios/tecnico', [UserController::class, 'storeTecnico']);
        Route::post('/usuarios/sucursal', [UserController::class, 'storeSucursal']);

        Route::put('/usuarios/{id_usuario}', [UserController::class, 'update']);
        Route::patch('/usuarios/{id}/estado', [UserController::class, 'cambiarEstado']);
        Route::delete('/usuarios/{id}', [UserController::class, 'destroy']);

        // ===== TAREAS =====
        Route::post('/tareas', [TareaController::class, 'store']);

        // ===== PROBLEMAS =====
        Route::post('/tipo-problema', [TipoProblemaController::class, 'store']);
        Route::put('/tipo-problema/{id}', [TipoProblemaController::class, 'update']);
        Route::delete('/tipo-problema/{id}', [TipoProblemaController::class, 'destroy']);
    });
});