<?php

namespace Database\Factories;

use App\Models\MlModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MlModel>
 */
class MlModelFactory extends Factory
{
    public function definition(): array
    {
        $algorithm = fake()->randomElement(['xgboost', 'indobert']);

        return [
            'algorithm' => $algorithm,
            'model_version' => fake()->numerify('1.#.0'),
            'dataset_version' => '1.0.0',
            'training_date' => now(),
            'metrics' => [
                'accuracy' => fake()->randomFloat(4, 0.8, 0.98),
                'f1' => fake()->randomFloat(4, 0.78, 0.97),
                'auc' => fake()->randomFloat(4, 0.85, 0.99),
            ],
            'status' => 'experiment',
            'model_path' => fake()->optional(0.9)->filePath(),
            'storage_size_kb' => fake()->numberBetween(10, 50000),
        ];
    }
}