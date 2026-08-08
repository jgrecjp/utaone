# 本番Ubuntuへの設置（Dockerなし）

非力なサーバーでは、この構成を推奨します。LaravelはApacheとPHP-FPMではなく既存のApache PHP構成、FastAPIと解析ワーカーはPython仮想環境とsystemdで直接動かします。SQLiteと音源は`/var/lib/utaone`へ保存します。

## 1. パッケージを入れる

Ubuntu 24.04で次を実行します。

```bash
sudo apt update
sudo apt install -y apache2 libapache2-mod-php php-cli php-sqlite3 php-mbstring php-xml php-curl php-zip composer \
  python3 python3-venv python3-dev build-essential ffmpeg libsndfile1 git unzip certbot python3-certbot-apache
sudo a2enmod rewrite proxy proxy_http headers ssl
```

音声解析時はFFmpegとlibrosaがCPUとメモリを使います。ワーカーは1プロセスだけ起動し、同時解析を増やさない構成です。

## 2. 専用ユーザーと保存場所を作る

```bash
sudo useradd --system --home /var/lib/utaone --shell /usr/sbin/nologin utaone
sudo mkdir -p /var/lib/utaone/media /etc/utaone
sudo chown -R utaone:utaone /var/lib/utaone
sudo chmod 750 /var/lib/utaone /etc/utaone
```

## 3. Python環境を作る

```bash
cd /var/www/utaone
python3 -m venv .venv
.venv/bin/pip install --upgrade pip
.venv/bin/pip install -r services/api/requirements.txt -r services/worker/requirements.txt
sudo chown -R root:root /var/www/utaone/.venv
```

APIの設定を配置します。

```bash
sudo cp infra/systemd/api.env.example /etc/utaone/api.env
openssl rand -hex 32
sudo nano /etc/utaone/api.env
sudo chown root:utaone /etc/utaone/api.env
sudo chmod 640 /etc/utaone/api.env
```

Gemini、RevenueCat、管理APIトークンを実際の値へ変更してください。

## 4. systemdへ登録する

```bash
sudo cp infra/systemd/utaone-api.service /etc/systemd/system/
sudo cp infra/systemd/utaone-worker.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now utaone-api utaone-worker
sudo systemctl status utaone-api utaone-worker --no-pager
curl http://127.0.0.1:8000/health
```

ログは次で確認できます。

```bash
sudo journalctl -u utaone-api -u utaone-worker -f
```

APIは`127.0.0.1:8000`だけで待ち受けるため、インターネットへ直接公開されません。

## 5. Laravelを設定する

```bash
cd /var/www/utaone/apps/web
composer install --no-dev --optimize-autoloader
cp .env.example .env
touch database/database.sqlite
php artisan key:generate
nano .env
```

次の値を設定します。管理APIトークンは`/etc/utaone/api.env`と同じ値です。

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://uta.one
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/utaone/apps/web/database/database.sqlite
UTAONE_API_URL=http://127.0.0.1:8000
UTAONE_ADMIN_API_TOKEN=API側と同じ値
```

権限とキャッシュを整えます。

```bash
sudo chown -R www-data:www-data storage bootstrap/cache database
sudo chmod -R ug+rwX storage bootstrap/cache database
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
```

フロントエンドを変更した場合だけ`npm ci && npm run build`を実行します。

## 6. Apacheを設定する

設定例は実際の`uta.one`と`api.uta.one`を記載済みです。別ドメインの場合は`ServerName`を変更します。

```bash
cd /var/www/utaone
sudo cp infra/apache/utaone-web.conf /etc/apache2/sites-available/
sudo cp infra/apache/utaone-api.conf /etc/apache2/sites-available/
sudo a2ensite utaone-web utaone-api
sudo apache2ctl configtest
sudo systemctl reload apache2
```

DNSのAレコードを設定してからHTTPSを有効にします。WebとAPIはVirtualHostを混同しないよう、証明書を分けて発行します。

```bash
sudo certbot --apache -d uta.one
sudo certbot certonly --apache --cert-name api.uta.one -d api.uta.one
sudo cp infra/apache/utaone-api-ssl.conf /etc/apache2/sites-available/
sudo a2ensite utaone-api-ssl
sudo apache2ctl configtest
sudo systemctl reload apache2
curl https://api.uta.one/health
```

`api.uta.one`でApacheのエラーページが表示される場合、443番のAPI VirtualHostが選択されていません。`sudo apache2ctl -S`で`api.uta.one:443`が`utaone-api-ssl.conf`へ割り当てられていることを確認します。

## 7. 更新する

```bash
cd /var/www/utaone
git pull --ff-only
.venv/bin/pip install -r services/api/requirements.txt -r services/worker/requirements.txt

cd apps/web
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache

sudo systemctl restart utaone-api utaone-worker
sudo systemctl reload apache2
```

## 8. 停止・ログ・バックアップ

```bash
sudo systemctl stop utaone-worker utaone-api
sudo journalctl -u utaone-api -u utaone-worker --since today
sudo tar czf /root/utaone-data-backup.tgz /var/lib/utaone /var/www/utaone/apps/web/database/database.sqlite
sudo systemctl start utaone-api utaone-worker
```

バックアップは別サーバーにもコピーしてください。ワーカーだけ一時停止する場合は`sudo systemctl stop utaone-worker`です。WebとAPIはそのまま利用できますが、新規解析ジョブは待機状態になります。
