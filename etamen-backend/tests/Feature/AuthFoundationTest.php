<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\AuditLogs\Infrastructure\Models\AuditLog;
use App\Modules\Identity\Domain\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Patient One',
            'email' => 'patient@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonMissingPath('data.user.password')
            ->assertJsonPath('data.user.email', 'patient@example.com')
            ->assertJsonStructure(['data' => ['token', 'token_type']]);

        $user = User::query()->where('email', 'patient@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole(UserRole::Patient->value));
        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.register']);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('Password123'),
        ]);

        Role::findOrCreate(UserRole::Patient->value);
        $user->assignRole(UserRole::Patient->value);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'Password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'token_type', 'user']]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.login',
            'actor_id' => $user->id,
        ]);
    }

    public function test_password_is_hashed_on_registration(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Secure Patient',
            'email' => 'secure@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertCreated();

        $user = User::query()->where('email', 'secure@example.com')->firstOrFail();

        $this->assertNotSame('Password123', $user->password);
        $this->assertTrue(Hash::check('Password123', $user->password));
    }

    public function test_invalid_login_fails(): void
    {
        User::factory()->create([
            'email' => 'invalid-login@example.com',
            'password' => Hash::make('Password123'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'invalid-login@example.com',
            'password' => 'WrongPassword123',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_authenticated_user_can_call_me(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate(UserRole::Patient->value);
        $user->assignRole(UserRole::Patient->value);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_unauthenticated_user_cannot_call_me(): void
    {
        $this->getJson('/api/v1/me')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_logout_invalidates_token(): void
    {
        $user = User::factory()->create([
            'email' => 'logout@example.com',
            'password' => Hash::make('Password123'),
        ]);

        Role::findOrCreate(UserRole::Patient->value);
        $user->assignRole(UserRole::Patient->value);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'logout@example.com',
            'password' => 'Password123',
        ])->assertOk();

        $token = $loginResponse->json('data.token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNull(PersonalAccessToken::findToken($token));
        $this->assertTrue(AuditLog::query()->where('action', 'auth.logout')->exists());
    }
}
