# GitHub Actions設定

## 用意されているWorkflow

### CI

ファイル：`.github/workflows/ci.yml`

Pull Requestと`main`へのpushで次を確認します。

- Pythonの構文と単体テスト
- Laravel Web／APIの依存、ルート、SQLiteマイグレーション、テスト
- Flutterフォーマット、解析、テスト
- API／WorkerのDockerイメージビルド

### Mobile builds

ファイル：`.github/workflows/mobile-build.yml`

次のタイミングで実行されます。

- `main`へモバイル関連変更をpush
- `v1.0.0`のような`v*`タグをpush
- GitHub画面から手動実行

生成物：

- Android AAB
- Android APK
- iOS署名なし`Runner.app`

Artifactの保存期間は14日です。

## GitHubへ設定する値

GitHubリポジトリの`Settings` → `Secrets and variables` → `Actions`を開きます。

### Variables

| 名前 | 例 |
|---|---|
| `UTAONE_API_URL` | `https://api.example.com` |

末尾の`/`は付けないでください。本番ではHTTPSだけを使用します。

### Secrets

| 名前 | 内容 |
|---|---|
| `REVENUECAT_IOS_API_KEY` | `appl_...`公開SDKキー |
| `REVENUECAT_ANDROID_API_KEY` | `goog_...`公開SDKキー |

API管理トークン、Geminiキー、Webhook署名秘密はモバイルビルドに不要です。これらをWorkflowの`--dart-define`へ渡してはいけません。

## 手動ビルド

1. GitHubの`Actions`を開きます。
2. 左側から`Mobile builds`を選びます。
3. `Run workflow`を押します。
4. 対象ブランチを選び、実行します。
5. 完了した実行結果の`Artifacts`からダウンロードします。

## iOSについて

現在生成するiOSアプリは署名なしです。App Storeへ提出できるIPAではありません。

本番配布には次が必要です。

- Apple Distribution証明書
- Provisioning Profile
- App Store Connect API Key
- 署名・IPA作成・アップロードのWorkflow

証明書や秘密鍵をリポジトリへ直接コミットしないでください。

## ブランチ保護の推奨

GitHubのBranch protectionまたはRulesetで、`main`へのマージ前にCIの成功を必須にします。

推奨Required checks：

- `Python API and worker`
- `Laravel web`
- `Flutter analyze and test`
- `Container build`
