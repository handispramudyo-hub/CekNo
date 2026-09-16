<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_sends_reset_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson('/api/auth/forgot-password', ['email' => $user->email])
            ->assertOk()
            ->assertJsonStructure(['message']);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_forgot_password_stays_neutral_for_unknown_email(): void
    {
        Notification::fake();

        $this->postJson('/api/auth/forgot-password', ['email' => 'unknown@example.com'])
            ->assertOk();

        Notification::assertNothingSent();
    }

    public function test_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create(['password' => 'password123']);
        $token = Password::broker()->createToken($user);

        $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertOk();

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_reset_password_rejects_invalid_token(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/auth/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertStatus(422);
    }
}