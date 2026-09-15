"""Konfigurasi umum ML Service melalui environment variable."""

from __future__ import annotations

from pathlib import Path

from pydantic_settings import BaseSettings

BASE_DIR = Path(__file__).resolve().parent.parent


class Settings(BaseSettings):
    model_config = {"env_file": str(BASE_DIR / ".env"), "env_prefix": "ML_", "extra": "ignore"}

    model_dir: Path = BASE_DIR / "models"
    model_version: str = "1.0.0"
    # Path opsional model IndoBERT fine-tuned. Bila None/kosong, fallback heuristic dipakai.
    indobert_model_path: str | None = None
    # Mulai mulai versi ini, file model dihapus dari disk bila tidak lagi dipakai.
    max_model_files: int = 3


settings = Settings()