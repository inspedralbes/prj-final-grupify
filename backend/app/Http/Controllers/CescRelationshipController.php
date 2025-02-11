<?php

namespace App\Http\Controllers;

use App\Models\CescRelationship;
use App\Models\CescResult;
use App\Models\Form;
use App\Models\User;
use App\Models\TagCesc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CescRelationshipController extends Controller
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

            // Obtener las relaciones Cesc de los usuarios encontrados
            $relationships = CescRelationship::whereIn('user_id', $userIds)
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
                                            'tag_id' => $relationship->tag_id, // Se cambió de relationship_type a tag_id
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
            $relationships = CescRelationship::with([
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
                                            'tag_id' => $relationship->tag_id, 
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

            // Obtener los IDs únicos de los usuarios que han respondido el formulario
            $userIds = CescRelationship::whereHas('question', function ($query) use ($formId) {
                $query->where('form_id', $formId); // Relación con preguntas dentro del formulario
            })
                ->pluck('user_id')
                ->unique();

            // Verificar si existen usuarios
            if ($userIds->isEmpty()) {
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

        // Obtener las relaciones Cesc del usuario que pertenecen a las preguntas del formulario
        $relationships = CescRelationship::where('user_id', $userId)
            ->whereIn('question_id', $questions->pluck('id')) // Filtrar solo las relaciones con preguntas del formulario
            ->with(['peer', 'question']) // Cargar las relaciones con peers y preguntas
            ->get();

        // Verificar si existen relaciones para el usuario
        if ($relationships->isEmpty()) {
            return response()->json(['message' => 'El usuario no tiene relaciones Cesc registradas para este formulario.'], 404);
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
                        'tag_id' => $relationship->tag_id, // Se cambió de relationship_type a tag_id
                    ];
                }),
            ];
        });

        // Devolver las relaciones con el título del formulario
        return response()->json([
            'form_title' => $form->title, // Suponiendo que el modelo Form tiene un campo 'title'
            'user_name' => $user->name,
            'user_lastname' => $user->last_name,
            'relationships' => $formattedRelationships,
        ], 200);
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
        'relationships' => 'required|array',
        'relationships.*.peer_id' => 'required|exists:users,id',
        'relationships.*.question_id' => 'required|exists:questions,id',
        'relationships.*.tag_id' => 'required|exists:tags_cesc,id', // Cambio de relationship_type a tag_id
    ]);

    foreach ($data['relationships'] as $relationship) {
        CescRelationship::create([
            'user_id' => $data['user_id'],
            'peer_id' => $relationship['peer_id'],
            'question_id' => $relationship['question_id'],
            'tag_id' => $relationship['tag_id'], // Usamos tag_id en lugar de relationship_type
        ]);
    }

    $form = Form::find(2); // Cambio aquí de 3 a 2
    if ($form) {
        $form->increment('responses_count');
    } else {
        return response()->json(['error' => 'Formulario Cesc no encontrado.'], 404);
    }

    $user = User::find($data['user_id']);
    if ($user) {
        $user->forms()->updateExistingPivot($form->id, ['answered' => true]);
    } else {
        return response()->json(['error' => 'Usuario no encontrado.'], 404);
    }

    // Devolver una respuesta exitosa
    return response()->json(['message' => 'Relaciones guardadas, contador de respuestas actualizado y formulario marcado como respondido.'], 201);
}

/**
 * Eliminar una relación específica.
 */
public function destroy($id)
{
    $relationship = CescRelationship::findOrFail($id);
    $relationship->delete();

    return response()->json(['message' => 'Relación eliminada correctamente.'], 200);
}

/**
 * Ver relaciones para hacer el Cesc.
 */
public function getRelationships()
{
    $relationships = CescRelationship::with(['user', 'peer', 'question', 'tag'])
        ->get()
        ->makeHidden(['user', 'peer', 'question', 'tag']); // Asegúrate de que los datos no sean revelados

    return response()->json($relationships);
}

public function calcularResultados()
{
    // Obtener el recuento de votos agrupado por peer_id y tag_id
    $resultados = CescRelationship::select('peer_id', 'tag_id', \DB::raw('COUNT(*) as vote_count'))
        ->groupBy('peer_id', 'tag_id')
        ->get();

    // Guardar los resultados en la tabla cesc_results
    foreach ($resultados as $resultado) {
        CescResult::updateOrCreate(
            ['peer_id' => $resultado->peer_id, 'tag_id' => $resultado->tag_id],
            ['vote_count' => $resultado->vote_count]
        );
    }

    return response()->json(['message' => 'Resultados actualizados']);
}

public function verResultados()
{
    $resultados = CescResult::with(['peer', 'tag'])->get();
    
    return response()->json($resultados);
}

}


