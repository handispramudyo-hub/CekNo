<?php

namespace Tests\Feature;

use App\Models\ModerationLog;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_moderate(): void
    {
        $user = User::factory()->create();

        $this->withUserToken($user)
            ->getJson('/api/admin/reports')
            ->assertForbidden();
    }

    public function test_admin_can_moderate_report_and_recalculate_risk(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $reporter = User::factory()->create();

        // Search = route publik, lalu buat laporan pending sbg reporter
        $search = $this->withUserToken($reporter)
            ->getJson('/api/numbers/search?phone=081234567890')
            ->assertOk();

        $phoneId = $search->json('phone_number.id');

        $report = Report::create([
            'phone_number_id' => $phoneId,
            'user_id' => $reporter->id,
            'category' => 'fraud',
            'description' => 'Meminta OTP berulang kali kepada korban.',
            'status' => 'pending',
        ]);

        $this->withUserToken($admin)
            ->postJson("/api/admin/reports/{$report->id}/moderate", ['action' => 'approved'])
            ->assertOk()
            ->assertJsonPath('report.status', 'approved');

        $this->assertDatabaseHas('moderation_logs', [
            'target_type' => 'report',
            'target_id' => $report->id,
            'action' => 'approved',
        ]);

        // Skor nomor kini > 0 karena laporan fraud approved
        $this->assertDatabaseHas('risk_assessments', [
            'phone_number_id' => $phoneId,
        ]);
    }

    public function test_admin_review_approval_hides_from_features_until_greenlit(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user = User::factory()->create();

        $search = $this->withUserToken($user)
            ->getJson('/api/numbers/search?phone=087812345678')
            ->assertOk();
        $phoneId = $search->json('phone_number.id');

        $review = Review::create([
            'phone_number_id' => $phoneId,
            'user_id' => $user->id,
            'rating' => 1,
            'comment' => 'Nomor ini mencurigakan, banyak telepon tengah malam.',
            'status' => 'pending',
        ]);

        // Review pending tidak tampil ke publik
        $this->withUserToken($user)
            ->getJson("/api/numbers/{$phoneId}/reviews")
            ->assertOk()
            ->assertJsonCount(0, 'reviews');

        $this->withUserToken($admin)
            ->postJson("/api/admin/reviews/{$review->id}/moderate", ['action' => 'approved'])
            ->assertOk();

        $this->withUserToken($user)
            ->getJson("/api/numbers/{$phoneId}/reviews")
            ->assertOk()
            ->assertJsonCount(1, 'reviews');
    }

    public function test_suspended_user_has_no_access_to_protected_endpoints(): void
    {
        $user = User::factory()->create(['status' => 'suspended']);

        // Cari nomor dulu (route publik) agar dapat id yang valid
        $search = $this->withUserToken($user)
            ->getJson('/api/numbers/search?phone=081234567890')
            ->assertOk();
        $phoneId = $search->json('phone_number.id');

        // Middleware user.active memblokir akses termasuk ke /auth/me
        $this->withUserToken($user)
            ->getJson('/api/auth/me')
            ->assertForbidden();

        $this->withUserToken($user)
            ->postJson("/api/numbers/{$phoneId}/reports", ['category' => 'fraud', 'description' => 'x'])
            ->assertForbidden();
    }
}