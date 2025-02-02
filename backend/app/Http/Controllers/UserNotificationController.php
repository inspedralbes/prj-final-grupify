<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use App\Jobs\ProcessScheduledNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserNotificationController extends Controller
{
    public function index(Request $request)
    {
        // Solo se muestran notificaciones que ya han sido enviadas o
        // aquellas sin fecha programada, o cuya fecha programada ya pasó.
        $notifications = UserNotification::where(function ($query) {
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
}
