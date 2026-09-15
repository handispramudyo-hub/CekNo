<?php

namespace Database\Factories;

use App\Models\PhoneNumber;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PhoneNumber>
 */
class PhoneNumberFactory extends Factory
{
    public function definition(): array
    {
        $raw = '08'.fake()->numerify('##########');

        return [
            'phone_number' => $raw,
            'normalized_number' => '+62'.Str::after($raw, '0'),
            'risk_score' => 0.0,
            'risk_level' => 'low',
            'search_count' => 0,
            'status' => 'active',
        ];
    }

    public function risky(int $score = 78): static
    {
        return $this->state(fn () => [
            'risk_score' => $score,
            'risk_level' => $score >= 75 ? 'high' : ($score >= 50 ? 'risky' : ($score >= 25 ? 'caution' : 'low')),
        ]);
    }
}