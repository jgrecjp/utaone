from __future__ import annotations

import json
import sqlite3
from contextlib import asynccontextmanager
from pathlib import Path

from fastapi import Depends, FastAPI, File, Form, Header, HTTPException, Request, UploadFile, status
from fastapi.responses import FileResponse

from .config import Settings
from .database import connect, initialize_database
from .schemas import JobOut, SongCreate, SongOut, TimelineUpdate
from .security import secure_equals, verify_revenuecat_signature
from .storage import ALLOWED_ASSET_KINDS, ALLOWED_AUDIO_TYPES, store_bytes

settings = Settings.from_env()


@asynccontextmanager
async def lifespan(_: FastAPI):
    initialize_database(settings.database_path)
    settings.storage_path.mkdir(parents=True, exist_ok=True)
    yield


app = FastAPI(title="UtaOne API", version="0.1.0", lifespan=lifespan)


def require_admin(authorization: str = Header(default="")) -> None:
    expected = f"Bearer {settings.admin_api_token}"
    if not secure_equals(authorization, expected):
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Invalid admin token")


@app.get("/health")
def health() -> dict[str, str]:
    return {"status": "ok"}


@app.get("/v1/songs", response_model=list[SongOut])
def list_songs(include_drafts: bool = False) -> list[dict]:
    query = "SELECT id, title, artist, status, difficulty FROM songs"
    params: tuple = ()
    if not include_drafts:
        query += " WHERE status = ?"
        params = ("published",)
    query += " ORDER BY created_at DESC"
    with connect(settings.database_path) as connection:
        return [dict(row) for row in connection.execute(query, params).fetchall()]


@app.get("/v1/songs/{song_id}")
def get_song(song_id: int, request: Request) -> dict:
    with connect(settings.database_path) as connection:
        song = connection.execute(
            "SELECT id, title, artist, status, difficulty FROM songs WHERE id=? AND status='published'", (song_id,)
        ).fetchone()
        release = connection.execute(
            "SELECT timeline_json FROM karaoke_releases WHERE song_id=? AND published_at IS NOT NULL ORDER BY version DESC LIMIT 1",
            (song_id,),
        ).fetchone()
    if not song or not release:
        raise HTTPException(404, "Published song not found")
    result = dict(song)
    result["timeline"] = json.loads(release["timeline_json"])
    result["stream_url"] = str(request.url_for("stream_song", song_id=song_id))
    return result


@app.post("/v1/admin/songs", response_model=SongOut, dependencies=[Depends(require_admin)])
def create_song(payload: SongCreate) -> dict:
    with connect(settings.database_path) as connection:
        cursor = connection.execute(
            "INSERT INTO songs(title, artist, difficulty) VALUES (?, ?, ?)",
            (payload.title, payload.artist, payload.difficulty),
        )
        row = connection.execute(
            "SELECT id, title, artist, status, difficulty FROM songs WHERE id = ?", (cursor.lastrowid,)
        ).fetchone()
    return dict(row)


@app.get("/v1/admin/songs", response_model=list[SongOut], dependencies=[Depends(require_admin)])
def list_admin_songs() -> list[dict]:
    with connect(settings.database_path) as connection:
        return [
            dict(row)
            for row in connection.execute(
                "SELECT id, title, artist, status, difficulty FROM songs ORDER BY created_at DESC"
            ).fetchall()
        ]


@app.post("/v1/admin/songs/{song_id}/assets", dependencies=[Depends(require_admin)])
async def upload_asset(
    song_id: int,
    kind: str = Form(...),
    asset: UploadFile = File(...),
) -> dict:
    if kind not in ALLOWED_ASSET_KINDS:
        raise HTTPException(422, "Unsupported asset kind")
    content_type = (asset.content_type or "application/octet-stream").lower()
    if kind == "lyrics":
        if content_type not in {"text/plain", "application/octet-stream"}:
            raise HTTPException(415, "Lyrics must be a text file")
    elif content_type not in ALLOWED_AUDIO_TYPES:
        raise HTTPException(415, "Audio must be WAV or MP3")
    content = await asset.read()
    if not content or len(content) > 250 * 1024 * 1024:
        raise HTTPException(413, "File is empty or exceeds 250 MB")
    with connect(settings.database_path) as connection:
        if not connection.execute("SELECT 1 FROM songs WHERE id = ?", (song_id,)).fetchone():
            raise HTTPException(404, "Song not found")
        path, digest = store_bytes(settings.storage_path, song_id, kind, asset.filename or kind, content)
        cursor = connection.execute(
            """INSERT INTO song_assets(song_id, kind, original_name, storage_path, mime_type, sha256)
               VALUES (?, ?, ?, ?, ?, ?)""",
            (song_id, kind, asset.filename or kind, str(path), content_type, digest),
        )
    return {"id": cursor.lastrowid, "kind": kind, "sha256": digest}


@app.post("/v1/admin/songs/{song_id}/jobs", response_model=JobOut, dependencies=[Depends(require_admin)])
def enqueue_song_build(song_id: int) -> dict:
    with connect(settings.database_path) as connection:
        kinds = {row[0] for row in connection.execute("SELECT kind FROM song_assets WHERE song_id = ?", (song_id,))}
        missing = sorted({"original", "instrumental", "vocal", "lyrics"} - kinds)
        if missing:
            raise HTTPException(409, detail={"missing_assets": missing})
        cursor = connection.execute("INSERT INTO processing_jobs(song_id) VALUES (?)", (song_id,))
        connection.execute("UPDATE songs SET status = 'analyzing', updated_at = CURRENT_TIMESTAMP WHERE id = ?", (song_id,))
        row = connection.execute(
            "SELECT id, song_id, job_type, status, progress, error_message FROM processing_jobs WHERE id = ?",
            (cursor.lastrowid,),
        ).fetchone()
    return dict(row)


@app.get("/v1/admin/jobs/{job_id}", response_model=JobOut, dependencies=[Depends(require_admin)])
def get_job(job_id: int) -> dict:
    with connect(settings.database_path) as connection:
        row = connection.execute(
            "SELECT id, song_id, job_type, status, progress, error_message FROM processing_jobs WHERE id = ?",
            (job_id,),
        ).fetchone()
    if not row:
        raise HTTPException(404, "Job not found")
    return dict(row)


@app.get("/v1/admin/songs/{song_id}/timeline", dependencies=[Depends(require_admin)])
def get_timeline(song_id: int) -> dict:
    with connect(settings.database_path) as connection:
        rows = connection.execute(
            """SELECT position, text, start_ms, end_ms, confidence FROM lyric_segments
               WHERE song_id=? AND version=1 ORDER BY position""",
            (song_id,),
        ).fetchall()
    return {"song_id": song_id, "segments": [dict(row) for row in rows]}


@app.put("/v1/admin/songs/{song_id}/timeline", dependencies=[Depends(require_admin)])
def update_timeline(song_id: int, payload: TimelineUpdate) -> dict:
    ordered = sorted(payload.segments, key=lambda item: item.position)
    previous_end = 0
    for expected_position, segment in enumerate(ordered):
        if segment.position != expected_position or segment.start_ms < previous_end or segment.end_ms <= segment.start_ms:
            raise HTTPException(422, "Timeline segments must be contiguous in position and ordered in time")
        previous_end = segment.end_ms
    with connect(settings.database_path) as connection:
        connection.execute("DELETE FROM lyric_segments WHERE song_id=? AND version=1", (song_id,))
        connection.executemany(
            """INSERT INTO lyric_segments(song_id, version, position, text, start_ms, end_ms, confidence)
               VALUES (?, 1, ?, ?, ?, ?, 1.0)""",
            [(song_id, item.position, item.text, item.start_ms, item.end_ms) for item in ordered],
        )
        connection.execute(
            "UPDATE karaoke_releases SET timeline_json=? WHERE song_id=? AND version=1",
            (json.dumps([item.model_dump() for item in ordered], ensure_ascii=False), song_id),
        )
    return {"updated": len(ordered)}


@app.post("/v1/admin/songs/{song_id}/publish", dependencies=[Depends(require_admin)])
def publish_song(song_id: int) -> dict:
    with connect(settings.database_path) as connection:
        song = connection.execute("SELECT status FROM songs WHERE id=?", (song_id,)).fetchone()
        release = connection.execute("SELECT id FROM karaoke_releases WHERE song_id=? AND version=1", (song_id,)).fetchone()
        if not song or not release:
            raise HTTPException(404, "Song or generated release not found")
        if song["status"] not in {"review_required", "published"}:
            raise HTTPException(409, "Song is not ready for publication")
        connection.execute("UPDATE karaoke_releases SET published_at=CURRENT_TIMESTAMP WHERE id=?", (release["id"],))
        connection.execute("UPDATE songs SET status='published', updated_at=CURRENT_TIMESTAMP WHERE id=?", (song_id,))
    return {"published": True}


@app.get("/v1/songs/{song_id}/stream")
def stream_song(song_id: int) -> FileResponse:
    with connect(settings.database_path) as connection:
        row = connection.execute(
            """SELECT a.storage_path FROM karaoke_releases r
               JOIN song_assets a ON a.id = r.stream_asset_id
               WHERE r.song_id = ? AND r.published_at IS NOT NULL
               ORDER BY r.version DESC LIMIT 1""",
            (song_id,),
        ).fetchone()
    if not row or not Path(row[0]).is_file():
        raise HTTPException(404, "Published audio not found")
    return FileResponse(row[0], media_type="audio/mp4")


@app.post("/v1/songs/{song_id}/recordings")
async def upload_recording(
    song_id: int,
    recording: UploadFile = File(...),
    x_app_user_id: str = Header(..., min_length=1, max_length=255),
) -> dict:
    content_type = (recording.content_type or "application/octet-stream").lower()
    if content_type not in ALLOWED_AUDIO_TYPES | {"audio/mp4", "audio/x-m4a", "application/octet-stream"}:
        raise HTTPException(415, "Recording must be M4A, WAV, or MP3")
    content = await recording.read()
    if not content or len(content) > 100 * 1024 * 1024:
        raise HTTPException(413, "Recording is empty or exceeds 100 MB")
    with connect(settings.database_path) as connection:
        if not connection.execute("SELECT 1 FROM songs WHERE id=? AND status='published'", (song_id,)).fetchone():
            raise HTTPException(404, "Published song not found")
        if settings.require_subscription:
            subscription = connection.execute(
                "SELECT is_active FROM subscriptions WHERE app_user_id=? AND entitlement_id=?",
                (x_app_user_id, settings.revenuecat_entitlement_id),
            ).fetchone()
            if not subscription or not subscription["is_active"]:
                raise HTTPException(403, "Active premium subscription required")
        path, _ = store_bytes(settings.storage_path, song_id, "recording", recording.filename or "recording.m4a", content)
        cursor = connection.execute(
            "INSERT INTO recordings(app_user_id, song_id, storage_path, status) VALUES (?, ?, ?, 'queued')",
            (x_app_user_id, song_id, str(path)),
        )
        recording_id = cursor.lastrowid
        job = connection.execute(
            "INSERT INTO processing_jobs(song_id, recording_id, job_type) VALUES (?, ?, 'score_recording')",
            (song_id, recording_id),
        )
    return {"recording_id": recording_id, "job_id": job.lastrowid, "status": "queued"}


@app.get("/v1/recordings/{recording_id}")
def get_recording(recording_id: int, x_app_user_id: str = Header(...)) -> dict:
    with connect(settings.database_path) as connection:
        row = connection.execute(
            "SELECT id, song_id, status, score, score_detail_json, created_at FROM recordings WHERE id=? AND app_user_id=?",
            (recording_id, x_app_user_id),
        ).fetchone()
    if not row:
        raise HTTPException(404, "Recording not found")
    result = dict(row)
    result["score_detail"] = json.loads(result.pop("score_detail_json"))
    return result


@app.post("/v1/webhooks/revenuecat")
async def revenuecat_webhook(
    request: Request,
    authorization: str = Header(default=""),
    x_revenuecat_webhook_signature: str = Header(default=""),
) -> dict:
    raw = await request.body()
    auth_ok = secure_equals(authorization, settings.revenuecat_authorization)
    signature_ok = verify_revenuecat_signature(raw, x_revenuecat_webhook_signature, settings.revenuecat_signing_secret)
    if not auth_ok or (settings.revenuecat_signing_secret and not signature_ok):
        raise HTTPException(401, "Invalid RevenueCat webhook authentication")
    try:
        payload = json.loads(raw)
        event = payload["event"]
        event_id = str(event["id"])
        app_user_id = str(event["app_user_id"])
    except (json.JSONDecodeError, KeyError, TypeError):
        raise HTTPException(422, "Invalid webhook payload")

    active_types = {"INITIAL_PURCHASE", "RENEWAL", "UNCANCELLATION", "PRODUCT_CHANGE", "SUBSCRIPTION_EXTENDED"}
    inactive_types = {"EXPIRATION"}
    event_type = str(event.get("type", "UNKNOWN"))
    is_active = 1 if event_type in active_types else 0 if event_type in inactive_types else None
    try:
        with connect(settings.database_path) as connection:
            connection.execute(
                "INSERT INTO webhook_events(id, event_type, payload_json) VALUES (?, ?, ?)",
                (event_id, event_type, raw.decode()),
            )
            if is_active is not None:
                connection.execute(
                    """INSERT INTO subscriptions(app_user_id, entitlement_id, is_active, product_id, expires_at,
                           environment, last_event_id) VALUES (?, ?, ?, ?, ?, ?, ?)
                       ON CONFLICT(app_user_id) DO UPDATE SET entitlement_id=excluded.entitlement_id,
                           is_active=excluded.is_active, product_id=excluded.product_id,
                           expires_at=excluded.expires_at, environment=excluded.environment,
                           last_event_id=excluded.last_event_id, updated_at=CURRENT_TIMESTAMP""",
                    (
                        app_user_id,
                        settings.revenuecat_entitlement_id,
                        is_active,
                        event.get("product_id"),
                        event.get("expiration_at_ms"),
                        event.get("environment"),
                        event_id,
                    ),
                )
    except sqlite3.IntegrityError:
        return {"received": True, "duplicate": True}
    return {"received": True}
