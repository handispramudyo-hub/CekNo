<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NumberFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_login_and_search(): void
    {
        $register = $this->postJson('/api/auth/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $register->assertCreated();
        $this->assertDatabaseHas('users', ['email' => 'budi@example.com', 'role' => 'user']);

        $token = $register->json('token');

        $this->withToken($token)
            ->getJson('/api/numbers/search?phone=0812-9988-7766')
            ->assertOk()
            ->assertJsonStructure(['phone_number', 'risk_assessment']);
    }

    public function test_search_normalizes_duplicate_formats_to_same_record(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/numbers/search?phone=081234567890')->assertOk();
        $this->withToken($token)->getJson('/api/numbers/search?phone=+6281234567890')->assertOk();

        $this->assertDatabaseCount('phone_numbers', 1);
        $this->assertDatabaseHas('phone_numbers', ['normalized_number' => '+6281234567890']);
    }

    public function test_guest_can_search_without_identity(): void
    {
        // Guest diperbolehkan search (rate-limited) tanpa menyimpan identitas pribadi.
        $this->getJson('/api/numbers/search?phone=081234567890')->assertOk();

        $this->assertDatabaseHas('phone_numbers', ['normalized_number' => '+6281234567890']);

        // Tidak ada identitas (user_id null) di search history guest.
        $this->assertDatabaseHas('search_histories', ['user_id' => null]);
    }

    public function test_submit_report_goes_pending(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $search = $this->withToken($token)
            ->getJson('/api/numbers/search?phone=0819988776')
            ->assertOk();

        $phoneId = $search->json('phone_number.id');

        $this->withToken($token)
            ->postJson("/api/numbers/{$phoneId}/reports", [
                'category' => 'fraud',
                'description' => 'Mengaku dari bank dan meminta kode OTP untuk verifikasi rekening penuh.',
            ])
            ->assertCreated()
            ->assertJsonPath('report.status', 'pending');
    }
}