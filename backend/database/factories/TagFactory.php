<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Fraud', 'Spam', 'PINJOL', 'Telepon', 'Tidak Dikenal', 'Penagihan', 'SMS Ambigu']);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(4),
            'status' => 'active',
        ];
    }
}