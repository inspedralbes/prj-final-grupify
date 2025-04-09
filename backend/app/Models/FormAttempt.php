<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'form_id',
        'attempted_at',
        'average_rating',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
