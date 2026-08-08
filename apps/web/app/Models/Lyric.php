<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lyric extends Model
{
    protected $fillable = ['song_id', 'content'];

    public function song()
    {
        return $this->belongsTo(Song::class);
    }
}


