<?php

namespace App\Http\Controllers;

use App\Support\StoresMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminSongController extends Controller
{
    use StoresMedia;

    public function index(): JsonResponse
    {
        return response()->json(DB::table('songs')->orderByDesc('created_at')->get(['id', 'title', 'artist', 'status', 'difficulty']));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:255'], 'artist' => ['required', 'string', 'max:255'], 'difficulty' => ['sometimes', 'integer', 'between:1,10']]);
        $id = DB::table('songs')->insertGetId([...$data, 'difficulty' => $data['difficulty'] ?? 1, 'created_at' => now(), 'updated_at' => now()]);

        return response()->json((array) DB::table('songs')->find($id, ['id', 'title', 'artist', 'status', 'difficulty']));
    }

    public function upload(Request $request, int $songId): JsonResponse
    {
        abort_unless(DB::table('songs')->where('id', $songId)->exists(), 404, 'Song not found');
        $data = $request->validate(['kind' => ['required', 'in:original,instrumental,vocal,lyrics'], 'asset' => ['required', 'file', 'max:256000']]);
        $file = $request->file('asset');
        $mime = strtolower((string) ($file->getMimeType() ?: 'application/octet-stream'));
        $allowed = $data['kind'] === 'lyrics' ? ['text/plain', 'application/octet-stream'] : ['audio/wav', 'audio/x-wav', 'audio/wave', 'audio/mpeg', 'audio/mp3', 'application/octet-stream'];
        abort_unless(in_array($mime, $allowed, true), 415, $data['kind'] === 'lyrics' ? 'Lyrics must be a text file' : 'Audio must be WAV or MP3');
        [$path, $digest] = $this->storeUpload($file, $songId, $data['kind']);
        $id = DB::table('song_assets')->insertGetId(['song_id' => $songId, 'kind' => $data['kind'], 'original_name' => $file->getClientOriginalName(), 'storage_path' => $path, 'mime_type' => $mime, 'sha256' => $digest, 'metadata_json' => '{}', 'created_at' => now()]);

        return response()->json(['id' => $id, 'kind' => $data['kind'], 'sha256' => $digest]);
    }

    public function enqueue(int $songId): JsonResponse
    {
        $missing = array_values(array_diff(['original', 'instrumental', 'vocal', 'lyrics'], DB::table('song_assets')->where('song_id', $songId)->pluck('kind')->all()));
        if ($missing) {
            return response()->json(['detail' => ['missing_assets' => $missing]], 409);
        }
        $id = DB::transaction(function () use ($songId) {
            $id = DB::table('processing_jobs')->insertGetId(['song_id' => $songId, 'job_type' => 'karaoke_build', 'status' => 'queued', 'progress' => 0, 'result_json' => '{}', 'created_at' => now(), 'updated_at' => now()]);
            DB::table('songs')->where('id', $songId)->update(['status' => 'analyzing', 'updated_at' => now()]);

            return $id;
        });

        return response()->json((array) DB::table('processing_jobs')->find($id, ['id', 'song_id', 'job_type', 'status', 'progress', 'error_message']));
    }

    public function job(int $jobId): JsonResponse
    {
        $job = DB::table('processing_jobs')->find($jobId, ['id', 'song_id', 'job_type', 'status', 'progress', 'error_message']);
        abort_if(! $job, 404, 'Job not found');

        return response()->json($job);
    }

    public function timeline(int $songId): JsonResponse
    {
        return response()->json(['song_id' => $songId, 'segments' => DB::table('lyric_segments')->where('song_id', $songId)->where('version', 1)->orderBy('position')->get(['position', 'text', 'start_ms', 'end_ms', 'confidence'])]);
    }

    public function updateTimeline(Request $request, int $songId): JsonResponse
    {
        $data = $request->validate(['segments' => ['required', 'array'], 'segments.*.position' => ['required', 'integer', 'min:0'], 'segments.*.text' => ['required', 'string', 'max:1000'], 'segments.*.start_ms' => ['required', 'integer', 'min:0'], 'segments.*.end_ms' => ['required', 'integer', 'min:1']]);
        $segments = collect($data['segments'])->sortBy('position')->values();
        $previousEnd = 0;
        foreach ($segments as $position => $segment) {
            if ($segment['position'] !== $position || $segment['start_ms'] < $previousEnd || $segment['end_ms'] <= $segment['start_ms']) {
                return response()->json(['detail' => 'Timeline segments must be contiguous in position and ordered in time'], 422);
            }
            $previousEnd = $segment['end_ms'];
        }
        DB::transaction(function () use ($songId, $segments) {
            DB::table('lyric_segments')->where('song_id', $songId)->where('version', 1)->delete();
            foreach ($segments as $segment) {
                DB::table('lyric_segments')->insert([...$segment, 'song_id' => $songId, 'version' => 1, 'confidence' => 1.0]);
            }
            DB::table('karaoke_releases')->where('song_id', $songId)->where('version', 1)->update(['timeline_json' => json_encode($segments->all(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)]);
        });

        return response()->json(['updated' => $segments->count()]);
    }

    public function publish(int $songId): JsonResponse
    {
        $song = DB::table('songs')->find($songId);
        $release = DB::table('karaoke_releases')->where('song_id', $songId)->where('version', 1)->first();
        abort_if(! $song || ! $release, 404, 'Song or generated release not found');
        if (! in_array($song->status, ['review_required', 'published'], true)) {
            return response()->json(['detail' => 'Song is not ready for publication'], 409);
        }
        DB::transaction(function () use ($songId, $release) {
            DB::table('karaoke_releases')->where('id', $release->id)->update(['published_at' => now()]);
            DB::table('songs')->where('id', $songId)->update(['status' => 'published', 'updated_at' => now()]);
        });

        return response()->json(['published' => true]);
    }
}
