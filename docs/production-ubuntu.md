# 本番Ubuntuへの設置

この手順はUbuntu 24.04 LTSのVPSまたは専用サーバーを想定します。本番構成はCaddyだけを外部公開し、Laravel、FastAPI、ワーカー、SQLiteをDockerネットワーク内で動かします。CaddyがTLS証明書を自動取得します。

## 1. 事前に用意するもの

- Ubuntu 24.04 LTS、メモリ4GB以上を推奨
- SSH鍵で接続できるsudoユーザー
- Web用ドメイン（例：`utaone.example.com`）
- API用ドメイン（例：`api.utaone.example.com`）
- Gemini APIキーとRevenueCatの設定値

両ドメインのAレコードをサーバーのIPv4へ向けます。IPv6を使う場合だけAAAAレコードも設定します。TCPの22、80、443とUDPの443をクラウド側ファイアウォールで許可してください。

## 2. OSとDockerを準備する

```bash
sudo apt update
sudo apt upgrade -y
sudo apt install -y ca-certificates curl git openssl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo \"${UBUNTU_CODENAME:-$VERSION_CODENAME}\") stable" | sudo tee /etc/apt/sources.list.d/docker.list
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
sudo usermod -aG docker "$USER"
```

一度ログアウトして再接続し、確認します。

```bash
docker --version
docker compose version
```

## 3. ソースと本番設定を配置する

```bash
sudo mkdir -p /opt/utaone
sudo chown "$USER":"$USER" /opt/utaone
git clone <新しいGitHubリポジトリのURL> /opt/utaone
cd /opt/utaone
cp .env.production.example .env.production
chmod 600 .env.production
openssl rand -hex 32
openssl rand -hex 32
```

2つの乱数を管理APIトークンとRevenueCat Webhook用シークレットに使います。LaravelのAPP_KEYは次で生成できます。

```bash
printf 'base64:%s\n' "$(openssl rand -base64 32)"
nano .env.production
```

最低限、次を実際の値へ変更します。

```dotenv
WEB_DOMAIN=utaone.example.com
API_DOMAIN=api.utaone.example.com
ACME_EMAIL=admin@example.com
APP_KEY=base64:生成した値
APP_URL=https://utaone.example.com
UTAONE_ADMIN_API_TOKEN=生成した乱数
GEMINI_API_KEY=実際のGemini APIキー
REVENUECAT_WEBHOOK_AUTHORIZATION=Bearer 生成した別の乱数
REVENUECAT_WEBHOOK_SIGNING_SECRET=RevenueCatで設定した値
```

`.env.production`はGitへ追加しません。GeminiとRevenueCatの秘密鍵をFlutterやWebページへ埋め込まないでください。

## 4. 設定確認と初回起動

```bash
docker compose --env-file .env.production -f compose.production.yaml config
docker compose --env-file .env.production -f compose.production.yaml up -d --build
docker compose --env-file .env.production -f compose.production.yaml ps
```

Laravelの画面が更新されない場合や、トップページ以外が404になる場合は、Webイメージをキャッシュなしで再構築します。

```bash
docker compose --env-file .env.production -f compose.production.yaml build --no-cache web
docker compose --env-file .env.production -f compose.production.yaml up -d web
docker compose --env-file .env.production -f compose.production.yaml exec web php artisan optimize:clear
```

起動ログを確認します。

```bash
docker compose --env-file .env.production -f compose.production.yaml logs --tail=100 web api worker caddy
curl https://api.utaone.example.com/health
```

ブラウザーでWebドメインを開きます。DNS反映前や80/443番ポートが閉じている状態ではCaddyが証明書を取得できません。

## 5. 最初の管理者を作る

Web画面でユーザー登録後、コンテナ内でLaravel Tinkerを開きます。

```bash
docker compose --env-file .env.production -f compose.production.yaml exec web php artisan tinker
```

```php
$user = App\Models\User::where('email', '自分のメールアドレス')->firstOrFail();
$user->is_admin = true;
$user->save();
exit
```

詳しい操作は[管理画面ガイド](admin-guide.md)を参照してください。

## 6. RevenueCatとアプリの接続

- RevenueCat Webhook URL：`https://api.utaone.example.com/webhooks/revenuecat`
- Authorizationヘッダー：`.env.production`と同じ値
- GitHub Actionsの`UTAONE_API_BASE_URL`：`https://api.utaone.example.com`

残りの設定は[RevenueCat設定](revenuecat.md)と[GitHub Actions設定](github-actions.md)に従います。

## 7. 更新する

更新前にバックアップを取り、次を実行します。

```bash
cd /opt/utaone
git pull --ff-only
docker compose --env-file .env.production -f compose.production.yaml up -d --build
docker compose --env-file .env.production -f compose.production.yaml ps
```

LaravelのマイグレーションはWebコンテナ起動時に自動実行されます。

## 8. バックアップ

SQLiteは単一サーバー構成向けです。バックアップ中の書き込みを避けるためAPIとワーカーを停止します。

```bash
cd /opt/utaone
mkdir -p backups
docker compose --env-file .env.production -f compose.production.yaml stop api worker web
docker compose --env-file .env.production -f compose.production.yaml config --volumes
docker run --rm -v utaone_utaone_data:/source:ro -v "$PWD/backups:/backup" alpine sh -c 'tar czf /backup/utaone-data.tgz -C /source .'
docker run --rm -v utaone_laravel_data:/source:ro -v "$PWD/backups:/backup" alpine sh -c 'tar czf /backup/laravel-data.tgz -C /source .'
docker compose --env-file .env.production -f compose.production.yaml up -d
```

ボリューム名は設置ディレクトリ名により変わるため、`config --volumes`と`docker volume ls`で実名を確認してから実行してください。バックアップは暗号化し、別サーバーまたはオブジェクトストレージにも保管します。復元時はサービスを停止し、空のボリュームへ展開してください。

## 9. 障害確認

```bash
docker compose --env-file .env.production -f compose.production.yaml ps
docker compose --env-file .env.production -f compose.production.yaml logs --since=30m api worker web caddy
df -h
docker system df
```

SQLiteのため、APIを複数台へ水平分散しないでください。利用者や同時処理が増えた段階でPostgreSQL、オブジェクトストレージ、外部ジョブキューへ移行します。
