<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'name'
    ];

    public function users()
    {
        return $this->hasManyThrough(User::class, CourseDivisionUser::class, 'course_id', 'id', 'id', 'user_id');
    }


    public function subjects()
    {
        return $this->belongsToMany(Subject::class);
    }

    public function divisions()
    {
        return $this->belongsToMany(Division::class, 'course_division_user', 'course_id', 'division_id')
            ->withPivot('user_id')
            ->withTimestamps();
    }

    public function forms()
    {
        return $this->belongsToMany(Form::class);
    }
    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }
}
