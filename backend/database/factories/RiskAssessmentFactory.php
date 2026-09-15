<?php

namespace Database\Factories;

use App\Models\PhoneNumber;
use App\Models\RiskAssessment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RiskAssessment>
 */
class RiskAssessmentFactory extends Factory
{
    public function definition(): array
    {
        $score = fake()->randomFloat(1, 0, 100);

        return [
            'phone_number_id' => PhoneNumber::factory(),
            'risk_score' => $score,
            'risk_level' => $score >= 75 ? 'high' : ($score >= 50 ? 'risky' : ($score >= 25 ? 'caution' : 'low')),
            'model_version' => '0.0.0',
            'factors' => [
                'community' => fake()->randomFloat(2, 0, 100),
                'rules' => fake()->randomFloat(2, 0, 100),
                'xgboost' => fake()->randomFloat(2, 0, 100),
                'indobert' => null,
            ],
            'assessed_at' => now(),
        ];
    }
}