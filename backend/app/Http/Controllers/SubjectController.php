<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubjectController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/subjects",
     *     summary="Obtenir la llista d'assignatures",
     *     tags={"Subjects"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista d'assignatures obtenida correctament"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $subjects = Subject::all();
        if ($request->expectsJson()) {
        return response()->json($subjects, 200);
        }
        return view('subjects', compact('subjects'));
    }
    /**
     * @OA\Post(
     *     path="/api/subjects",
     *     summary="Crear una nova asignatura",
     *     tags={"Subjects"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dades necessaries per crear una nova asignatura",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Matemàtiques")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Assignatura creada correctament"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $subject = Subject::create($validatedData);
        if ($request->expectsJson()) {
            return response()->json($subject, 201);
        }
        return redirect()->route('subjects.index')->with('success', 'Asignatura creada correctamente');
    }

    /**
     * @OA\Get(
     *     path="/api/subjects/{id}",
     *     summary="Obtenir una asignatura",
     *     tags={"Subjects"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'assignatura",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Assignatura obtenida correctament"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Assignatura no trobada"
     *     )
     * )
     */
    public function show(Request $request, $id)
    {
        $subject = Subject::find($id);

        if (is_null($subject)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Asignatura no encontrada'], 404);
            }
            return redirect()->route('subjects.index')->with('error', 'Asignatura no encontrada');
        }

        if ($request->expectsJson()) {
            return response()->json($subject, 200);
        }

        return view('subjects.show', compact('subject'));
    }


    /**
     * @OA\Put(
     *     path="/api/subjects/{id}",
     *     summary="Actualitzar una asignatura",
     *     tags={"Subjects"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'assignatura",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dades necessaries per actualitzar l'assignatura",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Català")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Assignatura actualitzada correctament"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Assignatura no trobada"
     *     )
     * )
     */
    public function update(Request $request, $id)
{
    $subject = Subject::find($id);

    if (is_null($subject)) {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Asignatura no encontrada'], 404);
        }
        return redirect()->route('subjects.index')->with('error', 'Asignatura no encontrada');
    }

    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        if ($request->expectsJson()) {
            return response()->json($validator->errors(), 400);
        }
        return redirect()->back()->withErrors($validator)->withInput();
    }

    $subject->update($validator->validated());

    if ($request->expectsJson()) {
        return response()->json($subject, 200);
    }

    return redirect()->route('subjects.index')->with('success', 'Asignatura actualizada exitosamente');
}


    /**
     * @OA\Delete(
     *     path="/api/subjects/{id}",
     *     summary="Eliminar una asignatura",
     *     tags={"Subjects"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'assignatura",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Assignatura eliminada correctament"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Assignatura no trobada"
     *     )
     * )
     */
    public function destroy(Request $request, $id)
{
    $subject = Subject::find($id);

    if (is_null($subject)) {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Asignatura no encontrada'], 404);
        }
        return redirect()->route('subjects.index')->with('error', 'Asignatura no encontrada');
    }

    $subject->delete();

    if ($request->expectsJson()) {
        return response()->json(null, 204);
    }

    return redirect()->route('subjects.index')->with('success', 'Asignatura eliminada correctamente');
}

}
