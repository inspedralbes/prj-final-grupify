<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\User;
use App\Models\FormUser;
use App\Models\CescRelationship;
use App\Models\TagCesc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class CescRelationshipController extends Controller
{
    /**
     * Obtener los usuarios que respondieron a un formulario Cesc.
     */
    public function getRespondedUsers($formId)
    {
        try {
            // Validar la existencia del formulario
            $form = Form::find($formId);
            if (!$form) {
                return response()->json(['message' => 'Formulario no encontrado'], 404);
            }

            // Obtener los usuarios que han respondido basado en la tabla form_user
            $respondedUserIds = \App\Models\FormUser::where('form_id', $formId)
                ->where('answered', true)
                ->pluck('user_id')
                ->unique()
                ->toArray();
                
            // También obtener los IDs de usuarios con relaciones CESC (como respaldo)
            $relationshipUserIds = CescRelationship::whereHas('question', function ($query) use ($formId) {
                $query->where('form_id', $formId);
            })
                ->pluck('user_id')
                ->unique()
                ->toArray();
                
            // Combinar ambos conjuntos de IDs para mayor certeza
            $userIds = array_unique(array_merge($respondedUserIds, $relationshipUserIds));

            // Verificar si existen usuarios
            if (empty($userIds)) {
                return response()->json(['message' => 'No hay usuarios que hayan respondido este formulario'], 404);
            }

            // Obtener los detalles de los usuarios
            $users = User::whereIn('id', $userIds)->get(['id', 'name', 'last_name']);

            // Retornar la lista de usuarios
            return response()->json($users, 200);
        } catch (\Exception $e) {
            // Manejar cualquier excepción
            return response()->json(['message' => 'Error interno en el servidor', 'error' => $e->getMessage()], 500);
        }
    }

    public function getAnswersByUser($formId, $userId)
    {
        try {
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

            // Obtener las preguntas del formulario 
            $questions = $form->questions()->get();
            
            if ($questions->isEmpty()) {
                return response()->json([
                    'form_title' => $form->title,
                    'user_name' => $user->name,
                    'user_lastname' => $user->last_name,
                    'relationships' => [],
                    'message' => 'No hay preguntas en este formulario'
                ], 200);
            }

            // Obtener las relaciones CESC donde el usuario es el que respondió (no el peer)
            $relationships = CescRelationship::where('user_id', $userId)
                ->whereIn('question_id', $questions->pluck('id'))
                ->with(['peer', 'question', 'tag'])
                ->get();

            // Si no hay relaciones, devolver una respuesta vacía
            if ($relationships->isEmpty()) {
                return response()->json([
                    'form_title' => $form->title,
                    'user_name' => $user->name,
                    'user_lastname' => $user->last_name,
                    'relationships' => [],
                    'message' => 'El usuario no ha completado el cuestionario CESC'
                ], 200);
            }

            // Agrupar las relaciones por question_id
            $groupedRelationships = [];
            
            foreach ($relationships as $relationship) {
                $questionId = $relationship->question_id;
                
                if (!isset($groupedRelationships[$questionId])) {
                    $groupedRelationships[$questionId] = [
                        'question_id' => $questionId,
                        'question_title' => $relationship->question ? $relationship->question->title : 'Pregunta sin título',
                        'peers' => []
                    ];
                }
                
                // Añadir el peer a la lista de peers de esta pregunta
                $peer = $relationship->peer;
                if ($peer) {
                    $groupedRelationships[$questionId]['peers'][] = [
                        'id' => $peer->id,
                        'name' => $peer->name,
                        'last_name' => $peer->last_name,
                        'tag_id' => $relationship->tag_id,
                        'tag_name' => $relationship->tag ? $relationship->tag->name : 'Etiqueta desconocida'
                    ];
                }
            }

            // Devolver las relaciones con el título del formulario
            return response()->json([
                'form_title' => $form->title,
                'user_name' => $user->name,
                'user_lastname' => $user->last_name,
                'relationships' => $groupedRelationships,
            ], 200);
            
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error en getAnswersByUser CESC: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            // Return a more informative error message
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 500);
        }
    }

    /**
     * Listar todas las relaciones Cesc.
     */
    public function index()
    {
        $relationships = CescRelationship::with(['user', 'peer', 'question', 'tag'])->get();
        return response()->json($relationships, 200);
    }

    /**
     * Filtrar relaciones por usuario que respondió.
     */
    public function byUser($userId)
    {
        $relationships = CescRelationship::where('user_id', $userId)
            ->with(['peer', 'question', 'tag'])
            ->get();

        return response()->json($relationships, 200);
    }

    /**
     * Guardar nuevas relaciones Cesc.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'form_id' => 'required|exists:forms,id', // Añadido: recibir el ID del formulario desde la solicitud
            'relationships' => 'required|array',
            'relationships.*.peer_id' => 'required|exists:users,id',
            'relationships.*.question_id' => 'required|exists:questions,id',
            'relationships.*.tag_id' => 'required|exists:tags_cesc,id',
        ]);

        // Iniciar una transacción para garantizar consistencia
        DB::beginTransaction();
        
        try {
            // Guardar cada relación individual
            foreach ($data['relationships'] as $relationship) {
                CescRelationship::create([
                    'user_id' => $data['user_id'],
                    'peer_id' => $relationship['peer_id'],
                    'question_id' => $relationship['question_id'],
                    'tag_id' => $relationship['tag_id'],
                ]);
            }
            
            // Obtener el formulario usando el ID proporcionado en la solicitud
            $form = Form::find($data['form_id']);
            if (!$form) {
                throw new \Exception("Formulario no encontrado con ID: " . $data['form_id']);
            }

            // Obtener el usuario
            $user = User::find($data['user_id']);
            if (!$user) {
                throw new \Exception("Usuario no encontrado.");
            }
            
            // Obtener el curso y división del usuario
            $courseDivision = DB::table('course_division_user')
                ->where('user_id', $data['user_id'])
                ->orderBy('id', 'desc')
                ->first();
                
            if (!$courseDivision) {
                throw new \Exception("Usuario sin curso/división asignados.");
            }
            
            // Actualizar o crear el registro en form_user con answered = 1
            DB::table('form_user')->updateOrInsert(
                [
                    'user_id' => $data['user_id'],
                    'form_id' => $form->id,
                    'course_id' => $courseDivision->course_id,
                    'division_id' => $courseDivision->division_id,
                ],
                [
                    'answered' => 1, // Marca como respondido (1 en lugar de true para asegurar compatibilidad)
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            
            // Actualizar el contador de respuestas en el formulario si es necesario
            if (Schema::hasTable('form_assignments')) {
                $formAssignment = \App\Models\FormAssignment::where('form_id', $form->id)
                    ->where('course_id', $courseDivision->course_id)
                    ->where('division_id', $courseDivision->division_id)
                    ->first();
                    
                if ($formAssignment) {
                    // Contar el número de usuarios que han respondido el formulario 
                    $answeredCount = DB::table('form_user')
                        ->where('form_id', $form->id)
                        ->where('course_id', $courseDivision->course_id)
                        ->where('division_id', $courseDivision->division_id)
                        ->where('answered', 1)
                        ->count();
                    
                    // Actualizar el contador de respuestas
                    $formAssignment->responses_count = $answeredCount;
                    $formAssignment->save();
                }
            }
            
            // Confirmar la transacción
            DB::commit();
            
            // Registrar la acción completada
            \Illuminate\Support\Facades\Log::info('Cesc completado', [
                'user_id' => $data['user_id'],
                'form_id' => $form->id,
                'course_id' => $courseDivision->course_id,
                'division_id' => $courseDivision->division_id
            ]);
            
            // Devolver una respuesta exitosa
            return response()->json(['message' => 'Relaciones guardadas correctamente y formulario marcado como respondido.'], 201);
            
        } catch (\Exception $e) {
            // Si hay algún error, revertir la transacción
            DB::rollback();
            
            // Registrar el error
            \Illuminate\Support\Facades\Log::error('Error al guardar relaciones CESC', [
                'error' => $e->getMessage(),
                'user_id' => $data['user_id'] ?? null,
                'form_id' => $data['form_id'] ?? null
            ]);
            
            // Devolver un mensaje de error
            return response()->json(['message' => 'Error al guardar las relaciones: ' . $e->getMessage()], 500);
        }
    }
}
