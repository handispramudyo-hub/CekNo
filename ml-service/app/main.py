"""FastAPI entrypoint ML Service CekNO."""

from __future__ import annotations

from fastapi import FastAPI

from app.config import settings
from app.models.xgboost_model import default_model
from app.schemas import (
    CommentAnalysis,
    CommentRequest,
    HealthResponse,
    RiskFeatures,
    RiskPrediction,
)
from app.services.analyzer import analyzer

app = FastAPI(
    title="CekNO ML Service",
    description="Prediksi risiko penipuan nomor (XGBoost) & analisis ulasan (IndoBERT/heuristic).",
    version="1.0.0",
    docs_url="/ml/docs",
    openapi_url="/ml/openapi.json",
)


@app.get("/ml/health", response_model=HealthResponse, tags=["sistem"])
def health() -> HealthResponse:
    default_model.load()
    return HealthResponse(
        status="ok",
        model="xgboost" if default_model.clf else "xgboost-unloaded",
        model_version=default_model.active_version() or settings.model_version,
        analyzer=analyzer.model_name,
        indobert_loaded=analyzer._loaded,
    )


@app.post("/ml/predict/risk", response_model=RiskPrediction, tags=["prediksi"])
def predict_risk(features: RiskFeatures) -> RiskPrediction:
    default_model.load()
    features_dict = features.model_dump()

    if default_model.clf is None:
        # ML belum dilatih — gunakan probe rule-based yang memetakan fitur komunitas.
        prob = _fallback_probability(features_dict)
        score = round(prob * 100, 2)
        return RiskPrediction(
            fraud_probability=prob,
            risk_score=score,
            risk_level=_score_to_level(score),
            model="rule-fallback",
            model_version="0.0.0",
        )

    prob, score, level = default_model.predict_db(features_dict)
    return RiskPrediction(
        fraud_probability=round(prob, 4),
        risk_score=score,
        risk_level=level,
        model="xgboost",
        model_version=default_model.version or settings.model_version,
    )


@app.post("/ml/analyze/comment", response_model=CommentAnalysis, tags=["analisis"])
def analyze_comment(req: CommentRequest) -> CommentAnalysis:
    return CommentAnalysis(**analyzer.analyze(req.text))


def _fallback_probability(f: dict) -> float:
    from math import exp

    logit = (
        -2.8
        + 0.5 * min(f["fraud_reports"], 10)
        + 0.15 * min(f["unique_reporters"], 15)
        + 0.1 * min(f["recent_reports"], 10)
        + 0.75 * min(f["fraud_tags"], 4)
        - 0.4 * f["average_rating"]
    )
    return round(1.0 / (1.0 + exp(-max(min(logit, 30.0), -30.0))), 4)


def _score_to_level(score: float) -> str:
    from app.models.xgboost_model import score_to_level

    return score_to_level(score / 100)