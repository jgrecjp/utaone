# WSL（Ubuntu）でのローカル構築

この手順はWindows 11、WSL 2、Ubuntu、Docker Desktopを使う初心者向けです。API、ワーカー、LaravelはUbuntu上で動かし、Windowsのブラウザーから確認します。

## 1. WSLとUbuntuを準備する

管理者としてPowerShellを開き、次を実行します。

```powershell
wsl --update
wsl --install -d Ubuntu
```

Windowsを再起動し、Ubuntuを起動してユーザー名とパスワードを決めます。PowerShellでWSL 2になっていることを確認します。

```powershell
wsl --list --verbose
```

Ubuntuの`VERSION`が`2`でない場合は次を実行します。

```powershell
wsl --set-version Ubuntu 2
```

## 2. Docker Desktopを設定する

Docker Desktopをインストールし、次を有効にします。

1. Settings → General → Use the WSL 2 based engine
2. Settings → Resources → WSL Integration → Ubuntu

この構成ではUbuntu内へ別のDocker Engineをインストールしません。Ubuntuで確認します。

```bash
docker --version
docker compose version
```

## 3. Ubuntu内へソースを置く

I/O性能とファイル権限の問題を避けるため、`/mnt/c`ではなくUbuntuのホームディレクトリに置きます。

```bash
sudo apt update
sudo apt install -y git curl unzip openssl php-cli php-sqlite3 php-mbstring php-xml php-curl composer
mkdir -p ~/projects
cd ~/projects
git clone <新しいGitHubリポジトリのURL> utaone
cd utaone
```

VS Codeを使う場合はWSL拡張を入れ、Ubuntuで`code .`を実行します。

## 4. APIとワーカーを起動する

```bash
cp .env.example .env
openssl rand -hex 32
nano .env
```

表示された乱数を`UTAONE_ADMIN_API_TOKEN`へ貼り、Gemini APIキーも設定します。

```dotenv
UTAONE_ADMIN_API_TOKEN=生成した乱数
GEMINI_API_KEY=Google AI Studioで発行したキー
GEMINI_AUDIO_MODEL=gemini-3.6-flash
```

保存後に起動します。

```bash
docker compose up --build -d
docker compose ps
curl http://localhost:8000/health
```

`{"status":"ok"}`が返れば成功です。初回ビルドには数分かかることがあります。

## 5. Laravelを起動する

別のUbuntuターミナルを開きます。

```bash
cd ~/projects/utaone/apps/web
cp .env.example .env
composer install
touch database/database.sqlite
php artisan key:generate
php artisan migrate
nano .env
```

`apps/web/.env`の`UTAONE_ADMIN_API_TOKEN`をルート`.env`と同じ値にします。その後、Laravelを起動します。

```bash
php artisan serve --host=0.0.0.0 --port=8080
```

Windowsのブラウザーで`http://localhost:8080`を開きます。最初のユーザー登録と管理者化は[管理画面ガイド](admin-guide.md)に従ってください。

## 6. Flutterアプリから接続する

AndroidエミュレーターはWindows版Android Studioで動かすのが簡単です。エミュレーターからホストのAPIへ接続するURLは`http://10.0.2.2:8000`です。実機の場合はPCのLAN内IPアドレスを指定し、Windowsファイアウォールも確認します。

Flutter SDKをWindowsに置く場合は、モバイル開発用にWindows側へ別途cloneする方法が最も単純です。APIと管理画面だけをWSL側で起動します。iOSのローカルビルドにはMacとXcodeが必要で、WindowsではGitHub Actionsの署名なしビルドを利用できます。

## 7. 終了と再起動

Laravelは`Ctrl+C`で終了します。APIとワーカーは次で停止します。

```bash
cd ~/projects/utaone
docker compose down
```

保存データを残したまま再開する場合は`docker compose up -d`です。`docker compose down -v`はSQLiteと音源を含むボリュームを削除するため、通常は実行しないでください。

問題が起きた場合は[トラブルシューティング](troubleshooting.md)を参照してください。
