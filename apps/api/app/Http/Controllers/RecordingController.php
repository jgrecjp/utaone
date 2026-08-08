<?php

namespace App\Http\Controllers;

use App\Support\StoresMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecordingController extends Controller
{
    use StoresMedia;

    public function store(Request $request, int $songId): JsonResponse
    {
        $userId = (string) $request->header('X-App-User-Id', '');
        abort_if($userId === '' || strlen($userId) > 255, 422, 'X-App-User-Id is required');
        abort_unless(DB::table('songs')->where('id', $songId)->where('status', 'published')->exists(), 404, 'Published song not found');
        if (config('utaone.require_subscription')) {
            abort_unless(DB::table('subscriptions')->where('app_user_id', $userId)->where('entitlement_id', config('utaone.revenuecat_entitlement_id'))->where('is_active', 1)->exists(), 403, 'Active premium subscription required');
        }
        $data = $request->validate(['recording' => ['required', 'file', 'max:102400']]);
        $file = $data['recording'];
        [$path] = $this->storeUpload($file, $songId, 'recording');
        [$recordingId,$jobId] = DB::transaction(function () use ($userId, $songId, $path) {
            $recordingId = DB::table('recordings')->insertGetId(['app_user_id' => $userId, 'song_id' => $songId, 'storage_path' => $path, 'status' => 'queued', 'score_detail_json' => '{}', 'created_at' => now()]);
            $jobId = DB::table('processing_jobs')->insertGetId(['song_id' => $songId, 'recording_id' => $recordingId, 'job_type' => 'score_recording', 'status' => 'queued', 'progress' => 0, 'result_json' => '{}', 'created_at' => now(), 'updated_at' => now()]);

            return [$recordingId, $jobId];
        });

        return response()->json(['recording_id' => $recordingId, 'job_id' => $jobId, 'status' => 'queued']);
    }

    public function show(Request $request, int $recordingId): JsonResponse
    {
        $userId = (string) $request->header('X-App-User-Id', '');
        $row = DB::table('recordings')->where('id', $recordingId)->where('app_user_id', $userId)->first(['id', 'song_id', 'status', 'score', 'score_detail_json', 'created_at']);
        abort_if(! $row, 404, 'Recording not found');
        $result = (array) $row;
        $result['score_detail'] = json_decode($result['score_detail_json'], true) ?: [];
        unset($result['score_detail_json']);

        return response()->json($result);
    }
}
