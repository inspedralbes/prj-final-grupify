<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $userRole = Auth::user()->role->name;
        
        // Si no se especifican roles, cualquier usuario autenticado puede acceder
        if (empty($roles)) {
            return $next($request);
        }
        
        // Comprobar si el rol del usuario está en la lista de roles permitidos
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        return response()->json(['message' => 'Acceso denegado. No tienes permisos suficientes.'], 403);
    }
}
