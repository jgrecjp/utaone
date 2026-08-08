from __future__ import annotations

import hashlib
import json
from pathlib import Path

from .database import connect
from .settings import Settings

from .alignment import gemini_alignment, normalize_lyrics
from .media import create_stream_copy, normalize_for_analysis, probe
from .scoring import score_pitch


def claim_next_job(settings: Settings) -> dict | None:
    with connect(settings.database_path) as connection:
        connection.execute("BEGIN IMMEDIATE")
        row = connection.execute(
            "SELECT id, song_id, recording_id, job_type FROM processing_jobs WHERE status = 'queued' ORDER BY id LIMIT 1"
        ).fetchone()
        if not row:
            return None
        updated = connection.execute(
            """UPDATE processing_jobs SET status='processing', progress=1, started_at=CURRENT_TIMESTAMP,
               updated_at=CURRENT_TIMESTAMP WHERE id=? AND status='queued'""",
            (row["id"],),
        )
        return dict(row) if updated.rowcount == 1 else None


def _progress(settings: Settings, job_id: int, value: int) -> None:
    with connect(settings.database_path) as connection:
        connection.execute(
            "UPDATE processing_jobs SET progress=?, updated_at=CURRENT_TIMESTAMP WHERE id=?", (value, job_id)
        )


def process_job(settings: Settings, job: dict) -> None:
    job_id, song_id = job["id"], job["song_id"]
    try:
        if job["job_type"] == "score_recording":
            _process_scoring_job(settings, job)
            return
        with connect(settings.database_path) as connection:
            rows = connection.execute(
                "SELECT kind, original_name, storage_path, mime_type, sha256 FROM song_assets WHERE song_id=?",
                (song_id,),
            ).fetchall()
        assets = {row["kind"]: dict(row) for row in rows if row["kind"] in {"original", "instrumental", "vocal", "lyrics"}}
        missing = {"original", "instrumental", "vocal", "lyrics"} - set(assets)
        if missing:
            raise RuntimeError(f"Missing assets: {', '.join(sorted(missing))}")

        analysis_root = settings.storage_path / "songs" / str(song_id) / "generated"
        infos = {}
        for kind in ("original", "instrumental", "vocal"):
            source = Path(assets[kind]["storage_path"])
            info = probe(source, settings.ffprobe_binary)
            if info.codec not in {"mp3", "pcm_s16le", "pcm_s24le", "pcm_s32le", "pcm_f32le"}:
                raise RuntimeError(f"Unsupported {kind} codec: {info.codec}")
            infos[kind] = info
            normalize_for_analysis(source, analysis_root / f"{kind}.wav", settings.ffmpeg_binary)
        _progress(settings, job_id, 40)

        durations = [item.duration_seconds for item in infos.values()]
        if max(durations) - min(durations) > 2.0:
            raise RuntimeError("The three audio sources differ in duration by more than 2 seconds")

        lyric_bytes = Path(assets["lyrics"]["storage_path"]).read_bytes()
        lyrics = lyric_bytes.decode("utf-8-sig")
        lines = normalize_lyrics(lyrics)
        if not lines:
            raise RuntimeError("Lyrics file contains no lines")
        segments = gemini_alignment(analysis_root / "vocal.wav", lines, infos["vocal"].duration_seconds)
        _progress(settings, job_id, 70)

        stream_path = analysis_root / "instrumental.m4a"
        create_stream_copy(Path(assets["instrumental"]["storage_path"]), stream_path, settings.ffmpeg_binary)
        stream_digest = hashlib.sha256(stream_path.read_bytes()).hexdigest()
        timeline = [segment.__dict__ for segment in segments]
        with connect(settings.database_path) as connection:
            connection.execute("DELETE FROM lyric_segments WHERE song_id=? AND version=1", (song_id,))
            connection.executemany(
                """INSERT INTO lyric_segments(song_id, version, position, text, start_ms, end_ms, confidence)
                   VALUES (?, 1, ?, ?, ?, ?, ?)""",
                [(song_id, item.position, item.text, item.start_ms, item.end_ms, item.confidence) for item in segments],
            )
            cursor = connection.execute(
                """INSERT INTO song_assets(song_id, kind, original_name, storage_path, mime_type, sha256, metadata_json)
                   VALUES (?, 'stream', 'instrumental.m4a', ?, 'audio/mp4', ?, ?)""",
                (song_id, str(stream_path), stream_digest, json.dumps({"codec": "aac", "source": "instrumental"})),
            )
            connection.execute(
                """INSERT INTO karaoke_releases(song_id, version, stream_asset_id, timeline_json)
                   VALUES (?, 1, ?, ?)
                   ON CONFLICT(song_id, version) DO UPDATE SET stream_asset_id=excluded.stream_asset_id,
                       timeline_json=excluded.timeline_json""",
                (song_id, cursor.lastrowid, json.dumps(timeline, ensure_ascii=False)),
            )
            connection.execute("UPDATE songs SET status='review_required', updated_at=CURRENT_TIMESTAMP WHERE id=?", (song_id,))
            connection.execute(
                """UPDATE processing_jobs SET status='completed', progress=100, result_json=?,
                   finished_at=CURRENT_TIMESTAMP, updated_at=CURRENT_TIMESTAMP WHERE id=?""",
                (json.dumps({"segments": len(segments), "requires_review": True}), job_id),
            )
    except Exception as exc:
        with connect(settings.database_path) as connection:
            if job["job_type"] == "score_recording":
                connection.execute("UPDATE recordings SET status='failed' WHERE id=?", (job["recording_id"],))
            else:
                connection.execute("UPDATE songs SET status='failed', updated_at=CURRENT_TIMESTAMP WHERE id=?", (song_id,))
            connection.execute(
                """UPDATE processing_jobs SET status='failed', error_message=?, finished_at=CURRENT_TIMESTAMP,
                   updated_at=CURRENT_TIMESTAMP WHERE id=?""",
                (str(exc)[:4000], job_id),
            )


def _process_scoring_job(settings: Settings, job: dict) -> None:
    with connect(settings.database_path) as connection:
        recording = connection.execute("SELECT storage_path FROM recordings WHERE id=?", (job["recording_id"],)).fetchone()
    if not recording:
        raise RuntimeError("Recording not found")
    generated = settings.storage_path / "songs" / str(job["song_id"]) / "generated"
    normalized = generated / f"recording-{job['recording_id']}.wav"
    normalize_for_analysis(Path(recording["storage_path"]), normalized, settings.ffmpeg_binary)
    result = score_pitch(generated / "vocal.wav", normalized)
    with connect(settings.database_path) as connection:
        connection.execute(
            "UPDATE recordings SET status='completed', score=?, score_detail_json=? WHERE id=?",
            (result["total"], json.dumps(result), job["recording_id"]),
        )
        connection.execute(
            """UPDATE processing_jobs SET status='completed', progress=100, result_json=?,
               finished_at=CURRENT_TIMESTAMP, updated_at=CURRENT_TIMESTAMP WHERE id=?""",
            (json.dumps(result), job["id"]),
        )


def run_once(settings: Settings | None = None) -> bool:
    settings = settings or Settings.from_env()
    job = claim_next_job(settings)
    if not job:
        return False
    process_job(settings, job)
    return True
