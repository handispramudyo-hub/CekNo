<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/user/profile', [
                'name' => 'Nama Baru',
                'phone' => '+6281234567890',
            ])
            ->assertOk()
            ->assertJsonPath('user.name', 'Nama Baru');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru',
            'phone' => '+6281234567890',
        ]);
    }

    public function test_update_profile_requires_name(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/user/profile', ['name' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_update_password_rejects_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => 'password123']);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/user/password', [
                'current_password' => 'wrong-password',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('current_password');

        $this->assertTrue(Hash::check('password123', $user->fresh()->password));
    }

    public function test_update_password_then_login_with_new_password(): void
    {
        $user = User::factory()->create(['password' => 'password123']);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/user/password', [
                'current_password' => 'password123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertOk();

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'newpassword123',
        ])->assertOk()->assertJsonStructure(['user', 'token']);
    }

    public function test_email_can_be_verified_with_valid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $this->postJson('/api/auth/email/verify', [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ])->assertOk();

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_email_verification_rejects_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $this->postJson('/api/auth/email/verify', [
            'id' => $user->id,
            'hash' => 'not-a-valid-hash',
        ])->assertStatus(422);
    }
}