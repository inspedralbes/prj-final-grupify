<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'title',
        'body',
        'scheduled_at',
        'status'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    // Relación con el profesor (creador de la notificación)
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Scope para obtener notificaciones pendientes
    public function scopePending($query)
    {
        return $query->where('status', 'pending')
                    ->where('scheduled_at', '<=', now());
    }
}