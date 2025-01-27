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

    public function index()
    {
        $users = User::all();

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
         return view('users.create', compact('courses', 'divisions', 'roles','subjects'));
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
             'courses' => 'nullable|array', // Asegúrate de que esto sea un array
             'divisions' => 'nullable|array', // Divisiones son opcionales
         ]);
     
         // Si la validación falla, retorna errores
         if ($validator->fails()) {
             if ($request->wantsJson()) {
                 return response()->json($validator->errors(), 400);
             } else {
                 return redirect()->back()->withErrors($validator)->withInput();
             }
         }
     
         // Crear los datos base del usuario
         $userData = [
             'name' => $request->name,
             'last_name' => $request->last_name,
             'email' => $request->email,
             'password' => bcrypt($request->password),
             'role_id' => $request->role_id,
         ];
     
         // Si se ha proporcionado una imagen, agregarla a los datos del usuario
         if ($request->has('image')) {
             $userData['image'] = $request->image;
         }
     
         // Crear el usuario en la base de datos
         $user = User::create($userData);
     
         // Si el rol es Alumno (ID = 2) o Profesor (ID = 1), asociar cursos y divisiones
         if (in_array($request->role_id, [1, 2])) {
             // Validar y asociar los cursos si el usuario es Profesor o Alumno
             if ($request->has('courses') && count($request->courses) > 0) {
                 $user->courses()->sync($request->courses);
             }
     
             // Si el usuario es Alumno, asociar divisiones a los cursos seleccionados
             if ($request->role_id == 2 && $request->has('divisions') && count($request->divisions) > 0) {
                 foreach ($request->courses as $courseId) {
                     $course = Course::find($courseId);
                     if ($course) {
                         $course->divisions()->sync($request->divisions);
                     }
                 }
             }
         }
     
         // Si la solicitud es JSON, devolver el usuario recién creado
         if ($request->wantsJson()) {
             return response()->json($user, 201);
         }
     
         // Si la solicitud es HTML, redirigir con mensaje de éxito
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
        $user = User::with(['role', 'subjects'])->find($id); // Cargar rol y asignaturas relacionadas

        if (is_null($user)) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (request()->wantsJson()) {
            return response()->json($user, 200);
        }

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
        $user = User::findOrFail($id); // Obtener el usuario por ID
        $roles = Role::all(); // Obtener todos los roles
        return view('users.edit', [
            'user' => $user,
            'roles' => $roles
        ]);
        // Pasar las variables a la vista
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
            'image' => 'sometimes|required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json($validator->errors(), 400);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }

        $user->update($request->all());

        if ($request->wantsJson()) {
            return response()->json($user, 200);
        }

        return redirect()->route('users.index', $user->id)->with('success', 'User updated successfully');
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

    public function getStudents()
    {
        $students = User::where('role_id', 2) // Obtener solo estudiantes
            ->with(['courses.divisions']) // Cargar cursos y sus divisiones
            ->get();

        $formatted = $students->map(function ($student) {
            $firstCourse = $student->courses->first();
            return [
                'id' => $student->id,
                'name' => $student->name,
                'last_name' => $student->last_name,
                'email' => $student->email,
                'course' => $firstCourse?->name ?? 'Sin Curso', // Usamos "?" para manejar nulos
                'division' => $firstCourse?->divisions->first()?->division ?? 'Sin División',
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
}