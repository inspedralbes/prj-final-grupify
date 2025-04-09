<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormAttempt;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Form;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class FormAttemptController extends Controller
{
    /**
     * Guarda las respuestas de un usuario para un formulario y calcula la media.
     */
    public function store(Request $request)
    {
        // Validar que el formulario y las respuestas sean correctas
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'form_id' => 'required|exists:forms,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.rating' => 'required|integer|min:1|max:5', // Asegura que el rating sea entre 1 y 5
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Obtener los datos del formulario
        $form = Form::findOrFail($request->form_id);
        $user = $request->user_id;
        
        // Obtener las respuestas del formulario
        $answers = $request->answers;

        // Calcular la media de las respuestas (rating)
        $ratings = collect($answers)->pluck('rating');
        $averageRating = $ratings->avg();

        // Crear el intento de formulario (form attempt)
        $formAttempt = FormAttempt::create([
            'user_id' => $user,
            'form_id' => $form->id,
            'attempted_at' => Carbon::now(),
            'average_rating' => round($averageRating, 2), // Guardamos la media redondeada
        ]);

        // Guardar las respuestas del usuario
        foreach ($answers as $answer) {
            Answer::create([
                'user_id' => $user,
                'form_id' => $form->id,
                'question_id' => $answer['question_id'],
                'form_attempt_id' => $formAttempt->id,
                'rating' => $answer['rating'],
                'answer' => (string) $answer['rating'],
                'answer_type' => 'rating', // Tipo de respuesta
            ]);
        }

        return response()->json([
            'message' => 'Respuestas guardadas con éxito y media calculada',
            'data' => $formAttempt,
        ], 201);
    }

    /**
     * Obtiene los intentos de un usuario con su media por año
     */
    public function getUserPerformance($userId, $formId)
    {
        $attempts = FormAttempt::selectRaw('YEAR(attempted_at) as year, AVG(average_rating) as avg_rating')
            ->where('user_id', $userId)
            ->where('form_id', $formId)
            ->groupByRaw('YEAR(attempted_at)')
            ->orderBy('year')
            ->get();

        return response()->json([
            'user_id' => $userId,
            'form_id' => $formId,
            'performance_data' => $attempts
        ]);
    }
}
