from __future__ import annotations

import json
import os
from dataclasses import dataclass
from pathlib import Path


@dataclass(frozen=True)
class Segment:
    position: int
    text: str
    start_ms: int
    end_ms: int
    confidence: float


def normalize_lyrics(raw: str) -> list[str]:
    return [line.strip() for line in raw.replace("\r\n", "\n").replace("\r", "\n").split("\n") if line.strip()]


def fallback_alignment(lines: list[str], duration_seconds: float) -> list[Segment]:
    if not lines:
        return []
    total_weight = sum(max(len(line), 1) for line in lines)
    cursor = 0
    segments: list[Segment] = []
    duration_ms = max(int(duration_seconds * 1000), len(lines))
    for position, line in enumerate(lines):
        span = round(duration_ms * max(len(line), 1) / total_weight)
        end = duration_ms if position == len(lines) - 1 else min(duration_ms, cursor + span)
        segments.append(Segment(position, line, cursor, end, 0.25))
        cursor = end
    return segments


def gemini_alignment(audio_path: Path, lines: list[str], duration_seconds: float) -> list[Segment]:
    api_key = os.getenv("GEMINI_API_KEY", "")
    if not api_key:
        return fallback_alignment(lines, duration_seconds)
    from google import genai

    client = genai.Client(api_key=api_key)
    uploaded = client.files.upload(file=str(audio_path))
    prompt = """Align the supplied sung vocal audio with the supplied authoritative lyrics.
Return only JSON as {\"segments\":[{\"position\":0,\"text\":\"...\",\"start_ms\":0,\"end_ms\":1000,\"confidence\":0.8}]}.
Do not rewrite or omit lyrics. Mark uncertain timing with lower confidence. Lyrics:\n""" + "\n".join(lines)
    response = client.models.generate_content(
        model=os.getenv("GEMINI_AUDIO_MODEL", "gemini-3.6-flash"),
        contents=[uploaded, prompt],
    )
    text = response.text.strip().removeprefix("```json").removesuffix("```").strip()
    payload = json.loads(text)
    by_position = {int(item["position"]): item for item in payload["segments"]}
    if set(by_position) != set(range(len(lines))):
        raise ValueError("Gemini alignment did not preserve every lyric line")
    result = []
    previous_end = 0
    max_ms = int(duration_seconds * 1000)
    for position, line in enumerate(lines):
        item = by_position[position]
        start = max(previous_end, int(item["start_ms"]))
        end = min(max_ms, max(start + 1, int(item["end_ms"])))
        result.append(Segment(position, line, start, end, float(item.get("confidence", 0.5))))
        previous_end = end
    return result
