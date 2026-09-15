<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * HTTP client ke ML Service (FastAPI) — hanya diakses secara internal.
 */
class MlServiceClient
{
    public function __construct(protected string $baseUrl = '')
    {
        $this->baseUrl = config('ml.url', 'http://127.0.0.1:8001');
    }

    public function isHealthy(): bool
    {
        try {
            return Http::timeout(2)->get($this->baseUrl.'/ml/health')->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    public function predictRisk(array $features): float
    {
        $response = Http::timeout(3)->post($this->baseUrl.'/ml/predict/risk', $features);

        $response->throw();

        $data = $response->json();

        return (float) ($data['fraud_probability'] ?? 0.0);
    }

    public function analyzeComment(string $text): array
    {
        $response = Http::timeout(15)->post($this->baseUrl.'/ml/analyze/comment', ['text' => $text]);

        $response->throw();

        return $response->json();
    }
}