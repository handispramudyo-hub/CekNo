<?php

namespace App\Services;

use App\Models\PhoneNumber;
use App\Models\RiskAssessment;

/**
 * Menghitung rule_score (0-100) dan seluruh metrik fitur komunitas dari data approved.
 *
 * Rule Engine (cap 100):
 *  +40 jika >=3 approved report kategori fraud/phishing
 *  +20 jika unique_reporters >= 8
 *  +15 jika average_rating <= 2.0
 *  +10 jika recent_reports (approved, 7 hari) >= 3
 *  +10 jika search_count >= 200
 *  +5  jika ada tag penipuan/spam/phishing
 */
class RuleEngine
{
    public function metrics(PhoneNumber $phoneNumber): array
    {
        $now = now();

        return [
            'total_reports' => (int) $phoneNumber->reports()->where('status', 'approved')->count(),
            'fraud_reports' => (int) $phoneNumber->reports()->where('status', 'approved')->whereIn('category', ['fraud', 'phishing'])->count(),
            'spam_reports' => (int) $phoneNumber->reports()->where('status', 'approved')->whereIn('category', ['spam', 'telemarketing'])->count(),
            'unique_reporters' => (int) $phoneNumber->reports()->where('status', 'approved')->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
            'total_reviews' => (int) $phoneNumber->reviews()->where('status', 'approved')->count(),
            'average_rating' => (float) $phoneNumber->reviews()->where('status', 'approved')->avg('rating') ?? 0.0,
            'negative_reviews' => (int) $phoneNumber->reviews()->where('status', 'approved')->where('rating', '<=', 2)->count(),
            'recent_reports' => (int) $phoneNumber->reports()->where('status', 'approved')->where('created_at', '>=', $now->subDays(7))->count(),
            'search_count' => (int) $phoneNumber->search_count,
            'tag_count' => (int) $phoneNumber->phoneTags()->where('status', 'approved')->count(),
            'fraud_tags' => (int) $phoneNumber->phoneTags()
                ->where('status', 'approved')
                ->whereHas('tag', fn ($q) => $q->whereIn('slug', ['penipuan', 'spam', 'phishing', 'telemarketing', 'bank-palsu', 'link-apk-bahaya']))
                ->count(),
        ];
    }

    public function communityScore(array $m): float
    {
        $riskyReports = $m['fraud_reports'] + $m['spam_reports'];

        $r = min($riskyReports / 20, 1);
        $u = min($m['unique_reporters'] / 15, 1);
        $t = min($m['fraud_tags'] / 5, 1);
        $negRatio = $m['total_reviews'] > 0 ? min($m['negative_reviews'] / $m['total_reviews'], 1) : 0;

        $score = 100 * (0.40 * $r + 0.25 * $u + 0.20 * $t + 0.15 * $negRatio);

        return round(max(0, min($score, 100)), 2);
    }

    public function ruleScore(array $m): float
    {
        $score = 0;

        if ($m['fraud_reports'] >= 3) {
            $score += 40;
        }
        if ($m['unique_reporters'] >= 8) {
            $score += 20;
        }
        if ($m['average_rating'] > 0 && $m['average_rating'] <= 2.0) {
            $score += 15;
        }
        if ($m['recent_reports'] >= 3) {
            $score += 10;
        }
        if ($m['search_count'] >= 200) {
            $score += 10;
        }
        if ($m['fraud_tags'] > 0) {
            $score += 5;
        }

        return (float) min($score, 100);
    }

    public function explainFactors(array $m, float $community, float $rule): array
    {
        $factors = [];

        if ($m['fraud_reports'] >= 1) {
            $factors[] = $m['fraud_reports'].' laporan penipuan/phishing yang disetujui';
        }
        if ($m['unique_reporters'] >= 1) {
            $factors[] = $m['unique_reporters'].' pengguna berbeda melaporkan nomor ini';
        }
        if ($m['negative_reviews'] >= 1) {
            $factors[] = $m['negative_reviews'].' review bernada negatif';
        }
        if ($m['recent_reports'] >= 1) {
            $factors[] = $m['recent_reports'].' laporan terjadi dalam 7 hari terakhir';
        }
        if ($m['search_count'] >= 50) {
            $factors[] = 'Ditelusuri '.$m['search_count'].' kali oleh komunitas';
        }
        if ($m['fraud_tags'] >= 1) {
            $factors[] = $m['fraud_tags'].' tag berisiko (penipuan/spam/phishing)';
        }
        if (count($factors) === 0) {
            $factors[] = 'Belum ada laporan atau tanda berisiko dari komunitas';
        }

        return $factors;
    }
}