from __future__ import annotations
import os
from dataclasses import dataclass
from pathlib import Path
@dataclass(frozen=True)
class Settings:
    database_path: Path
    storage_path: Path
    ffmpeg_binary: str
    ffprobe_binary: str
    @classmethod
    def from_env(cls) -> "Settings":
        root = Path(os.getenv("UTAONE_ROOT", Path.cwd())).resolve()
        return cls(database_path=Path(os.getenv("UTAONE_DATABASE_PATH", root / "storage" / "utaone.sqlite3")).resolve(), storage_path=Path(os.getenv("UTAONE_STORAGE_PATH", root / "storage" / "media")).resolve(), ffmpeg_binary=os.getenv("FFMPEG_BINARY", "ffmpeg"), ffprobe_binary=os.getenv("FFPROBE_BINARY", "ffprobe"))
