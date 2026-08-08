# UtaOne Worker

SQLiteから解析ジョブを取得し、FFmpeg／ffprobeで音源を検査・標準化した後、Geminiによる区間候補と歌詞を照合します。

```bash
pip install -r services/worker/requirements.txt
set PYTHONPATH=services/worker
python -m utaone_worker --once
```

FFmpegとffprobeをPATHへ追加するか、`FFMPEG_BINARY`と`FFPROBE_BINARY`を指定してください。
