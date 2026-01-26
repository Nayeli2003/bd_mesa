<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::with(['rol','sucursal'])
            ->where('username', $request->username)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        // opcional: revocar tokens anteriores
        $user->tokens()->delete();

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id_usuario' => $user->id_usuario,
                'username' => $user->username,
                'id_rol' => $user->id_rol,
                'rol' => $user->rol?->nombre_rol,     // admin / tecnico / sucursal
                'id_sucursal' => $user->id_sucursal,
                'sucursal' => $user->sucursal?->nombre,
            ]
        ]);
    }

    public function me(Request $request)
    {
        return $request->user()->load(['rol','sucursal']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }
}
