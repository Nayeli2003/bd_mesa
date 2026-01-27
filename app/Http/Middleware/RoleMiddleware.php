<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        // la relación de rol
        if (!$user->relationLoaded('rol')) {
            $user->load('rol');
        }

        $rolNombre = strtolower($user->rol?->nombre_rol ?? '');
        $rolesPermitidos = array_map(fn($r) => strtolower(trim($r)), $roles);

        if (!in_array($rolNombre, $rolesPermitidos, true)) {
            return response()->json([
                'message' => 'No autorizado',
                'tu_rol' => $rolNombre,
                'roles_permitidos' => $rolesPermitidos
            ], 403);
        }

        return $next($request);
    }
}
