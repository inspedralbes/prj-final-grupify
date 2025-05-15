<?php

namespace App\Http\Controllers;

use App\Models\FormUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FormUserController extends Controller
{
    /**
     * Actualiza el estado de respuesta de un formulario para un usuario.
     */
    public function updateAnsweredStatus(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'form_id' => 'required|exists:forms,id',
            'course_id' => 'required|exists:courses,id',
            'division_id' => 'required|exists:divisions,id',
            'answered' => 'required|boolean',
        ]);
        
        try {
            // Buscar el registro FormUser
            $formUser = FormUser::where('user_id', $validated['user_id'])
                ->where('form_id', $validated['form_id'])
                ->where('course_id', $validated['course_id'])
                ->where('division_id', $validated['division_id'])
                ->first();
            
            if (!$formUser) {
                // Si no existe, crear un nuevo registro
                $formUser = FormUser::create([
                    'user_id' => $validated['user_id'],
                    'form_id' => $validated['form_id'],
                    'course_id' => $validated['course_id'],
                    'division_id' => $validated['division_id'],
                    'answered' => $validated['answered'],
                ]);
                
                $result = 'created';
            } else {
                // Si existe, actualizar su estado
                $formUser->answered = $validated['answered'];
                $formUser->save();
                
                $result = 'updated';
            }
            
            return response()->json([
                'message' => 'Estado de respuesta actualizado correctamente',
                'result' => $result,
                'formUser' => $formUser,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar el estado de respuesta: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al actualizar el estado de respuesta',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Obtiene los usuarios que han respondido a un formulario.
     */
    public function getFormResponses(Request $request)
    {
        $validated = $request->validate([
            'form_id' => 'required|exists:forms,id',
            'course_id' => 'required|exists:courses,id',
            'division_id' => 'required|exists:divisions,id',
        ]);
        
        try {
            // Obtener todos los usuarios de este curso/división y su estado de respuesta
            $responses = FormUser::where('form_id', $validated['form_id'])
                ->where('course_id', $validated['course_id'])
                ->where('division_id', $validated['division_id'])
                ->with('user') // Cargar la información del usuario
                ->get()
                ->map(function($formUser) {
                    return [
                        'user_id' => $formUser->user_id,
                        'name' => $formUser->user->name ?? 'N/A',
                        'email' => $formUser->user->email ?? 'N/A',
                        'answered' => $formUser->answered,
                    ];
                });
            
            // Contar las estadísticas
            $total = $responses->count();
            $answered = $responses->where('answered', true)->count();
            
            return response()->json([
                'responses' => $responses,
                'stats' => [
                    'total' => $total,
                    'answered' => $answered,
                    'pending' => $total - $answered,
                    'percentage' => $total > 0 ? round(($answered / $total) * 100, 2) : 0,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener las respuestas del formulario: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al obtener las respuestas del formulario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
