<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = ['Sales', 'Kurir', 'Telemarketing', 'Bank', 'Customer Service', 'Spam', 'Penipuan', 'Phishing', 'Bank Palsu', 'Link APK Bahaya'];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(['slug' => str()->slug($tag)], [
                'name' => $tag,
                'status' => 'approved',
            ]);
        }
    }
}