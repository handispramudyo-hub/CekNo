<?php

namespace App\Services;

use App\Models\PhoneNumber;
use App\Models\User;

/**
 * Anti-abuse detection:
 *  - Fake reports      : rate limit per user/guest + batas kuota.
 *  - Duplicate reports : hash(category+description) per phone dalam X hari.
 *  - Coordinated abuse : N akun baru (umur < 48 jam) melaporkan nomor sama -> flag suspicious_activity.
 */
class AntiAbuseService
{
    public function markDuplicateIfNeeded(PhoneNumber $phoneNumber, string $category, string $description): void
    {
        $hash = hash('sha256', strtolower($category.'|'.$description));
        $duplicate = $phoneNumber->reports()
            ->where('description_hash', $hash)
            ->where('created_at', '>=', now()->subDays(7))
            ->first();

        if ($duplicate) {
            report(new \RuntimeException("Duplicate report detected for phone #{$phoneNumber->id}"));
        }
    }

    public function flagCoordinatedAbuse(PhoneNumber $phoneNumber): void
    {
        $recentAccounts = $phoneNumber->reports()
            ->whereHas('user', function ($q) {
                $q->where('created_at', '>=', now()->subHours(48));
            })
            ->distinct('user_id')
            ->count('user_id');

        if ($recentAccounts >= 10) {
            $phoneNumber->reports()
                ->whereHas('user')
                ->get()
                ->each(function ($report) {
                    $report->user?->forceFill(['suspicious_activity' => true])->save();
                });

            $phoneNumber->forceFill(['status' => 'hidden'])->save();
        }
    }
}