<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Muestra todos los roles disponibles
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $roles = Role::all();

        // Si la petición viene de la API, devolver JSON
        if ($request->is('api/*')) {
            return response()->json($roles);
        }

        // Si es una petición web, devolver la vista
        return view('roles', compact('roles'));
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
     * Crea un nuevo rol
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'description' => 'nullable|string'
        ]);

        $role = Role::create($validatedData);

        // Si la petición viene de la API, devolver JSON
        if ($request->is('api/*')) {
            return response()->json($role, 201);
        }

        // Si es una petición web, redirigir
        return redirect()->route('roles.index')->with('success', 'Rol creat correctament');
    }

    /**
     * Actualiza un rol existente
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Rol no encontrado'], 404);
            }
            return redirect()->route('roles.index')->with('error', 'Rol no encontrado');
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|unique:roles,name,' . $id,
            'description' => 'nullable|string'
        ]);

        $role->update($validatedData);

        // Si la petición viene de la API, devolver JSON
        if ($request->is('api/*')) {
            return response()->json($role);
        }

        // Si es una petición web, redirigir
        return redirect()->route('roles.index')->with('success', 'Rol actualizat correctament');
    }

    /**
     * Elimina un rol
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Rol no encontrado'], 404);
            }
            return redirect()->route('roles.index')->with('error', 'Rol no encontrado');
        }

        // Verificar si hay usuarios con este rol
        if ($role->users()->count() > 0) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'No se puede eliminar este rol porque hay usuarios asignados a él'], 400);
            }
            return redirect()->route('roles.index')->with('error', 'No se puede eliminar este rol porque hay usuarios asignados a él');
        }

        $role->delete();

        // Si la petición viene de la API, devolver JSON
        if ($request->is('api/*')) {
            return response()->json(['message' => 'Rol eliminat correctament']);
        }

        // Si es una petición web, redirigir
        return redirect()->route('roles.index')->with('success', 'Rol eliminat correctament');
    }
}
