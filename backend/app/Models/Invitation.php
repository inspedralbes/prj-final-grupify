<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Invitation extends Model
{
    protected $fillable = [
        'token',
        'course_id',
        'division_id',
        'professor_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Relación con el curso asociado a la invitación.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Relación con la división asociada a la invitación.
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Relación con el profesor que creó la invitación.
     */
    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    /**
     * Verifica si la invitación ha caducado.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
