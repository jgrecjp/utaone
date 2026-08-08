# トラブルシューティング

## 最初に確認するコマンド

```powershell
docker compose ps
docker compose logs --tail=100 api
docker compose logs --tail=100 worker
```

Laravel：

```powershell
Set-Location apps\web
php artisan about
php artisan route:list
```

Flutter：

```powershell
Set-Location apps\mobile
flutter doctor -v
flutter pub get
flutter analyze
```

## `docker`が見つからない

Docker Desktopをインストールして起動し、PowerShellを開き直します。WSL2利用を求められた場合はDocker Desktopの案内に従います。

## APIの`/health`が開かない

1. `docker compose ps`で`api`が起動しているか確認します。
2. `docker compose logs api`でエラーを確認します。
3. ポート8000を別アプリが使用していないか確認します。
4. `.env`がルートに存在するか確認します。

## Workerが`ffmpeg`または`ffprobe`を見つけられない

DockerイメージにはFFmpegが含まれます。ローカルPythonを直接使っている場合はFFmpegをインストールし、PATHへ追加するか、`FFMPEG_BINARY`と`FFPROBE_BINARY`へ絶対パスを指定します。

## 楽曲登録時に「Python APIへ接続できません」

- APIが`http://localhost:8000`で起動しているか確認します。
- `apps/web/.env`の`UTAONE_API_URL`を確認します。
- ルートとLaravelの`UTAONE_ADMIN_API_TOKEN`が完全に一致しているか確認します。
- Laravel設定キャッシュを消します。

```powershell
php artisan config:clear
```

## 管理画面が403

ログインユーザーが管理者ではありません。

```powershell
php artisan tinker
```

```php
App\Models\User::where('email', 'admin@example.com')->update(['is_admin' => true]);
```

## 解析が`failed`

`docker compose logs worker`を確認します。主な原因：

- 3音源の長さが2秒以上違う
- MP3／WAVが破損している
- 音声ではないファイルを登録した
- 歌詞がUTF-8ではない
- `GEMINI_API_KEY`が無効
- GeminiのJSON応答が全歌詞行を保持しなかった

失敗後の再解析ボタンは現状未実装です。素材を確認して楽曲を再登録してください。

## Geminiキーがない

キーなしでも文字数ベースの仮タイムラインを生成しますが、音声を理解した同期ではありません。実運用ではGoogle AI Studioでキーを発行してください。

## Flutterに楽曲が表示されない

- 管理画面の状態が`published`か確認します。
- AndroidエミュレーターではAPI URLに`10.0.2.2`を使用します。
- 実機からPCのLAN IPへ接続できるか確認します。
- APIの`GET /v1/songs`をブラウザーまたはAPI仕様画面で確認します。

## RevenueCatで購入商品が表示されない

- Entitlement IDが`premium`か確認します。
- Default OfferingにPackageがあるか確認します。
- ProductがEntitlementへ関連付いているか確認します。
- iOS／Androidに対応した公開SDKキーを渡しているか確認します。
- ストアのSandbox／License testerを使用しているか確認します。

## 録音できない

- OS設定でマイク権限を許可します。
- `python tool/prepare_platforms.py`を実行後にアプリを再ビルドします。
- Androidは`RECORD_AUDIO`、iOSは`NSMicrophoneUsageDescription`が必要です。

## GitHub Actionsが失敗する

失敗したJobを開き、最初に赤くなったStepを確認します。

- Laravel：`composer.lock`と`composer.json`の不一致
- Flutter：フォーマット、解析エラー、パッケージAPI変更
- Android／iOS：Repository variableまたはRevenueCat key未設定
- Docker：依存パッケージのダウンロード障害

秘密値をログへ貼り付けて質問しないでください。必要な場合は値を伏せ、変数名とエラーメッセージだけを共有します。
