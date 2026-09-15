"""Analisis komentar: IndoBERT bila tersedia, fallback heuristic bila tidak.

IndoBERT (`indobenchmark/indobert-base-p1`, IndoNLU) dimuat secara lazy dan
hanya bila env `ML_INDOBERT_MODEL_PATH` menunjuk model fine-tuned. Tanpa
model, layanan tetap berfungsi penuh via lexicon heuristic berbahasa Indonesia
— cukup untuk MVP & CI, diganti otomatis di produksi dengan model asli.
"""

from __future__ import annotations

import math
import re

from app.schemas import RiskLevel

POSITIVE_WORDS = {
    "aman", "bagus", "ok", "oke", "baik", "normal", "terpercaya", "halus",
    "cepat", "ramah", "lega", "jelas", "membantu", "resmi",
}

NEGATIVE_WORDS = {
    "tipu", "penipu", "penipuan", "scam", "fraud", "otp", "kode", "verifikasi",
    "transfer", "pinjol", "apk", "link", "kurir", "mengaku", "minta", "ancam",
    "mencurigakan", "spam", "rekening", "bayar", "tagihan", "tengah", "berkali",
    "marah", "ganggu", "menipu", "blokir", "berbahaya", "tidak", "dikenal",
}

STRONG_NEGATIVE = {
    "penipu", "penipuan", "scam", "fraud", "otp", "kode", "verifikasi",
    "transfer", "pinjol", "apk", "mengaku", "mencurigakan", "berbahaya", "menipu",
}


def _tokens(text: str) -> set[str]:
    return set(re.findall(r"[a-z0-9']+", text.lower()))


class RuleAnalyzer:
    """Heuristic berbasis lexicon — selalu tersedia, cepat, deterministik."""

    name = "rule-lexicon-v1"

    def sentiment(self, text: str) -> tuple[str, float]:
        toks = _tokens(text)
        pos = len(toks & POSITIVE_WORDS)
        neg_hits = len(toks & NEGATIVE_WORDS)
        neg = neg_hits

        if neg > pos:
            return "negative", neg_hits
        if pos > neg:
            return "positive", pos
        return "neutral", max(pos, neg)

    def fraud_probability(self, text: str) -> float:
        toks = _tokens(text)
        strong = len(toks & STRONG_NEGATIVE)
        mild = len(toks & (NEGATIVE_WORDS - STRONG_NEGATIVE))
        length = max(5, len(text))

        raw = 0.02 + (strong * 0.28) + (mild * 0.08)
        raw = min(raw, 0.98)

        # panjang-pendek tidak relevan utk MVP; biarkan stabil
        return round(raw, 4)


class Analyzer:
    """Perantara untuk memilih model IndoBERT atau fallback heuristic."""

    def __init__(self) -> None:
        self._pipeline = None
        self._load_error: str | None = None

    @property
    def model_name(self) -> str:
        return "indobert-base-p1" if self._loaded else "rule-lexicon-v1"

    @property
    def _loaded(self) -> bool:
        return self._pipeline is not None

    def _load_indobert(self) -> bool:
        if self._loaded or self._load_error is not None:
            return self._loaded

        from app.config import settings

        if not settings.indobert_model_path:
            self._load_error = "ML_INDOBERT_MODEL_PATH tidak di-set"
            return False

        try:
            from transformers import pipeline as hf_pipeline

            self._pipeline = hf_pipeline(
                "text-classification",
                model=settings.indobert_model_path,
                tokenizer=settings.indobert_model_path,
            )
            return True
        except Exception as exc:  # noqa: BLE001
            self._load_error = f"{type(exc).__name__}: {exc}"
            return False

    def analyze(self, text: str) -> dict:
        if self._load_indobert():
            return self._analyze_indobert(text)
        return self._analyze_rule(text)

    def _analyze_indobert(self, text: str) -> dict:
        label, conf = self._extract(self._pipeline(text))
        sentiment, prob = self._indobert_to_core(label, text)

        return {
            "category": sentiment,
            "fraud_probability": prob,
            "confidence": conf,
            "level": "high" if prob >= 0.55 else ("risky" if prob >= 0.35 else ("caution" if prob >= 0.18 else "low")),
            "model": self.model_name,
            "model_version": "1.0.0",
        }

    @staticmethod
    def _indobert_to_core(label: str, text: str) -> tuple[str, float]:
        label = label.lower()
        if label in {"negative", "neg", "sad", "0"}:
            return "negative", min(0.55 + 0.4 * math.log1p(len(text)) / 8.0, 0.97)
        if label in {"positive", "pos", "happy", "2"}:
            return "positive", max(0.05, 0.15 - 0.1 * (len(text) > 40))
        return "neutral", 0.25

    def _extract(self, out) -> tuple[str, float]:
        first = out[0] if isinstance(out, list) else out
        label = str(first.get("label", "neutral"))
        conf = float(first.get("score", 0.0))
        return label, conf

    def _analyze_rule(self, text: str) -> dict:
        ra = RuleAnalyzer()
        sentiment, _ = ra.sentiment(text)
        prob = ra.fraud_probability(text)
        level: RiskLevel = "high" if prob >= 0.55 else ("risky" if prob >= 0.35 else ("caution" if prob >= 0.18 else "low"))

        return {
            "category": sentiment,
            "fraud_probability": prob,
            "confidence": round(0.6 + 0.25 * prob, 4) if sentiment != "neutral" else 0.5,
            "level": level,
            "model": self.model_name,
            "model_version": "1.0.0",
        }


analyzer = Analyzer()