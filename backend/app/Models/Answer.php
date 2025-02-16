<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'form_id',
        'question_id',
        'answer',
        'rating',
        'answer_type',
    ];

    protected $casts = [
        'answer' => 'array',  // Para que el campo 'answer' se pueda almacenar como un array (si es JSON)
    ];
    // Relación con el modelo de User (usuario)
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación con el modelo de Question (pregunta)
    public function question() {
        return $this->belongsTo(Question::class);
    }
}
