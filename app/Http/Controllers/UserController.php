<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Crear usuario (admin normalmente)
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:Usuario,username',
            'password' => 'required|string|min:4',
            'id_rol' => 'required|integer',
            'id_sucursal' => 'nullable|integer',
        ]);

        $user = User::create([
            'username' => $request->username,
            // AQUÍ HASHEA
            'password' => Hash::make($request->password),
            'id_rol' => $request->id_rol,
            'id_sucursal' => $request->id_sucursal,
        ]);

        return response()->json([
            'message' => 'Usuario creado',
            'user' => $user,
        ], 201);
    }
}
