<?php

namespace App\Services;

use App\Models\PhoneNumber;
use App\Models\RiskAssessment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Risk Engine: menggabungkan XGBoost, IndoBERT, community, dan rule engine.
 *
 * Bobot (parameter eksperimen):
 *   XGBoost 0.40, IndoBERT 0.25, Community 0.20, Rules 0.15
 *
 * Jika nomor tidak punya review/teks (IndoBERT tidak tersedia), bobot IndoBERT
 * direnormalisasi proporsional ke komponen lain.
 */
class RiskEngine
{
    public function __construct(
        protected RuleEngine $ruleEngine,
        protected MlServiceClient $ml,
    ) {}

    public function assess(PhoneNumber $phoneNumber): RiskAssessment
    {
        $metrics = $this->ruleEngine->metrics($phoneNumber);

        $community = $this->ruleEngine->communityScore($metrics);
        $rule = $this->ruleEngine->ruleScore($metrics);
        $factors = $this->ruleEngine->explainFactors($metrics, $community, $rule);

        // XGBoost prediction dari ml-service (fallback ke rule-based jika ML down)
        $xgboost = $this->predictXgboost($phoneNumber, $metrics);

        // IndoBERT hanya bila ada teks review approved
        $indobert = $this->predictIndobert($phoneNumber);

        [$score, $level] = $this->blend($xgboost, $indobert, $community, $rule);

        $explanation = $this->buildExplanation($level, $factors);

        return RiskAssessment::updateOrCreate(
            ['phone_number_id' => $phoneNumber->id, 'model_version' => $this->activeModelVersion('xgboost')],
            [
                'xgboost_probability' => $xgboost,
                'indobert_probability' => $indobert,
                'community_score' => $community,
                'rule_score' => $rule,
                'final_score' => $score,
                'risk_level' => $level,
                'explanation' => $explanation,
                'factors' => $factors,
            ]
        );
    }

    protected function predictXgboost(PhoneNumber $phoneNumber, array $metrics): ?float
    {
        try {
            $prob = $this->ml->predictRisk($metrics);

            $phoneNumber->mlPredictions()->create([
                'model_type' => 'xgboost',
                'model_version' => $this->activeModelVersion('xgboost'),
                'input_features' => $metrics,
                'prediction' => ['fraud_probability' => $prob],
                'confidence' => $prob,
            ]);

            return $prob;
        } catch (\Throwable $e) {
            Log::warning('ML XGBoost tidak tersedia, fallback ke rule score.', ['err' => $e->getMessage()]);

            return null;
        }
    }

    protected function predictIndobert(PhoneNumber $phoneNumber): ?float
    {
        $review = $phoneNumber->reviews()
            ->where('status', 'approved')
            ->whereNotNull('comment')
            ->latest()
            ->first();

        if (! $review) {
            return null;
        }

        try {
            $result = $this->ml->analyzeComment($review->comment);

            $phoneNumber->mlPredictions()->create([
                'model_type' => 'indobert',
                'model_version' => $this->activeModelVersion('indobert'),
                'input_features' => ['text' => $review->comment],
                'prediction' => $result,
                'confidence' => $result['fraud_probability'] ?? null,
            ]);

            $review->forceFill([
                'sentiment' => $result['category'] ?? null,
                'fraud_probability' => $result['fraud_probability'] ?? null,
                'category' => $result['category'] ?? null,
            ])->save();

            return $result['fraud_probability'] ?? null;
        } catch (\Throwable $e) {
            Log::warning('ML IndoBERT tidak tersedia.', ['err' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Bobot default. Tanpa IndoBERT, bobotnya (0.25) dibagikan proporsional.
     *
     * @return array{0: float, 1: float, 2: float, 3: float}
     */
    public function weights(?float $indobert = null): array
    {
        $w = [0.40, 0.25, 0.20, 0.15];

        if ($indobert === null) {
            $lost = $w[1];
            $remaining = array_sum([$w[0], $w[2], $w[3]]);
            if ($remaining > 0) {
                $w[0] += $lost * ($w[0] / $remaining);
                $w[2] += $lost * ($w[2] / $remaining);
                $w[3] += $lost * ($w[3] / $remaining);
            }
            $w[1] = 0;
        }

        return $w;
    }

    /**
     * @return array{0: int, 1: string}
     */
    protected function blend(?float $xgboost, ?float $indobert, float $community, float $rule): array
    {
        [$wx, $wi, $wc, $wr] = $this->weights($indobert);

        $score = 0.0;
        $score += $wx * ($xgboost !== null ? $xgboost * 100 : $rule);
        if ($indobert !== null) {
            $score += $wi * $indobert * 100;
        }
        $score += $wc * $community;
        $score += $wr * $rule;

        $score = (int) round(max(0, min($score, 100)));

        return [$score, $this->classify($score)];
    }

    public function classify(int $score): string
    {
        return match (true) {
            $score >= 75 => 'high',
            $score >= 50 => 'risky',
            $score >= 25 => 'caution',
            default => 'low',
        };
    }

    protected function buildExplanation(string $level, array $factors): string
    {
        $label = match ($level) {
            'low' => 'risiko rendah',
            'caution' => 'perlu kewaspadaan',
            'risky' => 'berisiko',
            'high' => 'risiko tinggi',
            default => 'berisiko',
        };

        $top = count($factors) > 0 ? implode(', ', array_slice($factors, 0, 3)) : 'belum ada data komunitas';

        return "Nomor ini memiliki {$label} berdasarkan pola laporan dan informasi komunitas: {$top}.";
    }

    protected function activeModelVersion(string $algorithm): string
    {
        $active = \App\Models\MlModel::where('algorithm', $algorithm)->active()->latest('id')->first();

        return $active?->model_version ?? '0.0.0';
    }
}