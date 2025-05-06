<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    protected $table = 'groups';
    protected $fillable = [
        'name',
        'description',
        'number_of_students',
        'creator_id',
    ];
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user', 'group_id', 'user_id');
    }
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'group_subject', 'group_id', 'subject_id');
    }
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'group_course', 'group_id', 'course_id');
    }
    public function divisions()
    {
        return $this->belongsToMany(Division::class, 'group_division', 'group_id', 'division_id');
    }
    public function comments()
    {
        return $this->belongsToMany(Comment::class, 'comments_groups', 'group_id', 'comment_id');
    }
    public function bitacora()
    {
        return $this->hasOne(Bitacora::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
