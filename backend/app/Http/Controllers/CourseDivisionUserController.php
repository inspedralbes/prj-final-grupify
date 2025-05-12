<?php

namespace App\Http\Controllers;

use App\Models\CourseDivisionUser;
use App\Models\User;
use App\Models\Course;
use App\Models\Division;
use Illuminate\Http\Request;

class CourseDivisionUserController extends Controller
{
    // Listar las asignaciones
    public function index(Request $request)
    {
        $query = CourseDivisionUser::with(['course', 'division', 'user']);
        
        // Filtrar por usuario específico si se proporciona user_id
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filtrar por curso específico si se proporciona course_id
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }
        
        // Filtrar por división específica si se proporciona division_id
        if ($request->has('division_id')) {
            $query->where('division_id', $request->division_id);
        }
        
        return response()->json($query->get());
    }

    // Crear una nueva asignación
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'division_id' => 'required|exists:divisions,id',
            'user_id' => 'required|exists:users,id',
        ]);

        // Verificar si ya existe la asignación para evitar duplicados
        $existingAssignment = CourseDivisionUser::where('user_id', $validated['user_id'])
            ->where('course_id', $validated['course_id'])
            ->where('division_id', $validated['division_id'])
            ->first();
            
        if ($existingAssignment) {
            return response()->json([
                'message' => 'Esta asignación ya existe para este usuario',
                'data' => $existingAssignment
            ], 200);
        }

        $assignment = CourseDivisionUser::create($validated);
        
        // También actualizar tabla course_user por compatibilidad
        $user = User::find($validated['user_id']);
        if ($user && !$user->courses()->where('courses.id', $validated['course_id'])->exists()) {
            $user->courses()->attach($validated['course_id']);
        }

        return response()->json([
            'message' => 'Asignación creada correctamente',
            'data' => $assignment->load(['course', 'division', 'user'])
        ], 201);
    }
    
    // Mostrar una asignación específica
    public function show($id)
    {
        $assignment = CourseDivisionUser::with(['course', 'division', 'user'])->find($id);
        
        if (!$assignment) {
            return response()->json(['message' => 'Asignación no encontrada'], 404);
        }
        
        return response()->json($assignment);
    }
    
    // Actualizar una asignación existente
    public function update(Request $request, $id)
    {
        $assignment = CourseDivisionUser::find($id);
        
        if (!$assignment) {
            return response()->json(['message' => 'Asignación no encontrada'], 404);
        }
        
        $validated = $request->validate([
            'course_id' => 'sometimes|required|exists:courses,id',
            'division_id' => 'sometimes|required|exists:divisions,id',
            'user_id' => 'sometimes|required|exists:users,id',
        ]);
        
        $assignment->update($validated);
        
        return response()->json([
            'message' => 'Asignación actualizada correctamente',
            'data' => $assignment->load(['course', 'division', 'user'])
        ]);
    }

    // Eliminar una asignación
    public function destroy($id)
    {
        $assignment = CourseDivisionUser::findOrFail($id);
        
        // Guardar datos temporales para la respuesta
        $tempData = $assignment->load(['course', 'division', 'user']);
        
        $assignment->delete();

        return response()->json([
            'message' => 'Asignación eliminada correctamente',
            'data' => $tempData
        ], 200);
    }
    
    // Método para manejar asignaciones masivas
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'assignments' => 'required|array',
            'assignments.*.user_id' => 'required|exists:users,id',
            'assignments.*.course_id' => 'required|exists:courses,id',
            'assignments.*.division_id' => 'required|exists:divisions,id',
        ]);
        
        $results = [];
        
        foreach ($request->assignments as $assignment) {
            // Verificar si ya existe esta asignación
            $existing = CourseDivisionUser::where('user_id', $assignment['user_id'])
                ->where('course_id', $assignment['course_id'])
                ->where('division_id', $assignment['division_id'])
                ->first();
                
            if (!$existing) {
                // Crear nueva asignación
                $newAssignment = CourseDivisionUser::create([
                    'user_id' => $assignment['user_id'],
                    'course_id' => $assignment['course_id'],
                    'division_id' => $assignment['division_id'],
                ]);
                
                // Actualizar course_user por compatibilidad
                $user = User::find($assignment['user_id']);
                if ($user && !$user->courses()->where('courses.id', $assignment['course_id'])->exists()) {
                    $user->courses()->attach($assignment['course_id']);
                }
                
                $results[] = [
                    'status' => 'created',
                    'data' => $newAssignment
                ];
            } else {
                $results[] = [
                    'status' => 'exists',
                    'data' => $existing
                ];
            }
        }
        
        return response()->json([
            'message' => 'Proceso de asignación masiva completado',
            'results' => $results
        ]);
    }
    
    // Método para obtener usuarios por curso y división
    public function getUsersByCourseAndDivision(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'division_id' => 'required|exists:divisions,id',
            'role_id' => 'nullable|exists:roles,id'
        ]);
        
        $query = User::whereHas('courseDivisionUsers', function($q) use ($validated) {
            $q->where('course_id', $validated['course_id'])
              ->where('division_id', $validated['division_id']);
        });
        
        // Filtrar por rol si se proporciona
        if (isset($validated['role_id'])) {
            $query->where('role_id', $validated['role_id']);
        }
        
        $users = $query->with(['role', 'courseDivisionUsers.course', 'courseDivisionUsers.division'])->get();
        
        return response()->json($users);
    }
}
