<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Form;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AnswerController extends Controller
{
    /**
     * Método para enviar respuestas de un formulario
     */
    public function submitResponses(Request $request, $formId)
    {
        Log::info('Datos recibidos en submitResponses:', [
            'formId' => $formId,
            'request_data' => $request->all()
        ]);

        // Obtener el ID del usuario del cuerpo de la solicitud
        $userId = $request->input('user_id');
        $courseId = $request->input('course_id');
        $divisionId = $request->input('division_id');

        // Verificar entradas obligatorias
        if (!$userId || !$courseId || !$divisionId) {
            Log::error('Faltan datos obligatorios en la solicitud', [
                'user_id' => $userId, 
                'course_id' => $courseId, 
                'division_id' => $divisionId
            ]);
            return response()->json(['message' => 'Faltan datos obligatorios: user_id, course_id o division_id'], 422);
        }

        // Validar que el formulario existe
        $form = Form::find($formId);
        if (!$form) {
            Log::error('Formulario no encontrado', ['form_id' => $formId]);
            return response()->json(['message' => 'Formulario no encontrado'], 404);
        }

        // Validar las respuestas (asegurándonos de que son válidas)
        $validator = Validator::make($request->all(), [
            'responses' => 'required|array',
            'responses.*.question_id' => 'required|integer|exists:questions,id',
            'responses.*.answer' => 'required',
            'responses.*.answer_type' => 'required|in:string,number,boolean,array,object,multiple,checkbox,rating',
            'course_id' => 'required|exists:courses,id',
            'division_id' => 'required|exists:divisions,id',
        ]);

        if ($validator->fails()) {
            Log::error('Errores de validación:', $validator->errors()->toArray());
            return response()->json([
                'message' => 'Errores de validación', 
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        
        DB::beginTransaction();
        
        try {
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

            // Marcar como respondido en la tabla intermedia "form_user"
            $user = User::find($userId);
            if (!$user) {
                throw new \Exception("Usuario no encontrado");
            }

            // Verificar si existe una relación en form_user
            $formUser = DB::table('form_user')
                ->where('user_id', $userId)
                ->where('form_id', $formId)
                ->where('course_id', $courseId)
                ->where('division_id', $divisionId)
                ->first();
                
            if ($formUser) {
                // Si existe el registro, actualizarlo
                DB::table('form_user')
                    ->where('user_id', $userId)
                    ->where('form_id', $formId)
                    ->where('course_id', $courseId)
                    ->where('division_id', $divisionId)
                    ->update(['answered' => 1]);

                Log::info('Registro form_user actualizado');
            } else {
                // Si no existe, crear un nuevo registro
                DB::table('form_user')->insert([
                    'user_id' => $userId,
                    'form_id' => $formId,
                    'course_id' => $courseId,
                    'division_id' => $divisionId,
                    'answered' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                Log::info('Nuevo registro form_user creado');
            }

            // Actualizar el contador en la tabla form_assignments 
            if (Schema::hasTable('form_assignments')) {
                $formAssignment = \App\Models\FormAssignment::where('form_id', $formId)
                    ->where('course_id', $courseId)
                    ->where('division_id', $divisionId)
                    ->first();
                    
                if ($formAssignment) {
                    // Contar el número de usuarios que han respondido el formulario 
                    $answeredCount = DB::table('form_user')
                        ->where('form_id', $formId)
                        ->where('course_id', $courseId)
                        ->where('division_id', $divisionId)
                        ->where('answered', 1)
                        ->count();
                    
                    // Actualizar el contador de respuestas
                    $formAssignment->responses_count = $answeredCount;
                    $formAssignment->save();
                    
                    Log::info('Contador de respuestas actualizado en form_assignments', [
                        'form_id' => $formId,
                        'course_id' => $courseId,
                        'division_id' => $divisionId,
                        'responses_count' => $answeredCount
                    ]);
                }
            }
            
            DB::commit();
            Log::info('Transacción completada exitosamente');
            
            return response()->json(['message' => 'Respuestas guardadas correctamente'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar respuestas: ' . $e->getMessage(), [
                'exception' => $e, 
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error al guardar las respuestas: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método para formatear la respuesta según su tipo
    protected function formatAnswer($response)
    {
        try {
            switch ($response['answer_type']) {
                case 'checkbox':
                case 'multiple':
                    // Asegurarse de que es un array antes de codificarlo
                    if (is_array($response['answer'])) {
                        return json_encode($response['answer']);
                    } elseif (is_string($response['answer']) && $this->isJson($response['answer'])) {
                        // Si ya es un string JSON, devolverlo tal cual
                        return $response['answer'];
                    } else {
                        // Si no es un array ni un JSON, convertirlo a string JSON
                        return json_encode([$response['answer']]);
                    }
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
        } catch (\Exception $e) {
            Log::error('Error al formatear respuesta:', [
                'response' => $response,
                'error' => $e->getMessage()
            ]);
            // En caso de error, devolver la respuesta como string
            return (string) $response['answer'];
        }
    }
    
    // Método auxiliar para verificar si un string es JSON válido
    private function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    // Resto de métodos del controlador...
}
