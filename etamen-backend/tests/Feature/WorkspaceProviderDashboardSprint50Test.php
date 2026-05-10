<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Providers\Domain\Enums\ProviderPermission;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderStaff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkspaceProviderDashboardSprint50Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_patient_receives_patient_workspace_only(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/me/workspaces')
            ->assertOk()
            ->assertJsonPath('data.default_workspace', 'patient')
            ->assertJsonPath('data.available_workspaces.0.type', 'patient')
            ->assertJsonCount(1, 'data.available_workspaces');
    }

    public function test_provider_owner_receives_workspace_and_dashboard_with_permissions(): void
    {
        [$owner, $provider] = $this->providerWithStaff(ProviderType::Hospital, ProviderStaffRole::Owner);

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/me/workspaces')
            ->assertOk()
            ->assertJsonFragment([
                'type' => 'provider',
                'provider_id' => $provider->id,
                'provider_type' => ProviderType::Hospital->value,
                'role' => ProviderStaffRole::Owner->value,
                'is_owner' => true,
            ])
            ->assertJsonMissing(['contract_terms'])
            ->assertJsonMissing(['payment_config']);

        $dashboard = $this->getJson('/api/v1/provider/workspace/'.$provider->id.'/dashboard')
            ->assertOk()
            ->assertJsonPath('data.provider.id', $provider->id)
            ->assertJsonPath('data.role', ProviderStaffRole::Owner->value)
            ->assertJsonPath('data.is_owner', true)
            ->assertJsonMissing(['provider_documents'])
            ->assertJsonMissing(['national_id'])
            ->assertJsonMissing(['payment_config']);

        $this->assertContains(ProviderPermission::ManageStaff->value, $dashboard->json('data.permissions'));
    }

    public function test_staff_workspace_is_limited_and_quick_actions_are_permission_filtered(): void
    {
        [$staff, $provider] = $this->providerWithStaff(
            ProviderType::Gym,
            ProviderStaffRole::Staff,
            [ProviderPermission::ViewGymBookings->value],
        );

        Sanctum::actingAs($staff);

        $response = $this->getJson('/api/v1/provider/workspace/'.$provider->id.'/dashboard')
            ->assertOk()
            ->assertJsonPath('data.role', ProviderStaffRole::Staff->value)
            ->assertJsonPath('data.permissions.0', ProviderPermission::ViewGymBookings->value);

        $actionKeys = collect($response->json('data.quick_actions'))->pluck('key')->all();

        $this->assertContains('bookings', $actionKeys);
        $this->assertNotContains('plans', $actionKeys);
        $this->assertNotContains('classes', $actionKeys);
    }

    public function test_provider_dashboard_rejects_other_provider_staff(): void
    {
        [$staff] = $this->providerWithStaff(ProviderType::Doctor, ProviderStaffRole::Owner);
        [, $otherProvider] = $this->providerWithStaff(ProviderType::Radiology, ProviderStaffRole::Owner);

        Sanctum::actingAs($staff);

        $this->getJson('/api/v1/provider/workspace/'.$otherProvider->id.'/dashboard')
            ->assertForbidden();
    }

    public function test_manage_staff_permission_is_required_and_owner_cannot_be_deleted(): void
    {
        [$owner, $provider, $ownerStaff] = $this->providerWithStaff(ProviderType::Doctor, ProviderStaffRole::Owner);
        [$limitedStaff] = $this->addStaff($provider, ProviderStaffRole::Staff, [ProviderPermission::ViewBookings->value]);
        $newUser = User::factory()->create(['email' => 'workspace.staff@example.test']);

        Sanctum::actingAs($limitedStaff);
        $this->getJson('/api/v1/provider/workspace/'.$provider->id.'/staff')
            ->assertForbidden();

        Sanctum::actingAs($owner);
        $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/staff', [
            'email' => $newUser->email,
            'role' => ProviderStaffRole::Staff->value,
            'permissions' => [ProviderPermission::ViewAppointments->value],
        ])
            ->assertCreated()
            ->assertJsonPath('data.user.email', $newUser->email)
            ->assertJsonPath('data.permissions.0', ProviderPermission::ViewAppointments->value);

        $this->deleteJson('/api/v1/provider/workspace/'.$provider->id.'/staff/'.$ownerStaff->id)
            ->assertUnprocessable();
    }

    public function test_platform_admin_workspace_is_returned_only_for_platform_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::Admin->value);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/me/workspaces')
            ->assertOk()
            ->assertJsonFragment([
                'type' => 'platform_admin',
                'key' => 'platform_admin',
            ]);
    }

    private function providerWithStaff(ProviderType $type, ProviderStaffRole $role, ?array $permissions = null): array
    {
        $user = User::factory()->create();
        $provider = Provider::query()->create([
            'type' => $type,
            'owner_user_id' => $user->id,
            'name_ar' => $type->value.' demo',
            'name_en' => ucfirst(str_replace('_', ' ', $type->value)).' Demo',
            'slug' => $type->value.'-demo-'.uniqid(),
            'status' => ProviderStatus::Approved,
            'is_active' => true,
            'approved_at' => now(),
        ]);

        $staff = ProviderStaff::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $user->id,
            'role' => $role,
            'is_owner' => $role === ProviderStaffRole::Owner,
            'status' => 'active',
            'permissions' => $permissions,
        ]);

        return [$user, $provider, $staff];
    }

    private function addStaff(Provider $provider, ProviderStaffRole $role, ?array $permissions = null): array
    {
        $user = User::factory()->create(['password' => Hash::make('Password1234')]);
        $staff = ProviderStaff::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $user->id,
            'role' => $role,
            'is_owner' => $role === ProviderStaffRole::Owner,
            'status' => 'active',
            'permissions' => $permissions,
        ]);

        return [$user, $staff];
    }
}
