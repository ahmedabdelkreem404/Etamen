<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_id_and_role_are_not_trusted_from_registration_request(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'user_id' => 999,
            'role' => UserRole::SuperAdmin->value,
            'name' => 'Patient Two',
            'email' => 'patient-two@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertCreated();

        $user = User::query()->where('email', 'patient-two@example.com')->firstOrFail();

        $this->assertNotSame(999, $user->id);
        $this->assertTrue($user->hasRole(UserRole::Patient->value));
        $this->assertFalse($user->hasRole(UserRole::SuperAdmin->value));
    }

    public function test_unauthenticated_access_is_blocked(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_payment_status_is_owned_by_authenticated_user(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        Role::findOrCreate(UserRole::Patient->value);
        $otherUser->assignRole(UserRole::Patient->value);

        $payment = Payment::query()->create([
            'user_id' => $owner->id,
            'amount' => 150,
            'currency' => 'EGP',
            'status' => PaymentStatus::Draft,
        ]);

        Sanctum::actingAs($otherUser);

        $this->getJson('/api/v1/payments/'.$payment->id.'/status')
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_payment_cannot_be_marked_verified_from_public_api(): void
    {
        $user = User::factory()->create();

        $payment = Payment::query()->create([
            'user_id' => $user->id,
            'amount' => 200,
            'currency' => 'EGP',
            'status' => PaymentStatus::Draft,
        ]);

        $this->postJson('/api/v1/payments/'.$payment->id.'/status', [
            'status' => PaymentStatus::Verified->value,
            'user_id' => $user->id,
        ])->assertStatus(405);

        $this->assertSame(PaymentStatus::Draft, $payment->refresh()->status);
    }
}
