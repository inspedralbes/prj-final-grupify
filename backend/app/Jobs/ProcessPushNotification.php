<?php

namespace App\Jobs;

use App\Models\UserNotification;
use App\Models\PushSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable; // <-- Agregado
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class ProcessPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels; // <-- Se agrega Dispatchable

    protected $notification;

    /**
     * Crea una nueva instancia del job.
     *
     * @param  \App\Models\UserNotification  $notification
     * @return void
     */
    public function __construct(UserNotification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Ejecuta el job.
     *
     * @return void
     */
    public function handle()
    {
        // Recuperar todas las suscripciones (en una implementación real puedes filtrar solo los alumnos)
        $subscriptions = PushSubscription::all();

        // Configuración VAPID: asegúrate de tener estas variables en tu archivo .env
        $vapid = [
            'subject'   => 'mailto:example@example.com',
            'publicKey' => env('VAPID_PUBLIC_KEY', 'TU_PUBLIC_VAPID_KEY'),
            'privateKey'=> env('VAPID_PRIVATE_KEY', 'TU_PRIVATE_VAPID_KEY'),
        ];

        $webPush = new WebPush(['VAPID' => $vapid]);

        // Construir el payload a enviar
        $payload = json_encode([
            'title' => $this->notification->title,
            'body'  => $this->notification->body,
            'url'   => env('APP_URL') . '/notifications'
        ]);

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint'       => $sub->endpoint,
                'publicKey'      => $sub->p256dh,
                'authToken'      => $sub->auth,
                'contentEncoding'=> 'aesgcm', // o 'aes128gcm', según convenga
            ]);

            $webPush->queueNotification($subscription, $payload);
        }

        // Envía las notificaciones y procesa los resultados
        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            if (!$report->isSuccess()) {
                \Log::error("Error al enviar notificación a {$endpoint}: {$report->getReason()}");
                // Opcional: eliminar o actualizar suscripciones inválidas
            }
        }
    }
}
