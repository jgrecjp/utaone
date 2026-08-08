
$(document).ready(function() {
    // 変数の初期化
    let audioPlayer = $('#audio-player')[0];
    let lyricsContainer = $('#lyrics-container');
    let currentLyrics = [];
    let scoreTotal = 0;
    let noteCount = 0;
    let audioContext = null;
    let microphone = null;
    let analyser = null;
    let isRecording = false;
    let mediaRecorder = null;
    let recordedChunks = [];
    let audioStream = null;

    // iOSのSafari互換性チェック
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

    // MediaRecorderの対応確認とポリフィル読み込み
    if (typeof MediaRecorder === 'undefined') {
        // MediaRecorder APIが利用できない場合は代替ライブラリを読み込む
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/audio-recorder-polyfill/dist/audio-recorder-polyfill.min.js';
        document.head.appendChild(script);

        script.onload = function() {
            window.MediaRecorder = AudioRecorderPolyfill;
        };
    }

    // 歌詞データの解析（LRC形式）
    function parseLyrics(lrcText) {
        const lines = lrcText.trim().split('\n');
        const pattern = /\[(\d+):(\d+)\.(\d+)\](.*)/;
        const result = [];

        lines.forEach(line => {
            const match = pattern.exec(line);
            if (match) {
                const min = parseInt(match[1], 10);
                const sec = parseInt(match[2], 10);
                const ms = parseInt(match[3], 10);
                const timeInMs = (min * 60 + sec) * 1000 + ms;
                const text = match[4].trim();

                result.push({
                    time: timeInMs,
                    text: text
                });
            }
        });

        return result.sort((a, b) => a.time - b.time);
    }

    // 歌詞表示の更新

    function updateLyrics() {
        if (!audioPlayer || currentLyrics.length === 0) return;

        const currentTime = audioPlayer.currentTime * 1000;
        let activeIndex = -1;

        // 現在の時間に合う歌詞を探す
        for (let i = 0; i < currentLyrics.length; i++) {
            if (currentLyrics[i].time <= currentTime) {
                activeIndex = i;
            } else {
                break;
            }
        }

        // 歌詞表示の更新
        if (activeIndex >= 0) {
            // アクティブでない歌詞のクラスをまず解除
            lyricsContainer.find('.lyric-line.active').removeClass('active');

            // アクティブな歌詞要素を取得
            const activeElement = lyricsContainer.find('.lyric-line').eq(activeIndex);

            // 次にクラスを追加
            activeElement.addClass('active');

            // スクロール位置を固定ロジックで計算
            const containerHeight = lyricsContainer.height();
            const elementTop = activeElement.position().top;
            const elementHeight = activeElement.outerHeight();
            const currentScroll = lyricsContainer.scrollTop();

            // 表示領域内に収まっているか確認
            const elementBottom = elementTop + elementHeight;
            const viewportBottom = containerHeight;

            // 表示領域外の場合のみスクロール
            if (elementTop < 0 || elementBottom > viewportBottom) {
                // 中央に配置する位置を計算
                const targetScroll = currentScroll + elementTop - (containerHeight - elementHeight) / 2;

                // 滑らかにスクロール
                lyricsContainer.stop().animate({
                    scrollTop: targetScroll
                }, 200, 'linear');
            }
        }
    }
    // マイク初期化とレコーディング開始
    $('#start-singing').on('click', function() {
        if (!audioContext) {
            // iOSのSafariではユーザーインタラクションでAudioContextを生成
            audioContext = new (window.AudioContext || window.webkitAudioContext)();

            // マイクへのアクセス要求
            navigator.mediaDevices.getUserMedia({
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true
                }
            })
                .then(function(stream) {
                    audioStream = stream;
                    microphone = audioContext.createMediaStreamSource(stream);
                    analyser = audioContext.createAnalyser();
                    analyser.fftSize = 2048;
                    microphone.connect(analyser);

                    // MediaRecorderの設定
                    try {
                        const options = { mimeType: 'audio/webm' };
                        mediaRecorder = new MediaRecorder(stream, options);
                    } catch (e) {
                        // WebMがサポートされていない場合、他の形式を試す
                        try {
                            const options = { mimeType: 'audio/mp4' };
                            mediaRecorder = new MediaRecorder(stream, options);
                        } catch (e) {
                            // 最後の手段
                            mediaRecorder = new MediaRecorder(stream);
                        }
                    }

                    // 録音データの収集
                    mediaRecorder.ondataavailable = function(event) {
                        if (event.data.size > 0) {
                            recordedChunks.push(event.data);
                        }
                    };

                    // 録音終了時の処理
                    mediaRecorder.onstop = function() {
                        // 録音データをサーバーに送信
                        saveRecording();
                    };

                    // 録音開始（iOSのSafariは連続記録に制限があるため、
                    // 短いインターバルで定期的に記録する）
                    recordedChunks = [];
                    mediaRecorder.start(1000); // 1秒ごとにデータを分割

                    // 採点開始
                    isRecording = true;
                    startScoring();

                    // 曲の再生開始（iOSではユーザーインタラクションが必要）
                    audioPlayer.play();

                    $(this).prop('disabled', true);
                    $('#stop-singing').prop('disabled', false);

                    // ステータス表示
                    $('#recording-status').text('録音中...').show();
                })
                .catch(function(err) {
                    console.error('マイクへのアクセスが拒否されました:', err);
                    alert('マイクへのアクセスを許可してください。iOSの場合は「設定」アプリからSafariのマイク許可を確認してください。');
                });
        }
    });

    // 歌唱停止と録音終了
    $('#stop-singing').on('click', function() {
        if (isRecording) {
            isRecording = false;
            audioPlayer.pause();

            // 録音を停止
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
            }

            // 音声トラックを停止（メモリリーク防止）
            if (audioStream) {
                audioStream.getTracks().forEach(track => track.stop());
            }

            // 最終スコアの計算と送信
            const finalScore = Math.round(scoreTotal / (noteCount || 1));
            saveScore(finalScore);

            $(this).prop('disabled', true);
            $('#start-singing').prop('disabled', false);

            // ステータス表示
            $('#recording-status').text('録音完了！');
        }
    });

    // 録音データをサーバーに保存
    function saveRecording() {
        if (recordedChunks.length === 0) {
            console.warn('録音データがありません。');
            return;
        }

        // 録音データをBlobに変換
        let mimeType = 'audio/webm';
        if (/Safari/.test(navigator.userAgent) && !/Chrome/.test(navigator.userAgent)) {
            mimeType = 'audio/mp4';
        }

        const blob = new Blob(recordedChunks, { type: mimeType });

        // FormDataで送信
        const formData = new FormData();
        formData.append('audio_data', blob, 'recording.' + mimeType.split('/')[1]);
        formData.append('song_id', $('#song-id').val());
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        // 送信中表示
        $('#recording-status').text('サーバーに送信中...');

        // サーバーに送信
        $.ajax({
            url: '/songs/save-recording',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#recording-status').text('録音が保存されました！');
                    $('#recording-link').html(`<a href="${response.recording_url}" target="_blank">録音を聴く</a>`).show();
                }
            },
            error: function(xhr) {
                console.error('録音の保存に失敗しました:', xhr);
                $('#recording-status').text('録音の保存に失敗しました。');
            }
        });
    }

    // ピッチ検出と採点処理
    function startScoring() {
        if (!isRecording) return;

        const bufferLength = analyser.frequencyBinCount;
        const dataArray = new Uint8Array(bufferLength);

        function detectPitch() {
            if (!isRecording) return;

            analyser.getByteFrequencyData(dataArray);

            // ここで簡易的なピッチ検出と採点を行う
            // 実際のアプリでは、より高度なアルゴリズムを使用することをお勧めします
            let maxValue = 0;
            let maxIndex = 0;

            for (let i = 0; i < bufferLength; i++) {
                if (dataArray[i] > maxValue) {
                    maxValue = dataArray[i];
                    maxIndex = i;
                }
            }

            // 現在の音符との比較と採点（簡易版）
            // 実際のアプリでは、歌詞の各音符にピッチ情報を含める必要があります
            if (maxValue > 50) { // ノイズフィルタリング
                noteCount++;

                // 正確性の計算（デモ用の単純なロジック）
                const accuracy = Math.min(100, Math.round(Math.random() * 40) + 60);
                scoreTotal += accuracy;

                // リアルタイムスコア表示
                $('#current-score').text(Math.round(scoreTotal / noteCount));
            }

            requestAnimationFrame(detectPitch);
        }

        detectPitch();
    }

    // スコアをサーバーに保存
    function saveScore(score) {
        const songId = $('#song-id').val();

        $.ajax({
            url: '/songs/score',
            method: 'POST',
            data: {
                song_id: songId,
                score: score,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#score-message').text('スコア: ' + score + ' 点が保存されました！');
                }
            },
            error: function() {
                $('#score-message').text('スコアの保存に失敗しました。');
            }
        });
    }

    // 歌詞の読み込みと表示
    function loadLyrics() {
        const lyricsText = $('#lyrics-data').text();
        currentLyrics = parseLyrics(lyricsText);

        // 歌詞の表示
        lyricsContainer.empty();
        currentLyrics.forEach(line => {
            lyricsContainer.append(`<div class="lyric-line">${line.text}</div>`);
        });
    }

    // 音楽再生時の歌詞更新
    audioPlayer.addEventListener('timeupdate', updateLyrics);

    // 歌詞の初期ロード
    loadLyrics();

    // iOSのSafariのための特別な処理
    if (isIOS && isSafari) {
        // 無音ファイル再生でAudioContextのロックを解除
        const unlockAudio = function() {
            // 無音の短いオーディオを作成
            const silentAudio = new Audio();
            silentAudio.src = 'data:audio/mp3;base64,//uQxAAAAAAAAAAAAAAAAAAAAAAASW5mbwAAAA8AAAABAAADQgD///////////////////////////////////////////8AAAA5TEFNRTMuMTAwAc0AAAAAAAAAABSAJAJAQgAAgAAAA0L2S2evAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//sQxAADwAABpAAAACAAADSAAAAETEFNRTMuMTAwVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV';

            // 再生を試みる
            silentAudio.play().then(() => {
                // 成功したら一度停止
                silentAudio.pause();
                silentAudio.currentTime = 0;

                // イベントリスナーを削除
                document.removeEventListener('touchstart', unlockAudio);
            }).catch(e => {
                console.log('Audio unlock failed', e);
            });
        };

        // タッチイベントでオーディオのロックを解除
        document.addEventListener('touchstart', unlockAudio);
    }
});

