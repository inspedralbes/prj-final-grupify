<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $fillable = [
        'division'
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_division_user', 'division_id', 'course_id')
            ->withPivot('user_id')
            ->withTimestamps();
    }

    public function forms()
    {
        return $this->belongsToMany(Form::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'course_division_user', 'division_id', 'user_id')
            ->withPivot('course_id')
            ->withTimestamps();
    }
    
    public function courseDivisionUser()
    {
        return $this->hasMany(CourseDivisionUser::class, 'division_id');
    }
    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }
}
