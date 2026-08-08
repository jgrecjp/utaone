# システム構成

## 全体像

```text
Flutter iOS／Android
  ├─ 公開楽曲一覧・音源再生・同期歌詞
  ├─ マイク録音・採点結果
  └─ RevenueCat購入・復元
              │ HTTPS REST API
              ▼
Python FastAPI ───── RevenueCat Webhook
  ├─ SQLiteの所有者
  ├─ 音源・録音ファイル保存
  ├─ 公開音源配信
  └─ 解析・採点ジョブ登録
              │ SQLiteジョブ
              ▼
Python Worker
  ├─ ffprobe検査
  ├─ FFmpeg変換
  ├─ Gemini 3.6 Flash歌詞区間候補
  └─ librosa音程採点

Laravel 12 LP／管理画面
  └─ 管理APIトークンでFastAPIを操作
```

## 責務

### FastAPI

SQLiteへの主な書き込みを担当します。LaravelとFlutterはSQLiteファイルを直接操作せず、APIを通します。ワーカーはジョブ取得と解析結果保存の短いトランザクションだけを行います。

### Worker

重い音声処理をAPIリクエストから分離します。管理画面が解析開始を依頼するとジョブが`queued`になり、Workerが取得します。

### Laravel

管理者認証、素材登録画面、歌詞時刻編集、公開操作を担当します。カラオケデータ本体はFastAPIへ送ります。

### Flutter

公開データだけを取得します。RevenueCatの公開SDKキーを利用し、サーバー秘密情報は保持しません。

## データ保存

- API DB：Docker volume内の`/data/utaone.sqlite3`
- 音源：Docker volume内の`/data/media`
- Laravel DB：`apps/web/database/database.sqlite`

2つのSQLiteは用途が異なります。API DBは楽曲・解析・録音・購読状態、Laravel DBは管理者ログインを保存します。

## 本番拡張

初期段階はSQLiteと共有ボリュームを使用します。複数サーバー化する段階で、API DBをPostgreSQL、ファイルをS3互換Object Storage、ジョブをRedisなどへ移行します。
