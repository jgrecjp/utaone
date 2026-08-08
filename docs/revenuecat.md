# RevenueCat設定

RevenueCatはiOS App StoreとGoogle Playのサブスクリプション状態をFlutterへ提供し、Webhookでサーバー側SQLiteにも状態を同期します。

## 用語

- Product：App Store Connect／Google Play Consoleで作る購入商品
- Entitlement：購入者へ与える権利。本プロジェクトでは`premium`
- Offering：アプリへ表示する商品のまとまり
- Public SDK Key：Flutterへ埋め込める公開キー
- Webhook secret：サーバーだけに保存する秘密情報

## 1. ストア側の商品を作る

### iOS

1. Apple Developer Programへ加入します。
2. App Store Connectでアプリを作ります。
3. Bundle IDをFlutterプロジェクトと一致させます。
4. 自動更新サブスクリプションを作ります。

### Android

1. Google Play Consoleでアプリを作ります。
2. Package nameをFlutterプロジェクトと一致させます。
3. 定期購入商品とBase planを作ります。

商品IDの例は`utaone_premium_monthly`です。一度使った商品IDは変更しにくいため、公開前に命名を確定してください。

## 2. RevenueCatプロジェクト

1. RevenueCatで新規Projectを作成します。
2. iOS AppとAndroid Appを追加します。
3. 各ストアとの認証情報を設定します。
4. Productsへストア商品を登録します。
5. Entitlementを`premium`という正確なIDで作成します。
6. Productを`premium`へ関連付けます。
7. Default Offeringを作り、購入Packageを追加します。

Entitlement IDが`premium`以外だと、購入成功後もアプリが未購読として扱います。

## 3. Flutter公開SDKキー

RevenueCatのAPI Keys画面から、iOSの`appl_...`とAndroidの`goog_...`を取得します。

ローカル起動：

```powershell
flutter run `
  --dart-define=UTAONE_API_URL=http://10.0.2.2:8000 `
  --dart-define=REVENUECAT_ANDROID_API_KEY=goog_xxxxxxxxx
```

公開SDKキーはアプリ内で使う前提のキーです。ただし、RevenueCatのSecret API Keyやストア秘密鍵をFlutterへ入れてはいけません。

## 4. Webhook

ローカルの`localhost`はRevenueCatからアクセスできません。本番HTTPS URL、または開発用HTTPSトンネルを用意します。

Webhook URL：

```text
https://api.example.com/v1/webhooks/revenuecat
```

ルート`.env`に設定します。

```dotenv
REVENUECAT_ENTITLEMENT_ID=premium
REVENUECAT_WEBHOOK_AUTHORIZATION=Bearer 十分に長いランダム値
REVENUECAT_WEBHOOK_SIGNING_SECRET=RevenueCatで発行した署名シークレット
```

RevenueCatのWebhook設定に、同じAuthorization headerをそのまま登録します。HMAC signingを有効化し、表示されたSigning secretを安全に保存します。

署名シークレットは作成・ローテーション時しか表示されないため、パスワード管理ツールへ保管してください。

## 5. 開発と本番の購読制限

開発初期：

```dotenv
UTAONE_REQUIRE_SUBSCRIPTION=false
```

本番：

```dotenv
UTAONE_REQUIRE_SUBSCRIPTION=true
```

本番で`true`にすると、RevenueCat Webhookで`premium`が有効と同期されたユーザーだけが録音・採点APIを利用できます。

## 6. テスト

- iOSはStoreKit ConfigurationまたはSandbox Apple Accountを使用します。
- AndroidはLicense testerと内部テストトラックを使用します。
- 購入、キャンセル、更新、期限切れ、復元を確認します。
- RevenueCat DashboardからテストWebhookを送信し、APIがHTTP 200を返すことを確認します。

実際のストア商品は、エミュレーターや未公開APKでは取得できない場合があります。各ストアの公式テスト環境を使用してください。
