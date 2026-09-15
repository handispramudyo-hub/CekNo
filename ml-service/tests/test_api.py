"""Uji integrasi FastAPI: health, predict, analyze."""

from __future__ import annotations

from fastapi.testclient import TestClient

from app.main import app

client = TestClient(app)


def test_health() -> None:
    resp = client.get("/ml/health")
    assert resp.status_code == 200
    body = resp.json()
    assert body["status"] == "ok"
    assert "model_version" in body


def test_predict_risk_schema() -> None:
    resp = client.post(
        "/ml/predict/risk",
        json={
            "total_reports": 12,
            "fraud_reports": 8,
            "spam_reports": 2,
            "unique_reporters": 9,
            "total_reviews": 5,
            "average_rating": 1.4,
            "negative_reviews": 4,
            "recent_reports": 6,
            "search_count": 1200,
            "tag_count": 3,
            "fraud_tags": 3,
        },
    )
    assert resp.status_code == 200
    body = resp.json()
    assert 0.0 <= body["fraud_probability"] <= 1.0
    assert body["risk_level"] in {"low", "caution", "risky", "high"}


def test_predict_risk_defaults_for_empty() -> None:
    resp = client.post("/ml/predict/risk", json={})
    assert resp.status_code == 200
    assert resp.json()["risk_level"] == "low"
    assert resp.json()["fraud_probability"] < 0.2


def test_analyze_comment_negative() -> None:
    resp = client.post("/ml/analyze/comment", json={"text": "Penipuan, minta OTP berulang kali, sangat mencurigakan."})
    assert resp.status_code == 200
    body = resp.json()
    assert body["category"] in {"positive", "neutral", "negative"}
    assert body["fraud_probability"] > 0.5


def test_analyze_comment_validation() -> None:
    assert client.post("/ml/analyze/comment", json={"text": ""}).status_code == 422


def test_health_under_ml_prefix() -> None:
    # Klien backend memanggil {base}/ml/health
    assert client.get("/ml/health").status_code == 200