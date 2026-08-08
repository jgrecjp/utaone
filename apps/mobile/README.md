# UtaOne Mobile

iOS／Android向けFlutterアプリです。

```bash
flutter create --platforms=ios,android .
flutter pub get
flutter run \
  --dart-define=UTAONE_API_URL=https://api.example.com \
  --dart-define=REVENUECAT_IOS_API_KEY=appl_xxx \
  --dart-define=REVENUECAT_ANDROID_API_KEY=goog_xxx
```

RevenueCatのEntitlement IDは`premium`です。公開SDKキーだけをアプリへ設定し、秘密鍵は含めません。
