<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Bitacora;
use App\Models\BitacoraNote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            // Obtener el usuario autenticado
            $user = $request->user();
            
            // Debug: Registrar información sobre el usuario actual
            \Log::info('GroupController@index - Usuario', [
                'user_id' => $user ? $user->id : 'no user',
                'role_id' => $user ? $user->role_id : 'no role_id',
                'role_name' => $user && $user->role ? $user->role->name : 'no role name'
            ]);
            
            // Iniciar consulta con relaciones
            $query = Group::with(['users', 'courses', 'divisions']);
            
            // Verificar si la columna creator_id existe
            $hasCreatorId = \Schema::hasColumn('groups', 'creator_id');
            
            // Si el usuario existe, cargar la relación de rol (por si no está cargada)
            if ($user) {
                if (!$user->relationLoaded('role')) {
                    $user->load('role');
                }
                
                // Obtener el nombre del rol (podría ser 'alumne' o 'alumno' dependiendo de la configuración)
                $userRole = $user->role ? $user->role->name : null;
                $roleId = $user->role_id;
                
                \Log::info('GroupController@index - Rol del usuario', [
                    'role_id' => $roleId,
                    'role_name' => $userRole
                ]);
                
                // Si el usuario es alumno (verificando tanto por nombre como por ID de rol)
                // Asumimos que role_id 2 o 3 es para alumnos basado en el código que hemos visto
                if ($userRole === 'alumne' || $userRole === 'alumno' || $roleId == 2 || $roleId == 3) {
                    \Log::info('GroupController@index - Filtrando grupos para alumno', ['user_id' => $user->id]);
                    
                    // Filtrar solo los grupos donde el usuario está como miembro
                    $query->whereHas('users', function($q) use ($user) {
                        $q->where('users.id', $user->id);
                    });
                    
                    // Debug: Ver cuántos grupos deberían mostrarse
                    $groupCount = $query->count();
                    \Log::info('GroupController@index - Número de grupos del alumno', ['count' => $groupCount]);
                }
                // Si es profesor o tutor, filtrar por creator_id
                elseif ($hasCreatorId && ($userRole === 'professor' || $userRole === 'profesor' || $userRole === 'tutor' || $roleId == 1 || $roleId == 4)) {
                    $query->where('creator_id', $user->id);
                    \Log::info('GroupController@index - Filtrando grupos para profesor/tutor', ['creator_id' => $user->id]);
                }
            }
            
            // Filtrar por curso y división si se especifican
            if ($request->has('course_id')) {
                $courseId = $request->input('course_id');
                $query->whereHas('courses', function($q) use ($courseId) {
                    $q->where('courses.id', $courseId);
                });
            }
            
            if ($request->has('division_id')) {
                $divisionId = $request->input('division_id');
                $query->whereHas('divisions', function($q) use ($divisionId) {
                    $q->where('divisions.id', $divisionId);
                });
            }
            
            $groups = $query->get();
            
            // Debug: Ver cuántos grupos se están devolviendo
            \Log::info('GroupController@index - Grupos devueltos', [
                'count' => count($groups),
                'ids' => $groups->pluck('id')->toArray()
            ]);

            // Si la solicitud es AJAX o espera JSON, devuelve JSON
            if ($request->expectsJson()) {
                return response()->json($groups);
            }

            // Si no, devuelve la vista
            return view('groups', compact('groups'));
        } catch (\Exception $e) {
            \Log::error('GroupController@index - Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Error interno del servidor: ' . $e->getMessage()], 500);
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
        try {
            // Validar datos básicos del grupo
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'number_of_students' => 'required|integer',
                'course_id' => 'nullable|integer',
                'division_id' => 'nullable|integer',
            ]);

            // Crear datos básicos del grupo
            $groupData = [
                'name' => $validated['name'],
                'description' => $validated['description'],
                'number_of_students' => $validated['number_of_students'],
            ];

            // Añadir el ID del creador (profesor logueado)
            $user = $request->user();
            if ($user) {
                $groupData['creator_id'] = $user->id;
            }

            // Crear el grupo con los datos básicos
            $group = Group::create($groupData);

            // Si se proporcionaron course_id y division_id, intentar asociarlos
            if (isset($validated['course_id']) && isset($validated['division_id'])) {
                if (method_exists($group, 'courses') && method_exists($group->courses(), 'attach')) {
                    $group->courses()->attach($validated['course_id']);
                }
                
                if (method_exists($group, 'divisions') && method_exists($group->divisions(), 'attach')) {
                    $group->divisions()->attach($validated['division_id']);
                }
            }

            // Crear bitácora asociada
            $bitacora = new Bitacora();
            $bitacora->group_id = $group->id;
            $bitacora->title = "Bitácora del grupo " . $group->name;
            $bitacora->description = "Bitácora asociada al grupo " . $group->name;
            $bitacora->save();

            // Si la solicitud espera JSON, devolver JSON
            if ($request->expectsJson()) {
                return response()->json($group, 201);
            }
            
            // Si no, redirigir con mensaje flash
            return redirect()->route('groups.index')->with('success', 'Grup creat correctament');
        } catch (\Exception $e) {
            // Si la solicitud espera JSON, devolver error en JSON
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Error al crear el grupo: ' . $e->getMessage()], 500);
            }
            
            // Si no, redirigir con mensaje de error
            return redirect()->route('groups.index')->with('error', 'Error al crear el grup: ' . $e->getMessage());
        }
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
    public function show(Request $request, $id)
    {
        $group = Group::with('users')->find($id);
        if (!$group) return response()->json(['message' => 'Grupo no encontrado'], 404);

        // Verificar si el usuario tiene permiso para ver este grupo
        $user = $request->user();
        
        if ($user) {
            // Cargar relación de rol si no está cargada
            if (!$user->relationLoaded('role')) {
                $user->load('role');
            }
            
            $userRole = $user->role ? $user->role->name : null;
            $roleId = $user->role_id;
            
            // Si es alumno (verificando tanto por nombre como por ID)
            if ($userRole === 'alumne' || $userRole === 'alumno' || $roleId == 2 || $roleId == 3) {
                // Verificar que el alumno pertenezca al grupo
                $userBelongsToGroup = $group->users()->where('users.id', $user->id)->exists();
                
                \Log::info('GroupController@show - Verificando permisos de alumno', [
                    'user_id' => $user->id,
                    'group_id' => $group->id,
                    'belongs_to_group' => $userBelongsToGroup
                ]);
                
                if (!$userBelongsToGroup) {
                    return response()->json(['message' => 'No tienes permiso para ver este grupo'], 403);
                }
            }
            // Si es profesor, verificar que sea el creador (si la columna existe)
            elseif ($userRole === 'professor' || $userRole === 'profesor' || $userRole === 'tutor' || $roleId == 1 || $roleId == 4) {
                $hasCreatorId = \Schema::hasColumn('groups', 'creator_id');
                
                \Log::info('GroupController@show - Verificando permisos de profesor', [
                    'user_id' => $user->id,
                    'group_creator_id' => $group->creator_id,
                    'has_creator_column' => $hasCreatorId
                ]);
                
                if ($hasCreatorId && $group->creator_id != $user->id) {
                    return response()->json(['message' => 'No tienes permiso para ver este grupo'], 403);
                }
            }
        }

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
            return redirect()->route('groups.index')->with('error', 'Grup no trobat');
        }

        // Verificar si el usuario es el creador del grupo (si la columna existe)
        $user = $request->user();
        $hasCreatorId = \Schema::hasColumn('groups', 'creator_id');
        
        if ($hasCreatorId && $user && $user->role_id == 1 && $group->creator_id != $user->id) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No tienes permiso para modificar este grupo'], 403);
            }
            return redirect()->route('groups.index')->with('error', 'No tens permís per modificar aquest grup');
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

        return redirect()->route('groups.index')->with('success', 'Grup editat correctament');
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

            // Verificar si el usuario es el creador del grupo (si la columna existe)
            $user = $request->user();
            $hasCreatorId = \Schema::hasColumn('groups', 'creator_id');
            
            if ($hasCreatorId && $user && $user->role_id == 1 && $group->creator_id != $user->id) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'No tienes permiso para eliminar este grupo'], 403);
                }
                return redirect()->route('groups.index')->with('error', 'No tens permís per eliminar aquest grup');
            }

            // Eliminar relaciones en la tabla pivot
            $group->users()->detach();
            
            // Eliminar relaciones con cursos y divisiones si existen
            if (method_exists($group, 'courses')) {
                $group->courses()->detach();
            }
            if (method_exists($group, 'divisions')) {
                $group->divisions()->detach();
            }

            // Eliminar bitácora asociada si existe
            if ($group->bitacora) {
                $group->bitacora()->delete();
            }

            // Eliminar el grupo
            $group->delete();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Grupo eliminado correctamente'], 200);
            }
            
            return redirect()->route('groups.index')->with('success', 'Grup eliminat correctament');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Grupo no encontrado'], 404);
            }
            return redirect()->route('groups.index')->with('error', 'Grup no trobat');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Error interno del servidor: ' . $e->getMessage()], 500);
            }
            return redirect()->route('groups.index')->with('error', 'Error intern del servidor: ' . $e->getMessage());
        }
    }

    public function getMembers(Request $request, $id)
    {
        $group = Group::find($id);

        if (!$group) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        // Verificar si el usuario tiene permiso para ver este grupo
        $user = $request->user();
        
        if ($user) {
            // Cargar relación de rol si no está cargada
            if (!$user->relationLoaded('role')) {
                $user->load('role');
            }
            
            $userRole = $user->role ? $user->role->name : null;
            $roleId = $user->role_id;
            
            // Si es alumno (verificando tanto por nombre como por ID)
            if ($userRole === 'alumne' || $userRole === 'alumno' || $roleId == 2 || $roleId == 3) {
                // Verificar que el alumno pertenezca al grupo
                $userBelongsToGroup = $group->users()->where('users.id', $user->id)->exists();
                
                \Log::info('GroupController@getMembers - Verificando permisos de alumno', [
                    'user_id' => $user->id,
                    'group_id' => $group->id,
                    'belongs_to_group' => $userBelongsToGroup
                ]);
                
                if (!$userBelongsToGroup) {
                    return response()->json(['message' => 'No tienes permiso para ver los miembros de este grupo'], 403);
                }
            }
            // Si es profesor, verificar que sea el creador (si la columna existe)
            elseif ($userRole === 'professor' || $userRole === 'profesor' || $userRole === 'tutor' || $roleId == 1 || $roleId == 4) {
                $hasCreatorId = \Schema::hasColumn('groups', 'creator_id');
                
                \Log::info('GroupController@getMembers - Verificando permisos de profesor', [
                    'user_id' => $user->id,
                    'group_creator_id' => $group->creator_id,
                    'has_creator_column' => $hasCreatorId
                ]);
                
                if ($hasCreatorId && $group->creator_id != $user->id) {
                    return response()->json(['message' => 'No tienes permiso para ver los miembros de este grupo'], 403);
                }
            }
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
                'student_ids.*' => 'integer|exists:users,id',
            ]);

            // Buscar el grupo
            $group = Group::findOrFail($groupId);

            // Verificar si el usuario es el creador del grupo (si la columna existe)
            $user = $request->user();
            $hasCreatorId = \Schema::hasColumn('groups', 'creator_id');
            
            if ($hasCreatorId && $user && $user->role_id == 1 && $group->creator_id != $user->id) {
                return response()->json(['message' => 'No tienes permiso para modificar este grupo'], 403);
            }

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
            return response()->json(['message' => 'Error interno del servidor: ' . $e->getMessage()], 500);
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
            'bitacora_id' => 'nullable|integer|exists:bitacoras,id',
        ]);

        // Buscar el grupo
        $group = Group::find($groupId);

        if (!$group) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        // Verificar si el usuario es el creador del grupo (si la columna existe)
        $user = $request->user();
        $hasCreatorId = \Schema::hasColumn('groups', 'creator_id');
        
        if ($hasCreatorId && $user && $user->role_id == 1 && $group->creator_id != $user->id) {
            return response()->json(['message' => 'No tienes permiso para modificar este grupo'], 403);
        }

        try {
            // Si se proporciona bitacora_id, eliminar las notas del usuario en esta bitácora
            if (isset($validated['bitacora_id'])) {
                BitacoraNote::where('bitacora_id', $validated['bitacora_id'])
                        ->where('user_id', $validated['student_id'])
                        ->delete();
            }

            // Eliminar la relación entre el estudiante y el grupo
            $group->users()->detach($validated['student_id']);

            // Actualizar el número de estudiantes en el grupo
            $group->number_of_students = $group->users()->count();
            $group->save();

            return response()->json([
                'message' => 'Estudiante y sus notas eliminados correctamente del grupo.',
                'removed_student_id' => $validated['student_id'],
                'number_of_students' => $group->number_of_students
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error interno del servidor: ' . $e->getMessage()], 500);
        }
    }
     
}
