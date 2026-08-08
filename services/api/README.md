# UtaOne API

FastAPI製のアプリ／管理画面共通APIです。SQLiteへの書き込みはこのサービスに集約します。

## 起動

```bash
python -m venv .venv
.venv/Scripts/pip install -r services/api/requirements.txt
set PYTHONPATH=services/api
uvicorn utaone_api.main:app --reload
```

環境変数はリポジトリ直下の`.env.example`を参照してください。
