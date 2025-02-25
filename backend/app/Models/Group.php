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
    ];
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user', 'group_id', 'user_id');
    }
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'group_subject', 'group_id', 'id_subject');
    }
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'group_course', 'group_id', 'id_course');
    }
    public function divisions()
    {
        return $this->belongsToMany(Division::class, 'group_division', 'group_id', 'id_division');
    }
    public function comments()
    {
        return $this->belongsToMany(Comment::class, 'comments_groups', 'group_id', 'comment_id');
    }
    public function bitacora()
    {
        return $this->hasOne(Bitacora::class);
    }

}
