<?php

namespace App\Jobs;

use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessScheduledNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        // Solo procesar si la notificación sigue pendiente
        if ($this->notification->status === 'pending') {
            // Lógica de envío aquí...

            $this->notification->update(['status' => 'sent']);
        }
    }
}
