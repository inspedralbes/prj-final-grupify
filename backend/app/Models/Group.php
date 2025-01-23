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
        return $this->belongsToMany(User::class, 'group_user', 'id_group', 'user_id');
    }
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'group_subject', 'id_group', 'id_subject');
    }
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'group_course', 'id_group', 'id_course');
    }
    public function divisions()
    {
        return $this->belongsToMany(Division::class, 'group_division', 'id_group', 'id_division');
    }
}