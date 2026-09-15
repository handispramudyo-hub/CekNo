<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Spam', 'slug' => 'spam', 'description' => 'Promo / iklan mengganggu', 'risk_weight' => 30],
            ['name' => 'Penipuan', 'slug' => 'fraud', 'description' => 'Penipuan, undian, phising rekening', 'risk_weight' => 95],
            ['name' => 'Phishing', 'slug' => 'phishing', 'description' => 'Meminta OTP / data pribadi / link palsu', 'risk_weight' => 95],
            ['name' => 'Telemarketing', 'slug' => 'telemarketing', 'description' => 'Telepon penawaran agresif', 'risk_weight' => 20],
            ['name' => 'Pinjaman (Pinjol)', 'slug' => 'loan', 'description' => 'Pinjol, debt collector', 'risk_weight' => 70],
            ['name' => 'Pelecehan / Teror', 'slug' => 'harassment', 'description' => 'Ancaman, teror, pelecehan', 'risk_weight' => 80],
            ['name' => 'Lainnya', 'slug' => 'other', 'description' => 'Modus mencurigakan lainnya', 'risk_weight' => 40],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}