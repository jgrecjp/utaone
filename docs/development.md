# 開発環境リファレンス

初めて設置する場合は、先に[初心者向けセットアップ](getting-started.md)を参照してください。

## 対応バージョン

- Python 3.13
- PHP 8.2以降
- Laravel 12.61.1以降（lockは12.65.0）
- Composer 2
- Flutter stable／Dart 3.4以降
- Docker DesktopまたはDocker Engine＋Compose v2
- FFmpeg／ffprobe（Dockerイメージ内に同梱）
- Gemini model：`gemini-3.6-flash`

## API・ワーカー

```powershell
Copy-Item .env.example .env
docker compose up --build
```

- API：`http://localhost:8000`
- SQLiteと音源：Docker volume `utaone_data`

ログ：

```powershell
docker compose logs -f api
docker compose logs -f worker
```

Pythonテスト：

```powershell
$env:PYTHONPATH='services/worker'
python -m unittest discover -s services/worker/tests -v
```

## Laravel APIを個別起動

```powershell
Set-Location apps\api
Copy-Item .env.example .env
composer install
New-Item database\database.sqlite -ItemType File -Force
php artisan key:generate
php artisan migrate
php artisan serve --port=8000
```

## Laravel Webを個別起動

```powershell
Set-Location apps\web
Copy-Item .env.example .env
composer install
New-Item database\database.sqlite -ItemType File -Force
php artisan key:generate
php artisan migrate
php artisan serve --port=8080
```

テストと検査：

```powershell
php artisan route:list
php artisan test
composer validate --no-check-publish
composer audit --locked
```

## Flutter

```powershell
Set-Location apps\mobile
python tool\generate_platforms.py --platforms=android,ios
flutter pub get
dart format lib test
flutter analyze
flutter test
```

Androidエミュレーター：

```powershell
flutter run `
  --dart-define=UTAONE_API_URL=http://10.0.2.2:8000 `
  --dart-define=REVENUECAT_ANDROID_API_KEY=goog_xxx
```

## 主要環境変数

| 変数 | 使用場所 | 秘密 | 説明 |
|---|---|---:|---|
| `DB_DATABASE` | API | いいえ | Laravel API用SQLite |
| `UTAONE_DATABASE_PATH` | Worker | いいえ | Workerが共有するAPI用SQLite |
| `UTAONE_STORAGE_PATH` | API／Worker | いいえ | 音源保存先 |
| `UTAONE_ADMIN_API_TOKEN` | API／Laravel | はい | 管理API認証 |
| `GEMINI_API_KEY` | Worker | はい | Gemini APIキー |
| `GEMINI_AUDIO_MODEL` | Worker | いいえ | 既定`gemini-3.6-flash` |
| `REVENUECAT_WEBHOOK_AUTHORIZATION` | API | はい | Webhook認証値 |
| `REVENUECAT_WEBHOOK_SIGNING_SECRET` | API | はい | HMAC署名検証 |
| `UTAONE_REQUIRE_SUBSCRIPTION` | API | いいえ | 本番では`true` |
| `UTAONE_API_URL` | Laravel／Flutter | いいえ | APIのURL |

## 現在の制約

- SQLiteのためWorkerは低並列運用を前提とします。
- 管理画面のタイムラインは数値入力で、波形UIは未実装です。
- Geminiの同期単位は歌詞行です。
- 採点はアカペラと録音の基本周波数比較による初期版です。
- iOSのGitHub Artifactは署名なしです。
