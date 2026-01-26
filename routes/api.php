<?php
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

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
    