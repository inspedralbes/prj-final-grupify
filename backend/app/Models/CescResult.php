<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TagCesc;

class CescResult extends Model
{
    use HasFactory;

    protected $table = 'cesc_results';

    protected $fillable = ['peer_id', 'tag_id', 'vote_count'];

    public function peer()
    {
        return $this->belongsTo(User::class, 'peer_id');
    }

    public function tag()
    {
        return $this->belongsTo(TagCesc::class, 'tag_id');
    }
}
