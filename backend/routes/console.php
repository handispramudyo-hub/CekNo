<?php

use App\Models\ContactContribution;
use App\Services\ContributionAggregator;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('contributions:aggregate', function (ContributionAggregator $aggregator) {
    $phoneIds = ContactContribution::query()
        ->where('status', ContactContribution::STATUS_APPROVED)
        ->select('phone_number_id')
        ->distinct()
        ->pluck('phone_number_id');

    $phones = \App\Models\PhoneNumber::whereIn('id', $phoneIds)->get();
    $updated = $aggregator->bulk($phones);

    $this->info("Aggregated contributions for {$updated} numbers.");
})->purpose('Recompute total_contributions & contributor_count per number');
