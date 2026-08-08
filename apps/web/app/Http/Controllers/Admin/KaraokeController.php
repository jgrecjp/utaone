<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UtaOneApi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class KaraokeController extends Controller
{
    public function __construct(private readonly UtaOneApi $api)
    {
    }

    public function index(): View
    {
        try {
            return view('admin.karaoke.index', ['songs' => $this->api->songs(), 'apiError' => null]);
        } catch (Throwable $exception) {
            report($exception);
            return view('admin.karaoke.index', ['songs' => [], 'apiError' => 'Python APIへ接続できません。']);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'artist' => ['required', 'string', 'max:255'],
            'difficulty' => ['required', 'integer', 'between:1,10'],
            'original' => ['required', 'file', 'mimes:wav,mp3', 'max:256000'],
            'instrumental' => ['required', 'file', 'mimes:wav,mp3', 'max:256000'],
            'vocal' => ['required', 'file', 'mimes:wav,mp3', 'max:256000'],
            'lyrics' => ['required', 'file', 'mimes:txt', 'max:2048'],
        ]);

        try {
            $song = $this->api->createSong($request->only('title', 'artist', 'difficulty'));
            foreach (['original', 'instrumental', 'vocal', 'lyrics'] as $kind) {
                $this->api->uploadAsset($song['id'], $kind, $validated[$kind]);
            }
            $job = $this->api->enqueue($song['id']);
            return redirect()->route('admin.karaoke.index')
                ->with('status', "解析ジョブ #{$job['id']} を開始しました。");
        } catch (Throwable $exception) {
            report($exception);
            return back()->withInput()->withErrors(['upload' => '登録に失敗しました。APIとワーカーのログを確認してください。']);
        }
    }

    public function review(int $songId): View
    {
        return view('admin.karaoke.review', $this->api->timeline($songId));
    }

    public function updateTimeline(Request $request, int $songId): RedirectResponse
    {
        $validated = $request->validate([
            'segments' => ['required', 'array'],
            'segments.*.position' => ['required', 'integer', 'min:0'],
            'segments.*.text' => ['required', 'string', 'max:1000'],
            'segments.*.start_ms' => ['required', 'integer', 'min:0'],
            'segments.*.end_ms' => ['required', 'integer', 'min:1'],
        ]);
        $this->api->updateTimeline($songId, $validated['segments']);
        return back()->with('status', '歌詞タイムラインを保存しました。');
    }

    public function publish(int $songId): RedirectResponse
    {
        $this->api->publish($songId);
        return redirect()->route('admin.karaoke.index')->with('status', '楽曲を公開しました。');
    }
}
