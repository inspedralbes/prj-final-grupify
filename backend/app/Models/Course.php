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
        return $this->belongsToMany(User::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class);
    }

    public function divisions()
    {
        return $this->belongsToMany(Division::class,'course_division','course_id', 'division_id');
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
