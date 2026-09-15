<?php

namespace App\Jobs;

use App\Models\Review;
use App\Services\MlServiceClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AnalyzeReviewWithAi implements ShouldQueue
{
    use Queueable;

    public int $timeout = 60;

    public function __construct(public Review $review)
    {
    }

    public function handle(MlServiceClient $ml): void
    {
        try {
            $result = $ml->analyzeComment($this->review->comment);

            $this->review->forceFill([
                'sentiment' => $result['category'] ?? null,
                'fraud_probability' => $result['fraud_probability'] ?? null,
                'category' => $result['category'] ?? null,
            ])->save();

            $this->review->mlPredictions()->create([
                'phone_number_id' => $this->review->phone_number_id,
                'model_type' => 'indobert',
                'model_version' => '0.0.0',
                'input_features' => ['text' => $this->review->comment],
                'prediction' => $result,
                'confidence' => $result['fraud_probability'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal menganalisis review dengan AI.', ['review_id' => $this->review->id, 'err' => $e->getMessage()]);
            $this->release(30);
        }
    }
}