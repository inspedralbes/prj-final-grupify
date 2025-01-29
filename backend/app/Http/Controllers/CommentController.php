<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\User;
use App\Models\Group;
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
            'id_group' => 'nullable|exists:groups,id', // Asegura que group_id sea un grupo válido (opcional)
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

        // Relacionar el comentario con un estudiante si se proporciona student_id
        if ($request->has('student_id')) {
            $comment->students()->attach($request->student_id, ['created_at' => now(), 'updated_at' => now()]);
        }

        // Relacionar el comentario con un grupo si se proporciona id_group
        if ($request->has('id_group')) {
            $comment->groups()->attach($request->id_group, ['created_at' => now(), 'updated_at' => now()]);
        }

        // Respuesta
        return response()->json([
            'message' => 'Comentario creado exitosamente',
            'comment' => $comment,
            'related_to' => [
                'student' => $request->has('student_id') ? $request->student_id : null,
                'group' => $request->has('id_group') ? $request->id_group : null,
            ]
        ], 201);
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

    // Obtener comentarios de un grupo
    public function getCommentsForGroup($idGroup)
{
    // Intentar obtener el grupo
    $group = Group::findOrFail($idGroup);

    // Comprobar si la relación tiene datos
    $comments = $group->comments;  // Sin usar paginate inicialmente

    // Verificar si realmente hay comentarios
    if ($comments->isEmpty()) {
        return response()->json(['message' => 'No comments found for this group'], 404);
    }

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

    public function addCommentToGroup(Request $request, $idGroup)
{
    // Validar que el grupo exista
    $group = Group::findOrFail($idGroup);

    // Validar los datos de entrada
    $request->validate([
        'teacher_id' => 'required|exists:users,id,role_id,1', // ID del profesor
        'content' => 'required|string|max:1000', // Contenido del comentario
    ]);

    // Crear el comentario
    $comment = Comment::create([
        'teacher_id' => $request->teacher_id,
        'content' => $request->content,
    ]);

    // Asociar el comentario al grupo
    $group->comments()->attach($comment->id, [
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Respuesta
    return response()->json([
        'message' => 'Comentario agregado al grupo exitosamente',
        'comment' => $comment,
        'group' => $group->name,
    ]);
}

}
