"""Skrip pelatihan XGBoost; menulis model ke models/ + registry.json."""

from __future__ import annotations

import argparse
import sys

from app.models.xgboost_model import XgbModel


def main() -> int:
    parser = argparse.ArgumentParser(description="Latih model XGBoost CekNO")
    parser.add_argument("--rows", type=int, default=20_000, help="Jumlah baris sintetis")
    parser.add_argument("--seed", type=int, default=42)
    parser.add_argument("--no-tune", action="store_true", help="Lewati tuning grid kecil")
    parser.add_argument("--version", default=None, help="Override ML_MODEL_VERSION")
    args = parser.parse_args()

    import os

    if args.version:
        os.environ["ML_MODEL_VERSION"] = args.version

    model = XgbModel()
    metrics = model.train(rows=args.rows, seed=args.seed, tuning=not args.no_tune)
    path = model.save()

    print(f"Model disimpan: {path}")
    print("Metrik validasi:", metrics)

    if metrics.get("auc", 0) < 0.7:
        print("PERINGATAN: AUC < 0.7, pertimbangkan dataset/kandidat lain.", file=sys.stderr)
        return 1
    return 0


if __name__ == "__main__":
    raise SystemExit(main())