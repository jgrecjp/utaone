from __future__ import annotations

import os
from dataclasses import dataclass
from pathlib import Path


@dataclass(frozen=True)
class Settings:
    database_path: Path
    storage_path: Path
    admin_api_token: str
    revenuecat_authorization: str
    revenuecat_signing_secret: str
    revenuecat_entitlement_id: str
    require_subscription: bool
    ffmpeg_binary: str
    ffprobe_binary: str

    @classmethod
    def from_env(cls) -> "Settings":
        root = Path(os.getenv("UTAONE_ROOT", Path.cwd())).resolve()
        return cls(
            database_path=Path(os.getenv("UTAONE_DATABASE_PATH", root / "storage" / "utaone.sqlite3")).resolve(),
            storage_path=Path(os.getenv("UTAONE_STORAGE_PATH", root / "storage" / "media")).resolve(),
            admin_api_token=os.getenv("UTAONE_ADMIN_API_TOKEN", "change-me"),
            revenuecat_authorization=os.getenv("REVENUECAT_WEBHOOK_AUTHORIZATION", ""),
            revenuecat_signing_secret=os.getenv("REVENUECAT_WEBHOOK_SIGNING_SECRET", ""),
            revenuecat_entitlement_id=os.getenv("REVENUECAT_ENTITLEMENT_ID", "premium"),
            require_subscription=os.getenv("UTAONE_REQUIRE_SUBSCRIPTION", "false").lower() in {"1", "true", "yes"},
            ffmpeg_binary=os.getenv("FFMPEG_BINARY", "ffmpeg"),
            ffprobe_binary=os.getenv("FFPROBE_BINARY", "ffprobe"),
        )
