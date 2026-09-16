<?php

namespace Database\Factories;

use App\Models\ContactContribution;
use App\Models\PhoneNumber;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactContribution>
 */
class ContactContributionFactory extends Factory
{
    public function definition(): array
    {
        $label = fake()->randomElement(['Penipuan', 'Telemarketing', 'Spam', 'Penagihan', 'Nakal']);

        return [
            'user_id' => User::factory(),
            'phone_number_id' => PhoneNumber::factory(),
            'label' => $label,
            'label_normalized' => strtolower(trim($label)),
            'category' => 'general',
            'consent_version' => '1.0',
            'status' => fake()->randomElement(['pending', 'approved', 'rejected', 'withdrawn']),
        ];
    }
}