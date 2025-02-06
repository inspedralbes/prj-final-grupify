<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BitacoraNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'bitacora_id',
        'user_id',
        'title',
        'content'
    ];

    public function bitacora()
    {
        return $this->belongsTo(Bitacora::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
