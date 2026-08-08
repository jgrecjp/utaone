<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;

class SongController extends Controller
{
    public function index()
    {
        $songs = Song::orderBy('title')->get();
        return view('songs.index', compact('songs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'audio_file' => 'required|file|mimes:mp3',
            'instrumental_file' => 'required|file|mimes:mp3',
            'lyrics' => 'required|string',
            'thumbnail' => 'nullable|image',
            'difficulty' => 'required|integer|min:1|max:5',
        ]);

        // ファイルの保存処理
        $audioPath = $request->file('audio_file')->store('songs/audio', 'public');
        $instrumentalPath = $request->file('instrumental_file')->store('songs/instrumental', 'public');
        $thumbnailPath = null;

        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('songs/thumbnails', 'public');
        }

        // 曲情報の保存
        $song = Song::create([
            'title' => $validated['title'],
            'artist' => $validated['artist'],
            'audio_file_path' => $audioPath,
            'instrumental_file_path' => $instrumentalPath,
            'thumbnail' => $thumbnailPath,
            'difficulty' => $validated['difficulty'],
        ]);

        // 歌詞情報の保存
        $song->lyrics()->create([
            'content' => $validated['lyrics']
        ]);

        return redirect()->route('songs.index')
            ->with('success', '曲が追加されました。');
    }
}

