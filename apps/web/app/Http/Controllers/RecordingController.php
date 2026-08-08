<?php

namespace App\Http\Controllers;

use App\Models\Recording;
use App\Models\UserScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RecordingController extends Controller
{
    /**
     * ユーザーの録音一覧を表示
     */
    public function index()
    {
        $recordings = Auth::user()->recordings()
            ->with('song')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $userScores = Auth::user()->scores;

        return view('user.recordings', compact('recordings', 'userScores'));
    }

    /**
     * 録音を削除
     */
    public function destroy(Recording $recording)
    {
        // 権限チェック
        if ($recording->user_id !== Auth::id()) {
            return redirect()->back()->with('error', '他のユーザーの録音は削除できません。');
        }

        // ファイルの削除
        if (Storage::disk('public')->exists($recording->file_path)) {
            Storage::disk('public')->delete($recording->file_path);
        }

        // レコードの削除
        $recording->delete();

        return redirect()->back()->with('success', '録音が削除されました。');
    }

    /**
     * 録音をダウンロード
     */
    public function download(Recording $recording)
    {
        // 権限チェック
        if ($recording->user_id !== Auth::id() && !Auth::user()->is_admin) {
            return redirect()->back()->with('error', 'アクセス権限がありません。');
        }

        if (Storage::disk('public')->exists($recording->file_path)) {
            return Storage::disk('public')->download(
                $recording->file_path,
                $recording->song->title . ' - ' . $recording->created_at->format('Y-m-d') . '.' . pathinfo($recording->file_path, PATHINFO_EXTENSION)
            );
        }

        return redirect()->back()->with('error', 'ファイルが見つかりません。');
    }
}

