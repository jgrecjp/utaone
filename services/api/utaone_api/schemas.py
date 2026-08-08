from __future__ import annotations

from pydantic import BaseModel, Field


class SongCreate(BaseModel):
    title: str = Field(min_length=1, max_length=255)
    artist: str = Field(min_length=1, max_length=255)
    difficulty: int = Field(default=1, ge=1, le=10)


class SongOut(BaseModel):
    id: int
    title: str
    artist: str
    status: str
    difficulty: int


class JobOut(BaseModel):
    id: int
    song_id: int
    job_type: str
    status: str
    progress: int
    error_message: str | None = None


class LyricSegmentUpdate(BaseModel):
    position: int = Field(ge=0)
    text: str = Field(min_length=1, max_length=1000)
    start_ms: int = Field(ge=0)
    end_ms: int = Field(gt=0)


class TimelineUpdate(BaseModel):
    segments: list[LyricSegmentUpdate]
