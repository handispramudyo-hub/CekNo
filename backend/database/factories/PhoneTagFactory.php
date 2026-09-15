<?php

namespace Database\Factories;

use App\Models\PhoneTag;
use App\Models\PhoneNumber;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PhoneTag>
 */
class PhoneTagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'phone_number_id' => PhoneNumber::factory(),
            'tag_id' => Tag::factory(),
            'user_id' => User::factory(),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
        ];
    }
}