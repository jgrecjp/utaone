# 運用チェックリスト

## 毎回のリリース前

- GitHub Actionsの全CIが成功している
- `.env`や秘密鍵がGitへ追加されていない
- `APP_DEBUG=false`になっている
- API URLがHTTPSになっている
- `UTAONE_ADMIN_API_TOKEN`が初期値ではない
- `UTAONE_REQUIRE_SUBSCRIPTION=true`になっている
- RevenueCat Sandbox／Productionの対象を確認した
- 管理者以外が`/admin/karaoke`へアクセスできない
- Android APK／AABとiOSビルドで再生・録音を確認した

## バックアップ対象

### Docker volume

APIのSQLiteと音源を含む`utaone_data`をバックアップします。コンテナ稼働中のSQLiteファイルだけを直接コピーせず、API・Workerを停止して一貫性を確保するか、SQLiteのバックアップAPIを利用します。

### Laravel

- `apps/web/database/database.sqlite`
- 本番`.env`は安全なSecrets Managerで再作成可能にする

## 復元テスト

バックアップは取得するだけでなく、別環境で定期的に復元してください。

1. 新しい空の環境を作る
2. DBと音源を復元する
3. API・Workerを起動する
4. 管理画面で楽曲を確認する
5. Flutterから公開曲を再生する

## 秘密情報

サーバーだけに置くもの：

- `GEMINI_API_KEY`
- `UTAONE_ADMIN_API_TOKEN`
- `REVENUECAT_WEBHOOK_AUTHORIZATION`
- `REVENUECAT_WEBHOOK_SIGNING_SECRET`
- Laravel `APP_KEY`

Flutterへ渡してよいもの：

- RevenueCat iOS／Android Public SDK Key
- 公開API URL

## ログ

音源ファイル、歌詞全文、APIキー、Authorization headerをログへ出さないでください。障害調査用にジョブID、楽曲ID、処理時間、FFmpeg終了コード、モデル名を記録します。

## SQLiteから移行する目安

次のいずれかが発生したらPostgreSQLと専用ジョブキューを検討します。

- Workerを複数台で常時動かしたい
- DBロック待ちが頻発する
- 管理画面とアプリの同時利用が増える
- 複数APIサーバーへ水平分割したい
