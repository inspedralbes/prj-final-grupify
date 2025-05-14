<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        // Extraer los datos de la suscripción de la estructura recibida
        // Se asume que la estructura es la generada por subscription.toJSON()
        $data = $request->all();

        $endpoint = $data['endpoint'] ?? null;
        $p256dh   = isset($data['keys']) && isset($data['keys']['p256dh']) ? $data['keys']['p256dh'] : null;
        $auth     = isset($data['keys']) && isset($data['keys']['auth']) ? $data['keys']['auth'] : null;

        $validator = Validator::make(
            [
                'endpoint' => $endpoint,
                'p256dh'   => $p256dh,
                'auth'     => $auth,
            ],
            [
                'endpoint' => 'required|url',
                'p256dh'   => 'required|string',
                'auth'     => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = Auth::user();

        // Crear o actualizar la suscripción
        $subscription = PushSubscription::updateOrCreate(
            [
                'user_id'  => $user->id,
                'endpoint' => $endpoint,
            ],
            [
                'p256dh' => $p256dh,
                'auth'   => $auth,
            ]
        );

        return response()->json([
            'message'      => 'Suscripción guardada correctamente',
            'subscription' => $subscription,
        ], 201);
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();

        // Se espera que en el request se envíe el endpoint de la suscripción a eliminar
        $endpoint = $request->get('endpoint');
        if (!$endpoint) {
            return response()->json(['message' => 'El endpoint es requerido'], 400);
        }

        $subscription = PushSubscription::where('user_id', $user->id)
            ->where('endpoint', $endpoint)
            ->first();

        if ($subscription) {
            $subscription->delete();
            return response()->json(['message' => 'Suscripción eliminada correctamente'], 200);
        }

        return response()->json(['message' => 'Suscripción no encontrada'], 404);
    }
}