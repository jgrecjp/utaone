# 本番Ubuntuへの設置（Dockerなし）

非力なサーバー向けの推奨構成です。Web管理画面とAPIはLaravel 12＋Apache、音声解析だけPythonワーカー＋systemdで動かします。SQLiteと音源は`/var/lib/utaone`で共有します。

## 1. 必要パッケージと共有領域

```bash
sudo apt update
sudo apt install -y apache2 libapache2-mod-php php-cli php-sqlite3 php-mbstring php-xml php-curl php-zip composer python3 python3-venv python3-dev build-essential ffmpeg libsndfile1 git unzip certbot python3-certbot-apache
sudo a2enmod rewrite headers ssl
sudo cp /var/www/utaone/infra/php/utaone.ini /etc/php/8.3/apache2/conf.d/99-utaone.ini
sudo groupadd -f utaone
id utaone >/dev/null 2>&1 || sudo useradd --system --home /var/lib/utaone --shell /usr/sbin/nologin --gid utaone utaone
sudo usermod -aG utaone www-data
sudo mkdir -p /var/lib/utaone/media /etc/utaone
sudo chown -R utaone:utaone /var/lib/utaone
sudo chmod 2770 /var/lib/utaone /var/lib/utaone/media
```

## 2. Laravel API

```bash
cd /var/www/utaone/apps/api
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
sudo nano .env
```

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.uta.one
DB_CONNECTION=sqlite
DB_DATABASE=/var/lib/utaone/utaone.sqlite3
UTAONE_STORAGE_PATH=/var/lib/utaone/media
UTAONE_ADMIN_API_TOKEN=長いランダム値
UTAONE_REQUIRE_SUBSCRIPTION=true
REVENUECAT_ENTITLEMENT_ID=premium
REVENUECAT_WEBHOOK_AUTHORIZATION="Bearer Webhook用ランダム値"
REVENUECAT_WEBHOOK_SIGNING_SECRET=RevenueCatで設定した値
CACHE_STORE=file
SESSION_DRIVER=file
```

```bash
sudo touch /var/lib/utaone/utaone.sqlite3
sudo chown utaone:utaone /var/lib/utaone/utaone.sqlite3
sudo chmod 660 /var/lib/utaone/utaone.sqlite3
sudo chown -R www-data:utaone storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
```

## 3. Web管理画面

`apps/web/.env`は次を設定し、管理APIトークンをAPIと完全に同じ値にします。

```dotenv
UTAONE_API_URL=https://api.uta.one
UTAONE_ADMIN_API_TOKEN=APIと完全に同じ値
```

```bash
cd /var/www/utaone/apps/web
php artisan optimize:clear
php artisan config:cache
```

## 4. ApacheとHTTPS

```bash
cd /var/www/utaone
sudo cp infra/apache/utaone-web.conf infra/apache/utaone-api.conf /etc/apache2/sites-available/
sudo a2ensite utaone-web utaone-api
sudo apache2ctl configtest && sudo systemctl reload apache2
sudo certbot --apache -d uta.one
sudo certbot certonly --apache --cert-name api.uta.one -d api.uta.one
sudo cp infra/apache/utaone-api-ssl.conf /etc/apache2/sites-available/
sudo a2ensite utaone-api-ssl
sudo apache2ctl configtest && sudo systemctl reload apache2
curl https://api.uta.one/health
```

## 5. Python解析ワーカー

```bash
cd /var/www/utaone
python3 -m venv .venv
.venv/bin/pip install --upgrade pip
.venv/bin/pip install -r services/worker/requirements.txt
sudo cp infra/systemd/worker.env.example /etc/utaone/worker.env
sudo nano /etc/utaone/worker.env
sudo chown root:utaone /etc/utaone/worker.env
sudo chmod 640 /etc/utaone/worker.env
sudo cp infra/systemd/utaone-worker.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now utaone-worker
```

`worker.env`へGemini APIキーを設定します。API用systemdサービスとUvicornは不要です。

## 6. 確認と更新

```bash
curl https://api.uta.one/health
sudo systemctl status utaone-worker --no-pager
sudo journalctl -u utaone-worker -n 50 --no-pager
```

更新時は`git pull --ff-only`後、`apps/api`と`apps/web`で`composer install --no-dev --optimize-autoloader`、`php artisan migrate --force`、`php artisan optimize:clear`、`php artisan config:cache`を実行し、Apacheと`utaone-worker`を再起動します。
