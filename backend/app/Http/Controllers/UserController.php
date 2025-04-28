<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Division;
use App\Models\Course;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Asignar un rol a un usuario
     *
     * @param Request $request
     * @param int $id ID del usuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignRole(Request $request, $id)
    {
        // Validar la solicitud
        $validatedData = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        // Obtener el usuario
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Asignar el nuevo rol
        $user->role_id = $validatedData['role_id'];
        $user->save();

        return response()->json([
            'message' => 'Rol asignado correctamente',
            'user' => $user->load('role')
        ], 200);
    }
    
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Obtenir tots els usuaris",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=200,
     *         description="Llista d'usuaris obtinguda correctament"
     *     )
     * )
     */

    //Este método se encargará de asociar el usuario con el curso y la división
    public function assignCourseAndDivision(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'division_id' => 'required|exists:divisions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Asociar el curso con el usuario
        $user->courses()->syncWithoutDetaching([$request->course_id]);

        // Asociar la división al curso (ya que la división está asociada a un curso)
        $course = Course::find($request->course_id);
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        // Asociar la división al curso
        $course->divisions()->syncWithoutDetaching([$request->division_id]);

        return response()->json([
            'message' => 'Course and division assigned successfully',
            'user' => $user->load(['courses.divisions']),  // Cargar los cursos y divisiones
        ], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        // Validar el estado
        $validated = $request->validate([
            'status' => 'required|in:0,1', // solo acepta 0 o 1
        ]);

        // Buscar el usuario
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Actualizar el estado
        $user->status = $validated['status'];
        $user->save();

        return response()->json($user, 200);
    }




    public function index()
    {
        //$users = User::all();
        $users = User::paginate(20);

        // Si la solicitud es AJAX, devolver una respuesta JSON
        if (request()->wantsJson()) {
            return response()->json($users, 200);
        }

        // Si no es AJAX (es una solicitud tradicional), devolver una vista
        return view('users.users', compact('users'));
    }


    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Crear un nou usuari",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dades del nou usuari",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="Joan"),
     *             @OA\Property(property="email", type="string", example="joan@example.com"),
     *             @OA\Property(property="password", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuari creat correctament"
     *     )
     * )
     */

    public function create()
    {
        // Obtener todos los cursos y divisiones disponibles
        $courses = Course::all();
        $divisions = Division::all();
        $roles = Role::all();
        $subjects = Subject::all();


        // Obtener los roles disponibles
        $roles = Role::all();

        // Pasar los datos a la vista
        return view('users.create', compact('courses', 'divisions', 'roles', 'subjects'));
    }


    public function store(Request $request)
    {
        // Validación base para todos los usuarios
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'image' => 'nullable|string|max:255', // Imagen opcional
            'courses' => 'nullable|array',
            'divisions' => 'nullable|array',
            'subjects' => 'nullable|array',
        ]);

        // Si la validación falla, retorna errores
        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json($validator->errors(), 400);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }

        // Crear el usuario
        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $request->role_id,
            'image' => $request->image ?? null,
        ]);

        // Si se ha proporcionado una imagen, agregarla a los datos del usuario
        if ($request->has('image')) {
            $userData['image'] = $request->image;
        }

        // Si el usuario es Profesor (ID = 1), asociar materias y opcionalmente cursos/divisiones
        if ($request->role_id == 1) {
            // Asociar materias si se han seleccionado
            if ($request->has('subjects') && count($request->subjects) > 0) {
                $user->subjects()->sync($request->subjects);
            }
            
            // Procesar los pares curso-división
            if ($request->has('course_division_pairs')) {
                foreach ($request->course_division_pairs as $pair) {
                    // Verificar que ambos valores existan
                    if (!empty($pair['course_id']) && !empty($pair['division_id'])) {
                        \App\Models\CourseDivisionUser::create([
                            'course_id' => $pair['course_id'],
                            'division_id' => $pair['division_id'],
                            'user_id' => $user->id,
                        ]);
                    }
                }
            }
            
            // Mantener compatibilidad con el formato anterior (por si acaso)
            elseif ($request->has('courses') && count($request->courses) > 0 && 
                $request->has('divisions') && count($request->divisions) > 0) {
                foreach ($request->courses as $courseId) {
                    foreach ($request->divisions as $divisionId) {
                        \App\Models\CourseDivisionUser::create([
                            'course_id' => $courseId,
                            'division_id' => $divisionId,
                            'user_id' => $user->id,
                        ]);
                    }
                }
            }
        }
        
        // Si el usuario es Tutor (ID = 4), asociar un único curso y división
        elseif ($request->role_id == 4) {
            // Procesar el par curso-división para el tutor
            if ($request->has('course_division_pairs')) {
                // Para tutores, solo tomamos el primer par curso-división
                $pair = $request->course_division_pairs[0] ?? null;
                if ($pair && !empty($pair['course_id']) && !empty($pair['division_id'])) {
                    \App\Models\CourseDivisionUser::create([
                        'course_id' => $pair['course_id'],
                        'division_id' => $pair['division_id'],
                        'user_id' => $user->id,
                    ]);
                }
            }
            // Mantener compatibilidad con el formato anterior
            elseif ($request->has('tutor_course_id') && $request->has('tutor_division_id')) {
                \App\Models\CourseDivisionUser::create([
                    'course_id' => $request->tutor_course_id,
                    'division_id' => $request->tutor_division_id,
                    'user_id' => $user->id,
                ]);
            }
        }

        // Si el usuario es Alumno (ID = 2), asociar curso y división en CourseDivisionUser
        elseif ($request->role_id == 2 && $request->has('courses') && count($request->courses) > 0) {
            foreach ($request->courses as $courseId) {
                if ($request->has('divisions') && count($request->divisions) > 0) {
                    foreach ($request->divisions as $divisionId) {
                        \App\Models\CourseDivisionUser::create([
                            'course_id' => $courseId,
                            'division_id' => $divisionId,
                            'user_id' => $user->id,
                        ]);
                    }
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json($user->load(['courses', 'subjects']), 201);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }





    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Obtenir un usuari específic",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'usuari",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuari obtingut correctament"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuari no trobat"
     *     )
     * )
     */
    public function show($id)
    {
        $user = User::with([
            'courseDivisionUsers.course',
            'courseDivisionUsers.division',
            'role',
            'subjects',
            'courses.divisions',
        ])->find($id);

        if (is_null($user)) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (request()->wantsJson()) {
            // Para API: estructurar datos según el rol
            if ($user->role_id == 1) { // Profesor
                $courseDivisions = $user->courseDivisionUsers->map(function($cdu) {
                    return [
                        'course_id' => $cdu->course_id,
                        'course_name' => $cdu->course->name ?? null,
                        'division_id' => $cdu->division_id,
                        'division_name' => $cdu->division->division ?? null,
                    ];
                });
                
                return response()->json([
                    'id' => $user->id,
                    'name' => $user->name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'image' => $user->image,
                    'role' => $user->role->name,
                    'course_divisions' => $courseDivisions,
                    'subjects' => $user->subjects,
                ], 200);
            } else { // Estudiante u otro rol
                $courseDivision = $user->courseDivisionUsers->first();
                
                return response()->json([
                    'id' => $user->id,
                    'name' => $user->name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'image' => $user->image,
                    'course' => $courseDivision?->course?->name ?? 'Sin Curso',
                    'division' => $courseDivision?->division?->division ?? 'Sin División',
                    'course_id' => $courseDivision?->course_id,
                    'division_id' => $courseDivision?->division_id,
                ], 200);
            }
        }

        // Para vista: todos los datos están en $user con relaciones cargadas
        return view('users.show', compact('user'));
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Actualitzar un usuari existent",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'usuari",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dades actualitzades de l'usuari",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Joana"),
     *             @OA\Property(property="email", type="string", example="joana@example.com"),
     *             @OA\Property(property="password", type="string", example="654321")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuari actualitzat correctament"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuari no trobat"
     *     )
     * )
     */

    public function edit($id)
    {
        $user = User::with(['subjects', 'courses', 'courseDivisionUsers'])->findOrFail($id);
        $roles = Role::all();
        $courses = Course::all();
        $divisions = Division::all();
        $subjects = Subject::all();

        return view('users.edit', compact('user', 'roles', 'courses', 'divisions', 'subjects'));
    }



    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'role_id' => 'sometimes|required|exists:roles,id',
            'image' => 'sometimes|required|string|max:255',
            'course_id' => 'nullable|exists:courses,id',
            'division_id' => 'nullable|exists:divisions,id',
            'courses' => 'nullable|array',
            'courses.*' => 'exists:courses,id',
            'divisions' => 'nullable|array',
            'divisions.*' => 'exists:divisions,id',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
            'course_division_pairs' => 'nullable|array',
            'course_division_pairs.*.course_id' => 'required_with:course_division_pairs|exists:courses,id',
            'course_division_pairs.*.division_id' => 'required_with:course_division_pairs|exists:divisions,id'
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json($validator->errors(), 400);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }

        $user->update($request->only(['name', 'last_name', 'email', 'role_id', 'image']));

        // Si es profesor y hay subjects seleccionados, actualizar las materias
        if ($user->role_id == 1 && $request->has('subjects')) {
            $user->subjects()->sync($request->subjects);
        }

        // Eliminar asignaciones previas de curso/división
        if ($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 4) {
            \App\Models\CourseDivisionUser::where('user_id', $user->id)->delete();
        }
        
        // Si es profesor, procesar los pares curso-división
        if ($user->role_id == 1) {
            // Nuevo formato: pares curso-división
            if ($request->has('course_division_pairs')) {
                foreach ($request->course_division_pairs as $pair) {
                    // Verificar que ambos valores existan
                    if (!empty($pair['course_id']) && !empty($pair['division_id'])) {
                        \App\Models\CourseDivisionUser::create([
                            'user_id' => $user->id,
                            'course_id' => $pair['course_id'],
                            'division_id' => $pair['division_id'],
                        ]);
                    }
                }
            }
            // Mantener compatibilidad con el formato anterior
            elseif ($request->has('courses') && is_array($request->courses) && 
                   $request->has('divisions') && is_array($request->divisions)) {
                
                foreach ($request->courses as $courseId) {
                    foreach ($request->divisions as $divisionId) {
                        \App\Models\CourseDivisionUser::create([
                            'user_id' => $user->id,
                            'course_id' => $courseId,
                            'division_id' => $divisionId,
                        ]);
                    }
                }
            }
        }
        // Si es tutor, procesar un único par curso-división
        elseif ($user->role_id == 4) {
            // Nuevo formato: pares curso-división (solo tomamos el primero para tutores)
            if ($request->has('course_division_pairs')) {
                $pair = $request->course_division_pairs[0] ?? null;
                if ($pair && !empty($pair['course_id']) && !empty($pair['division_id'])) {
                    \App\Models\CourseDivisionUser::create([
                        'user_id' => $user->id,
                        'course_id' => $pair['course_id'],
                        'division_id' => $pair['division_id'],
                    ]);
                }
            }
            // Compatibilidad con la posible nueva implementación específica para tutores
            elseif ($request->has('tutor_course_id') && $request->has('tutor_division_id')) {
                \App\Models\CourseDivisionUser::create([
                    'user_id' => $user->id,
                    'course_id' => $request->tutor_course_id,
                    'division_id' => $request->tutor_division_id,
                ]);
            }
        }
        // Si es estudiante, usar el formato individual
        elseif ($user->role_id == 2 && $request->has('course_id') && $request->has('division_id')) {
            \App\Models\CourseDivisionUser::create([
                'user_id' => $user->id,
                'course_id' => $request->course_id,
                'division_id' => $request->division_id,
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }
    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Eliminar un usuari",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'usuari",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Usuari eliminat correctament"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuari no trobat"
     *     )
     * )
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(null, 204);
    }

    public function getStudents(Request $request)
{
    $query = User::where('role_id', 2)
        ->with(['courseDivisionUsers.course', 'courseDivisionUsers.division']);
        
    // Filtrar por cursos y divisiones si se proporcionan los parámetros
    if ($request->has('course_ids') || $request->has('division_ids')) {
        $query->whereHas('courseDivisionUsers', function ($q) use ($request) {
            // Si hay course_ids, filtrar por ellos
            if ($request->has('course_ids') && is_array($request->course_ids)) {
                $q->whereIn('course_id', $request->course_ids);
            } elseif ($request->has('course_id')) {
                // Compatibilidad con el formato anterior
                $q->where('course_id', $request->course_id);
            }
            
            // Si hay division_ids, filtrar por ellos
            if ($request->has('division_ids') && is_array($request->division_ids)) {
                $q->whereIn('division_id', $request->division_ids);
            } elseif ($request->has('division_id')) {
                // Compatibilidad con el formato anterior
                $q->where('division_id', $request->division_id);
            }
        });
    }
    
    $students = $query->get();

    $formatted = $students->map(function ($student) {
        $courseDivision = $student->courseDivisionUsers->first();

        return [
            'id' => $student->id,
            'name' => $student->name,
            'last_name' => $student->last_name,
            'email' => $student->email,
            'image' => $student->image,
            'course' => optional($courseDivision?->course)->name ?? 'Sin Curso',
            'division' => optional($courseDivision?->division)->division ?? 'Sin División',
            'course_id' => optional($courseDivision)->course_id,
            'division_id' => optional($courseDivision)->division_id,
        ];
    });

    return response()->json($formatted);
}

    public function getTeachers()
    {
        $teachers = User::where('role_id', 1) // Obtener solo profesores
            ->with(['courses.divisions']) // Cargar cursos y sus divisiones
            ->get();

        $formatted = $teachers->map(function ($teacher) {
            $firstCourse = $teacher->courses->first();
            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'last_name' => $teacher->last_name,
                'email' => $teacher->email,
                'course' => $firstCourse?->name ?? 'Sin Curso', // Usamos "?" para manejar nulos
                'division' => $firstCourse?->divisions->first()?->division ?? 'Sin División',
            ];
        });
        return response()->json($formatted);
    }

    public function getAuthenticatedUser(Request $request)
    {
        $user = $request->user()->load([
            'courseDivisionUsers.course',
            'courseDivisionUsers.division',
            'role',
            'subjects'
        ]);
        
        // Estructurar respuesta según rol
        if ($user->role_id == 1 || $user->role_id == 4) { // Profesor o Tutor
            // Obtener todas las combinaciones curso-división asignadas
            $courseDivisions = $user->courseDivisionUsers->map(function($cdu) {
                return [
                    'course_id' => $cdu->course_id,
                    'course_name' => $cdu->course->name ?? null,
                    'division_id' => $cdu->division_id,
                    'division_name' => $cdu->division->division ?? null,
                ];
            });
            
            // Para compatibilidad con código actual, mantener course_id y division_id
            $firstAssignment = $user->courseDivisionUsers->first();
            
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'image' => $user->image,
                    'role_id' => $user->role_id,
                    'role_name' => $user->role->name,
                    // Mantener para compatibilidad con el código actual
                    'course_id' => $firstAssignment?->course_id,
                    'division_id' => $firstAssignment?->division_id,
                    'course_name' => $firstAssignment?->course?->name,
                    'division_name' => $firstAssignment?->division?->division,
                    // Nuevo campo con todas las asignaciones
                    'course_divisions' => $courseDivisions,
                    'subjects' => $user->subjects
                ]
            ]);
        } else { // Estudiante u otro rol
            $courseDivision = $user->courseDivisionUsers->first();
            
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'image' => $user->image,
                    'role_id' => $user->role_id,
                    'role_name' => $user->role->name,
                    'course_id' => $courseDivision?->course_id,
                    'division_id' => $courseDivision?->division_id,
                    'course_name' => $courseDivision?->course?->name,
                    'division_name' => $courseDivision?->division?->division,
                ]
            ]);
        }
    }
}
