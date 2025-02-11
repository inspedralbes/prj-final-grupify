<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagCesc extends Model
{
    use HasFactory;

    protected $table = 'tags_cesc'; 

    protected $fillable = ['name'];

    public function cescRelationships()
    {
        return $this->hasMany(CescRelationship::class, 'tag_id');
    }
}
