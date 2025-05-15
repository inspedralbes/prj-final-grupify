<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Support\Facades\Log;

class QuestionsController extends Controller
{
    /**
     * Obtiene todas las preguntas de un formulario específico con sus opciones
     */
    public function getFormQuestions($formId)
    {
        Log::info('getFormQuestions llamado para formulario ID: ' . $formId);
        
        try {
            // Cargar el formulario con las preguntas y las opciones de cada pregunta
            $form = Form::with(['questions.options'])->find($formId);
            
            // Verificar si el formulario existe
            if (!$form) {
                Log::error('Formulario no encontrado: ' . $formId);
                return response()->json(['message' => 'Formulario no encontrado'], 404);
            }
            
            // Extraer solo las preguntas con sus opciones
            $questions = $form->questions;
            
            Log::info('Preguntas encontradas para formulario ' . $formId . ': ' . $questions->count(), [
                'preguntas' => $questions->toArray()
            ]);
            
            // Devolver directamente las preguntas como JSON
            return response()->json($questions, 200);
        } catch (\Exception $e) {
            Log::error('Error en getFormQuestions: ' . $e->getMessage(), [
                'formId' => $formId,
                'exception' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Error al obtener las preguntas: ' . $e->getMessage()], 500);
        }
    }
}