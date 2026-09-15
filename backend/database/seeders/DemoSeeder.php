<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\PhoneNumber;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data demo untuk pengembangan frontend & integrasi.
 * Nomor telko di-domain 0812xxxx — contoh konten komunitas, bukan data pribadi.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $fraud = Category::where('slug', 'fraud')->value('id');
        $spam = Category::where('slug', 'spam')->value('id');
        $phishing = Category::where('slug', 'phishing')->value('id');

        $phones = [
            ['81299887766', 85, 'high', 12, 540],
            ['81299887767', 61, 'risky', 7, 210],
            ['81299887768', 18, 'low', 0, 45],
        ];

        foreach ($phones as [$sub, $score, $level, $reports, $searches]) {
            $p = PhoneNumber::firstOrCreate(
                ['normalized_number' => '+628'.$sub],
                [
                    'phone_number' => '08'.$sub,
                    'country_code' => '+62',
                    'status' => 'active',
                    'search_count' => $searches,
                ],
            );

            $p->forceFill(['risk_score' => $score, 'risk_level' => $level])->save();

            if ($reports > 0) {
                $cat = in_array($level, ['high', 'risky']) ? $fraud : $spam;
                $hash = hash('sha256', strtolower('fraud|Mengaku dari kurir paket minta klik link APK.'));
                DB::table('reports')->insert([
                    'phone_number_id' => $p->id,
                    'category_id' => $cat,
                    'category' => in_array($level, ['high', 'risky']) ? 'fraud' : 'spam',
                    'description' => 'Mengaku dari kurir paket minta klik link APK. Untung sudah cek di sini dulu.',
                    'evidence' => $level === 'high' ? 'https://example.com/screenshot' : null,
                    'evidence_type' => 'text',
                    'description_hash' => $hash,
                    'status' => 'approved',
                    'created_at' => now()->subDays(2),
                    'updated_at' => now()->subDays(2),
                ]);
            }
        }
    }
}