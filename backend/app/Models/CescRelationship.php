<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CescRelationship extends Model
{
    use HasFactory;

    // Definir la tabla, si se llama de manera distinta a la convencional
    protected $table = 'cesc_relationships';

    // Campos que se pueden llenar de manera masiva
    protected $fillable = [
        'user_id', 'peer_id', 'question_id', 'tag_id'
    ];

    // Relación con el modelo User (Usuario que respondió)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación con el modelo Peer (Estudiante con el que se establece la relación)
    public function peer()
    {
        return $this->belongsTo(User::class, 'peer_id');
    }

    // Relación con el modelo Question (Pregunta relacionada)
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
    
    public function tag()
    {
        return $this->belongsTo(TagCesc::class, 'tag_id');
    }
}



