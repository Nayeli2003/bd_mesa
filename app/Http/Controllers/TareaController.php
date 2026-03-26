<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TareaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'materiales' => 'nullable|string',
            'fecha_limite' => 'required|date',
            'prioridad' => 'required|integer',
            'tecnicos' => 'required|array|min:1',
            'tecnicos.*' => 'integer|exists:usuario,id_usuario',
        ]);

        $usuario = $request->user();

        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        $tecnicos = array_unique($request->tecnicos);

        DB::beginTransaction();

        try {
            $idTarea = DB::table('tarea')->insertGetId([
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'materiales' => $request->materiales,
                'fecha_limite' => $request->fecha_limite,
                'prioridad' => $request->prioridad,
                'creado_por' => $usuario->id_usuario,
                'estado' => 'pendiente',
                'fecha_creacion' => now(),
            ], 'id_tarea');

            foreach ($tecnicos as $idTecnico) {
                DB::table('tarea_tecnico')->insert([
                    'id_tarea' => $idTarea,
                    'id_tecnico' => $idTecnico,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Tarea creada correctamente',
                'id_tarea' => $idTarea
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al crear la tarea',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
