<?php

namespace App\Http\Controllers;

use App\Models\CourseDivisionUser;
use Illuminate\Http\Request;

class CourseDivisionUserController extends Controller
{
    // Listar las asignaciones
    public function index()
    {
        return response()->json(CourseDivisionUser::with(['course', 'division', 'user'])->get());
    }

    // Crear una nueva asignación
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'division_id' => 'required|exists:divisions,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $assignment = CourseDivisionUser::create($validated);

        return response()->json($assignment, 201);
    }

    // Eliminar una asignación
    public function destroy($id)
    {
        $assignment = CourseDivisionUser::findOrFail($id);
        $assignment->delete();

        return response()->json(null, 204);
    }
}
