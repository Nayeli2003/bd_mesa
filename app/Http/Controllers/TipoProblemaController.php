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
            'descripcion' => 'required',
            'id_prioridad' => 'required|exists:prioridad,id_prioridad'
        ]);

        $id = DB::table('tipo_problema')->insertGetId([
    'nombre' => $request->nombre,
    'descripcion' => $request->descripcion,
    'id_prioridad' => $request->id_prioridad,
    'activo' => true,
], 'id_tipo_problema');

        return response()->json(['id' => $id], 201);
    }

    // EDITAR
    // EDITAR
public function update(Request $request, $id)
{
    $data = $request->json()->all();

    if (!isset($data['prioridad'])) {
        return response()->json([
            'error' => 'Prioridad requerida'
        ], 422);
    }

    $nombre = trim(ucfirst(strtolower($data['prioridad'])));

    $prioridad = DB::table('prioridad')
        ->where('nombre', $nombre)
        ->first();

    if (!$prioridad) {
        return response()->json([
            'error' => 'Prioridad no encontrada'
        ], 422);
    }

    DB::table('tipo_problema')
        ->where('id_tipo_problema', $id)
        ->update([
            'id_prioridad' => $prioridad->id_prioridad
        ]);

    return response()->json([
        'message' => 'Actualizado correctamente'
    ]);
}

    // ELIMINAR 

    public function toggle($id)
    {
        $problema = DB::table('tipo_problema')
            ->where('id_tipo_problema', $id)
            ->first();
            if (!$problema) {
    return response()->json(['error' => 'No encontrado'], 404);
}

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
        $existe = DB::table('tipo_problema')
    ->where('id_tipo_problema', $id)
    ->exists();

if (!$existe) {
    return response()->json(['error' => 'No encontrado'], 404);
}

DB::table('tipo_problema')
    ->where('id_tipo_problema', $id)
    ->delete();
        return response()->json(['message' => 'Eliminado']);
    }
}
