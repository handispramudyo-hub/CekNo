<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MlModel;
use App\Models\PhoneNumber;
use App\Models\PhoneTag;
use App\Models\Report;
use App\Models\Review;
use App\Models\RiskAssessment;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data demo untuk pengembangan frontend & integrasi.
 * Nomor di-domain 08xx adalah contoh konten komunitas, bukan data pribadi.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@cekno.id'],
            [
                'name' => 'Admin Demo',
                'phone' => '+62811990001',
                'password' => 'password',
                'role' => 'admin',
                'status' => 'active',
            ],
        );

        $user = User::firstOrCreate(
            ['email' => 'user@cekno.id'],
            [
                'name' => 'Budi Santoso',
                'phone' => '+62811990002',
                'password' => 'password',
                'role' => 'user',
                'status' => 'active',
            ],
        );

        $fraud = Category::where('slug', 'like', 'fraud%')->value('id');
        $spam = Category::where('slug', 'like', 'spam%')->value('id');
        $bank = Category::where('slug', 'like', 'bank%')->value('id');
        $tagFraud = Tag::where('slug', 'like', 'penipuan%')->value('id');
        $tagSpam = Tag::where('slug', 'like', 'spam%')->value('id');
        $tagPinjol = Tag::where('slug', 'like', 'telemarketing%')->value('id');

        $phones = [
            ['81299887761', 88, 'high',  41, 2140, 'fraud', 'Mengaku bank BRI meminta OTP untuk "aktivasi rekening korban". Sangat agresif dan meyakinkan.'],
            ['81299887762', 92, 'high',  57, 3310, 'bank_otp', 'Memakai mesin voice dan meminta kode verifikasi. Banyak korban melaporkan kehilangan dana.'],
            ['81299887763', 66, 'risky', 14, 640,  'pinjol', 'Menagih dengan ancaman berulang meski tidak pernah meminjam. Nada bicara intimidatif.'],
            ['81299887764', 58, 'risky', 9,  420,  'spam',   'Sekali daftar promo langsung banjir SMS spam berkali-kali dalam sehari.'],
            ['81299887765', 33, 'caution', 4,  180,  'scam_identity', 'Mengaku dari marketplace meminta verifikasi ulang via link singkat yang mencurigakan.'],
            ['81299887766', 22, 'caution', 2,  95,   'telemarketing', 'Sering kirim SMS promo pinjaman, sesekali menelepon di jam kerja.'],
            ['81299887767', 68, 'risky',  17, 890,  'fraud', 'Mengaku kurir meminta korban klik link APK untuk "lacak paket". Link berbahaya.'],
            ['81299887768', 8,  'low',    0,  30,   'unknown', 'Nomor bisnis biasa, tidak ada laporan mencurigakan.'],
            ['81299887769', 12, 'low',    1,  22,   'unknown', 'Sekali laporan telepon asing, ditinjau dan ditolak admin.'],
        ];

        foreach ($phones as [$sub, $score, $level, $reports, $searches, $label, $description]) {
            $p = PhoneNumber::firstOrCreate(
                ['normalized_number' => '+62'.$sub],
                ['phone_number' => '08'.$sub, 'country_code' => '+62', 'status' => 'active'],
            );

            $p->forceFill([
                'risk_score' => $score,
                'risk_level' => $level,
                'total_reports' => $reports,
                'search_count' => $searches,
            ])->save();

            $categoryId = match ($label) {
                'fraud' => $fraud,
                'bank_otp' => $bank,
                'pinjol' => $spam,
                'spam' => $spam,
                'scam_identity' => $fraud,
                'telemarketing' => $spam,
                default => null,
            };

            for ($i = 0; $i < min($reports, 6); $i++) {
                $hash = hash('sha256', strtolower($label.'|'.$description.'#'.$i));
                Report::firstOrCreate(
                    ['description_hash' => $hash],
                    [
                        'phone_number_id' => $p->id,
                        'user_id' => $i % 2 === 0 ? $admin->id : $user->id,
                        'category_id' => $categoryId,
                        'category' => $label,
                        'description' => $description,
                        'evidence_type' => 'text',
                        'status' => 'approved',
                        'moderated_by' => $admin->id,
                        'moderated_at' => now()->subDays(1),
                        'created_at' => now()->subDays(random_int(1, 28))->subMinutes($i),
                    ],
                );
            }

            if ($reports > 0) {
                for ($i = 0; $i < max(1, intdiv($reports, 8)); $i++) {
                    Review::create([
                        'phone_number_id' => $p->id,
                        'user_id' => $user->id,
                        'rating' => $score >= 50 ? 1 : ($score >= 25 ? 2 : 5),
                        'comment' => $score >= 50
                            ? 'Nomor ini sangat mencurigakan, hampir kena tipu.'
                            : 'Tidak ada masalah, nomor normal.',
                        'sentiment' => $score >= 50 ? 'negative' : 'positive',
                        'fraud_probability' => $score >= 50 ? 0.85 : 0.05,
                        'status' => 'approved',
                        'moderated_by' => $admin->id,
                        'moderated_at' => now()->subHours(3),
                    ]);
                }

                RiskAssessment::firstOrCreate(
                    ['phone_number_id' => $p->id],
                    [
                        'final_score' => $score,
                        'risk_level' => $level,
                        'model_version' => '1.0.0',
                        'community_score' => round($score * 0.38, 2),
                        'rule_score' => round($score * 0.25, 2),
                        'xgboost_probability' => round($score * 0.25 / 100, 4),
                        'indobert_probability' => round($score * 0.12 / 100, 4),
                        'factors' => [
                            'community' => round($score * 0.38, 2),
                            'rules' => round($score * 0.25, 2),
                            'xgboost' => round($score * 0.25, 2),
                            'indobert' => round($score * 0.12, 2),
                        ],
                        'assessed_at' => now()->subHours(2),
                    ],
                );
            }

            $tagId = $score >= 65 ? $tagFraud : ($score >= 25 ? $tagPinjol : $tagSpam);
            PhoneTag::firstOrCreate(
                ['phone_number_id' => $p->id, 'tag_id' => $tagId, 'user_id' => $user->id],
                ['status' => 'approved'],
            );
        }

        if (MlModel::where('algorithm', 'xgboost')->where('model_version', '1.0.0')->doesntExist()) {
            MlModel::create([
                'algorithm' => 'xgboost',
                'model_version' => '1.0.0',
                'dataset_version' => '1.0.0',
                'training_date' => now()->subDays(5),
                'metrics' => ['accuracy' => 0.94, 'f1' => 0.92, 'auc' => 0.97],
                'status' => 'active',
            ]);
            MlModel::create([
                'algorithm' => 'indobert',
                'model_version' => '1.0.0',
                'dataset_version' => '1.0.0',
                'training_date' => now()->subDays(4),
                'metrics' => ['accuracy' => 0.91, 'f1' => 0.89, 'auc' => 0.95],
                'status' => 'active',
            ]);
        }
    }
}