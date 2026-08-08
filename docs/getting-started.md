# 初心者向けセットアップ

このページでは、Windowsを主な例としてUtaOneを自分のPCで動かします。コマンドは特に記載がなければ、リポジトリ直下のPowerShellで実行します。

WSL 2のUbuntuを使う場合は、専用の[WSL（Ubuntu）セットアップ](wsl-ubuntu.md)を参照してください。本番サーバーは[本番Ubuntuへの設置](production-ubuntu.md)を参照してください。

## 1. 先に知っておくこと

UtaOneは次の3つを同時に動かします。

| 名前 | 役割 | 開発時URL |
|---|---|---|
| Python API・ワーカー | 音源保存、変換、Gemini解析、採点 | `http://localhost:8000` |
| Laravel | LPと管理画面 | `http://localhost:8080` |
| Flutter | iOS／Androidアプリ | エミュレーターまたは実機 |

最初はAPIとLaravel管理画面まで起動し、最後にFlutterを設定すると分かりやすく進められます。

## 2. 必要なソフト

### 必須

1. Git
2. Docker Desktop
3. Python 3.11以降（Flutter権限設定スクリプト用）
4. PHP 8.2以降
5. Composer 2
6. Flutter stable
7. Android Studio（Android SDKとエミュレーターを含む）

### iOSも開発する場合

iOSのローカルビルドにはMac、Xcode、CocoaPodsが必要です。WindowsだけではiOSアプリをビルドできません。Windows利用者はGitHub ActionsのmacOS runnerで署名なしビルドを作れます。

### インストール確認

```powershell
git --version
docker --version
docker compose version
python --version
php --version
composer --version
flutter --version
flutter doctor
```

`flutter doctor`に赤いエラーがある場合は、Android StudioのSDK設定やライセンス同意を先に済ませます。

## 3. ソースコードを取得

```powershell
git clone <新しいGitHubリポジトリのURL> utaone
Set-Location utaone
```

すでにこのフォルダーを開いている場合、この手順は不要です。

## 4. API・ワーカーの環境変数

サンプルをコピーします。

```powershell
Copy-Item .env.example .env
```

`.env`をテキストエディターで開き、最低限次を変更します。

```dotenv
UTAONE_ADMIN_API_TOKEN=十分に長いランダムな文字列
GEMINI_API_KEY=Google AI Studioで発行したAPIキー
GEMINI_AUDIO_MODEL=gemini-3.6-flash
```

ランダムな管理APIトークンはPowerShellで作れます。

```powershell
[guid]::NewGuid().ToString('N')
```

表示された値を`UTAONE_ADMIN_API_TOKEN`へ貼り付けます。`.env`はGitへコミットしないでください。

Geminiキーが空でも処理は止まりませんが、歌詞タイミングは文字数ベースの仮配置になり、実用精度にはなりません。

## 5. API・ワーカーを起動

Docker Desktopを起動してから実行します。

```powershell
docker compose up --build
```

初回はPythonライブラリとFFmpegをダウンロードするため時間がかかります。次の表示を確認します。

- API：`Uvicorn running on http://0.0.0.0:8000`
- Worker：エラーを出さず待機している

ブラウザーで次を開きます。

- ヘルスチェック：`http://localhost:8000/health`
- API仕様画面：`http://localhost:8000/docs`

停止するときは、そのPowerShellで`Ctrl+C`を押します。完全に停止する場合は別のPowerShellで次を実行します。

```powershell
docker compose down
```

通常は`-v`を付けないでください。`docker compose down -v`はSQLiteとアップロード音源を削除します。

## 6. Laravelを準備

新しいPowerShellを開きます。

```powershell
Set-Location <utaoneの保存場所>\apps\web
Copy-Item .env.example .env
composer install
New-Item database\database.sqlite -ItemType File -Force
php artisan key:generate
php artisan migrate
```

`apps/web/.env`を開き、ルート`.env`と同じ管理APIトークンを設定します。

```dotenv
UTAONE_API_URL=http://127.0.0.1:8000
UTAONE_ADMIN_API_TOKEN=ルート.envと同じ値
```

Laravelを起動します。

```powershell
php artisan serve --port=8080
```

ブラウザーで`http://localhost:8080/register`を開き、管理に使うユーザーを登録します。

## 7. 最初の管理者を設定

Laravelを動かしているPowerShellとは別のPowerShellを開き、`apps/web`へ移動します。

```powershell
php artisan tinker
```

表示された`>`の後へ、登録したメールアドレスを入れて実行します。

```php
App\Models\User::where('email', 'admin@example.com')->update(['is_admin' => true]);
```

`1`と表示されれば成功です。`exit`で終了します。

ログイン後、`http://localhost:8080/admin/karaoke`を開きます。楽曲登録は[管理画面ガイド](admin-guide.md)を参照してください。

## 8. Flutterを準備

新しいPowerShellで実行します。

```powershell
Set-Location <utaoneの保存場所>\apps\mobile
flutter create --platforms=android,ios --org jp.utaone --project-name utaone .
python tool\prepare_platforms.py
flutter pub get
flutter test
flutter analyze
```

`flutter create`は不足しているAndroid／iOSプロジェクトファイルを生成します。既存の`lib`以下がアプリ本体です。

## 9. Androidエミュレーターで起動

RevenueCatを先に[設定](revenuecat.md)し、Android公開SDKキーを取得します。Android Studioでエミュレーターを起動してから実行します。

```powershell
flutter run `
  --dart-define=UTAONE_API_URL=http://10.0.2.2:8000 `
  --dart-define=REVENUECAT_ANDROID_API_KEY=goog_xxxxxxxxx
```

Androidエミュレーターから見た`localhost`はPCではなくエミュレーター自身です。そのため、PCで動くAPIには`10.0.2.2`を使います。

実機ではPCと同じWi-Fiへ接続し、`http://192.168.x.x:8000`のようにPCのLANアドレスを指定します。本番では必ずHTTPSを使用してください。

## 10. 起動確認

次を順に確認します。

1. APIの`/health`が`{"status":"ok"}`を返す
2. Laravelへログインできる
3. `/admin/karaoke`を開ける
4. 4素材を登録できる
5. Workerが解析を完了する
6. 管理画面でタイミングを確認して公開できる
7. Flutterの楽曲一覧に公開曲が表示される
8. 購読後に再生・録音・採点できる

うまくいかない場合は[トラブルシューティング](troubleshooting.md)を参照してください。
