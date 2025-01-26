<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    // Crear un nuevo comentario
    public function store(Request $request)
    {
        // Validación de entrada
        $validator = Validator::make($request->all(), [
            'teacher_id' => 'required|exists:users,id,role_id,1', // Asegura que el teacher_id sea un profesor
            'student_id' => 'required|exists:users,id,role_id,2', // Asegura que student_id sea un alumno
            'content' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Crear el comentario
        $comment = Comment::create([
            'teacher_id' => $request->teacher_id,
            'content' => $request->content,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Relacionar los estudiantes con el comentario (en la tabla intermedia) y agregar timestamps
        $comment->students()->attach($request->student_id, ['created_at' => now(), 'updated_at' => now()]);

        return response()->json(['message' => 'Comentario creado exitosamente', 'comment' => $comment], 201);
    }

    // Obtener todos los comentarios de un alumno
    public function getCommentsForStudent($studentId)
    {
        // Buscar el alumno por ID
        $student = User::findOrFail($studentId);

        // Obtener los comentarios del alumno
        $comments = $student->studentComments;

        return response()->json(['comments' => $comments]);
    }

    // Editar un comentario
    public function update(Request $request, $commentId)
    {
        // Validar los datos
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Encontrar el comentario
        $comment = Comment::findOrFail($commentId);

        // Actualizar el comentario
        $comment->content = $request->content;
        $comment->save();

        return response()->json(['message' => 'Comentario actualizado exitosamente', 'comment' => $comment]);
    }

    // Eliminar un comentario
    public function destroy($commentId)
    {
        // Encontrar el comentario
        $comment = Comment::findOrFail($commentId);

        // Eliminar el comentario
        $comment->delete();

        return response()->json(['message' => 'Comentario eliminado exitosamente']);
    }
}
