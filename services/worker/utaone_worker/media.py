from __future__ import annotations

import json
import subprocess
from dataclasses import dataclass
from pathlib import Path


class MediaProcessingError(RuntimeError):
    pass


@dataclass(frozen=True)
class MediaInfo:
    duration_seconds: float
    codec: str
    sample_rate: int
    channels: int
    bit_rate: int | None


def _run(args: list[str], *, timeout: int) -> subprocess.CompletedProcess[str]:
    try:
        result = subprocess.run(args, capture_output=True, text=True, timeout=timeout, check=False)
    except (OSError, subprocess.TimeoutExpired) as exc:
        raise MediaProcessingError(str(exc)) from exc
    if result.returncode != 0:
        raise MediaProcessingError(result.stderr[-4000:])
    return result


def probe(path: Path, ffprobe_binary: str) -> MediaInfo:
    result = _run(
        [
            ffprobe_binary,
            "-v", "error",
            "-select_streams", "a:0",
            "-show_entries", "stream=codec_name,sample_rate,channels,bit_rate:format=duration",
            "-of", "json",
            str(path),
        ],
        timeout=30,
    )
    try:
        payload = json.loads(result.stdout)
        stream = payload["streams"][0]
        return MediaInfo(
            duration_seconds=float(payload["format"]["duration"]),
            codec=str(stream["codec_name"]),
            sample_rate=int(stream["sample_rate"]),
            channels=int(stream["channels"]),
            bit_rate=int(stream["bit_rate"]) if stream.get("bit_rate") else None,
        )
    except (KeyError, IndexError, TypeError, ValueError, json.JSONDecodeError) as exc:
        raise MediaProcessingError("ffprobe returned incomplete audio metadata") from exc


def normalize_for_analysis(source: Path, destination: Path, ffmpeg_binary: str) -> None:
    destination.parent.mkdir(parents=True, exist_ok=True)
    _run(
        [
            ffmpeg_binary, "-nostdin", "-hide_banner", "-y",
            "-i", str(source), "-vn", "-map_metadata", "-1",
            "-ac", "1", "-ar", "48000", "-c:a", "pcm_s16le", str(destination),
        ],
        timeout=600,
    )


def create_stream_copy(source: Path, destination: Path, ffmpeg_binary: str) -> None:
    destination.parent.mkdir(parents=True, exist_ok=True)
    _run(
        [
            ffmpeg_binary, "-nostdin", "-hide_banner", "-y",
            "-i", str(source), "-vn", "-map_metadata", "-1",
            "-af", "loudnorm=I=-16:LRA=11:TP=-1.5",
            "-c:a", "aac", "-b:a", "192k", "-movflags", "+faststart", str(destination),
        ],
        timeout=600,
    )
