<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'title', 'type', 'placeholder', 'context', 'form_id',
    ];

    public function answers(){
        return $this->hasMany(Answer::class);
    }

    public function forms() {
        return $this->belongsTo(Form::class);
    }

    public function options(){
        return $this->hasMany(Option::class);
    }
}
