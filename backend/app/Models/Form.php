<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{

    protected $fillable = [
        'title',
        'description',
        'status',
        'division'
    ];

    public function course() {
        return $this->belongsToMany(User::class, 'course_user', 'form_id', 'user_id');
    }

    public function divisions(){
        return $this->belongsToMany(Division::class, 'division_form', 'form_id', 'division_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function users()
{
    return $this->belongsToMany(User::class, 'form_user', 'form_id', 'user_id')
                ->withPivot('answered') // Incluir el campo 'answered' de la tabla intermedia
                ->withTimestamps();
}


    public function answers()
    {
        return $this->hasMany(Answer::class, Question::class);
    }

    public static function activeForms()
    {
        return self::where('status', 1)->get(); // Devuelve solo los formularios activos
    }
}
