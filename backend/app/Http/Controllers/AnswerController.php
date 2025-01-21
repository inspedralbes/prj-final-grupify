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
            'responses.*.answer_type' => 'required|in:string,number,boolean,array,object,multiple,checkbox',
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
                'answer' => $this->formatAnswer($response),  // Formateamos la respuesta
                'answer_type' => $response['answer_type'],  // Guardamos el tipo de respuesta
            ]);
        }

        // Incrementar el contador de respuestas en el formulario
        $form = Form::find($formId);
        if ($form) {
            // Incrementar el contador de respuestas del formulario
            $form->increment('responses_count'); // Esto incrementa el campo `responses_count`
        } else {
            Log::error('Formulario no encontrado', ['form_id' => $formId]);
        }

        // Marcar como respondido en la tabla intermedia "form_user"
        $user = User::find($userId);
        if ($user && $form) {
            // Actualizar la tabla pivot "form_user", seteando el campo "answered" a true
            $user->forms()->updateExistingPivot($formId, ['answered' => true]);
        }

        Log::info('Form ID recibido:', ['form_id' => $formId]);

        return response()->json(['message' => 'Respuestas guardadas correctamente'], 200);
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
