<?php

namespace Tests\Unit;

use App\Models\PhoneNumber;
use App\Services\RuleEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuleEngineTest extends TestCase
{
    use RefreshDatabase;

    private function phone(int $searchCount = 0): PhoneNumber
    {
        return PhoneNumber::create([
            'phone_number' => '+6281234567890',
            'normalized_number' => '+6281234567890',
            'search_count' => $searchCount,
        ]);
    }

    public function test_community_score_zero_with_no_data(): void
    {
        $engine = new RuleEngine;
        $metrics = $engine->metrics($this->phone());
        $this->assertSame(0.0, $engine->communityScore($metrics));
        $this->assertSame(0.0, $engine->ruleScore($metrics));
    }

    public function test_rule_score_accumulates(): void
    {
        $phone = $this->phone(500);
        $users = \App\Models\User::factory()->count(3)->create();

        \App\Models\Report::create([
            'phone_number_id' => $phone->id,
            'category' => 'fraud',
            'description' => 'Mengaku bank meminta OTP berkepanjangan sekali',
            'status' => 'approved',
            'user_id' => $users[0]->id,
            'created_at' => now()->subDays(1),
        ]);

        \App\Models\Report::create([
            'phone_number_id' => $phone->id,
            'category' => 'fraud',
            'description' => 'Kedua mengaku bank meminta OTP berkepanjangan',
            'status' => 'approved',
            'user_id' => $users[1]->id,
            'created_at' => now()->subDays(1),
        ]);

        \App\Models\Report::create([
            'phone_number_id' => $phone->id,
            'category' => 'fraud',
            'description' => 'Ketiga mengaku bank meminta OTP berkepanjangan',
            'status' => 'approved',
            'user_id' => $users[2]->id,
            'created_at' => now()->subDays(1),
        ]);

        \App\Models\Review::create([
            'phone_number_id' => $phone->id,
            'rating' => 1,
            'comment' => 'Mencurigakan',
            'status' => 'approved',
            'user_id' => $users[0]->id,
        ]);

        $engine = new RuleEngine;
        $metrics = $engine->metrics($phone);

        $this->assertGreaterThanOrEqual(3, $metrics['fraud_reports']);
        $this->assertGreaterThanOrEqual(3, $metrics['unique_reporters']);
        $this->assertGreaterThanOrEqual(1, $metrics['recent_reports']);

        $score = $engine->ruleScore($metrics);
        // fraud(40) + unique3(<8 -> 0) + rating 1 (15) + recent (10) + search 500 (10)
        $this->assertSame(75.0, $score);

        $community = $engine->communityScore($metrics);
        $this->assertGreaterThan(0.0, $community);
        $this->assertLessThanOrEqual(100.0, $community);
    }

    public function test_rejected_reports_ignored(): void
    {
        $phone = $this->phone();
        $user = \App\Models\User::factory()->create();
        \App\Models\Report::create([
            'phone_number_id' => $phone->id,
            'category' => 'fraud',
            'description' => 'Laporan yang ditolak tidak boleh memengaruhi skor',
            'status' => 'rejected',
            'user_id' => $user->id,
        ]);

        $engine = new RuleEngine;
        $metrics = $engine->metrics($phone);

        $this->assertSame(0, $metrics['total_reports']);
        $this->assertSame(0.0, $engine->ruleScore($metrics));
    }
}