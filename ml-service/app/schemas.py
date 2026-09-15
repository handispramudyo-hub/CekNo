"""Skema request/response FastAPI."""

from __future__ import annotations

from typing import Literal

from pydantic import BaseModel, Field

RiskLevel = Literal["low", "caution", "risky", "high"]
Sentiment = Literal["positive", "neutral", "negative"]


class RiskFeatures(BaseModel):
    """Fitur numerik nomor dari backend (RuleEngine.metrics)."""

    total_reports: float = 0
    fraud_reports: float = 0
    spam_reports: float = 0
    unique_reporters: float = 0
    total_reviews: float = 0
    average_rating: float = 0
    negative_reviews: float = 0
    recent_reports: float = 0
    search_count: float = 0
    tag_count: float = 0
    fraud_tags: float = 0


class RiskPrediction(BaseModel):
    fraud_probability: float = Field(ge=0.0, le=1.0)
    risk_score: float = Field(ge=0.0, le=100.0)
    risk_level: RiskLevel
    model: str
    model_version: str


class CommentRequest(BaseModel):
    text: str = Field(min_length=1, max_length=2000)


class CommentAnalysis(BaseModel):
    category: Sentiment
    fraud_probability: float = Field(ge=0.0, le=1.0)
    confidence: float = Field(ge=0.0, le=1.0)
    level: RiskLevel
    model: str
    model_version: str


class HealthResponse(BaseModel):
    status: str
    model: str
    model_version: str
    analyzer: str
    indobert_loaded: bool