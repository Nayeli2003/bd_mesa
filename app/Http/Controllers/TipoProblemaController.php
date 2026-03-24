<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipoProblemaController extends Controller
{
    // LISTAR
    public function index()
    {
        return DB::table('tipo_problema as tp')
            ->leftJoin('prioridad as p', 'tp.id_prioridad', '=', 'p.id_prioridad')
            ->select(
                'tp.*',
                'p.nombre as prioridad',
                'p.color as prioridad_color'
            )
            ->orderBy('tp.id_tipo_problema')
            ->get();
    }

    // CREAR
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'id_prioridad' => 'required|exists:prioridad,id_prioridad'
        ]);

        $id = DB::table('tipo_problema')->insertGetId([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion ?? '',
            'id_prioridad' => $request->id_prioridad, 
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
                'id_prioridad' => $request->id_prioridad, 
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Actualizado']);
    }

    // ELIMINAR 

    public function toggle($id)
    {
        $problema = DB::table('tipo_problema')
            ->where('id_tipo_problema', $id)
            ->first();

        DB::table('tipo_problema')
            ->where('id_tipo_problema', $id)
            ->update([
                'activo' => !$problema->activo
            ]);

        return response()->json(['message' => 'Estado actualizado']);
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
