<?php

namespace App\Http\Controllers;

use App\Models\SociogramRelationship;
use App\Models\Form;
use App\Models\User;
use Illuminate\Http\Request;

class SociogramRelationshipController extends Controller
{

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

            // Obtener los IDs únicos de los usuarios que han respondido el formulario
            $userIds = SociogramRelationship::whereHas('question', function ($query) use ($formId) {
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
            'relationships' => 'required|array',
            'relationships.*.peer_id' => 'required|exists:users,id',
            'relationships.*.question_id' => 'required|exists:questions,id',
            'relationships.*.relationship_type' => 'required|in:positive,negative',
        ]);

        foreach ($data['relationships'] as $relationship) {
            SociogramRelationship::create([
                'user_id' => $data['user_id'],
                'peer_id' => $relationship['peer_id'],
                'question_id' => $relationship['question_id'],
                'relationship_type' => $relationship['relationship_type'],
            ]);
        }

        $form = Form::find(3);
        if ($form) {
            $form->increment('responses_count');
        } else {
            return response()->json(['error' => 'Formulario sociograma no encontrado.'], 404);
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
        $relationship = SociogramRelationship::findOrFail($id);
        $relationship->delete();

        return response()->json(['message' => 'Relación eliminada correctamente.'], 200);
    }
    //ver relaciones para hacer el sociograma
    public function getRelationships()
    {
        $relationships = SociogramRelationship::with(['user', 'peer', 'question'])
            ->get()
            ->makeHidden(['user', 'peer', 'question']) // Ocultar las relaciones
            ->groupBy('relationship_type');

        return response()->json($relationships);
    }
}
