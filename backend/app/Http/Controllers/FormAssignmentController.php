<?php

namespace App\Http\Controllers;

use App\Models\FormAssignment;
use App\Models\User;
use App\Models\Course;
use App\Models\Division;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class FormAssignmentController extends Controller
{
    /**
     * Asignar un formulario a un curso y división
     */
    public function assign(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'form_id' => 'required|exists:forms,id',
            'course_id' => 'required|exists:courses,id',
            'division_id' => 'required|exists:divisions,id',
            'status' => 'boolean',
        ]);
        
        // Asegurar que status esté presente con valor predeterminado
        if (!isset($validated['status'])) {
            $validated['status'] = true; // Activo por defecto
        }

        try {
            $assignment = FormAssignment::create($validated);
            return response()->json(['message' => 'Formulario asignado correctamente', 'assignment' => $assignment], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al asignar el formulario', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener todas las asignaciones de formularios para un profesor
     */
    public function getByTeacher($teacherId)
    {
        try {
            // Verificar si el profesor existe
            $teacher = User::findOrFail($teacherId);
            
            // Obtener asignaciones
            $assignments = FormAssignment::where('teacher_id', $teacherId)
                ->get();
                
            // Cargar relaciones de manera segura
            $result = [];
            foreach ($assignments as $assignment) {
                $item = [
                    'id' => $assignment->id,
                    'teacher_id' => $assignment->teacher_id,
                    'form_id' => $assignment->form_id,
                    'course_id' => $assignment->course_id,
                    'division_id' => $assignment->division_id,
                    'responses_count' => $assignment->responses_count,
                    'status' => $assignment->status ?? true,
                    'created_at' => $assignment->created_at,
                    'updated_at' => $assignment->updated_at,
                ];
                
                // Cargar form si existe
                $form = Form::find($assignment->form_id);
                if ($form) {
                    $item['form'] = [
                        'id' => $form->id,
                        'title' => $form->title,
                        'description' => $form->description ?? '',
                        'status' => $form->status ?? 'active'
                    ];
                }
                
                // Cargar course si existe
                $course = Course::find($assignment->course_id);
                if ($course) {
                    $item['course'] = [
                        'id' => $course->id,
                        'name' => $course->name ?? 'Sin nombre'
                    ];
                }
                
                // Cargar division si existe
                $division = Division::find($assignment->division_id);
                if ($division) {
                    $item['division'] = [
                        'id' => $division->id,
                        'name' => $division->name ?? 'Sin nombre'
                    ];
                }
                
                $result[] = $item;
            }
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener asignaciones de formularios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener una asignación específica con detalles de respuestas
     */
    public function getAssignmentDetails($id)
    {
        $assignment = FormAssignment::with([
            'form', 
            'course', 
            'division',
            'responses.user'
        ])->findOrFail($id);

        // Obtener todos los estudiantes del curso y división
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'alumne');
        })
        ->whereHas('courseDivisionUsers', function($query) use ($assignment) {
            $query->where('course_id', $assignment->course_id)
                ->where('division_id', $assignment->division_id);
        })
        ->with(['formUsers' => function($query) use ($assignment) {
            $query->where('form_id', $assignment->form_id);
        }])
        ->get();

        // Preparar datos de respuesta
        $responseData = [
            'assignment' => $assignment,
            'students' => $students->map(function($student) use ($assignment) {
                $hasAnswered = $student->formUsers->contains(function($formUser) use ($assignment) {
                    return $formUser->form_id == $assignment->form_id && 
                        $formUser->course_id == $assignment->course_id && 
                        $formUser->division_id == $assignment->division_id && 
                        $formUser->answered;
                });

                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'answered' => $hasAnswered,
                ];
            }),
            'stats' => [
                'total' => $students->count(),
                'answered' => $students->filter(function($student) use ($assignment) {
                    return $student->formUsers->contains(function($formUser) use ($assignment) {
                        return $formUser->form_id == $assignment->form_id && 
                            $formUser->course_id == $assignment->course_id && 
                            $formUser->division_id == $assignment->division_id && 
                            $formUser->answered;
                    });
                })->count(),
            ]
        ];

        return response()->json($responseData);
    }

    /**
     * Actualizar el contador de respuestas
     */
    public function updateResponsesCount($assignmentId)
    {
        $assignment = FormAssignment::findOrFail($assignmentId);
        
        // Contar respuestas
        $count = DB::table('form_user')
            ->where('form_id', $assignment->form_id)
            ->where('course_id', $assignment->course_id)
            ->where('division_id', $assignment->division_id)
            ->where('answered', true)
            ->count();
        
        $assignment->responses_count = $count;
        $assignment->save();
        
        return response()->json(['message' => 'Contador actualizado', 'count' => $count]);
    }
    
    /**
     * Actualizar el estado de la asignación del formulario (activar/desactivar)
     */
    public function updateStatus(Request $request, $assignmentId)
    {
        $assignment = FormAssignment::findOrFail($assignmentId);
        
        $validated = $request->validate([
            'status' => 'required|in:0,1',  // Aceptar solo 0 o 1 como valores
        ]);
        
        $assignment->status = $validated['status'];
        $assignment->save();
        
        return response()->json([
            'message' => $validated['status'] == 1 ? 'Formulario activado' : 'Formulario desactivado',
            'assignment' => $assignment
        ]);
    }
}
