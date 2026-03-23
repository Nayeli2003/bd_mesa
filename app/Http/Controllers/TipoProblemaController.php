<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipoProblemaController extends Controller
{
    // LISTAR
    public function index()
    {
        return DB::table('tipo_problema')
            ->orderBy('id_tipo_problema')
            ->get();
    }

    // CREAR
    public function store(Request $request)
    {
        $id = DB::table('tipo_problema')->insertGetId([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion ?? '',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['id' => $id], 201);
    }

    // EDITAR
    public function update(Request $request, $id)
    {
        DB::table('tipo_problema')
            ->where('id_tipo_problema', $id)
            ->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion ?? '',
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Actualizado']);
    }

    // ELIMINAR
    public function destroy($id)
    {
        DB::table('tipo_problema')
            ->where('id_tipo_problema', $id)
            ->delete();

        return response()->json(['message' => 'Eliminado']);
    }
}