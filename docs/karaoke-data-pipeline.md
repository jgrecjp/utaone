# カラオケデータ生成パイプライン

このページは、現在コードで動く処理と今後の拡張を分けて説明します。

## 入力

1. 元音源：WAVまたはMP3
2. カラオケ音源：WAVまたはMP3
3. アカペラ音源：WAVまたはMP3
4. 歌詞：UTF-8 TXT

3音源は形式を混在できます。元ファイルは変更せず、SHA-256、MIMEタイプ、保存先をSQLiteへ記録します。

## 現在実装されている処理

### 1. アップロード

Laravel管理画面がLaravel APIへ4ファイルを送ります。APIはファイルサイズと申告MIMEタイプを確認し、ランダムな内部ファイル名で非公開領域へ保存します。

### 2. ジョブ取得

APIが`processing_jobs`へ`queued`ジョブを作成します。Workerは`BEGIN IMMEDIATE`の短いトランザクションで1件を取得し、`processing`へ変更します。

### 3. 音源検査

`ffprobe`からコーデック、サンプルレート、チャンネル数、ビットレート、再生時間を取得します。現在許可する主な入力はMP3とPCM WAVです。

3音源の最大・最小再生時間差が2秒を超える場合は失敗します。

### 4. 解析用音源

FFmpegで3音源をPCM WAV、48kHz、モノラルへ変換します。

```text
ffmpeg -nostdin -hide_banner -y -i INPUT -vn -map_metadata -1 \
  -ac 1 -ar 48000 -c:a pcm_s16le OUTPUT.wav
```

実装ではコマンド文字列ではなく引数配列を`subprocess.run`へ渡し、タイムアウトと終了コードを検査します。

### 5. 歌詞行タイミング

歌詞のBOM、改行、空行を正規化し、アカペラ解析用WAVと全歌詞行をGemini 3.6 Flashへ送ります。Geminiには原文の全行を保持したJSONを要求します。

各行の出力：

- `position`
- `text`
- `start_ms`
- `end_ms`
- `confidence`

行欠落、順序不正、時刻逆転は受け入れません。Geminiキーがない場合は文字数比率による仮タイムラインを作り、信頼度を0.25にします。

### 6. 配信用音源

カラオケ音源をAAC 192kbpsのM4Aへ変換します。現在の`loudnorm`は1パスです。

### 7. 管理者レビュー

楽曲を`review_required`にし、Laravel管理画面で行単位の開始・終了ミリ秒を修正できるようにします。管理者が公開すると`published`になります。

### 8. アプリ配信

Flutterへ公開するもの：

- 楽曲情報
- 行単位歌詞タイムライン
- 配信用カラオケM4A

元音源とアカペラ音源はアプリへ公開しません。

### 9. 採点

Flutterが録音したM4AをAPIへアップロードし、WorkerがPCM WAVへ変換します。librosaの`pyin`でアカペラと録音の基本周波数を求め、次を計算します。

- Pitch accuracy：音程差
- Voiced coverage：基準有声音に対する歌唱検出率
- Total：Pitch 80%、Coverage 20%

これはMVP用の初期採点であり、テンポずれ補正、キー変更、ビブラート、しゃくり、ロングトーンなどはまだ評価しません。

## 処理状態

| 状態 | 意味 |
|---|---|
| `draft` | 楽曲作成直後 |
| `analyzing` | 解析ジョブ投入後 |
| `review_required` | 自動生成完了、要確認 |
| `published` | アプリ公開済み |
| `failed` | 解析失敗 |

## 現在の主なテーブル

- `songs`
- `song_assets`
- `processing_jobs`
- `lyric_segments`
- `karaoke_releases`
- `recordings`
- `subscriptions`
- `webhook_events`

## 今後の拡張候補

次は設計候補であり、現在は未実装です。

- 強制アライナーを併用した単語・音素単位同期
- 波形を使ったドラッグ式タイムライン編集
- 低信頼区間だけの再解析
- 2パスEBU R128音量正規化
- 素材差し替え、再解析、公開バージョン履歴
- テンポ・キーずれに強いDTW採点
- PostgreSQL、Object Storage、専用Queueへの移行

## セキュリティ

- 管理画面は`is_admin=true`のユーザーだけ利用できます。
- API管理トークンとGeminiキーはサーバーだけに置きます。
- FFmpegへネットワークURLを渡しません。
- 元音源・アカペラを公開APIから配信しません。
- 外部AIへ音源を送る権利と利用条件を事前に確認してください。
