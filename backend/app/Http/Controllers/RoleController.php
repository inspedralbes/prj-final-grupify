<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Muestra todos los roles disponibles
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    /**
     * Muestra los detalles de un rol específico
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $role = Role::find($id);
        
        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }
        
        return response()->json($role);
    }

    /**
     * Crea un nuevo rol (solo administradores)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'description' => 'nullable|string'
        ]);

        $role = Role::create($validatedData);
        
        return response()->json($role, 201);
    }

    /**
     * Actualiza un rol existente (solo administradores)
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        
        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|unique:roles,name,' . $id,
            'description' => 'nullable|string'
        ]);

        $role->update($validatedData);
        
        return response()->json($role);
    }

    /**
     * Elimina un rol (solo administradores)
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $role = Role::find($id);
        
        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }
        
        // Verificar si hay usuarios con este rol
        if ($role->users()->count() > 0) {
            return response()->json(['message' => 'No se puede eliminar este rol porque hay usuarios asignados a él'], 400);
        }
        
        $role->delete();
        
        return response()->json(['message' => 'Rol eliminado correctamente']);
    }
}
