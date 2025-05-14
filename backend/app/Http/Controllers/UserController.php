<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Division;
use App\Models\Course;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Primero, eliminar las asignaciones existentes para este usuario
        // para evitar duplicados
        \App\Models\CourseDivisionUser::where('user_id', $userId)->delete();

        // Crear una nueva asignación en la tabla course_division_user
        $courseDivisionUser = \App\Models\CourseDivisionUser::create([
            'user_id' => $userId,
            'course_id' => $request->course_id,
            'division_id' => $request->division_id,
        ]);

        // También agregar a la tabla course_user para mantener la compatibilidad
        // con el sistema existente
        $user->courses()->syncWithoutDetaching([$request->course_id]);

        return response()->json([
            'message' => 'Curso y división asignados correctamente',
            'data' => $courseDivisionUser,
            'user' => $user->load(['courseDivisionUsers.course', 'courseDivisionUsers.division'])
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


    public function index(Request $request)
    {
        // Obtener todos los roles para el selector
        $roles = \App\Models\Role::all();

        // Iniciar query para usuarios
        $query = User::with('role');

        // Aplicar filtro por rol si se proporciona
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Obtener usuarios paginados
        $users = $query->paginate(20);

        // Si la solicitud es AJAX, devolver una respuesta JSON
        if (request()->wantsJson()) {
            return response()->json($users, 200);
        }

        // Si no es AJAX (es una solicitud tradicional), devolver una vista
        return view('users.users', compact('users', 'roles'));
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
        // Log para depuración - registrar los datos recibidos
        \Log::info('Datos recibidos en store de usuario:', $request->all());
        
        try {
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
                'course_division_pairs' => 'nullable|array',
                'course_division_pairs.*.course_id' => 'required_with:course_division_pairs|exists:courses,id',
                'course_division_pairs.*.division_id' => 'required_with:course_division_pairs|exists:divisions,id'
            ]);

            // Si la validación falla, retorna errores
            if ($validator->fails()) {
                \Log::warning('Validación fallida en creación de usuario:', $validator->errors()->toArray());
                
                if ($request->wantsJson()) {
                    return response()->json(['errors' => $validator->errors()], 400);
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
            
            \Log::info('Usuario básico creado con ID: ' . $user->id);

            // Si se ha proporcionado una imagen, agregarla a los datos del usuario
            if ($request->has('image')) {
                $user->image = $request->image;
                $user->save();
            }

            // Si el usuario es Orientador (rol 5)
            if ($request->role_id == 5) {
                \Log::info('Procesando usuario de tipo Orientador (role_id: 5)');
                
                // Procesar nivel educativo para orientadores
                $nivelEducativo = $request->input('nivel_educativo');
                
                if ($nivelEducativo) {
                    \Log::info("Procesando nivel educativo para orientador: {$nivelEducativo}");
                    
                    // Obtener cursos según el nivel seleccionado
                    $cursos = [];
                    if ($nivelEducativo == 'eso') {
                        // Obtenemos cursos de ESO por nombre
                        $cursos = Course::whereIn('name', ['1 ESO', '2 ESO', '3 ESO', '4 ESO'])
                                        ->orWhere('name', 'like', '%ESO%')
                                        ->pluck('id')->toArray();
                    } elseif ($nivelEducativo == 'bachillerato') {
                        // Obtenemos cursos de Bachillerato por nombre
                        $cursos = Course::whereIn('name', ['1 BATX', '2 BATX'])
                                        ->orWhere('name', 'like', '%BATX%')
                                        ->orWhere('name', 'like', '%BACHILLER%')
                                        ->pluck('id')->toArray();
                    }
                    
                    // Obtener las divisiones apropiadas según el nivel educativo
                    $divisiones = [];
                    if ($nivelEducativo == 'eso') {
                        // Para ESO, usar solo las divisiones con IDs 3 al 7 (A, B, C, D, E)
                        $divisiones = Division::whereBetween('id', [3, 7])->pluck('id')->toArray();
                    } elseif ($nivelEducativo == 'bachillerato') {
                        // Para Bachillerato, usar solo las divisiones 1 y 2
                        $divisiones = Division::whereIn('id', [1, 2])->pluck('id')->toArray();
                    }
                    
                    \Log::info("Divisiones seleccionadas para {$nivelEducativo}: " . implode(', ', $divisiones));
                    
                    // Crear todas las combinaciones posibles de curso y división
                    $combinacionesCreadas = 0;
                    foreach ($cursos as $cursoId) {
                        foreach ($divisiones as $divisionId) {
                            \App\Models\CourseDivisionUser::create([
                                'user_id' => $user->id,
                                'course_id' => $cursoId,
                                'division_id' => $divisionId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            
                            // Actualizar la tabla course_user
                            $user->courses()->syncWithoutDetaching([$cursoId]);
                            $combinacionesCreadas++;
                        }
                    }
                    
                    \Log::info("Asignadas {$combinacionesCreadas} combinaciones para el orientador");
                }
            }
            // Si el usuario es Profesor (rol 1) o Tutor (rol 4), manejar asignaciones
            else if (in_array($request->role_id, [1, 4])) {
                \Log::info('Procesando usuario de tipo Profesor/Tutor (role_id: ' . $request->role_id . ')');
                
                // Asociar materias si se han seleccionado (solo para profesores)
                if ($request->role_id == 1 && $request->has('subjects') && count($request->subjects) > 0) {
                    $user->subjects()->sync($request->subjects);
                    \Log::info('Asignaturas asociadas al profesor: ' . implode(', ', $request->subjects));
                }

                // Procesar los pares curso-división
                if ($request->has('course_division_pairs') && is_array($request->course_division_pairs)) {
                    \Log::info('Procesando pares curso-división: ', $request->course_division_pairs);
                    
                    foreach ($request->course_division_pairs as $pair) {
                        // Verificar que ambos valores existan
                        if (!empty($pair['course_id']) && !empty($pair['division_id'])) {
                            \App\Models\CourseDivisionUser::create([
                                'course_id' => $pair['course_id'],
                                'division_id' => $pair['division_id'],
                                'user_id' => $user->id,
                            ]);

                            // También agregar a la tabla course_user para mantener compatibilidad
                            $user->courses()->syncWithoutDetaching([$pair['course_id']]);
                            
                            \Log::info('Asociado curso:' . $pair['course_id'] . ' y división:' . $pair['division_id'] . ' al usuario');
                        } else {
                            \Log::warning('Par curso-división incompleto: ', $pair);
                        }
                    }
                } else {
                    \Log::warning('No se encontraron pares curso-división en la solicitud');
                }
                
                // Mantener compatibilidad con el formato anterior (por si acaso)
                if (
                    $request->has('courses') && is_array($request->courses) &&
                    $request->has('divisions') && is_array($request->divisions)
                ) {
                    \Log::info('Usando formato anterior de courses y divisions arrays');
                    
                    foreach ($request->courses as $courseId) {
                        foreach ($request->divisions as $divisionId) {
                            \App\Models\CourseDivisionUser::create([
                                'course_id' => $courseId,
                                'division_id' => $divisionId,
                                'user_id' => $user->id,
                            ]);

                            // También agregar a la tabla course_user para mantener compatibilidad
                            $user->courses()->syncWithoutDetaching([$courseId]);
                            
                            \Log::info('Asociado curso:' . $courseId . ' y división:' . $divisionId . ' al usuario');
                        }
                    }
                }
                
                // Si hay específicamente tutor_course_id y tutor_division_id
                if ($request->has('tutor_course_id') && $request->has('tutor_division_id')) {
                    \Log::info('Usando tutor_course_id y tutor_division_id');
                    
                    \App\Models\CourseDivisionUser::create([
                        'course_id' => $request->tutor_course_id,
                        'division_id' => $request->tutor_division_id,
                        'user_id' => $user->id,
                    ]);

                    // También agregar a la tabla course_user para mantener compatibilidad
                    $user->courses()->syncWithoutDetaching([$request->tutor_course_id]);
                    
                    \Log::info('Asociado curso:' . $request->tutor_course_id . ' y división:' . $request->tutor_division_id . ' al usuario');
                }
            }

            // Si el usuario es Alumno, asociar curso y división
            elseif ($request->role_id == 2) {
                \Log::info('Procesando usuario de tipo Alumno');
                
                // Procesar los pares curso-división (si existen)
                if ($request->has('course_division_pairs') && is_array($request->course_division_pairs)) {
                    \Log::info('Procesando pares curso-división para alumno: ', $request->course_division_pairs);
                    
                    foreach ($request->course_division_pairs as $pair) {
                        if (!empty($pair['course_id']) && !empty($pair['division_id'])) {
                            \App\Models\CourseDivisionUser::create([
                                'course_id' => $pair['course_id'],
                                'division_id' => $pair['division_id'],
                                'user_id' => $user->id,
                            ]);

                            // También agregar a la tabla course_user para mantener compatibilidad
                            $user->courses()->syncWithoutDetaching([$pair['course_id']]);
                            
                            \Log::info('Asociado curso:' . $pair['course_id'] . ' y división:' . $pair['division_id'] . ' al alumno');
                        } else {
                            \Log::warning('Par curso-división incompleto para alumno: ', $pair);
                        }
                    }
                } else {
                    \Log::warning('No se encontraron pares curso-división en la solicitud para el alumno');
                }
                
                // Si hay campos individuales course_id y division_id
                if ($request->has('course_id') && $request->has('division_id')) {
                    \Log::info('Usando course_id y division_id individuales para alumno');
                    
                    \App\Models\CourseDivisionUser::create([
                        'course_id' => $request->course_id,
                        'division_id' => $request->division_id,
                        'user_id' => $user->id,
                    ]);

                    // También agregar a la tabla course_user para mantener compatibilidad
                    $user->courses()->syncWithoutDetaching([$request->course_id]);
                    
                    \Log::info('Asociado curso:' . $request->course_id . ' y división:' . $request->division_id . ' al alumno');
                }
                
                // Mantener compatibilidad con formato anterior
                if (
                    $request->has('courses') && is_array($request->courses) &&
                    $request->has('divisions') && is_array($request->divisions)
                ) {
                    \Log::info('Usando formato anterior de courses y divisions arrays para alumno');
                    
                    foreach ($request->courses as $courseId) {
                        foreach ($request->divisions as $divisionId) {
                            \App\Models\CourseDivisionUser::create([
                                'course_id' => $courseId,
                                'division_id' => $divisionId,
                                'user_id' => $user->id,
                            ]);

                            // También agregar a la tabla course_user para mantener compatibilidad
                            $user->courses()->syncWithoutDetaching([$courseId]);
                            
                            \Log::info('Asociado curso:' . $courseId . ' y división:' . $divisionId . ' al alumno');
                        }
                    }
                }
            }

            \Log::info('Usuario creado correctamente');
            
            if ($request->wantsJson()) {
                return response()->json(
                    $user->load(['courses', 'subjects', 'courseDivisionUsers.course', 'courseDivisionUsers.division']),
                    201
                );
            }

            return redirect()->route('users.index')->with('success', 'Usuari creat correctament');
            
        } catch (\Exception $e) {
            \Log::error('Error al crear usuario: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Error al crear usuario: ' . $e->getMessage()], 500);
            }
            
            return redirect()->back()->with('error', 'Error al crear usuari: ' . $e->getMessage())->withInput();
        }
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
                $courseDivisions = $user->courseDivisionUsers->map(function ($cdu) {
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
        \Log::info('Actualización de usuario ID: ' . $id, $request->all());
        
        try {
            $user = User::find($id);

            if (is_null($user)) {
                if ($request->wantsJson()) {
                    return response()->json(['message' => 'Usuario no encontrado'], 404);
                }
                return redirect()->route('users.index')->with('error', 'Usuari no trobat');
            }

            // Modificar las reglas de validación para evitar problemas con course_division_pairs
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'role_id' => 'required|exists:roles,id',
                'image' => 'nullable|string|max:255',
                'course_id' => 'nullable|exists:courses,id',
                'division_id' => 'nullable|exists:divisions,id',
                'subjects' => 'nullable|array',
                'subjects.*' => 'exists:subjects,id',
            ]);

            if ($validator->fails()) {
                \Log::warning('Validación fallida en actualización de usuario:', $validator->errors()->toArray());
                
                if ($request->wantsJson()) {
                    return response()->json(['errors' => $validator->errors()], 400);
                } else {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
            }

            // Actualizar los datos básicos del usuario
            $user->update([
                'name' => $request->name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'role_id' => $request->role_id,
                'image' => $request->image,
            ]);
            
            \Log::info('Datos básicos de usuario actualizados correctamente');

            // Si es profesor y hay subjects seleccionados, actualizar las materias
            if ($user->role_id == 1 && $request->has('subjects')) {
                $user->subjects()->sync($request->has('subjects') ? $request->subjects : []);
                \Log::info('Materias del profesor actualizadas');
            }

            // Gestión completa de las relaciones curso-división y course_user
            // 1. Eliminar todas las asignaciones antiguas
            \Log::info('Eliminando asignaciones previas para el usuario ID: ' . $user->id);
            \App\Models\CourseDivisionUser::where('user_id', $user->id)->delete();
            
            // 2. Eliminar también las relaciones en course_user para mantener consistencia
            $user->courses()->detach();
            \Log::info('Eliminadas asignaciones previas de curso/división y course_user');
            
            // Procesar las asignaciones de curso/división
            $courseDivisionCreated = false;
            
            // Procesar los campos según el rol del usuario
            if ($user->role_id == 5) { // Orientador
                $nivelEducativo = $request->input('nivel_educativo');
                
                if ($nivelEducativo) {
                    \Log::info("Procesando nivel educativo para orientador: {$nivelEducativo}");
                    
                    // Obtener cursos según el nivel seleccionado
                    $cursos = [];
                    if ($nivelEducativo == 'eso') {
                        // Obtenemos cursos de ESO por nombre
                        $cursos = Course::whereIn('name', ['1 ESO', '2 ESO', '3 ESO', '4 ESO'])
                                        ->orWhere('name', 'like', '%ESO%')
                                        ->pluck('id')->toArray();
                    } elseif ($nivelEducativo == 'bachillerato') {
                        // Obtenemos cursos de Bachillerato por nombre
                        $cursos = Course::whereIn('name', ['1 BATX', '2 BATX'])
                                        ->orWhere('name', 'like', '%BATX%')
                                        ->orWhere('name', 'like', '%BACHILLER%')
                                        ->pluck('id')->toArray();
                    }
                    
                    // Obtener las divisiones apropiadas según el nivel educativo
                    $divisiones = [];
                    if ($nivelEducativo == 'eso') {
                        // Para ESO, usar solo las divisiones con IDs 3 al 7 (A, B, C, D, E)
                        $divisiones = Division::whereBetween('id', [3, 7])->pluck('id')->toArray();
                    } elseif ($nivelEducativo == 'bachillerato') {
                        // Para Bachillerato, usar solo las divisiones 1 y 2
                        $divisiones = Division::whereIn('id', [1, 2])->pluck('id')->toArray();
                    }
                    
                    \Log::info("Divisiones seleccionadas para {$nivelEducativo}: " . implode(', ', $divisiones));
                    
                    // Crear todas las combinaciones posibles de curso y división
                    $combinacionesCreadas = 0;
                    foreach ($cursos as $cursoId) {
                        foreach ($divisiones as $divisionId) {
                            \App\Models\CourseDivisionUser::create([
                                'user_id' => $user->id,
                                'course_id' => $cursoId,
                                'division_id' => $divisionId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            
                            // Actualizar la tabla course_user
                            $user->courses()->syncWithoutDetaching([$cursoId]);
                            $combinacionesCreadas++;
                        }
                    }
                    
                    \Log::info("Asignadas {$combinacionesCreadas} combinaciones para el orientador");
                    $courseDivisionCreated = true;
                }
            }
            else if ($user->role_id == 4) { // Tutor
                $tutorCourseId = $request->input('tutor_course_id');
                $tutorDivisionId = $request->input('tutor_division_id');
                
                if ($tutorCourseId && $tutorDivisionId) {
                    \Log::info("Usando campos específicos de tutor (rol {$user->role_id}): course_id={$tutorCourseId}, division_id={$tutorDivisionId}");
                    
                    // Crear la asignación en course_division_user
                    \App\Models\CourseDivisionUser::create([
                        'user_id' => $user->id,
                        'course_id' => $tutorCourseId,
                        'division_id' => $tutorDivisionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    // Actualizar la tabla course_user
                    $user->courses()->syncWithoutDetaching([$tutorCourseId]);
                    $courseDivisionCreated = true;
                }
            } 
            else if ($user->role_id == 2) { // Estudiante
                $studentCourseId = $request->input('student_course_id');
                $studentDivisionId = $request->input('student_division_id');
                
                if ($studentCourseId && $studentDivisionId) {
                    \Log::info("Usando campos específicos de estudiante (rol {$user->role_id}): course_id={$studentCourseId}, division_id={$studentDivisionId}");
                    
                    // Crear la asignación en course_division_user
                    \App\Models\CourseDivisionUser::create([
                        'user_id' => $user->id,
                        'course_id' => $studentCourseId,
                        'division_id' => $studentDivisionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    // Actualizar la tabla course_user
                    $user->courses()->syncWithoutDetaching([$studentCourseId]);
                    $courseDivisionCreated = true;
                }
            }
            else if ($user->role_id == 1) // Profesor
            
            // Procesar los pares curso-división (siempre preferir esta estructura)
            if ($request->has('course_division_pairs')) {
                \Log::info("Estructura de course_division_pairs:", ['datos' => $request->course_division_pairs]);
                
                // Asegurarnos de que es un array
                if (is_array($request->course_division_pairs) || is_object($request->course_division_pairs)) {
                    $processedPairs = []; // Para evitar duplicados
                    
                    foreach ($request->course_division_pairs as $index => $pair) {
                        // Obtener valores curso/división
                        $course_id = null;
                        $division_id = null;
                        
                        if (is_array($pair)) {
                            $course_id = isset($pair['course_id']) ? intval($pair['course_id']) : null;
                            $division_id = isset($pair['division_id']) ? intval($pair['division_id']) : null;
                        } elseif (is_object($pair)) {
                            $course_id = isset($pair->course_id) ? intval($pair->course_id) : null;
                            $division_id = isset($pair->division_id) ? intval($pair->division_id) : null;
                        }
                        
                        // Verificación estricta y evitar duplicados
                        if ($course_id > 0 && $division_id > 0) {
                            $pairKey = "{$course_id}-{$division_id}";
                            
                            if (!in_array($pairKey, $processedPairs)) {
                                \Log::info("Creando asignación de profesor: course_id={$course_id}, division_id={$division_id}");
                                
                                // Crear la asignación en course_division_user
                                \App\Models\CourseDivisionUser::create([
                                    'user_id' => $user->id,
                                    'course_id' => $course_id,
                                    'division_id' => $division_id,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                                
                                // Actualizar la tabla course_user
                                $user->courses()->syncWithoutDetaching([$course_id]);
                                
                                $processedPairs[] = $pairKey;
                                \Log::info("Asignación creada con éxito");
                                $courseDivisionCreated = true;
                            } else {
                                \Log::info("Par duplicado ignorado: {$pairKey}");
                            }
                        } else {
                            \Log::warning("Par inválido en índice {$index}: course_id={$course_id}, division_id={$division_id}");
                        }
                    }
                } else {
                    \Log::warning("course_division_pairs no es un array ni un objeto");
                }
            }
            
            // Verificar que se hayan creado asignaciones
            $newAssignments = \App\Models\CourseDivisionUser::where('user_id', $user->id)->get();
            \Log::info("Asignaciones creadas: " . $newAssignments->count(), [
                'asignaciones' => $newAssignments->toArray()
            ]);
            
            // Refrescar el usuario para asegurarnos de tener los datos más recientes
            $user->refresh();
            
            // Ya no es necesario este bloque, ya que tratamos los campos course_id/division_id al inicio

            \Log::info('Usuario actualizado correctamente');
            
            // Asegurarnos de que el usuario tiene la relación cargada correctamente
            $user = User::with(['courses', 'subjects', 'courseDivisionUsers.course', 'courseDivisionUsers.division'])->find($user->id);
            
            // Verificar explícitamente que hay asignaciones
            $assignments = $user->courseDivisionUsers;
            if ($assignments->isEmpty()) {
                \Log::warning("ADVERTENCIA: El usuario {$user->id} no tiene asignaciones después de guardar");
            } else {
                \Log::info("Asignaciones confirmadas para usuario {$user->id}: " . $assignments->count());
                foreach ($assignments as $idx => $assignment) {
                    \Log::info("Asignación #{$idx}: Curso {$assignment->course_id}, División {$assignment->division_id}");
                }
            }
            
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Usuari actualitzat correctament',
                    'user' => $user
                ], 200);
            }

            return redirect()->route('users.show', $user->id)->with('success', 'Usuari actualitzat correctament');
            
        } catch (\Exception $e) {
            \Log::error('Error al actualizar usuario: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Error al actualizar usuario: ' . $e->getMessage()], 500);
            }
            
            return redirect()->back()->with('error', 'Error al actualitzar usuari: ' . $e->getMessage())->withInput();
        }
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
            if (request()->wantsJson()) {
                return response()->json(['message' => 'User not found'], 404);
            }
            return redirect()->route('users.index')->with('error', 'Usuario no encontrado');
        }

        try {
            // Intento de eliminar el usuario
            $user->delete();

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Usuari eliminat correctament'], 200);
            }
            return redirect()->route('users.index')->with('success', 'Usuari eliminat correctament');
        } catch (\Illuminate\Database\QueryException $e) {
            // Verificar si el error es de restricción de clave foránea relacionada con grupos
            if ($e->getCode() == "23000" && strpos($e->getMessage(), 'group_user_user_id_foreign') !== false) {
                // Obtener los grupos asociados al usuario
                $groups = DB::table('group_user')
                    ->join('groups', 'group_user.group_id', '=', 'groups.id')
                    ->where('group_user.user_id', $id)
                    ->select('groups.id', 'groups.name')
                    ->get();

                $groupNames = $groups->pluck('name')->implode(', ');
                $errorMessage = 'No es pot eliminar usuari perquè pertany als següents grups: ' . $groupNames;

                if (request()->wantsJson()) {
                    return response()->json([
                        'error' => true,
                        'message' => $errorMessage
                    ], 409);
                }
                return redirect()->route('users.index')->with('error', $errorMessage);
            }

            // Si es otro tipo de error, devolver un mensaje genérico
            $errorMessage = 'Error al eliminar el usuario: ' . $e->getMessage();

            if (request()->wantsJson()) {
                return response()->json([
                    'error' => true,
                    'message' => $errorMessage
                ], 500);
            }
            return redirect()->route('users.index')->with('error', $errorMessage);
        }
    }

    public function getStudents(Request $request)
    {
        $query = User::where('role_id', 2)
            ->with(['courseDivisionUsers.course', 'courseDivisionUsers.division']);

        // Verificar si hay parámetros de filtrado
        $hasCourseFilter = $request->has('course_id') || $request->has('course_ids');
        $hasDivisionFilter = $request->has('division_id') || $request->has('division_ids');

        // Filtrar por cursos y divisiones si se proporcionan los parámetros
        if ($hasCourseFilter || $hasDivisionFilter) {
            $query->whereHas('courseDivisionUsers', function ($q) use ($request) {
                // Filtrar por curso
                if ($request->has('course_ids')) {
                    $courseIds = $request->input('course_ids');
                    $q->whereIn('course_id', $courseIds);
                } elseif ($request->has('course_id')) {
                    $courseId = $request->input('course_id');
                    $q->where('course_id', $courseId);
                }

                // Filtrar por división
                if ($request->has('division_ids')) {
                    $divisionIds = $request->input('division_ids');
                    $q->whereIn('division_id', $divisionIds);
                } elseif ($request->has('division_id')) {
                    $divisionId = $request->input('division_id');
                    $q->where('division_id', $divisionId);
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
        if ($user->role_id == 1 || $user->role_id == 4 || $user->role_id == 5) { // Profesor, Tutor o Orientador
            // Obtener todas las combinaciones curso-división asignadas
            $courseDivisions = $user->courseDivisionUsers->map(function ($cdu) {
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
