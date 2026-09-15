<?php

namespace Database\Factories;

use App\Models\ModerationLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ModerationLog>
 */
class ModerationLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'admin_id' => User::factory()->create(['role' => 'admin', 'status' => 'active']),
            'target_type' => fake()->randomElement(['report', 'review', 'phone_tag']),
            'target_id' => fake()->numberBetween(1, 1000),
            'action' => fake()->randomElement(['approved', 'rejected']),
            'reason' => fake()->optional()->sentence(),
        ];
    }
}