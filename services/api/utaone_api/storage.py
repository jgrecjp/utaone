from __future__ import annotations

import hashlib
import re
from pathlib import Path
from uuid import uuid4


ALLOWED_AUDIO_TYPES = {"audio/wav", "audio/x-wav", "audio/mpeg", "audio/mp3"}
ALLOWED_ASSET_KINDS = {"original", "instrumental", "vocal", "lyrics", "recording"}


def safe_suffix(filename: str) -> str:
    suffix = Path(filename).suffix.lower()
    return suffix if re.fullmatch(r"\.[a-z0-9]{1,8}", suffix) else ""


def store_bytes(root: Path, song_id: int, kind: str, filename: str, content: bytes) -> tuple[Path, str]:
    digest = hashlib.sha256(content).hexdigest()
    directory = root / "songs" / str(song_id) / "source" / kind
    directory.mkdir(parents=True, exist_ok=True)
    path = directory / f"{uuid4().hex}{safe_suffix(filename)}"
    path.write_bytes(content)
    return path, digest
