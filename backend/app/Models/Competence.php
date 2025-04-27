<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competence extends Model
{
    protected $fillable = ['name'];

    // Una competencia puede estar asociada a muchas preguntas
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
