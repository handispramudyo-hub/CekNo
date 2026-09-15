"""Wrapper model XGBoost: pelatihan, evaluasi, persistensi, dan inferensi."""

from __future__ import annotations

import json
import re
from pathlib import Path

import joblib
import numpy as np
import pandas as pd
from sklearn.metrics import f1_score, roc_auc_score
from sklearn.model_selection import train_test_split
from xgboost import XGBClassifier

from app.config import settings
from app.dataset import FEATURES, generate

_FNAME = re.compile(r"^xb_(\d+\.\d+\.\d+)\.joblib$")


def score_to_level(score: float) -> str:
    if score >= 0.55:
        return "high"
    if score >= 0.35:
        return "risky"
    if score >= 0.18:
        return "caution"
    return "low"


def classify(probability: float) -> str:
    return score_to_level(probability)


class XgbModel:
    def __init__(self, path: Path | None = None, version: str | None = None) -> None:
        self.path = path
        self.version = version
        self.clf: XGBClassifier | None = None

    # ---------- training ----------

    def train(self, rows: int = 20_000, seed: int = 42, tuning: bool = True) -> dict:
        df = generate(rows=rows, seed=seed)
        X = df[FEATURES].to_numpy()
        y = df["target"].to_numpy()

        X_train, X_test, y_train, y_test = train_test_split(
            X, y, test_size=0.2, random_state=seed, stratify=y
        )

        params = self._best_params(tuning, X_train, y_train) if tuning else self._default_params()

        base_params = {
            "n_estimators": 450,
            "learning_rate": 0.08,
            "max_depth": 5,
            "subsample": 0.85,
            "colsample_bytree": 0.8,
            "reg_lambda": 2.0,
            "eval_metric": "logloss",
            "random_state": seed,
        }
        model = XGBClassifier(**{**base_params, **params})
        model.fit(
            X_train,
            y_train,
            eval_set=[(X_test, y_test)],
            verbose=False,
        )

        proba = model.predict_proba(X_test)[:, 1]
        metrics = {
            "accuracy": float((model.predict(X_test) == y_test).mean()),
            "f1": float(f1_score(y_test, (proba >= 0.35).astype(int))),
            "auc": float(roc_auc_score(y_test, proba)),
        }

        self.clf = model
        self.version = settings.model_version
        self.path = settings.model_dir / f"xb_{self.version}.joblib"

        return metrics

    def _default_params(self) -> dict:
        return {}

    def _best_params(self, tuning: bool, X_train: np.ndarray, y_train: np.ndarray) -> dict:
        if not tuning:
            return {}

        # Forecast: grid sederhana & terbatas agar cepat.
        candidates = [{"max_depth": 4, "min_child_weight": 1}, {"max_depth": 6, "min_child_weight": 2}]
        best, best_auc = {}, -1.0

        for cand in candidates:
            m = XGBClassifier(
                n_estimators=200,
                learning_rate=0.1,
                subsample=0.85,
                colsample_bytree=0.8,
                eval_metric="logloss",
                random_state=42,
                **cand,
            )
            Xa, Xb, ya, yb = train_test_split(X_train, y_train, test_size=0.2, random_state=7, stratify=y_train)
            m.fit(Xa, ya, eval_set=[(Xb, yb)], verbose=False)
            auc = roc_auc_score(yb, m.predict_proba(Xb)[:, 1])
            if auc > best_auc:
                best, best_auc = cand, auc

        return best

    # ---------- inference ----------

    def predict_db(self, features: dict | pd.Series) -> tuple[float, float, str]:
        """Kembalikan (probabilitas, skor 0-100, level)."""
        row = np.array([[float(features.get(f, 0) or 0) for f in FEATURES]])
        prob = float(self.clf.predict_proba(row)[0, 1]) if self.clf else 0.0
        return prob, round(prob * 100, 2), classify(prob)

    # ---------- persistensi ----------

    def save(self) -> Path:
        if self.clf is None:
            raise RuntimeError("Belum ada model untuk disimpan.")
        settings.model_dir.mkdir(parents=True, exist_ok=True)
        joblib.dump(self.clf, self.path)
        self._update_registry()
        self._prune_old_files()
        return self.path

    def load(self, version: str | None = None) -> "XgbModel":
        target = version or self._active_version()
        self.path = settings.model_dir / f"xb_{target}.joblib"
        if not self.path.exists():
            return self
        self.clf = joblib.load(self.path)
        self.version = target
        return self

    def active_version(self) -> str | None:
        return self._active_version()

    def _active_version(self) -> str | None:
        reg = self._registry()
        return reg.get("active", settings.model_version)

    def _registry(self) -> dict:
        reg_file = settings.model_dir / "registry.json"
        if reg_file.exists():
            return json.loads(reg_file.read_text(encoding="utf-8"))
        return {}

    def _update_registry(self) -> None:
        reg_file = settings.model_dir / "registry.json"
        reg = self._registry()
        reg["active"] = self.version
        reg["versions"] = sorted(set(reg.get("versions", []) + [self.version]))
        reg_file.write_text(json.dumps(reg, indent=2), encoding="utf-8")

    def _prune_old_files(self) -> None:
        reg = self._registry()
        versions = sorted(reg.get("versions", []))
        keep = set(versions[-settings.max_model_files:])
        for f in settings.model_dir.glob("xb_*.joblib"):
            m = _FNAME.match(f.name)
            if m and m.group(1) not in keep:
                f.unlink(missing_ok=True)


default_model = XgbModel()