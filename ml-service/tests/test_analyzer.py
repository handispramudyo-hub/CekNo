"""Uji analyzer heuristic (IndoBERT lazy, mode fallback di CI)."""

from __future__ import annotations

import pytest

from app.services.analyzer import Analyzer, RuleAnalyzer


@pytest.fixture()
def analyzer() -> Analyzer:
    return Analyzer()


def test_rule_sentiment_negative(analyzer: Analyzer) -> None:
    out = analyzer.analyze("Nomor ini mengaku dari bank dan meminta kode OTP, sangat mencurigakan.")
    assert out["category"] == "negative"
    assert out["fraud_probability"] > 0.5


def test_rule_sentiment_positive(analyzer: Analyzer) -> None:
    out = analyzer.analyze("Nomor ini aman dan jelas, membantu sekali.")
    assert out["category"] == "positive"
    assert out["fraud_probability"] < 0.2


def test_rule_neutral() -> None:
    ra = RuleAnalyzer()
    sentiment, _ = ra.sentiment("Halo, nomor ini buat apa ya?")
    assert sentiment == "neutral"


def test_probability_bounds(analyzer: Analyzer) -> None:
    for text in ["aman", "penipuan mencurigakan minta OTP berkali-kali", "kode verifikasi transfer ribuan"]:
        out = analyzer.analyze(text)
        assert 0.0 <= out["fraud_probability"] <= 1.0
        assert out["level"] in {"low", "caution", "risky", "high"}


def test_load_error_cleared_without_model(analyzer: Analyzer) -> None:
    # Tanpa ML_INDOBERT_MODEL_PATH, fallback dipakai dan tidak crash
    assert analyzer.analyze("aman")["model"] == "rule-lexicon-v1"