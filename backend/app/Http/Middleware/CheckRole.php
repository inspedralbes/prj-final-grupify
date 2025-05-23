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

        $user = Auth::user();
        
        // Verificar si el usuario tiene una relación de rol válida
        if (!$user->role || !isset($user->role->name)) {
            return response()->json(['message' => 'El usuario no tiene un rol asignado correctamente'], 403);
        }
        
        $userRole = $user->role->name;
        
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
