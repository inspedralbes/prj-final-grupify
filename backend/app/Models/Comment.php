<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{

    use HasFactory;

    protected $fillable = ['teacher_id', 'content'];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'comment_user', 'comment_id', 'student_id');
    }
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'comments_groups', 'comment_id', 'id_group');
    }
}
