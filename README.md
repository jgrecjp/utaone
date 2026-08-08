# UtaOne

iOS / Android向けカラオケアプリのモノレポです。

## 構成

- `apps/mobile`: Flutterアプリ
- `apps/web`: Laravel製LP・管理画面
- `services/api`: Python API
- `services/worker`: カラオケデータ生成・採点バッチ
- `docs`: 要件・設計資料

NFT、暗号資産、ウォレット機能は本バージョンの対象外です。
必要なコードとアセットはすべてこのリポジトリ内で管理します。

## カラオケデータ

管理画面からWAVまたはMP3形式の元音源、カラオケ音源、アカペラ音源と歌詞を登録し、FFmpegで音源を標準化したうえで、歌詞タイムラインと採点用基準データを非同期生成します。詳細は [カラオケデータ生成設計](docs/karaoke-data-pipeline.md) を参照してください。

## 開発開始

APIとワーカーは`docker compose up --build`で起動できます。Laravel、Flutter、RevenueCatを含む手順は [開発環境](docs/development.md) を参照してください。

GitHub ActionsでPython、Laravel、Flutter、DockerのCIを実行し、Android AAB／APKとiOS署名なしアプリをArtifactとして生成します。
