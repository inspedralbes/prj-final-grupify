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
        
        // Para el formulario de autoavaluación (ID 4), siempre permitir acceso
        $isAutoavaluacionForm = (int)$formId === 4;
        
        // Comprobar autorización solo si NO es el formulario de autoavaluación
        if (!$isAutoavaluacionForm && auth()->check()) {
            $currentUserRole = auth()->user()->role->name;
            
            // Los tutores no pueden ver las respuestas de los formularios
            if ($currentUserRole === 'tutor') {
                return response()->json(['message' => 'Como tutor, no tienes permisos para ver las respuestas de los formularios.'], 403);
            }
        }

        // Obtener las respuestas del usuario en el formulario específico
        $answers = Answer::where('form_id', $formId)
            ->where('user_id', $userId)
            ->with('question') // Cargar la relación con la pregunta
            ->get();

        // Si no hay respuestas pero es el formulario de autoavaluación, devolver un array vacío 
        // en lugar de un error, para que el frontend pueda manejarlo adecuadamente
        if ($answers->isEmpty()) {
            if ($isAutoavaluacionForm) {
                return response()->json([
                    'form_title' => $form->title ?? 'Formulario de Autoavaluación',
                    'user_name' => $user->name,
                    'user_lastname' => $user->last_name,
                    'answers' => [],
                    'message' => 'El estudiante aún no ha respondido el formulario de autoavaluación'
                ], 200);
            } else {
                return response()->json(['message' => 'El usuario no ha respondido este formulario'], 404);
            }
        }

        // Formatear las respuestas
        $formattedAnswers = $answers->map(function ($answer) {
            $formattedAnswer = $answer->toArray();
            
            if (in_array($answer->answer_type, ['multiple', 'checkbox'])) {
                $formattedAnswer['answer'] = json_decode($answer->answer, true);
            } elseif ($answer->answer_type === 'rating') {
                $formattedAnswer['answer'] = $answer->rating;
            }
            
            return $formattedAnswer;
        });

        // Devolver las respuestas con el título del formulario
        return response()->json([
            'form_title' => $form->title ?? 'Formulario de Autoavaluación',
            'user_name' => $user->name,
            'user_lastname' => $user->last_name,
            'user_image' => $user->image,
            'answers' => $formattedAnswers,
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
     * Obtener información de estado de las respuestas de un formulario por curso y división
     * Esta ruta es accesible por profesores para ver quién ha respondido sin ver las respuestas
     */
    public function getFormResponseStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'form_id' => 'required|exists:forms,id',
            'course_id' => 'required|exists:courses,id',
            'division_id' => 'required|exists:divisions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $formId = $request->input('form_id');
        $courseId = $request->input('course_id');
        $divisionId = $request->input('division_id');

        // Obtener todos los estudiantes de esta clase
        $allStudents = User::whereHas('courseDivisions', function ($query) use ($courseId, $divisionId) {
            $query->where('course_id', $courseId)
                  ->where('division_id', $divisionId);
        })
        ->where('role_id', 2) // Solo estudiantes
        ->get(['id', 'name', 'last_name', 'email']);

        // Si no hay estudiantes, devolver un mensaje
        if ($allStudents->isEmpty()) {
            return response()->json(['message' => 'No hay estudiantes en esta clase'], 404);
        }

        // Obtener los IDs de los estudiantes que han respondido
        $respondedStudentIds = User::whereHas('forms', function ($query) use ($formId) {
            $query->where('form_id', $formId)
                  ->where('answered', 1);
        })->pluck('id')->toArray();

        // Agregar el estado de respuesta a cada estudiante
        $studentsWithStatus = $allStudents->map(function ($student) use ($respondedStudentIds) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'last_name' => $student->last_name,
                'email' => $student->email,
                'has_answered' => in_array($student->id, $respondedStudentIds)
            ];
        });

        return response()->json([
            'total_students' => count($allStudents),
            'answered_count' => count($respondedStudentIds),
            'pending_count' => count($allStudents) - count($respondedStudentIds),
            'students' => $studentsWithStatus
        ], 200);
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

/**
 * Método público específico para obtener respuestas de autoavaluación
 * Esta función NO requiere autenticación y está optimizada para el frontend
 */
public function getAutoavaluacionResponses($userId)
{ 
    $formId = 4; 
    
    // Verificar si el usuario existe
    $user = User::find($userId);
    if (!$user) {
        return response()->json(['message' => 'Estudiante no encontrado'], 404);
    }
    
    // Buscar el formulario
    $form = Form::find($formId);
    $formTitle = $form ? $form->title : 'Formulario de Autoavaluación';
    
    // CONSULTA 1: Verificar form_user para ver si el usuario está registrado como respondido
    $formUser = DB::table('form_user')
        ->where('user_id', $userId)
        ->where('form_id', $formId)
        ->first();
    
    // Estado oficial según la tabla form_user
    $officialAnsweredStatus = $formUser ? (bool)$formUser->answered : false;
    
    // CONSULTA 2: Obtener respuestas directamente de la tabla answers
    $answers = Answer::where('form_id', $formId)
        ->where('user_id', $userId)
        ->with('question') // Cargar la relación con la pregunta
        ->get();
    
    // Estado basado en si hay respuestas
    $hasAnswersStatus = !$answers->isEmpty();
    
    // Log para depuración
    \Log::info("Autoavaluacion check for student $userId:", [
        'form_user_exists' => (bool)$formUser,
        'form_user_answered' => $officialAnsweredStatus,
        'has_answers' => $hasAnswersStatus,
        'answers_count' => $answers->count(),
        'answers_ids' => $answers->pluck('id')->toArray()
    ]);
    
    // Si no hay respuestas pero hay un registro form_user con answered=true, verificar directamente en la tabla
    if (!$hasAnswersStatus && $officialAnsweredStatus) {
        \Log::warning("Inconsistencia: El estudiante $userId tiene answered=true pero no tiene respuestas");
    }
    
    // Crear respuestas para las competencias
    // Las IDs de las preguntas corresponden a las competencias (22-29)
    $defaultCompetences = [
        ['id' => 22, 'name' => 'Responsabilitat', 'question_id' => 22, 'rating' => 0],
        ['id' => 23, 'name' => 'Treball en equip', 'question_id' => 23, 'rating' => 0],
        ['id' => 24, 'name' => 'Gestió del temps', 'question_id' => 24, 'rating' => 0],
        ['id' => 25, 'name' => 'Comunicació', 'question_id' => 25, 'rating' => 0],
        ['id' => 26, 'name' => 'Adaptabilitat', 'question_id' => 26, 'rating' => 0],
        ['id' => 27, 'name' => 'Lideratge', 'question_id' => 27, 'rating' => 0],
        ['id' => 28, 'name' => 'Creativitat', 'question_id' => 28, 'rating' => 0],
        ['id' => 29, 'name' => 'Proactivitat', 'question_id' => 29, 'rating' => 0],
    ];
    
    if ($hasAnswersStatus) {
        foreach ($answers as $answer) {
            $questionId = $answer->question_id;
            $rating = 0;
            
            if ($answer->answer_type === 'rating' && $answer->rating !== null) {
                $rating = (int)$answer->rating;
            } else if ($answer->answer !== null && is_numeric($answer->answer)) {
                $rating = (int)$answer->answer;
            }
            
            // Buscar la competencia correspondiente y actualizar su valor
            foreach ($defaultCompetences as &$competence) {
                if ($competence['question_id'] === $questionId) {
                    $competence['rating'] = $rating;
                    break;
                }
            }
        }
    }
    
    // Devolver respuesta JSON con toda la información
    return response()->json([
        'form_title' => $formTitle,
        'user_name' => $user->name,
        'user_lastname' => $user->last_name,
        'user_id' => $userId,
        'form_id' => $formId,
        'answers' => $defaultCompetences,
        'has_answered' => $officialAnsweredStatus || $hasAnswersStatus,
        'form_user_status' => $officialAnsweredStatus,
        'has_answers_in_db' => $hasAnswersStatus,
        'raw_answers' => $answers,
        'form_user_record' => $formUser,
        'debug_info' => [
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'server' => $_SERVER['SERVER_NAME'] ?? 'unknown'
        ]
    ], 200);
}

/**
 * Método simple de depuración 
 * SOLO PARA DEPURACIÓN - Muestra todos los datos de respuestas para un estudiante
 */
public function debugStudentAnswers($userId)
{
    // ID fijo del formulario de autoavaluación
    $formId = 4;
    
    // Obtener usuario
    $user = User::find($userId);
    if (!$user) {
        return response()->json(['error' => 'Usuario no encontrado'], 404);
    }
    
    // Obtener todos los datos relevantes
    $userData = [
        'user_id' => $userId,
        'name' => $user->name,
        'last_name' => $user->last_name,
        'email' => $user->email
    ];
    
    // 1. Estado en form_user
    $formUser = DB::table('form_user')
        ->where('user_id', $userId)
        ->where('form_id', $formId)
        ->first();
    
    // 2. Respuestas en answers
    $answers = DB::table('answers')
        ->where('user_id', $userId)
        ->where('form_id', $formId)
        ->get();
    
    // 3. Todos los datos de las competencias (22-29)
    $competencesData = [];
    for ($id = 22; $id <= 29; $id++) {
        // Buscar si hay una respuesta para esta pregunta/competencia
        $answer = $answers->firstWhere('question_id', $id);
        
        $competencesData[] = [
            'question_id' => $id,
            'competence_name' => $this->getCompetenceName($id),
            'has_answer' => !is_null($answer),
            'answer_data' => $answer,
            'rating_value' => $answer ? ($answer->rating ?? $answer->answer ?? 0) : 0
        ];
    }
    
    // Devolver toda la información para depuración
    return response()->json([
        'user' => $userData,
        'form_user_status' => $formUser,
        'answers_count' => count($answers),
        'answers' => $answers,
        'competences' => $competencesData,
        'debug_timestamp' => now()->format('Y-m-d H:i:s')
    ]);
}

/**
 * Obtener respuestas de un usuario 
 * Útil para pruebas directas desde el navegador
 */
public function getPublicAnswersByUser($formId, $userId)
{
    // Verificación mínima
    if (!$formId || !$userId) {
        return response()->json(['error' => 'Faltan parámetros requeridos'], 400);
    }
    
    // Obtener datos básicos
    $user = User::find($userId);
    $form = Form::find($formId);
    
    if (!$user || !$form) {
        return response()->json(['error' => 'Usuario o formulario no encontrados'], 404);
    }
    
    // Para todas las competencias, obtener sus respuestas específicas
    $allCompetences = [
        ['id' => 22, 'name' => 'Responsabilitat', 'question_id' => 22],
        ['id' => 23, 'name' => 'Treball en equip', 'question_id' => 23],
        ['id' => 24, 'name' => 'Gestió del temps', 'question_id' => 24],
        ['id' => 25, 'name' => 'Comunicació', 'question_id' => 25],
        ['id' => 26, 'name' => 'Adaptabilitat', 'question_id' => 26],
        ['id' => 27, 'name' => 'Lideratge', 'question_id' => 27],
        ['id' => 28, 'name' => 'Creativitat', 'question_id' => 28],
        ['id' => 29, 'name' => 'Proactivitat', 'question_id' => 29],
    ];
    
    // Obtener las respuestas para estas competencias/preguntas
    $answers = [];
    foreach ($allCompetences as $competence) {
        $answer = Answer::where('form_id', $formId)
            ->where('user_id', $userId)
            ->where('question_id', $competence['question_id'])
            ->first();
        
        // Si existe una respuesta, obtener su valor
        $rating = 0;
        if ($answer) {
            if ($answer->rating !== null) {
                $rating = (int)$answer->rating;
            } else if ($answer->answer !== null && is_numeric($answer->answer)) {
                $rating = (int)$answer->answer;
            }
        }
        
        $answers[] = [
            'id' => $competence['id'],
            'name' => $competence['name'],
            'question_id' => $competence['question_id'],
            'rating' => $rating,
            'has_value' => $answer ? true : false
        ];
    }
    
    return response()->json([
        'user_id' => $userId,
        'user_name' => $user->name,
        'user_lastname' => $user->last_name,
        'form_id' => $formId,
        'form_title' => $form->title ?? 'Formulario',
        'answers' => $answers,
        'has_answered' => count(array_filter($answers, function($a) { return $a['has_value']; })) > 0
    ]);
}

/**
 * Método público específicamente para autoavaluaciones (Formulario ID 4)
 * Sin autenticación para pruebas directas en el navegador
 */
public function getPublicAutoavaluacionAnswers($userId)
{
    return $this->getPublicAnswersByUser(4, $userId);
}

/**
 * Método auxiliar para obtener el nombre de la competencia según el ID de la pregunta
 */
private function getCompetenceName($questionId)
{
    // Mapeo de preguntas a competencias para el formulario de autoavaluación (ID 4)
    $competenceMapping = [
        22 => 'Responsabilitat',
        23 => 'Treball en equip',
        24 => 'Gestió del temps',
        25 => 'Comunicació',
        26 => 'Adaptabilitat',
        27 => 'Lideratge',
        28 => 'Creativitat',
        29 => 'Proactivitat'
    ];
    
    return $competenceMapping[$questionId] ?? 'Competencia desconocida';
}



    public function submitResponses(Request $request, $formId)
    {
        Log::info('Datos recibidos en submitResponses:', ['request_data' => $request->all()]);

        // Obtener el ID del usuario del cuerpo de la solicitud
        $userId = $request->input('user_id');
        $courseId = $request->input('course_id');
        $divisionId = $request->input('division_id');

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

        // Obtenemos el formulario pero ya no incrementamos el contador de responses_count
        $form = Form::find($formId);
        if (!$form) {
            Log::error('Formulario no encontrado', ['form_id' => $formId]);
        }

        // Marcar como respondido en la tabla intermedia "form_user"
        $user = User::find($userId);
        if ($user && $form) {
            // Actualizar la tabla pivot "form_user", seteando el campo "answered" a true
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
                    ->update(['answered' => true]);
            } else {
                // Si no existe, crear un nuevo registro
                DB::table('form_user')->insert([
                    'user_id' => $userId,
                    'form_id' => $formId,
                    'course_id' => $courseId,
                    'division_id' => $divisionId,
                    'answered' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // Actualizar el contador en la tabla form_assignments basado en los registros en form_user con answered=1
        try {
            if (Schema::hasTable('form_assignments')) {
                $formAssignment = \App\Models\FormAssignment::where('form_id', $formId)
                    ->where('course_id', $courseId)
                    ->where('division_id', $divisionId)
                    ->first();
                    
                if ($formAssignment) {
                    // Contar el número de usuarios que han respondido el formulario (tienen answered=1)
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
        } catch (\Exception $e) {
            Log::error('Error al actualizar el contador de respuestas: ' . $e->getMessage());
            // Continuamos con la ejecución aunque falle esta parte
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
