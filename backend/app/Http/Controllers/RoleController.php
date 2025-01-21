<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/roles",
     *     summary="Obtener todos los roles",
     *     tags={"Roles"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de roles obtenida correctamente"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $roles = Role::all();
        if ($request->is('api/*')) {
            return response()->json($roles, 200);
        }

        return view('roles', compact('roles'));
    }

    /**
     * @OA\Post(
     *     path="/api/roles",
     *     summary="Crear un nuevo rol",
     *     tags={"Roles"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del nuevo rol",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Rol creado correctamente",
     *     )
     * )
     */
    public function store(Request $request)
    {
        if ($request->is('api/*')) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $role = Role::create($validator->validated());
            return response()->json($role, 201);
        }
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        Role::create($validatedData);
        return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/api/roles/{id}",
     *     summary="Obtener un rol específico",
     *     tags={"Roles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del rol",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rol obtenido correctamente",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rol no encontrado"
     *     )
     * )
     */
    public function show(Request $request, Role $role)
    {
        if ($request->is('api/*')) {
            return response()->json($role, 200);
        }
        return view('roles.show', compact('role'));
    }

    /**
     * @OA\Put(
     *     path="/api/roles/{id}",
     *     summary="Actualizar un rol existente",
     *     tags={"Roles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del rol",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos actualizados del rol",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rol actualizado correctamente",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rol no encontrado"
     *     )
     * )
     */
    public function update(Request $request, Role $role)
    {
        if ($request->is('api/*')) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $role->update($validator->validated());
            return response()->json($role, 200);
        }
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $role->update($validatedData);
        return redirect()->route('roles.index')->with('success', 'Rol actualizado exitosamente');
    }
    /**
     * @OA\Delete(
     *     path="/api/roles/{id}",
     *     summary="Eliminar un rol",
     *     tags={"Roles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del rol",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Rol eliminado correctamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rol no encontrado"
     *     )
     * )
     */
    public function destroy(Request $request, Role $role)
    {
        $role->delete();
        if ($request->is('api/*')) {
            return response()->json(null, 204);
        }
        return redirect()->route('roles.index')->with('success', 'Rol eliminado exitosamente');
    }
}
