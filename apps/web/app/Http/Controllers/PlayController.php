<?php
namespace App\Http\Controllers;

use App\Models\Song;
use App\Models\UserScore;
use App\Models\Recording;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlayController extends Controller
{
    public function show(Song $song)
    {
        return view('songs.play', compact('song'));
    }

    public function saveScore(Request $request)
    {
        $validated = $request->validate([
            'song_id' => 'required|exists:songs,id',
            'score' => 'required|integer|min:0|max:100',
        ]);

        UserScore::create([
            'user_id' => Auth::id(),
            'song_id' => $validated['song_id'],
            'score' => $validated['score'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'スコアが保存されました'
        ]);
    }


    public function saveRecording(Request $request)
    {
// リクエストの検証
        $request->validate([
            'audio_data' => 'required|file|mimes:webm,mp4,mp3,wav,m4a',
            'song_id' => 'required|exists:songs,id',
        ]);

        try {
            // ファイル名の生成（ユーザーID_曲ID_タイムスタンプ）
            $fileName = Auth::id() . '_' . $request->song_id . '_' . time() . '.' . $request->file('audio_data')->extension();

            // ファイルの保存
            $path = $request->file('audio_data')->storeAs('recordings', $fileName, 'public');

            // レコーディングレコードをデータベースに保存
            $recording = Recording::create([
                'user_id' => Auth::id(),
                'song_id' => $request->song_id,
                'file_path' => $path,
            ]);

            return response()->json([
                'success' => true,
                'message' => '録音が保存されました',
                'recording_url' => asset('storage/' . $path),
                'recording_id' => $recording->id
            ]);
        } catch (\Exception $e) {
            \Log::error('録音の保存に失敗しました: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => '録音の保存に失敗しました: ' . $e->getMessage()
            ], 500);
        }
    }
}

