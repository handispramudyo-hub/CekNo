"""Generator dataset sintetis realistis untuk latih XGBoost.

Nomor telepon di dataset 100% sintetis (bukan data pribadi). Target dibuat
menggunakan rumus logistik dari fitur agar ada keteraturan yang bisa dipelajari
model, plus noise agar tidak trivial.
"""

from __future__ import annotations

import numpy as np
import pandas as pd

FEATURES = [
    "total_reports",
    "fraud_reports",
    "spam_reports",
    "unique_reporters",
    "total_reviews",
    "average_rating",
    "negative_reviews",
    "recent_reports",
    "search_count",
    "tag_count",
    "fraud_tags",
]


def _sig(_x: float) -> float:
    _x = max(min(_x, 40.0), -40.0)
    return 1.0 / (1.0 + np.exp(-_x))


def _sample_number(rng: np.random.Generator) -> tuple[list[float], float, str]:
    """Satu baris fitur + probabilitas + label risiko."""

    profile = rng.choice(["clean", "moderate", "fraud", "spammy"], p=[0.45, 0.2, 0.25, 0.1])

    if profile == "clean":
        total_reports = int(rng.integers(0, 4))
        fraud_reports = int(rng.integers(0, 2))
        spam_reports = int(rng.integers(0, 2))
        unique_reporters = total_reports
        total_reviews = int(rng.integers(0, 5))
        average_rating = float(rng.uniform(3.5, 5.0))
        negative_reviews = int(rng.integers(0, 2))
        recent_reports = int(rng.binomial(total_reports, 0.2))
        search_count = int(rng.integers(0, 300))
        tag_count = int(rng.integers(0, 2))
        fraud_tags = 0
    elif profile == "spammy":
        total_reports = int(rng.integers(2, 12))
        fraud_reports = int(rng.integers(0, 3))
        spam_reports = int(rng.integers(2, 9))
        unique_reporters = int(rng.integers(2, 8))
        total_reviews = int(rng.integers(0, 6))
        average_rating = float(rng.uniform(2.0, 3.4))
        negative_reviews = int(rng.integers(0, 3))
        recent_reports = int(rng.binomial(total_reports, 0.5))
        search_count = int(rng.integers(100, 900))
        tag_count = int(rng.integers(1, 3))
        fraud_tags = int(rng.integers(0, 2))
    elif profile == "moderate":
        total_reports = int(rng.integers(3, 12))
        fraud_reports = int(rng.integers(1, 5))
        spam_reports = int(rng.integers(0, 3))
        unique_reporters = int(rng.integers(3, 10))
        total_reviews = int(rng.integers(1, 8))
        average_rating = float(rng.uniform(1.8, 3.2))
        negative_reviews = int(rng.integers(1, 5))
        recent_reports = int(rng.binomial(total_reports, 0.55))
        search_count = int(rng.integers(200, 2000))
        tag_count = int(rng.integers(1, 4))
        fraud_tags = int(rng.integers(1, 3))
    else:  # fraud
        total_reports = int(rng.integers(6, 30))
        fraud_reports = int(rng.integers(4, min(30, total_reports) + 1))
        spam_reports = int(rng.integers(0, 4))
        unique_reporters = int(rng.integers(5, 20))
        total_reviews = int(rng.integers(1, 12))
        average_rating = float(rng.uniform(1.0, 2.2))
        negative_reviews = int(rng.integers(max(1, total_reviews - 3), total_reviews + 1))
        recent_reports = int(rng.binomial(total_reports, 0.8))
        search_count = int(rng.integers(400, 8000))
        tag_count = int(rng.integers(2, 5))
        fraud_tags = int(rng.integers(2, 5))

    # Probabilitas target lewat model logit sederhana
    logit = (
        -3.2
        + 0.55 * min(fraud_reports, 12)
        + 0.18 * min(unique_reporters, 15)
        + 0.12 * min(recent_reports, 10)
        + 0.09 * min(spam_reports, 8)
        + 0.9 * min(fraud_tags, 4)
        - 0.55 * average_rating
        + 0.0025 * math_log(min(search_count + 1, 8000))
    )
    prob = _sig(logit + float(rng.normal(0, 0.25)))

    level = "high" if prob >= 0.55 else ("risky" if prob >= 0.35 else ("caution" if prob >= 0.18 else "low"))

    return [total_reports, fraud_reports, spam_reports, unique_reporters, total_reviews,
            round(average_rating, 2), negative_reviews, recent_reports, search_count,
            tag_count, fraud_tags], float(prob), level


def math_log(x: float) -> float:
    import math

    return math.log(x)


def generate(rows: int = 20_000, seed: int = 42) -> pd.DataFrame:
    rng = np.random.default_rng(seed)
    data = [_sample_number(rng) for _ in range(rows)]

    df = pd.DataFrame([r[0] for r in data], columns=FEATURES)
    df["fraud_probability"] = [r[1] for r in data]
    df["risk_level"] = [r[2] for r in data]
    df["target"] = (df["fraud_probability"] >= 0.35).astype(int)

    return df