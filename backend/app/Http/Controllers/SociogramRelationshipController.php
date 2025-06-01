<?php

namespace App\Http\Controllers;

use App\Models\SociogramRelationship;
use App\Models\Form;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SociogramRelationshipController extends Controller
{

    public function getResponsesByCourseAndDivision(Request $request)
    {
        try {
            // Validar los datos de entrada
            $validated = $request->validate([
                'course_id' => 'required|exists:courses,id',
                'division_id' => 'required|exists:divisions,id',
            ]);

            $courseId = $validated['course_id'];
            $divisionId = $validated['division_id'];

            // Obtener los usuarios que pertenecen al curso y división
            $userIds = DB::table('course_division_user')
                ->where('course_id', $courseId)
                ->where('division_id', $divisionId)
                ->pluck('user_id')
                ->unique();

            // Verificar si hay usuarios en el curso y división
            if ($userIds->isEmpty()) {
                return response()->json(['message' => 'No se encontraron usuarios en esta combinación de curso y división.'], 404);
            }

            // Obtener las relaciones sociométricas de los usuarios encontrados
            $relationships = SociogramRelationship::whereIn('user_id', $userIds)
                ->with(['user', 'peer', 'question']) // Cargar relaciones necesarias
                ->get();

            // Verificar si hay relaciones disponibles
            if ($relationships->isEmpty()) {
                return response()->json(['message' => 'No hay respuestas registradas para los usuarios de este curso y división.'], 404);
            }

            // Agrupar las relaciones por formulario
            $groupedResponses = $relationships->groupBy('question.form_id')->map(function ($formRelationships, $formId) {
                // Obtener el título del formulario (si existe)
                $formTitle = optional($formRelationships->first()->question->form)->title;

                // Agrupar las relaciones por usuario
                return [
                    'form_id' => $formId,
                    'form_title' => $formTitle,
                    'responses' => $formRelationships->groupBy('user_id')->map(function ($userRelationships, $userId) {
                        return [
                            'user_id' => $userId,
                            'user_name' => optional($userRelationships->first()->user)->name,
                            'user_last_name' => optional($userRelationships->first()->user)->last_name,
                            'responses' => $userRelationships->groupBy('question_id')->map(function ($questionRelationships, $questionId) {
                                return [
                                    'question_id' => $questionId,
                                    'question_title' => optional($questionRelationships->first()->question)->title,
                                    'peers' => $questionRelationships->map(function ($relationship) {
                                        return [
                                            'peer_id' => optional($relationship->peer)->id,
                                            'peer_name' => optional($relationship->peer)->name,
                                            'peer_last_name' => optional($relationship->peer)->last_name,
                                            'relationship_type' => $relationship->relationship_type,
                                        ];
                                    }),
                                ];
                            }),
                        ];
                    }),
                ];
            });

            // Devolver las respuestas estructuradas
            return response()->json([
                'all_responses' => $groupedResponses->values(), // Respuestas agrupadas por formulario
            ], 200);
        } catch (\Exception $e) {
            // Manejar cualquier excepción
            return response()->json(['message' => 'Error interno en el servidor', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener todas las respuestas de todos los formularios.
     */
    public function getAllResponses()
    {
        try {
            // Cargar las relaciones incluyendo las relaciones Many-to-Many de user con courses y divisions
            $relationships = SociogramRelationship::with([
                'user.courses',      // Carga los courses asociados al user
                'user.divisions',    // Carga las divisions asociadas al user
                'peer',
                'question.form'
            ])->get();

            // Verificar si hay relaciones disponibles
            if ($relationships->isEmpty()) {
                return response()->json(['message' => 'No hay respuestas registradas'], 404);
            }

            // Agrupar las relaciones por formulario
            $groupedResponses = $relationships->groupBy('question.form_id')->map(function ($formRelationships, $formId) {
                // Obtener el título del formulario (si existe)
                $formTitle = optional($formRelationships->first()->question->form)->title;

                // Agrupar las relaciones por usuario
                return [
                    'form_id' => $formId,
                    'form_title' => $formTitle,
                    'responses' => $formRelationships->groupBy('user_id')->map(function ($userRelationships, $userId) {
                        $firstUser = $userRelationships->first()->user;

                        return [
                            'user_id' => $userId,
                            'user_name' => optional($firstUser)->name,
                            'user_last_name' => optional($firstUser)->last_name,
                            // Mapear cada course asociado al usuario
                            'course' => $firstUser->courses->map(function ($course) {
                                return [
                                    'id' => $course->id,
                                    'name' => $course->name,
                                ];
                            }),
                            // Mapear cada division asociada al usuario
                            'division' => $firstUser->divisions->map(function ($division) {
                                return [
                                    'id' => $division->id,
                                    'name' => $division->name,
                                ];
                            }),
                            'responses' => $userRelationships->groupBy('question_id')->map(function ($questionRelationships, $questionId) {
                                return [
                                    'question_id' => $questionId,
                                    'question_title' => optional($questionRelationships->first()->question)->title,
                                    'peers' => $questionRelationships->map(function ($relationship) {
                                        return [
                                            'peer_id' => optional($relationship->peer)->id,
                                            'peer_name' => optional($relationship->peer)->name,
                                            'peer_last_name' => optional($relationship->peer)->last_name,
                                            'relationship_type' => $relationship->relationship_type,
                                        ];
                                    }),
                                ];
                            }),
                        ];
                    }),
                ];
            });

            // Devolver las respuestas estructuradas
            return response()->json([
                'all_responses' => $groupedResponses->values(),
            ], 200);
        } catch (\Exception $e) {
            // Manejar cualquier excepción
            return response()->json([
                'message' => 'Error interno en el servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    /**
     * Obtener los usuarios que respondieron a un formulario sociométrico.
     */
    /**
     * Obtener los usuarios que respondieron a un formulario sociométrico específico.
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
                
            // También obtener los IDs de usuarios con relaciones sociométricas (como respaldo)
            $relationshipUserIds = SociogramRelationship::whereHas('question', function ($query) use ($formId) {
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
        // Verificar si el usuario autenticado tiene permisos para ver estas respuestas
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }
        
        // Verificamos manualmente si el usuario es profesor, tutor o admin
        if ($user->role && in_array($user->role->name, ['profesor', 'tutor', 'admin', 'orientador'])) {
            // Ok, proceder con la lógica
        } else {
            return response()->json(['message' => 'No tienes permisos para ver estas respuestas'], 403);
        }
        
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
        $questions = $form->questions; // Suponiendo que el formulario tiene una relación de preguntas

        // Obtener las relaciones sociométricas del usuario que pertenecen a las preguntas del formulario
        $relationships = SociogramRelationship::where('user_id', $userId)
            ->whereIn('question_id', $questions->pluck('id')) // Filtrar solo las relaciones con preguntas del formulario
            ->with(['peer', 'question']) // Cargar las relaciones con peers y preguntas
            ->get();

        // Verificar si existen relaciones para el usuario
        if ($relationships->isEmpty()) {
            return response()->json(['message' => 'El usuario no tiene relaciones sociométricas registradas para este formulario.'], 404);
        }

        // Agrupar las relaciones por question_id
        $groupedRelationships = $relationships->groupBy(function ($relationship) {
            return $relationship->question_id;
        });

        // Formatear las relaciones agrupadas
        $formattedRelationships = $groupedRelationships->map(function ($group, $questionId) {
            return [
                'question_id' => $questionId,
                'question_title' => optional($group->first()->question)->title,
                'peers' => $group->map(function ($relationship) {
                    return [
                        'id' => optional($relationship->peer)->id,
                        'name' => optional($relationship->peer)->name,
                        'last_name' => optional($relationship->peer)->last_name,
                        'relationship_type' => $relationship->relationship_type,
                    ];
                }),
            ];
        });

        // Devolver las relaciones con el título del formulario
        return response()->json([
            'form_title' => $form->title, // Suponiendo que el modelo Form tiene un campo 'title'
            'user_name' => $user->name,
            'user_lastname' => $user->last_name,
            'user_image' => $user->image,
            'relationships' => $formattedRelationships,
        ], 200);
    }


    /**
     * Listar todas las relaciones sociométricas.
     */
    public function index()
    {
        $relationships = SociogramRelationship::with(['user', 'peer', 'question'])->get();
        return response()->json($relationships, 200);
    }

    /**
     * Filtrar relaciones por usuario que respondió.
     */
    public function byUser($userId)
    {
        $relationships = SociogramRelationship::where('user_id', $userId)
            ->with(['peer', 'question'])
            ->get();

        return response()->json($relationships, 200);
    }

    /**
     * Guardar nuevas relaciones sociométricas.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'form_id' => 'required|exists:forms,id', // Añadido: recibir el ID del formulario desde la solicitud
            'relationships' => 'required|array',
            'relationships.*.peer_id' => 'required|exists:users,id',
            'relationships.*.question_id' => 'required|exists:questions,id',
            'relationships.*.relationship_type' => 'required|in:positive,negative',
        ]);

        // Iniciar una transacción para garantizar consistencia
        DB::beginTransaction();
        
        try {
            // Guardar cada relación individual
            foreach ($data['relationships'] as $relationship) {
                SociogramRelationship::create([
                    'user_id' => $data['user_id'],
                    'peer_id' => $relationship['peer_id'],
                    'question_id' => $relationship['question_id'],
                    'relationship_type' => $relationship['relationship_type'],
                ]);
            }
            
            // Obtener el formulario usando el ID proporcionado
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
            \Illuminate\Support\Facades\Log::info('Sociograma completado', [
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
            \Illuminate\Support\Facades\Log::error('Error al guardar sociograma: ' . $e->getMessage());
            
            // Devolver respuesta de error
            return response()->json(['error' => 'Error al guardar las relaciones: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Eliminar una relación específica.
     */
    public function destroy($id)
    {
        $relationship = SociogramRelationship::findOrFail($id);
        $relationship->delete();

        return response()->json(['message' => 'Relación eliminada correctamente.'], 200);
    }
    //ver relaciones para hacer el sociograma
    public function getRelationships()
    {
        $relationships = SociogramRelationship::with(['user', 'peer', 'question'])
            ->get()
            ->makeHidden(['user', 'peer', 'question']);

        return response()->json($relationships);
    }
}
