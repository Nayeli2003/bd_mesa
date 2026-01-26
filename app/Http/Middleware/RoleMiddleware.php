<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        $rolNombre = strtolower($user->rol?->nombre_rol ?? '');

        if (!in_array($rolNombre, array_map('strtolower', $roles))) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return $next($request);
    }
}
