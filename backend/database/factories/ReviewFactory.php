<?php

namespace Database\Factories;

use App\Models\PhoneNumber;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        $rating = fake()->numberBetween(1, 5);

        return [
            'phone_number_id' => PhoneNumber::factory(),
            'user_id' => User::factory(),
            'rating' => $rating,
            'comment' => fake()->boolean(70) ? fake()->sentence(12) : null,
            'sentiment' => $rating <= 2 ? 'negative' : ($rating === 3 ? 'neutral' : 'positive'),
            'fraud_probability' => $rating <= 2 ? fake()->randomFloat(2, 0.5, 0.95) : fake()->randomFloat(2, 0.01, 0.3),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'moderated_by' => User::factory()->create(['role' => 'admin', 'status' => 'active']),
            'moderated_at' => now(),
        ]);
    }
}