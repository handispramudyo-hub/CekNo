<?php

namespace Database\Factories;

use App\Models\PhoneNumber;
use App\Models\SearchHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SearchHistory>
 */
class SearchHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'guest_id' => null,
            'phone_number_id' => PhoneNumber::factory(),
            'searched_at' => now(),
        ];
    }

    public function guest(): static
    {
        return $this->state(fn () => [
            'user_id' => null,
            'guest_id' => (string) fake()->uuid(),
        ]);
    }
}