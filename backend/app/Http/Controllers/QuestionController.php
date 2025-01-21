<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use Illuminate\Support\Facades\Validator;
use App\Services\QuestionService;


/**
 * @OA\Tag(
 *     name="Questions",
 *     description="Endpoints per gestionar preguntes"
 * )
 */
class QuestionController extends Controller

{

    protected $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

    /**
     * @OA\Get(
     *     path="/api/questions",
     *     summary="Llistar totes les preguntes",
     *     tags={"Questions"},
     *     @OA\Response(
     *         response=200,
     *         description="Llista de preguntes obtinguda correctament",
     *     )
     * )
     */
    public function index(Request $request)
    {
        // Obtener todas las preguntas
        $questions = $this->questionService->getAllQuestions();

        // Si la solicitud es una API, devolver las preguntas como JSON
        if ($request->is('api/*')) {
            return response()->json($questions);
        }

        // Si la solicitud es web, mostrar las preguntas en la vista 'questions.blade.php'
        return view('questions', compact('questions'));
    }

    /**
     * @OA\Get(
     *     path="/api/questions/{id}",
     *     summary="Obtenir una pregunta específica",
     *     tags={"Questions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la pregunta",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dades de la pregunta",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pregunta no trobada"
     *     )
     * )
     */
    public function show($id, Request $request)
    {
        // Obtener la pregunta por ID
        $question = $this->questionService->getQuestionById($id);

        // Si la pregunta no existe, retornar error
        if (is_null($question)) {
            return response()->json(['message' => 'Pregunta no encontrada'], 404);
        }

        // Si la solicitud es una API, devolver la pregunta como JSON
        if ($request->is('api/*')) {
            return response()->json($question);
        }

        // Si la solicitud es web, mostrar la pregunta en la vista 'question.show.blade.php'
        return view('question.show', compact('question'));
    }

    /**
     * @OA\Post(
     *     path="/api/questions",
     *     summary="Crear una nova pregunta",
     *     tags={"Questions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"question"},
     *             @OA\Property(property="question", type="string", maxLength=255, example="Quina és la teva resposta?"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pregunta creada correctament",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validació"
     *     )
     * )
     */
    public function store(Request $request)
    {
        // Validación de los datos recibidos
        $validatedData = $request->validate([
            'question' => 'required|string|max:255',
            //'form_id' => 'required|integer|exists:forms,id',  // Relación con el formulario
        ]);

        // Llamar al servicio para crear la nueva pregunta
        $question = $this->questionService->createQuestion($validatedData);

        // Retornar respuesta en función de la solicitud
        if ($request->is('api/*')) {
            return response()->json($question, 201);
        }

        // Redirigir a la lista de preguntas con un mensaje de éxito
        return redirect()->route('questions.index')->with('success', 'Pregunta creada correctamente');
    }

    /**
     * @OA\Put(
     *     path="/api/questions/{id}",
     *     summary="Actualitzar una pregunta existent",
     *     tags={"Questions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la pregunta a actualitzar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="question", type="string", maxLength=255, example="Pregunta actualitzada"),
     *             @OA\Property(property="form_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pregunta actualitzada correctament",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validació"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pregunta no trobada"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        // Validación de los datos recibidos
        $validatedData = $request->validate([
            'question' => 'sometimes|required|string|max:255',
        ]);

        // Actualizar la pregunta utilizando el servicio
        $question = $this->questionService->updateQuestion($id, $validatedData);

        // Si la solicitud es una API, devolver la respuesta en formato JSON
        if ($request->is('api/*')) {
            return response()->json($question, 200);
        }

        // Redirigir a la lista de preguntas con un mensaje de éxito
        return redirect()->route('questions.index')->with('success', 'Pregunta actualizada correctamente');
    }

    /**
     * @OA\Delete(
     *     path="/api/questions/{id}",
     *     summary="Eliminar una pregunta",
     *     tags={"Questions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la pregunta a eliminar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Pregunta eliminada correctament",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pregunta no trobada"
     *     )
     * )
     */
    public function destroy($id, Request $request)
    {
        // Llamar al servicio para eliminar la pregunta
        $this->questionService->deleteQuestion($id);

        // Si la solicitud es una API, devolver respuesta vacía con código 204
        if ($request->is('api/*')) {
            return response()->json(null, 204);
        }

        // Redirigir a la lista de preguntas con un mensaje de éxito
        return redirect()->route('questions.index')->with('success', 'Pregunta eliminada correctamente');
    }
}
