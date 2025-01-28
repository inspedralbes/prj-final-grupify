<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseDivision extends Model
{
    use HasFactory;

    // Laravel infiere automáticamente el nombre de la tabla como "course_divisions".
    // Si tu tabla es "course_division", indícalo explícitamente:
    protected $table = 'course_division';

    // Si necesitas declarar campos asignables, usa fillable
    protected $fillable = ['course_id', 'division_id'];

    // Relaciones
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
