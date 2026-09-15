<?php

namespace Database\Factories;

use App\Models\PhoneNumber;
use App\Models\MlPrediction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MlPrediction>
 */
class MlPredictionFactory extends Factory
{
    public function definition(): array
    {
        $modelType = fake()->randomElement(['xgboost', 'indobert']);

        return [
            'phone_number_id' => PhoneNumber::factory(),
            'review_id' => null,
            'model_type' => $modelType,
            'model_version' => '1.0.0',
            'input_features' => $modelType === 'xgboost'
                ? ['fraud_reports' => 1, 'unique_reporters' => 1, 'neg_reviews' => 1]
                : ['text' => 'Nomin contoh ulasan'],
            'prediction' => ['fraud_probability' => fake()->randomFloat(4, 0, 1)],
            'confidence' => fake()->randomFloat(4, 0.5, 1.0),
        ];
    }
}