<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Penipuan Umum', 'Bank / OTP', 'Pinjol Ilegal', 'Spam / Promosi', 'Pelecehan', 'Curi Identitas']);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(4),
            'description' => fake()->sentence(),
            'risk_weight' => fake()->randomElement([10, 20, 30, 40, 50, 60]),
            'status' => 'active',
        ];
    }
}