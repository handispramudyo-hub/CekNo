"""Uji dataset sintetis: bentuk, range, keteraturan dasar."""

from __future__ import annotations

import pandas as pd

from app.dataset import FEATURES, generate


def test_generate_shape_and_columns() -> None:
    df = generate(rows=500, seed=7)
    assert isinstance(df, pd.DataFrame)
    assert len(df) == 500
    assert set(FEATURES) <= set(df.columns)
    assert {"fraud_probability", "risk_level", "target"} <= set(df.columns)


def test_probability_in_range() -> None:
    df = generate(rows=500, seed=9)
    assert df["fraud_probability"].between(0, 1).all()


def test_clean_profile_is_low_risk() -> None:
    df = generate(rows=500, seed=11)
    clean = df[(df["total_reports"] == 0) & (df["fraud_reports"] == 0)]
    # Waspada: dengan noise, beberapa clean/berisiko rendah probabilitas 0.1-0.3
    assert clean["fraud_probability"].mean() < 0.4


def test_heavy_fraud_profile_high_probability() -> None:
    df = generate(rows=2000, seed=13)
    heavy = df[df["fraud_reports"] >= 6]
    assert heavy["fraud_probability"].mean() > 0.5


def test_deterministic_seed() -> None:
    a = generate(rows=300, seed=42)
    b = generate(rows=300, seed=42)
    assert a.equals(b)