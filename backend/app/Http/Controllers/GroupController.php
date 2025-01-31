<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Groups",
 *     description="Endpoints per gestionar grups d'estudiants"
 * )
 */
class GroupController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/groups",
     *     summary="Llistar tots els grups",
     *     tags={"Groups"},
     *     @OA\Response(
     *         response=200,
     *         description="Llista de grups obtinguda correctament",
     *     )
     * )
     */


    public function index(Request $request)
    {
        // Cargar todos los grupos con sus usuarios (integrantes)
        $groups = Group::with('users')->get();

        // Verificar si la solicitud es API
        if ($request->expectsJson()) {
            return response()->json($groups, 200);
        }

        // Para la vista
        return view('groups', compact('groups'));
    }

    /**
     * @OA\Post(
     *     path="/api/groups",
     *     summary="Crear un nou grup",
     *     tags={"Groups"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "number_of_students"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Grup A"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Grup de matemàtiques"),
     *             @OA\Property(property="number_of_students", type="integer", example=25)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Grup creat correctament",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validació"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'number_of_students' => 'required|integer',
        ]);

        $group = Group::create($validated);

        if ($request->expectsJson()) {
            return response()->json($group, 201);
        }

        return redirect()->route('groups.index')->with('success', 'Grupo creado correctamente');
    }

    /**
     * @OA\Get(
     *     path="/api/groups/{id}",
     *     summary="Obtenir un grup específic per ID",
     *     tags={"Groups"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del grup",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dades del grup",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Grup no trobat"
     *     )
     * )
     */
    public function show($id)
    {
        $group = Group::find($id);
        if (!$group) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        return response()->json($group);
    }

    /**
     * @OA\Put(
     *     path="/api/groups/{id}",
     *     summary="Actualitzar un grup existent",
     *     tags={"Groups"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del grup a actualitzar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "number_of_students"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Grup actualitzat"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Descripció actualitzada"),
     *             @OA\Property(property="number_of_students", type="integer", example=30)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grup actualitzat correctament",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Grup no trobat"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $group = Group::find($id);
        if (!$group) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Grupo no encontrado'], 404);
            }
            return redirect()->route('groups.index')->with('error', 'Grupo no encontrado');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'number_of_students' => 'required|integer',
        ]);

        $group->update($validated);

        if ($request->expectsJson()) {
            return response()->json($group);
        }

        return redirect()->route('groups.index')->with('success', 'Grupo actualizado correctamente');
    }

    /**
     * @OA\Delete(
     *     path="/api/groups/{id}",
     *     summary="Eliminar un grup",
     *     tags={"Groups"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del grup a eliminar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grup eliminat correctament"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Grup no trobat"
     *     )
     * )
     */
    public function destroy(Request $request, $id)
    {
        $group = Group::find($id);
        if (!$group) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Grupo no encontrado'], 404);
            }
            return redirect()->route('groups.index')->with('error', 'Grupo no encontrado');
        }

        $group->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Grupo eliminado']);
        }

        return redirect()->route('groups.index')->with('success', 'Grupo eliminado correctamente');
    }

    public function getMembers($id)
    {
        $group = Group::find($id);

        if (!$group) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        $members = $group->users;

        return response()->json($members);
    }

    // Añadir estudiantes a un grupo desde vista profesor 
    public function addStudentsToGroup(Request $request, $groupId)
    {
        // Validar los IDs de los estudiantes
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id', // Los IDs deben existir en la tabla de usuarios
        ]);

        $group = Group::findOrFail($groupId);

        $currentTimestamp = now(); // Obtener el timestamp actual

        // Asociar los estudiantes seleccionados al grupo con los timestamps
        foreach ($request->student_ids as $studentId) {
            // Insertar cada relación entre el grupo y los estudiantes
            $group->users()->attach($studentId, [
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp
            ]);
        }

        return response()->json(['message' => 'Estudiantes asignados correctamente al grupo.'], 200);
    }
}
