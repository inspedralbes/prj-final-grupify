<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagCescController extends Controller
{
    use HasFactory;

    // Define la tabla que este modelo representará
    protected $table = 'tags_cesc';

    // Define los campos que se pueden asignar masivamente
    protected $fillable = ['name'];

    /**
     * Relación con CescRelationship
     */
    public function cescRelationships()
    {
        return $this->hasMany(CescRelationship::class, 'tag_id');
    }
}
