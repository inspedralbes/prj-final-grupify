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
        try {
            $groups = Group::with('users')->get();

            // Si la solicitud es AJAX o espera JSON, devuelve JSON
            if ($request->expectsJson()) {
                return response()->json($groups);
            }

            // Si no, devuelve la vista
            return view('groups', compact('groups'));
        } catch (\Exception $e) {
            \Log::error("Error fetching groups: " . $e->getMessage());
            return response()->json(['message' => 'Error interno del servidor'], 500);
        }
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
        // Validación básica sin roles
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'number_of_students' => 'required|integer',
        ]);

        return Group::create($validated);
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
        $group = Group::with('users')->find($id);
        if (!$group) return response()->json(['message' => 'Grupo no encontrado'], 404);

        // Renombrar users a members para consistencia
        $group->members = $group->users;
        unset($group->users);

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
        try {
            $group = Group::findOrFail($id);

            // Eliminar relaciones en la tabla pivot
            $group->users()->detach();

            // Eliminar el grupo
            $group->delete();

            return response()->json(['message' => 'Grupo eliminado correctamente'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        } catch (\Exception $e) {
            \Log::error("Error eliminando grupo: " . $e->getMessage());
            return response()->json(['message' => 'Error interno del servidor'], 500);
        }
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

    /**
     * @OA\Post(
     *     path="/api/groups/{id}/addStudentsToGroup",
     *     summary="Añadir estudiantes a un grupo",
     *     tags={"Groups"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del grupo",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_ids"},
     *             @OA\Property(
     *                 property="student_ids",
     *                 type="array",
     *                 @OA\Items(type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estudiantes añadidos correctamente al grupo"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Grupo no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function addStudentsToGroup(Request $request, $groupId)
    {
        try {
            // Validar los IDs de los estudiantes
            $validated = $request->validate([
                'student_ids' => 'required|array|min:1',
                'student_ids.*' => 'integer|exists:users,id', // Asegurar que los IDs sean enteros y existan
            ]);

            // Buscar el grupo
            $group = Group::findOrFail($groupId);

            // Obtener IDs de estudiantes ya asignados
            $existingIds = $group->users()->pluck('users.id')->toArray();

            // Filtrar IDs nuevos
            $newIds = array_diff($validated['student_ids'], $existingIds);

            // Asociar nuevos estudiantes
            if (!empty($newIds)) {
                $group->users()->attach($newIds);
            }

            // Actualizar número de estudiantes
            $group->number_of_students = $group->users()->count();
            $group->save();

            return response()->json([
                'message' => 'Estudiantes agregados correctamente',
                'added_students' => $newIds
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        } catch (\Exception $e) {
            \Log::error("Error en addStudentsToGroup: " . $e->getMessage());
            return response()->json(['message' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/groups/{groupId}/removeStudentFromGroup",
     *     summary="Eliminar un estudiante de un grupo",
     *     tags={"Groups"},
     *     @OA\Parameter(
     *         name="groupId",
     *         in="path",
     *         required=true,
     *         description="ID del grupo",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_id"},
     *             @OA\Property(property="student_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estudiante eliminado correctamente del grupo"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Grupo no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */

    public function removeStudentFromGroup(Request $request, $groupId)
    {
        // Validar el ID del estudiante
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:users,id',
        ]);

        // Buscar el grupo
        $group = Group::find($groupId);

        if (!$group) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        // Eliminar la relación entre el estudiante y el grupo
        $group->users()->detach($validated['student_id']);

        // Actualizar el número de estudiantes en el grupo
        $group->number_of_students = $group->users()->count();
        $group->save();

        return response()->json([
            'message' => 'Estudiante eliminado correctamente del grupo.',
            'removed_student_id' => $validated['student_id'],
            'number_of_students' => $group->number_of_students
        ], 200);
    }
}
