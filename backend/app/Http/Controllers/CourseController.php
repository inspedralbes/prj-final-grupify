<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\Division;

class CourseController extends Controller
{
    public function getDivisionsByCourse(Request $request)
    {
        // Validar que el 'course_id' se proporcione y exista
        $validated = $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
        ]);

        $courseId = $validated['course_id'];

        // Filtrar divisiones relacionadas al curso específico usando la tabla intermedia
        $divisions = Division::whereIn('id', function ($query) use ($courseId) {
            $query->select('division_id')
                ->from('course_division')
                ->where('course_id', $courseId);
        })->get(['id', 'division']); // Seleccionar solo columnas necesarias

        // Validar si existen divisiones relacionadas
        if ($divisions->isEmpty()) {
            return response()->json(['message' => 'No divisions found for the specified course'], 404);
        }

        // Respuesta con las divisiones relacionadas
        return response()->json([
            'course_id' => $courseId,
            'divisions' => $divisions,
        ], 200);
    }




    /**
     * @OA\Get(
     *     path="/api/courses",
     *     summary="Obtenir la llista de cursos",
     *     tags={"Courses"},
     *     @OA\Response(
     *         response=200,
     *         description="Llista de cursos obtenida correctament",
     *     )
     * )
     */
    public function index(Request $request)
    {
        $courses = Course::all();
        if ($request->is('api/*')) {
            return response()->json($courses, 200);
        }
        return view('courses', compact('courses'));
    }

    /**
     * @OA\Post(
     *     path="/api/courses",
     *     summary="Crear un curs",
     *     tags={"Courses"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Introducció a PHP"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Curs creat correctament",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validació"
     *     )
     * )
     */
    public function store(Request $request)
    {
        if ($request->is('api/*')) {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $course = Course::create($validatedData);

            return response()->json($course, 201);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Course::create($validatedData);
        return redirect()->route('courses.index')->with('success', 'Curs creat correctament');
    }


    /**
     * @OA\Get(
     *     path="/api/courses/{id}",
     *     summary="Un curs en concret",
     *     tags={"Courses"},
     *     @OA\Response(
     *         response=200,
     *         description="Detalls del curs",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Curs no trobat"
     *     )
     * )
     */
    public function show(Request $request, Course $course)
    {
        if ($request->is('api/*')) {
            return response()->json($course, 200);
        }
        return view('courses.show', compact('course'));
    }


    /**
     * @OA\Put(
     *     path="/api/courses/{id}",
     *     summary="Canviar el nom d'un curs",
     *     tags={"Courses"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del curs",
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dades necessaries per actualitzar el curs",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Nou nom del curs"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Curs actualitzat correctament",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Curs no trobat"
     *     )
     * )
     */
    public function update(Request $request, Course $course)
    {
        if ($request->is('api/*')) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $course->update($validator->validated());
            return response()->json($course, 200);
        }
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $course->update($validatedData);
        return redirect()->route('courses.index')->with('success', 'Curs actualitzat correctament');
    }


    /**
     * @OA\Delete(
     *     path="/api/courses/{id}",
     *     summary="Borrar un curs",
     *     tags={"Courses"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del curs",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Curs borrat correctament"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Curs no trobat"
     *     )
     * )
     */
    public function destroy(Request $request, Course $course)
    {
        $course->delete();
        if ($request->is('api/*')) {
            return response()->json(null, 204);
        }
        return redirect()->route('courses.index')->with('success', 'Curs eliminat correctament');
    }
    //obtenir els cursos amb les divisions
    public function getCoursesWithDivisions(Request $request)
    {
        // Obtener el ID del usuario de la solicitud o del usuario autenticado
        $userId = $request->input('user_id', $request->user()->id ?? null);
        
        // Verificar si se proporcionó un ID de usuario
        if (!$userId) {
            return response()->json(['message' => 'Se requiere un ID de usuario'], 400);
        }
        
        // Obtener los cursos asociados al usuario a través de la tabla course_division_user
        $courses = Course::whereHas('divisions', function ($query) use ($userId) {
            $query->whereHas('courseDivisionUser', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        })
        ->with(['divisions' => function ($query) use ($userId) {
            $query->whereHas('courseDivisionUser', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }])
        ->get()
        ->map(function ($course) {
            // Filtrar las divisiones de los cursos de ESO (1-4 ESO)
            if ($course->id >= 1 && $course->id <= 4) {
                // Divisiones únicas para ESO (A, B, C, D, E)
                $divisions = $course->divisions->whereIn('division', ['A', 'B', 'C', 'D', 'E'])->unique('division');
            }
            // Filtrar las divisiones para el curso de Bachillerato (5)
            elseif ($course->id == 5) {
                // Divisiones solo 1 y 2 para Bachillerato
                $divisions = $course->divisions->whereIn('division', ['1', '2'])->unique('division');
            } else {
                $divisions = $course->divisions->unique('division');
            }

            // Mapear los resultados para devolver solo los datos necesarios
            return [
                'id' => $course->id,
                'name' => $course->name,
                'divisions' => $divisions->map(function ($division) {
                    return [
                        'id' => $division->id,
                        'name' => $division->division, // Suponiendo que el nombre de la división está en 'division'
                    ];
                }),
            ];
        });

        // Retornar la respuesta en formato JSON
        return response()->json($courses, 200);
    }
}
