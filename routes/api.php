<?php
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
Route::middleware(['auth:sanctum', 'role:admin'])->get('/admin', function () {
    return response()->json(['ok' => 'admin']);
});

Route::middleware(['auth:sanctum', 'role:tecnico'])->get('/tecnico', function () {
    return response()->json(['ok' => 'tecnico']);
});

Route::middleware(['auth:sanctum', 'role:sucursal'])->get('/sucursal', function () {
    return response()->json(['ok' => 'sucursal']);
});

//========Parte de tickets===============//

Route::middleware(['auth:sanctum','role:tecnico'])
    ->get('/mis-tickets', [TicketController::class, 'misTickets']);


// Tickets
Route::get('/tickets', [TicketController::class, 'index']);

// Sucursal crea ticket
Route::middleware('role:sucursal')->post('/tickets', [TicketController::class, 'store']);

// Técnico resuelve ticket
Route::middleware('role:tecnico')->post('/tickets/{id}/resolver', [TicketController::class, 'resolver']);

Route::middleware('role:tecnico')->get('/mis-tickets', [TicketController::class, 'misTickets']);