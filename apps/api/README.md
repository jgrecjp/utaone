# UtaOne Laravel API

FlutterアプリとLaravel管理画面が利用するLaravel 12 REST APIです。楽曲、素材、解析ジョブ、録音採点、RevenueCat Webhookを担当し、PythonワーカーとSQLite・音源領域を共有します。

ローカル起動：

```bash
cp .env.example .env
composer install
touch database/database.sqlite
php artisan key:generate
php artisan migrate
php artisan serve --port=8000
```

確認：`curl http://127.0.0.1:8000/health`

本番手順は[`docs/production-ubuntu-native.md`](../../docs/production-ubuntu-native.md)を参照してください。
