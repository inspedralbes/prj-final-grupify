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
        return $this->belongsToMany(Course::class, 'course_division', 'division_id', 'course_id');
    }

    public function forms()
    {
        return $this->belongsToMany(Form::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }
}
