<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

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

            'id_sucursal' => 'required|exists:sucursal,id_sucursal',
            'problematica' => 'required|string',

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
                'problematica' => $request->problematica,
                'materiales' => $request->materiales,
                'fecha_limite' => $request->fecha_limite,
                'prioridad' => $request->prioridad,
                'id_sucursal' => $request->id_sucursal,
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

    public function finalizar($id, Request $request)
    {
        $request->validate([
            'solucion' => 'required|string'
        ]);

        DB::beginTransaction();

        try {
            $tarea = DB::table('tarea')
                ->join('sucursal', 'tarea.id_sucursal', '=', 'sucursal.id_sucursal')
                ->select('tarea.*', 'sucursal.nombre as sucursal')
                ->where('id_tarea', $id)
                ->first();

            if (!$tarea) {
                return response()->json(['message' => 'Tarea no encontrada'], 404);
            }

            // 🚨 evitar duplicados
            if ($tarea->estado === 'finalizado') {
                return response()->json([
                    'message' => 'La tarea ya está finalizada'
                ], 400);
            }

            // actualizar tarea
            // actualizar tarea
            DB::table('tarea')
                ->where('id_tarea', $id)
                ->update([
                    'estado' => 'finalizado',
                    'solucion' => $request->solucion,
                    'fecha_finalizacion' => now(),
                ]);

            // AGREGA ESTO (RECARGAR TAREA)
            $tarea = DB::table('tarea')
                ->join('sucursal', 'tarea.id_sucursal', '=', 'sucursal.id_sucursal')
                ->select('tarea.*', 'sucursal.nombre as sucursal')
                ->where('id_tarea', $id)
                ->first();

            // generar PDF
            $pdf = Pdf::loadView('pdf.memoria_tarea', [
                'tarea' => $tarea,
                'solucion' => $request->solucion
            ]);
            $fileName = "memoria_tarea_{$id}.pdf";
            $path = "memorias/$fileName";

            $pdf->save(storage_path("app/public/$path"));

            // guardar en DB
            DB::table('tarea_memorias')->insert([
                'id_tarea' => $id,
                'pdf_url' => $path
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Tarea finalizada correctamente',
                'pdf_url' => asset("storage/$path")
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al finalizar la tarea',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function sucursales()
    {
        return DB::table('sucursal')
            ->select('id_sucursal', 'nombre')
            ->orderBy('nombre')
            ->get();
    }

    public function tecnicos()
    {
        return DB::table('usuario')
            ->join('rol', 'usuario.id_rol', '=', 'rol.id_rol')
            ->where('rol.nombre_rol', 'tecnico')
            ->select('usuario.id_usuario', 'usuario.nombre')
            ->get();
    }

    public function misTareas(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        return DB::table('tarea as t')
            ->join('tarea_tecnico as tt', 't.id_tarea', '=', 'tt.id_tarea')
            ->join('sucursal as s', 't.id_sucursal', '=', 's.id_sucursal')
            ->leftJoin('tarea_memorias as tm', 't.id_tarea', '=', 'tm.id_tarea')
            ->where('tt.id_tecnico', $user->id_usuario)
            ->select(
                't.id_tarea',
                't.titulo',
                't.descripcion',
                't.problematica',
                't.materiales',
                't.fecha_limite',
                't.estado',
                't.prioridad',
                's.nombre as sucursal',
                'tm.pdf_url'
            )
            ->orderByDesc('t.fecha_creacion')
            ->get();
    }
    public function todas()
    {
        return DB::table('tarea as t')
            ->join('sucursal as s', 't.id_sucursal', '=', 's.id_sucursal')
            ->leftJoin('tarea_tecnico as tt', 't.id_tarea', '=', 'tt.id_tarea')
            ->leftJoin('usuario as u', 'tt.id_tecnico', '=', 'u.id_usuario')
            ->select(
                't.id_tarea',
                't.titulo',
                't.descripcion',
                't.problematica',
                't.materiales',
                't.fecha_limite',
                't.estado',
                't.prioridad',
                's.nombre as sucursal',
                DB::raw("STRING_AGG(u.nombre, ', ') as tecnico")
            )

            ->groupBy(
                't.id_tarea',
                't.titulo',
                't.descripcion',
                't.problematica',
                't.materiales',
                't.fecha_limite',
                't.estado',
                't.prioridad',
                's.nombre'
            )
            ->orderByDesc('t.fecha_creacion')
            ->get();
    }

    public function descargarMemoria($id)
    {
        $memoria = DB::table('tarea_memorias')
            ->where('id_tarea', $id)
            ->first();

        if (!$memoria) {
            return response()->json(['message' => 'Memoria no encontrada'], 404);
        }

        $path = storage_path("app/public/" . $memoria->pdf_url);

        if (!file_exists($path)) {
            return response()->json(['message' => 'Archivo no existe'], 404);
        }

        return response()->download(
            $path,
            "memoria_tarea_{$id}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }

    public function reabrir($id)
    {
        $tarea = DB::table('tarea')->where('id_tarea', $id)->first();

        if (!$tarea) {
            return response()->json(['message' => 'Tarea no encontrada'], 404);
        }

        if ($tarea->estado !== 'finalizado') {
            return response()->json(['message' => 'La tarea no está finalizada'], 400);
        }

        DB::table('tarea')
            ->where('id_tarea', $id)
            ->update([
                'estado' => 'pendiente',
                'solucion' => null,
                'fecha_finalizacion' => null
            ]);

        return response()->json([
            'message' => 'Tarea reabierta correctamente'
        ]);
    }
    public function actualizar($id, Request $request)
    {
        $request->validate([
            'titulo' => 'required|string',
            'descripcion' => 'required|string',
            'problematica' => 'required|string',
            'materiales' => 'nullable|string',
            'fecha_limite' => 'required|date',
            'prioridad' => 'required|integer',
            'tecnicos' => 'required|array|min:1',
            'tecnicos.*' => 'integer|exists:usuario,id_usuario',
        ]);

        DB::beginTransaction();

        try {

            // actualizar datos básicos
            DB::table('tarea')
                ->where('id_tarea', $id)
                ->update([
                    'titulo' => $request->titulo,
                    'descripcion' => $request->descripcion,
                    'problematica' => $request->problematica,
                    'materiales' => $request->materiales,
                    'fecha_limite' => $request->fecha_limite,
                    'prioridad' => $request->prioridad,
                ]);

            // 🔥 BORRAR técnicos actuales
            DB::table('tarea_tecnico')
                ->where('id_tarea', $id)
                ->delete();

            // 🔥 INSERTAR nuevos
            foreach ($request->tecnicos as $idTecnico) {
                DB::table('tarea_tecnico')->insert([
                    'id_tarea' => $id,
                    'id_tecnico' => $idTecnico,
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Tarea actualizada']);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
