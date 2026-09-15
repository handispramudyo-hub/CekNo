<?php

namespace Database\Factories;

use App\Models\PhoneNumber;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    public function definition(): array
    {
        $category = fake()->randomElement(['fraud', 'bank_otp', 'pinjol', 'spam', 'scam_identity', 'other']);

        return [
            'phone_number_id' => PhoneNumber::factory(),
            'user_id' => User::factory(),
            'category' => $category,
            'description' => fake()->sentence(10),
            'evidence' => fake()->optional()->url(),
            'evidence_type' => 'text',
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