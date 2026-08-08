from __future__ import annotations

import math
from pathlib import Path


def score_pitch(reference_path: Path, recording_path: Path) -> dict[str, float]:
    import librosa
    import numpy as np

    reference, sample_rate = librosa.load(reference_path, sr=22050, mono=True)
    recording, _ = librosa.load(recording_path, sr=sample_rate, mono=True)
    ref_f0, _, ref_probability = librosa.pyin(reference, fmin=65, fmax=1050, sr=sample_rate)
    user_f0, _, user_probability = librosa.pyin(recording, fmin=65, fmax=1050, sr=sample_rate)
    points = min(len(ref_f0), len(user_f0))
    if points == 0:
        raise ValueError("No pitch frames were produced")
    ref_f0, user_f0 = ref_f0[:points], user_f0[:points]
    valid = (~np.isnan(ref_f0)) & (~np.isnan(user_f0)) & (ref_probability[:points] > 0.5) & (user_probability[:points] > 0.5)
    if int(valid.sum()) < 20:
        raise ValueError("Not enough voiced frames for scoring")
    cents = np.abs(1200 * np.log2(user_f0[valid] / ref_f0[valid]))
    pitch_accuracy = float(np.clip(100 - np.mean(np.minimum(cents, 600)) / 6, 0, 100))
    voiced_coverage = float(np.clip(valid.sum() / max((~np.isnan(ref_f0)).sum(), 1) * 100, 0, 100))
    total = round(pitch_accuracy * 0.8 + voiced_coverage * 0.2, 1)
    if not math.isfinite(total):
        raise ValueError("Score was not finite")
    return {"total": total, "pitch_accuracy": round(pitch_accuracy, 1), "voiced_coverage": round(voiced_coverage, 1)}
