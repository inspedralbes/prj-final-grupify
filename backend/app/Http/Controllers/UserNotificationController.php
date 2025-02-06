<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use App\Jobs\ProcessScheduledNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserNotificationController extends Controller
{
    public function index(Request $request)
    {
        // Mostrar solo notificaciones ENVIADAS (status = 'sent')
        $notifications = UserNotification::where('status', 'sent')
            ->where(function ($query) {
                $query->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'notifications' => $notifications,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'        => 'required|string|max:255',
            'body'         => 'required|string',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $teacher = $request->user();

        // Si se programa una notificación, se crea en estado "pending",
        // de lo contrario se marca como "sent" para enviarla de inmediato.
        $notification = UserNotification::create([
            'teacher_id'   => $teacher->id,
            'title'        => $request->title,
            'body'         => $request->body,
            'scheduled_at' => $request->scheduled_at,
            'status'       => $request->scheduled_at ? 'pending' : 'sent'
        ]);

        // Si se programó la notificación, se despacha un job que se ejecutará
        // en la fecha indicada para procesarla y actualizar el estado.
        if ($request->scheduled_at) {
            ProcessScheduledNotification::dispatch($notification)
                ->delay($notification->scheduled_at);
        }

        return response()->json([
            'message'      => $request->scheduled_at ? 'Notificación programada correctamente' : 'Notificación enviada correctamente',
            'notification' => $notification,
        ], 201);
    }

    public function teacherNotifications(Request $request)
    {
        $teacher = Auth::user();

        // Obtener todas las notificaciones del profesor (incluyendo pendientes y canceladas)
        $notifications = UserNotification::where('teacher_id', $teacher->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'notifications' => $notifications,
        ], 200);
    }

    public function destroy($id)
    {
        $notification = UserNotification::findOrFail($id);
        $teacher = Auth::user();

        // Verificar que la notificación pertenece al profesor
        if ($notification->teacher_id !== $teacher->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Solo se pueden cancelar notificaciones pendientes
        if ($notification->status !== 'pending') {
            return response()->json(['message' => 'Solo se pueden cancelar notificaciones programadas pendientes'], 400);
        }

        // Marcar como cancelada
        $notification->update(['status' => 'canceled']);

        return response()->json(['message' => 'Notificación cancelada correctamente'], 200);
    }
}