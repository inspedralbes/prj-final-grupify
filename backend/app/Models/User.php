<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Form;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    protected $fillable = [
        'image',
        'name',
        'last_name',
        'email',
        'password',
        'role_id',

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_user');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_user', 'user_id', 'subject_id');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
    public function divisions()
{
    return $this->belongsToMany(Division::class, 'course_division_user', 'user_id', 'division_id')
        ->withPivot('course_id')
        ->withTimestamps();
}

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user', 'user_id', 'id_group');
    }

    public function forms()
    {
        return $this->belongsToMany(Form::class, 'form_user', 'user_id', 'form_id')
                    ->withPivot('answered', 'course_id', 'division_id')
                    ->withTimestamps();
    }



    public function teacherComments()
    {
        return $this->hasMany(Comment::class, 'teacher_id');
    }

    public function studentComments()
    {
        return $this->belongsToMany(Comment::class, 'comment_user', 'student_id', 'comment_id');
    }

    public function courseDivisions()
    {
        return $this->belongsToMany(CourseDivision::class, 'course_division_user', 'user_id', 'division_id')
            ->withPivot('course_id')
            ->withTimestamps();
    }
}
