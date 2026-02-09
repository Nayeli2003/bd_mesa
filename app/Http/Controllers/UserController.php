<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * LISTAR USUARIOS
     * Filtros:
     * - q (buscar)
     * - id_rol
     * - activo
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Buscar por username o id_usuario
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('username', 'like', "%$q%")
                    ->orWhere('id_usuario', $q);
            });
        }

        // Filtrar por rol
        if ($request->filled('id_rol')) {
            $query->where('id_rol', $request->id_rol);
        }

        // Filtrar por estado
        if ($request->filled('activo')) {
            $query->where('activo', $request->activo);
        }

        //  ordenar por PK 
        return response()->json(
            $query->orderBy('id_usuario', 'desc')->get()
        );
    }


    /**
     * CREAR ADMIN
     */
    public function storeAdmin(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'username' => 'required|string|unique:usuario,username',
            'password' => 'required|string|min:4',
            'activo' => 'required|boolean',
        ]);

        $user = User::create([
            'nombre' => $request->nombre,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'id_rol' => 1,
            'id_sucursal' => null,
            'activo' => $request->activo,
        ]);

        return response()->json([
            'message' => 'Administrador creado',
            'user' => $user,
        ], 201);
    }

    /**
     * CREAR TÉCNICO
     */
    public function storeTecnico(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'username' => 'required|string|unique:usuario,username',
            'password' => 'required|string|min:4',
            'activo' => 'required|boolean',
        ]);

        $user = User::create([
            'nombre' => $request->nombre,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'id_rol' => 2,
            'id_sucursal' => null,
            'activo' => $request->activo,
        ]);

        return response()->json([
            'message' => 'Técnico creado',
            'user' => $user,
        ], 201);
    }

    /**
     * CREAR SUCURSAL (crea sucursal + usuario)
     */
    public function storeSucursal(Request $request)
    {
        $request->validate([
            'id_sucursal' => 'required|integer|unique:sucursal,id_sucursal',
            'nombre_sucursal' => 'required|string',
            'nombre' => 'required|string',
            'username' => 'required|string|unique:usuario,username',
            'password' => 'required|string|min:4',
            'activo' => 'required|boolean',
        ]);

        $result = DB::transaction(function () use ($request) {

            // Crear sucursal (ID manual)
            $sucursal = Sucursal::create([
                'id_sucursal' => $request->id_sucursal,
                'nombre' => $request->nombre_sucursal,
            ]);

            // Crear usuario ligado a esa sucursal
            $user = User::create([
                'nombre' => $request->nombre,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'id_rol' => 3,
                'id_sucursal' => $request->id_sucursal,
                'activo' => $request->activo,
            ]);

            return compact('sucursal', 'user');
        });

        return response()->json([
            'message' => 'Sucursal creada correctamente',
            'sucursal' => $result['sucursal'],
            'user' => $result['user'],
        ], 201);
    }

    /**
     * ACTIVAR / DESACTIVAR USUARIO
     */
    public function cambiarEstado($id_usuario)
    {
        $user = User::findOrFail($id_usuario);
        $user->activo = !$user->activo;
        $user->save();

        return response()->json([
            'message' => 'Estado actualizado',
            'activo' => $user->activo,
        ]);
    }

    /**
     * ELIMINAR USUARIO (solo si está inactivo)
     */
    public function destroy($id_usuario)
    {
        $user = User::findOrFail($id_usuario);

        if ($user->activo) {
            return response()->json([
                'message' => 'No se puede eliminar un usuario activo'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado correctamente'
        ]);
    }
}
