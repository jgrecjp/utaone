# 開発環境

## APIとワーカー

1. `.env.example`を`.env`へコピーし、管理APIトークンを変更する。
2. `docker compose up --build`を実行する。
3. `http://localhost:8000/docs`でAPI仕様を確認する。

DockerイメージにはFFmpegとffprobeが含まれる。SQLiteと音源は`utaone_data`ボリュームでAPI／ワーカー間共有するが、DBへの通常書き込みはAPIへ集約する。ワーカーは短いトランザクションでジョブ取得・結果反映だけを行う。

歌詞タイミング解析に使用するLLMはGeminiのみで、既定モデルは`gemini-3.6-flash`。`GEMINI_AUDIO_MODEL`で明示的に変更できる。

## Laravel

```bash
cd apps/web
composer install
copy .env.example .env
php artisan key:generate
php artisan serve --port=8080
```

Laravelの`.env`には`UTAONE_API_URL=http://127.0.0.1:8000`と、APIと同じ`UTAONE_ADMIN_API_TOKEN`を設定する。管理画面は`/admin/karaoke`。
初期管理者はユーザー作成後に`php artisan tinker`から対象ユーザーの`is_admin`を`true`へ変更する。

## Flutter

Flutter SDK導入後、`apps/mobile`でプラットフォームファイルを生成して依存を取得する。

```bash
flutter create --platforms=ios,android .
flutter pub get
flutter run --dart-define=UTAONE_API_URL=http://10.0.2.2:8000 \
  --dart-define=REVENUECAT_ANDROID_API_KEY=goog_xxx
```

iOSではHTTPSのAPI URLと`REVENUECAT_IOS_API_KEY=appl_xxx`を使用する。iOSへマイク利用目的文言とIn-App Purchase capability、Androidへ`RECORD_AUDIO`、`INTERNET`、`com.android.vending.BILLING`権限を設定する。

## RevenueCat

- Entitlement ID: `premium`
- Flutterには公開SDKキーのみ設定する。
- Webhook URL: `POST /v1/webhooks/revenuecat`
- RevenueCat管理画面のAuthorization headerを`REVENUECAT_WEBHOOK_AUTHORIZATION`と一致させる。
- HMAC署名を有効にし、署名シークレットを`REVENUECAT_WEBHOOK_SIGNING_SECRET`へ設定する。
- 本番環境では`UTAONE_REQUIRE_SUBSCRIPTION=true`とし、サーバー側でも録音・採点APIを購読者に限定する。

## GitHub Actions

`CI` workflowはPRと`main`へのpushでPythonテスト、Laravelテスト・マイグレーション、Flutter解析・テスト、API／ワーカーのDockerビルドを実行する。

`Mobile builds` workflowは`main`のモバイル変更、`v*`タグ、手動実行で次を生成し、14日間Artifactとして保存する。

- Android App Bundle（AAB）
- Android APK
- iOS署名なし`Runner.app`

GitHubリポジトリに次を設定する。

- Repository variable `UTAONE_API_URL`: 公開HTTPS API URL
- Actions secret `REVENUECAT_IOS_API_KEY`: iOS公開SDKキー
- Actions secret `REVENUECAT_ANDROID_API_KEY`: Android公開SDKキー

iOSのArtifactは動作確認用の署名なしビルドであり、App Storeへ提出するIPAではない。配布署名用の証明書とProvisioning Profileはリポジトリへ保存せず、GitHub Secretsまたは専用署名サービスで扱う。
