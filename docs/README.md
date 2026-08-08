# UtaOneドキュメント

## 導入する人向け

- [初心者向けセットアップ](getting-started.md)：必要ソフトの準備からローカル起動まで
- [WSL（Ubuntu）セットアップ](wsl-ubuntu.md)：Windows上のWSL 2を使うローカル環境
- [本番Ubuntuへの設置](production-ubuntu.md)：Docker、HTTPS、更新、バックアップ
- [管理画面ガイド](admin-guide.md)：管理者作成、素材登録、解析、歌詞確認、公開
- [RevenueCat設定](revenuecat.md)：iOS／Android商品、Entitlement、Webhook
- [GitHub Actions設定](github-actions.md)：CI、Android・iOSビルド、Artifact取得
- [トラブルシューティング](troubleshooting.md)：よくあるエラーと確認方法

## 開発・運用する人向け

- [開発環境リファレンス](development.md)：個別起動、テスト、主要環境変数
- [システム構成](architecture.md)：サービスの責務とデータの流れ
- [カラオケデータ生成](karaoke-data-pipeline.md)：現在の解析処理と今後の拡張
- [運用チェックリスト](operations.md)：バックアップ、鍵管理、公開前確認

## 最短ルート

Windowsへ直接構築する場合は[初心者向けセットアップ](getting-started.md)、WSLを使う場合は[WSL（Ubuntu）セットアップ](wsl-ubuntu.md)を上から順に実行してください。本番公開時は[本番Ubuntuへの設置](production-ubuntu.md)へ進みます。RevenueCatはAPI・管理画面の動作確認後に設定できますが、現在のFlutterアプリでカラオケ画面を開くには有効なRevenueCat Offeringが必要です。
