<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseDivisionUser extends Model
{
    use HasFactory;

    // Especifica la tabla asociada si el nombre no es el plural del modelo
    protected $table = 'course_division_user';

    // Campos asignables
    protected $fillable = ['course_id', 'division_id', 'user_id'];

    // Relaciones
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
