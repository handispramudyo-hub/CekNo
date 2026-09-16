<?php

namespace Tests\Feature;

use App\Models\ContactContribution;
use App\Models\PhoneNumber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactContributionTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): array
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        return [$user, $token];
    }

    private function contacts(): array
    {
        return [
            ['phone' => '081299887761', 'label' => 'Penipuan'],
            ['phone' => '085712345678', 'label' => 'Telemarketing'],
        ];
    }

    public function test_sync_requires_valid_consent_version(): void
    {
        [, $token] = $this->authUser();

        $this->withToken($token)
            ->postJson('/api/user/contact-contributions/sync', [
                'consent_version' => '9.9',
                'contacts' => $this->contacts(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('consent_version');
    }

    public function test_sync_creates_pending_contributions_and_consent_log(): void
    {
        [, $token] = $this->authUser();

        $this->withToken($token)
            ->postJson('/api/user/contact-contributions/sync', [
                'consent_version' => '1.0',
                'contacts' => $this->contacts(),
            ])
            ->assertOk()
            ->assertJsonPath('created', 2)
            ->assertJsonPath('duplicates', 0);

        $this->assertDatabaseHas('consents', ['consent_version' => '1.0', 'scope' => 'contact_contribution']);
        $this->assertDatabaseCount('contact_contributions', 2);
        $this->assertDatabaseHas('contact_contributions', ['label_normalized' => 'penipuan', 'status' => 'pending']);
        $this->assertDatabaseHas('phone_numbers', ['normalized_number' => '+6281299887761']);
    }

    public function test_sync_deduplicates_by_user_number_and_label(): void
    {
        [, $token] = $this->authUser();

        $first = $this->withToken($token)
            ->postJson('/api/user/contact-contributions/sync', [
                'consent_version' => '1.0',
                'contacts' => $this->contacts(),
            ])
            ->assertOk();

        $second = $this->withToken($token)
            ->postJson('/api/user/contact-contributions/sync', [
                'consent_version' => '1.0',
                'contacts' => $this->contacts(),
            ])
            ->assertOk();

        $this->assertSame(2, $first->json('created'));
        $this->assertSame(2, $second->json('duplicates'));
        $this->assertDatabaseCount('contact_contributions', 2);
    }

    public function test_sync_survives_invalid_contact_in_batch(): void
    {
        [, $token] = $this->authUser();

        $this->withToken($token)
            ->postJson('/api/user/contact-contributions/sync', [
                'consent_version' => '1.0',
                'contacts' => [
                    ['phone' => '081299887761', 'label' => 'Penipuan'],
                    ['phone' => 'abc', 'label' => 'Spam'],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('created', 1);

        $this->assertCount(1, $this->withToken($token)->getJson('/api/user/contact-contributions')->json('contributions.data'));
    }

    public function test_user_can_list_own_contributions(): void
    {
        [, $token] = $this->authUser();

        $this->withToken($token)
            ->postJson('/api/user/contact-contributions/sync', [
                'consent_version' => '1.0',
                'contacts' => $this->contacts(),
            ])
            ->assertOk();

        $this->withToken($token)
            ->getJson('/api/user/contact-contributions')
            ->assertOk()
            ->assertJsonCount(2, 'contributions.data');
    }

    public function test_user_can_withdraw_own_contribution(): void
    {
        [$user, $token] = $this->authUser();

        $contribution = ContactContribution::factory()->create(['user_id' => $user->id]);

        $this->withToken($token)
            ->deleteJson("/api/user/contact-contributions/{$contribution->id}")
            ->assertOk();

        $this->assertDatabaseHas('contact_contributions', [
            'id' => $contribution->id,
            'status' => 'withdrawn',
        ]);
    }

    public function test_user_cannot_withdraw_others_contribution(): void
    {
        $contribution = ContactContribution::factory()->create();
        [, $token] = $this->authUser();

        $this->withToken($token)
            ->deleteJson("/api/user/contact-contributions/{$contribution->id}")
            ->assertForbidden();
    }

    public function test_admin_can_moderate_and_aggregate_counts(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $phone = PhoneNumber::factory()->create();

        ContactContribution::factory()->create([
            'user_id' => $user->id, 'phone_number_id' => $phone->id, 'label' => 'Penipuan', 'status' => 'approved',
        ]);
        ContactContribution::factory()->create([
            'user_id' => $user->id, 'phone_number_id' => $phone->id, 'label' => 'Spam', 'status' => 'pending',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test')->plainTextToken;

        $pending = ContactContribution::where('phone_number_id', $phone->id)->where('status', 'pending')->first();

        $this->withToken($token)
            ->postJson("/api/admin/contributions/{$pending->id}/moderate", ['action' => 'approved'])
            ->assertOk()
            ->assertJsonPath('contribution.status', 'approved');

        $this->assertDatabaseHas('moderation_logs', [
            'target_type' => 'contact_contribution',
            'target_id' => $pending->id,
        ]);

        $phone->refresh();
        $this->assertSame(2, $phone->total_contributions, 'aggreggation should count both approved contributions');
        $this->assertSame(1, $phone->contributor_count, 'both contributions come from the same user');

        $secondUser = User::factory()->create();
        ContactContribution::factory()->create([
            'user_id' => $secondUser->id, 'phone_number_id' => $phone->id, 'label' => 'Penagihan', 'status' => 'approved',
        ]);

        \Illuminate\Support\Facades\Artisan::call('contributions:aggregate');

        $phone->refresh();
        $this->assertSame(3, $phone->total_contributions);
        $this->assertSame(2, $phone->contributor_count);
    }

    public function test_non_admin_cannot_moderate_contributions(): void
    {
        [, $token] = $this->authUser();
        $contribution = ContactContribution::factory()->create();

        $this->withToken($token)
            ->postJson("/api/admin/contributions/{$contribution->id}/moderate", ['action' => 'approved'])
            ->assertForbidden();
    }
}