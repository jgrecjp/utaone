@extends('layouts.okapp')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3>{{ $song->title }} - {{ $song->artist }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <!-- プレーヤーエリア -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-center mb-3">
                            <img src="{{ $song->thumbnail ? asset('storage/' . $song->thumbnail) : asset('images/default-thumbnail.jpg') }}" alt="{{ $song->title }}" style="max-width: 300px;">
                        </div>

                        <!-- オーディオプレーヤー（非表示） -->
                        <audio id="audio-player" preload="auto">
                            <source src="{{ asset('storage/' . $song->instrumental_file_path) }}" type="audio/mp3">
                            お使いのブラウザはオーディオ要素をサポートしていません。
                        </audio>

                        <!-- プレーヤーコントロール -->
                        <div class="d-flex justify-content-center my-3">
                            <button id="start-singing" class="btn btn-primary mx-2">歌い始める</button>
                            <button id="stop-singing" class="btn btn-danger mx-2" disabled>停止</button>
                        </div>

                        <!-- ステータス表示 -->
                        <div class="text-center">
                            <div id="recording-status" class="alert alert-info" style="display: none;"></div>
                            <div id="score-message" class="alert alert-success" style="display: none;"></div>
                            <div id="recording-link" class="mt-2" style="display: none;"></div>
                        </div>

                        <!-- iOSユーザー向け注意事項 -->
                        <div id="ios-notice" class="alert alert-warning" style="display: none;">
                            <p><strong>iOSユーザーの方へ</strong></p>
                            <p>iOSでは以下の点にご注意ください:</p>
                            <ul>
                                <li>「歌い始める」ボタンを押すと、マイクへのアクセス許可が求められます。必ず「許可」を選択してください。</li>
                                <li>マイクが機能しない場合は、Safariの設定で許可されているか確認してください。</li>
                                <li>ヘッドフォンの使用を推奨します（エコー防止のため）。</li>
                            </ul>
                        </div>
                    </div>

                    <!-- リアルタイムスコア表示 -->
{{--                    <div class="text-center mb-4">--}}
{{--                        <h4>現在のスコア: <span id="current-score">0</span></h4>--}}
{{--                    </div>--}}
                </div>

                <div class="col-md-4">
                    <!-- 歌詞表示エリア -->
                    <div class="card">
                        <div class="card-header">歌詞</div>
                        <div class="card-body">
                            <div id="lyrics-container" style="height: 400px; overflow-y: auto;">
                                <!-- 歌詞はJavaScriptで表示 -->
                            </div>
                            <!-- 歌詞データを非表示で保持 -->
                            <div id="lyrics-data" style="display: none;">{{ $song->lyrics->content }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 録音履歴 -->
            <div class="mt-4">
                <h4>自分の過去の録音</h4>
                <div id="recordings-container">
                    @forelse (Auth::user()->recordings()->where('song_id', $song->id)->latest()->get() as $recording)
                        <div class="card mb-2">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="mb-0">{{ $recording->created_at->format('Y/m/d H:i') }}</p>
                                    </div>
                                    <div>
                                        <audio controls>
                                            <source src="{{ asset('storage/' . $recording->file_path) }}" type="audio/{{ pathinfo($recording->file_path, PATHINFO_EXTENSION) }}">
                                            お使いのブラウザはオーディオをサポートしていません。
                                        </audio>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p>まだ録音がありません。</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="song-id" value="{{ $song->id }}">
@endsection

@section('scripts')
    <script>
        // iOSのSafariかどうかを検出して注意事項を表示
        $(document).ready(function() {
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
            const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

            if (isIOS || isSafari) {
                $('#ios-notice').show();
            }
        });
    </script>
    <script src="{{ asset('js/karaoke-player.js') }}"></script>
@endsection

