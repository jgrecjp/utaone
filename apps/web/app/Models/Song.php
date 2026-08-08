<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    protected $fillable = [
        'title',
        'artist',
        'audio_file_path',
        'instrumental_file_path',
        'thumbnail',
        'difficulty'
    ];

    public function lyrics()
    {
        return $this->hasOne(Lyric::class);
    }

    public function userScores()
    {
        return $this->hasMany(UserScore::class);
    }
}

