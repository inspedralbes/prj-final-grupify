<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserNotificationController extends Controller
{
    /**
     * Retorna la lista de notificaciones (para los alumnos).
     */
    public function index(Request $request)
    {
        $notifications = UserNotification::orderBy('created_at', 'desc')->get();

        return response()->json([
            'notifications' => $notifications,
        ], 200);
    }

    /**
     * Crea una nueva notificación (solo profesores deberían acceder a este endpoint).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body'  => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Se asume que el usuario autenticado (profesor) está en $request->user()
        $teacher = $request->user();

        $notification = UserNotification::create([
            'teacher_id' => $teacher->id,
            'title'      => $request->title,
            'body'       => $request->body,
        ]);

        return response()->json([
            'message'      => 'Notificación creada correctamente',
            'notification' => $notification,
        ], 201);
    }
}
