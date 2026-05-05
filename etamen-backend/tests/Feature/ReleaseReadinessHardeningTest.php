<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Domain\Enums\NotificationPriority;
use App\Modules\Notifications\Infrastructure\Models\Notification;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReleaseReadinessHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_system_health_is_public_minimal_and_readiness_is_admin_only(): void
    {
        $this->getJson('/api/v1/system/health')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonMissingPath('data.app_key')
            ->assertJsonMissingPath('data.database_password');

        $this->getJson('/api/v1/system/readiness')->assertUnauthorized();

        Sanctum::actingAs($this->patientUser());
        $this->getJson('/api/v1/system/readiness')->assertForbidden();

        Sanctum::actingAs($this->adminUser());
        $this->getJson('/api/v1/system/readiness')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.checks.database.status', 'ok')
            ->assertJsonPath('data.checks.private_storage.status', 'ok');
    }

    public function test_standard_error_shape_for_validation_auth_authorization_and_not_found(): void
    {
        $this->postJson('/api/v1/auth/login', [])
            ->assertUnprocessable()
            ->assertJsonStructure(['success', 'message', 'data', 'errors']);

        $this->getJson('/api/v1/me')
            ->assertUnauthorized()
            ->assertJsonStructure(['success', 'message', 'data', 'errors']);

        Sanctum::actingAs($this->patientUser());
        $this->getJson('/api/v1/admin/providers')
            ->assertForbidden()
            ->assertJsonStructure(['success', 'message', 'data', 'errors']);

        $this->getJson('/api/v1/does-not-exist')
            ->assertNotFound()
            ->assertJsonStructure(['success', 'message', 'data', 'errors']);
    }

    public function test_public_list_limits_and_paginated_endpoint_max_per_page_are_enforced(): void
    {
        for ($index = 0; $index < 105; $index++) {
            Provider::query()->create([
                'type' => ProviderType::Doctor,
                'owner_user_id' => User::factory()->create()->id,
                'name_en' => 'Doctor '.$index,
                'slug' => 'doctor-'.$index,
                'status' => ProviderStatus::Approved,
                'is_active' => true,
                'approved_at' => now(),
            ]);
        }

        $this->getJson('/api/v1/doctors?per_page=200')
            ->assertOk()
            ->assertJsonCount(100, 'data');

        $user = $this->patientUser('notifications-limit@example.com');
        for ($index = 0; $index < 120; $index++) {
            Notification::query()->create([
                'user_id' => $user->id,
                'category' => NotificationCategory::System,
                'type' => 'system_notice',
                'title' => 'Notice',
                'body' => 'Body',
                'priority' => NotificationPriority::Normal,
            ]);
        }

        Sanctum::actingAs($user);
        $this->getJson('/api/v1/notifications?per_page=200')
            ->assertOk()
            ->assertJsonCount(100, 'data');
    }

    public function test_sensitive_routes_have_expected_rate_limit_middleware(): void
    {
        $this->assertRouteHasMiddleware('POST', '/api/v1/auth/login', 'throttle:auth-sensitive');
        $this->assertRouteHasMiddleware('POST', '/api/v1/appointments', 'throttle:booking');
        $this->assertRouteHasMiddleware('POST', '/api/v1/payments/1/proofs', 'throttle:file-upload');
        $this->assertRouteHasMiddleware('POST', '/api/v1/ai/conversations/1/messages', 'throttle:ai-message');
        $this->assertRouteHasMiddleware('POST', '/api/v1/pharmacy/prescriptions', 'throttle:file-upload');
        $this->assertRouteHasMiddleware('POST', '/api/v1/provider/lab/orders/1/results', 'throttle:file-upload');
        $this->assertRouteHasMiddleware('POST', '/api/v1/notification-tokens', 'throttle:notification-write');
    }

    private function assertRouteHasMiddleware(string $method, string $uri, string $middleware): void
    {
        $route = Route::getRoutes()->match(Request::create($uri, $method));

        $this->assertContains($middleware, $route->gatherMiddleware(), $method.' '.$uri.' is missing '.$middleware);
    }

    private function patientUser(string $email = 'release-patient@example.com'): User
    {
        $user = User::factory()->create(['email' => $email]);
        $user->assignRole(UserRole::Patient->value);

        return $user;
    }

    private function adminUser(): User
    {
        $user = User::factory()->create(['email' => 'release-admin@example.com']);
        $user->assignRole(UserRole::SuperAdmin->value);

        return $user;
    }
}
