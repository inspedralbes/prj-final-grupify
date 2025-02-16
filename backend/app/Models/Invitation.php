<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'course_id',
        'division_id',
        'token',
    ];

    // Relación con el profesor (usuario que generó la invitación)
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Relación con el curso
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Relación con la división
    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
