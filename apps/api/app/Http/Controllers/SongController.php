<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SongController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(DB::table('songs')->where('status', 'published')->orderByDesc('created_at')->get(['id', 'title', 'artist', 'status', 'difficulty']));
    }

    public function show(int $songId): JsonResponse
    {
        $song = DB::table('songs')->where('id', $songId)->where('status', 'published')->first(['id', 'title', 'artist', 'status', 'difficulty']);
        $release = DB::table('karaoke_releases')->where('song_id', $songId)->whereNotNull('published_at')->orderByDesc('version')->first();
        abort_if(! $song || ! $release, 404, 'Published song not found');

        return response()->json([...((array) $song), 'timeline' => json_decode($release->timeline_json, true, flags: JSON_THROW_ON_ERROR), 'stream_url' => route('songs.stream', $songId)]);
    }

    public function stream(int $songId): BinaryFileResponse
    {
        $asset = DB::table('karaoke_releases as r')->join('song_assets as a', 'a.id', '=', 'r.stream_asset_id')
            ->where('r.song_id', $songId)->whereNotNull('r.published_at')->orderByDesc('r.version')->first(['a.storage_path']);
        abort_if(! $asset || ! is_file($asset->storage_path), 404, 'Published audio not found');

        return response()->file($asset->storage_path, ['Content-Type' => 'audio/mp4']);
    }
}
