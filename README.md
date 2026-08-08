# UtaOne

UtaOneは、iOS／Android向けのカラオケアプリです。

管理画面から元音源・カラオケ音源・アカペラ音源・歌詞を登録すると、PythonワーカーがFFmpegとGeminiを使って配信用音源と同期歌詞を生成します。アプリではカラオケ再生、録音、採点、RevenueCatサブスクリプションを利用できます。

## はじめての方へ

次の順番で進めてください。

1. [初心者向けセットアップ](docs/getting-started.md)
2. [楽曲登録と管理画面の使い方](docs/admin-guide.md)
3. [RevenueCat設定](docs/revenuecat.md)
4. [GitHub Actions設定](docs/github-actions.md)
5. 問題が起きたら[トラブルシューティング](docs/troubleshooting.md)

## リポジトリ構成

```text
apps/
  mobile/       Flutter製iOS／Androidアプリ
  web/          Laravel 12製LP・管理画面
services/
  api/          FastAPI製REST API・SQLite
  worker/       FFmpeg・Gemini・採点ワーカー
docs/           設置・運用・設計資料
```

## 使用技術

- アプリ：Flutter
- Web・管理画面：PHP 8.2以降、Laravel 12
- API：Python 3.13、FastAPI
- DB：SQLite（初期構成）
- 音声処理：FFmpeg、ffprobe、librosa、NumPy
- LLM：Gemini 3.6 Flashのみ
- サブスクリプション：RevenueCat
- CI／ビルド：GitHub Actions

NFT、暗号資産、ウォレット、アバター、Qwen、OpenAI、Veoは含みません。

## 対応するカラオケ素材

- 元音源：WAVまたはMP3
- カラオケ音源：WAVまたはMP3
- アカペラ音源：WAVまたはMP3
- 歌詞：UTF-8のTXT

## 現在のMVP

- 管理者による4素材のアップロード
- FFmpegによる音源検査・解析用WAV変換・AAC/M4A生成
- Geminiによる歌詞行タイミング候補の生成
- 管理画面での行単位タイミング修正・公開
- Flutterでの楽曲一覧、再生、同期歌詞、録音
- アカペラ基準音程と録音の初期採点
- RevenueCatの購入・復元・Webhook
- Android AAB／APK、iOS署名なしアプリのGitHub Actionsビルド

より詳しい資料は[ドキュメント一覧](docs/README.md)を参照してください。
