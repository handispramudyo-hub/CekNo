<?php

namespace App\Services;

use App\Models\ContactContribution;
use App\Models\PhoneNumber;
use Illuminate\Database\Eloquent\Collection;

class ContributionAggregator
{
    public function forNumber(PhoneNumber $phone): void
    {
        $stats = ContactContribution::query()
            ->where('phone_number_id', $phone->id)
            ->where('status', ContactContribution::STATUS_APPROVED)
            ->selectRaw('COUNT(*) as total, COUNT(DISTINCT user_id) as contributors')
            ->first();

        $phone->forceFill([
            'total_contributions' => (int) ($stats->total ?? 0),
            'contributor_count' => (int) ($stats->contributors ?? 0),
        ])->save();
    }

    public function bulk(Collection $phoneNumbers): int
    {
        $updated = 0;

        foreach ($phoneNumbers as $phone) {
            $this->forNumber($phone);
            $updated++;
        }

        return $updated;
    }
}