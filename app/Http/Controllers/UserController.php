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

        // Buscar por username o id (si tu tabla tiene id)
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('username', 'like', "%$q%")
                    ->orWhere('id', $q); // quítalo si tu tabla no tiene "id"
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

        return response()->json(
            $query->orderBy('id', 'desc')->get()
        );
    }

    /**
     * CREAR ADMIN
     */
    public function storeAdmin(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:usuario,username',
            'password' => 'required|string|min:4',
            'activo' => 'required|boolean',
        ]);

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'id_rol' => 1,
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
            'username' => 'required|string|unique:usuario,username',
            'password' => 'required|string|min:4',
            'activo' => 'required|boolean',
        ]);

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'id_rol' => 2,
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
            'nombre' => 'required|string',
            'username' => 'required|string|unique:usuario,username',
            'password' => 'required|string|min:4',
            'activo' => 'required|boolean',
        ]);

        $result = DB::transaction(function () use ($request) {

            //  Crear sucursal (ID manual)
            $sucursal = Sucursal::create([
                'id_sucursal' => $request->id_sucursal,
                'nombre' => $request->nombre,
            ]);

            //  Crear usuario ligado a esa sucursal (misma PK)
            $user = User::create([
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
    public function cambiarEstado($id)
    {
        $user = User::findOrFail($id);
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
    public function destroy($id)
    {
        $user = User::findOrFail($id);

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
