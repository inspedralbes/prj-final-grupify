<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Form;

/**
 * @OA\Tag(
 *     name="Respostes",
 *     description="Endpoints per gestionar les respostes"
 * )
 */
class AnswerController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/all-responses",
     *     summary="Obtener todas las respuestas de todos los usuarios",
     *     tags={"Respostes"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de todas las respuestas obtenida correctamente",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             type="object",
     *             @OA\Property(property="form_id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=2),
     *             @OA\Property(property="question_id", type="integer", example=3),
     *             @OA\Property(property="responses", type="array", @OA\Items(type="string"))
     *         ))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No hay respuestas disponibles"
     *     )
     * )
     */
    public function getAllResponses()
    {
        // Obtener todas las respuestas con sus relaciones
        $answers = Answer::all();

        // Verificar si hay respuestas
        if ($answers->isEmpty()) {
            return response()->json(['message' => 'No hay respuestas disponibles'], 404);
        }

        // Formatear las respuestas
        $formattedResponses = $answers->map(function ($answer) {
            return [
                'form_id' => $answer->form_id,
                'user_id' => $answer->user_id,
                'question_id' => $answer->question_id,
                'responses' => $this->formatAnswer($answer), // Formatear la respuesta según su tipo
            ];
        });

        // Devolver las respuestas formateadas
        return response()->json($formattedResponses, 200);
    }
    /**
     * @OA\Get(
     *     path="/api/forms/{formId}/users/{userId}/answers",
     *     summary="Obtener las respuestas de un usuario en un formulario específico",
     *     tags={"Respostes"},
     *     @OA\Parameter(
     *         name="formId",
     *         in="path",
     *         required=true,
     *         description="ID del formulario",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Respuestas obtenidas correctamente",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Answer"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Formulario o usuario no encontrado o el usuario no ha respondido este formulario"
     *     )
     * )
     */
    public function getAnswersByUser($formId, $userId)
    {
        // Verificar si el formulario existe
        $form = Form::find($formId);
        if (!$form) {
            return response()->json(['message' => 'Formulario no encontrado'], 404);
        }

        // Verificar si el usuario existe
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Obtener las respuestas del usuario en el formulario específico
        $answers = Answer::where('form_id', $formId)
            ->where('user_id', $userId)
            ->with('question') // Cargar la relación con la pregunta
            ->get();

        // Verificar si el usuario tiene respuestas para el formulario
        if ($answers->isEmpty()) {
            return response()->json(['message' => 'El usuario no ha respondido este formulario'], 404);
        }

        // Formatear las respuestas
        $answers->each(function ($answer) {
            if (in_array($answer->answer_type, ['multiple', 'checkbox'])) {
                $answer->answer = json_decode($answer->answer, true);
            } elseif ($answer->answer_type === 'rating') {
                $answer->answer = $answer->rating; // Mostrar el valor de rating en lugar de `answer`
            }
        });


        // Devolver las respuestas con el título del formulario
        return response()->json([
            'form_title' => $form->title, // Suponiendo que el modelo Form tiene un campo 'title'
            'user_name' => $user->name,
            'user_lastname' => $user->last_name,
            'answers' => $answers,
        ], 200);
    }



    /**
     * @OA\Get(
     *     path="/api/forms/{formId}/users",
     *     summary="Obtener usuarios que han respondido un formulario",
     *     tags={"Respostes"},
     *     @OA\Parameter(
     *         name="formId",
     *         in="path",
     *         required=true,
     *         description="ID del formulario",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de usuarios que han respondido el formulario",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Formulario no encontrado o sin usuarios que hayan respondido"
     *     )
     * )
     */
    public function getUsersByForm($formId)
    {
        // Verificar si el formulario existe
        $form = Form::find($formId);
        if (!$form) {
            return response()->json(['message' => 'Formulario no encontrado'], 404);
        }

        // Obtener los usuarios que han respondido el formulario
        $users = User::whereHas('answers', function ($query) use ($formId) {
            $query->where('form_id', $formId);
        })->get();

        // Verificar si hay usuarios
        if ($users->isEmpty()) {
            return response()->json(['message' => 'No hay usuarios que hayan respondido este formulario'], 404);
        }

        // Devolver la lista de usuarios
        return response()->json($users, 200);
    }

    /**
 * @OA\Get(
 *     path="/api/questions/{questionId}/average-rating",
 *     summary="Obtener el promedio de rating de una pregunta",
 *     tags={"Respostes"},
 *     @OA\Parameter(
 *         name="questionId",
 *         in="path",
 *         required=true,
 *         description="ID de la pregunta",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Promedio de rating calculado correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="question_id", type="integer", example=1),
 *             @OA\Property(property="average_rating", type="number", example=4.5)
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No hay ratings para esta pregunta"
 *     )
 * )
 */
public function getAverageRating($questionId)
{
    // Calcular el promedio de ratings de la pregunta
    $averageRating = Answer::where('question_id', $questionId)
        ->whereNotNull('rating')
        ->avg('rating');

    // Si no hay ratings, devolver un mensaje
    if (is_null($averageRating)) {
        return response()->json(['message' => 'No hay ratings para esta pregunta'], 404);
    }

    // Devolver el resultado en JSON
    return response()->json([
        'question_id' => $questionId,
        'average_rating' => round($averageRating, 2), // Redondeamos a 2 decimales
    ], 200);
}



    public function submitResponses(Request $request, $formId)
    {
        Log::info('Datos recibidos en submitResponses:', ['request_data' => $request->all()]);

        // Obtener el ID del usuario del cuerpo de la solicitud
        $userId = $request->input('user_id');

        // Validar las respuestas (asegurándonos de que son válidas)
        $validator = Validator::make($request->all(), [
            'responses' => 'required|array',
            'responses.*.question_id' => 'required|integer|exists:questions,id',
            'responses.*.answer' => 'required',
            'responses.*.answer_type' => 'required|in:string,number,boolean,array,object,multiple,checkbox,rating',
        ]);

        if ($validator->fails()) {
            Log::error('Errores de validación:', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        // Guardar las respuestas en la tabla "answers"
        foreach ($validated['responses'] as $response) {
            Log::info('Guardando respuesta:', $response);

            Answer::create([
                'user_id' => $userId,  // Se usa el userId que vino en la solicitud
                'form_id' => $formId,  // ID del formulario que vino en la URL
                'question_id' => $response['question_id'],  // ID de la pregunta
                'answer' => $response['answer_type'] === 'rating' ? '' : $this->formatAnswer($response),  // Asignar vacío o el valor formateado para 'answer'
                'rating' => $response['answer_type'] === 'rating' ? (int) $response['answer'] : null,  // Guardar el valor de rating si es una respuesta 'rating'
                'answer_type' => $response['answer_type'],  // Guardamos el tipo de respuesta
            ]);
            
            
        }

        // Incrementar el contador de respuestas en el formulario
        $form = Form::find($formId);
        if ($form) {
            // Incrementar el contador de respuestas del formulario
            $form->increment('responses_count'); // Esto incrementa el campo `responses_count`
        } else {
            Log::error('Formulari no trobat', ['form_id' => $formId]);
        }

        // Marcar como respondido en la tabla intermedia "form_user"
        $user = User::find($userId);
        if ($user && $form) {
            // Actualizar la tabla pivot "form_user", seteando el campo "answered" a true
            $user->forms()->updateExistingPivot($formId, ['answered' => true]);
        }

        Log::info('Form ID rebut:', ['form_id' => $formId]);

        return response()->json(['message' => 'Respostes desades correctament'], 200);
    }



    // Método para formatear la respuesta según su tipo
    protected function formatAnswer($response)
    {
        switch ($response['answer_type']) {
            case 'checkbox':
            case 'multiple':
                return json_encode($response['answer']); // Convertir arrays a JSON
            case 'number':
                return (int) $response['answer']; // Convertir a número
            case 'string':
                return (string) $response['answer']; // Convertir a string
            case 'boolean':
                return (bool) $response['answer']; // Convertir a booleano
            case 'rating':
                    return (int) $response['answer']; // Guardar rating como número entero (1-5)
            default:
                return $response['answer']; // Dejarlo tal cual
        }
    }

    /**
     * @OA\Get(
     *     path="/api/answers",
     *     summary="Llistar totes les respostes",
     *     tags={"Respostes"},
     *     @OA\Response(
     *         response=200,
     *         description="Llista de respostes obtinguda correctament",
     *     )
     * )
     */
    public function index()
    {
        $answers = Answer::all();
        return response()->json($answers);
    }

    /**
     * @OA\Get(
     *     path="/api/answers/{id}",
     *     summary="Obtenir una resposta específica per ID",
     *     tags={"Respostes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la resposta",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dades de la resposta",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resposta no trobada"
     *     )
     * )
     */
    public function show($id)
    {
        $answer = Answer::find($id);
        if (is_null($answer)) {
            return response()->json(['message' => 'Resposta no trobada'], 404);
        }
        return response()->json($answer);
    }

    /**
     * @OA\Post(
     *     path="/api/answers",
     *     summary="Crear una nova resposta",
     *     tags={"Respostes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"answer"},
     *             @OA\Property(property="answer", type="string", maxLength=255, example="Aquesta és una resposta d'exemple.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resposta creada correctament",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validació"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'answer' => 'required|string|max:255'
        ]);

        $answer = Answer::create($validatedData);

        return response()->json($answer, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/answers/{id}",
     *     summary="Actualitzar una resposta existent",
     *     tags={"Respostes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la resposta a actualitzar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="answer", type="string", maxLength=255, example="Text de la resposta actualitzada.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resposta actualitzada correctament",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validació"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'answer' => 'sometimes|required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $answer = Answer::findOrFail($id);
        $answer->update($validator->validated());

        return response()->json($answer, 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/answers/{id}",
     *     summary="Eliminar una resposta",
     *     tags={"Respostes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la resposta a eliminar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Resposta eliminada correctament"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resposta no trobada"
     *     )
     * )
     */
    public function destroy($id)
    {
        $answer = Answer::find($id);
        if (is_null($answer)) {
            return response()->json(['message' => 'Resposta no trobada'], 404);
        }
        $answer->delete();
        return response()->json(null, 204);
    }
}
