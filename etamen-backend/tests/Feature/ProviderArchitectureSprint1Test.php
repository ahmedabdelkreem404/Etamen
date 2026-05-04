<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\MedicalFiles\Infrastructure\Models\UploadedFile as UploadedFileModel;
use App\Modules\Patients\Infrastructure\Models\PatientProfile;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\LabProfile;
use App\Modules\Providers\Infrastructure\Models\PharmacyProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderStaff;
use App\Modules\Providers\Infrastructure\Models\Specialty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProviderArchitectureSprint1Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_patient_registration_creates_user_profile_and_role(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Patient One',
            'email' => 'patient-one@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertCreated();

        $user = User::query()->where('email', 'patient-one@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole(UserRole::Patient->value));
        $this->assertTrue(PatientProfile::query()->where('user_id', $user->id)->exists());
    }

    public function test_doctor_registration_creates_provider_profile_owner_staff_and_role(): void
    {
        $response = $this->registerDoctor('doctor-one@example.com');

        $response->assertCreated()->assertJsonPath('data.provider.type', ProviderType::Doctor->value);

        $user = User::query()->where('email', 'doctor-one@example.com')->firstOrFail();
        $provider = Provider::query()->where('owner_user_id', $user->id)->firstOrFail();

        $this->assertTrue($user->hasRole(UserRole::Doctor->value));
        $this->assertSame(ProviderType::Doctor, $provider->type);
        $this->assertSame(ProviderStatus::PendingReview, $provider->status);
        $this->assertFalse($provider->is_active);
        $this->assertTrue(DoctorProfile::query()->where('provider_id', $provider->id)->exists());
        $this->assertTrue(ProviderStaff::query()->where('provider_id', $provider->id)->where('user_id', $user->id)->where('is_owner', true)->exists());
        $this->assertTrue($provider->approvalRequests()->where('status', 'pending')->exists());
    }

    public function test_pharmacy_registration_creates_provider_profile_owner_staff_and_role(): void
    {
        $this->postJson('/api/v1/providers/register-pharmacy', $this->providerPayload('pharmacy-one@example.com', 'Pharmacy One') + [
            'license_number' => 'PH-123',
            'delivery_available' => true,
        ])->assertCreated();

        $user = User::query()->where('email', 'pharmacy-one@example.com')->firstOrFail();
        $provider = Provider::query()->where('owner_user_id', $user->id)->firstOrFail();

        $this->assertTrue($user->hasRole(UserRole::PharmacyAdmin->value));
        $this->assertSame(ProviderType::Pharmacy, $provider->type);
        $this->assertTrue(PharmacyProfile::query()->where('provider_id', $provider->id)->exists());
        $this->assertTrue($provider->staff()->where('user_id', $user->id)->where('is_owner', true)->exists());
    }

    public function test_lab_registration_creates_provider_profile_owner_staff_and_role(): void
    {
        $this->postJson('/api/v1/providers/register-lab', $this->providerPayload('lab-one@example.com', 'Lab One') + [
            'license_number' => 'LAB-123',
            'home_collection_available' => true,
        ])->assertCreated();

        $user = User::query()->where('email', 'lab-one@example.com')->firstOrFail();
        $provider = Provider::query()->where('owner_user_id', $user->id)->firstOrFail();

        $this->assertTrue($user->hasRole(UserRole::LabAdmin->value));
        $this->assertSame(ProviderType::Lab, $provider->type);
        $this->assertTrue(LabProfile::query()->where('provider_id', $provider->id)->exists());
        $this->assertTrue($provider->staff()->where('user_id', $user->id)->where('is_owner', true)->exists());
    }

    public function test_unapproved_providers_are_hidden_publicly(): void
    {
        $this->createProvider(ProviderType::Doctor, ProviderStatus::PendingReview, false);

        $this->getJson('/api/v1/doctors')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_approved_providers_appear_publicly(): void
    {
        $provider = $this->createProvider(ProviderType::Doctor, ProviderStatus::Approved, true);

        $this->getJson('/api/v1/doctors')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $provider->id);
    }

    public function test_suspended_providers_disappear_publicly(): void
    {
        $this->createProvider(ProviderType::Doctor, ProviderStatus::Suspended, false);

        $this->getJson('/api/v1/doctors')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_admin_can_approve_and_reject_provider(): void
    {
        $admin = $this->adminUser();
        Sanctum::actingAs($admin);

        $provider = $this->createProvider(ProviderType::Doctor, ProviderStatus::PendingReview, false);

        $this->postJson('/api/v1/admin/providers/'.$provider->id.'/approve')
            ->assertOk()
            ->assertJsonPath('data.status', ProviderStatus::Approved->value);

        $this->assertTrue($provider->refresh()->is_active);

        $this->postJson('/api/v1/admin/providers/'.$provider->id.'/reject', ['notes' => 'Missing license'])
            ->assertOk()
            ->assertJsonPath('data.status', ProviderStatus::Rejected->value);

        $this->assertFalse($provider->refresh()->is_active);
    }

    public function test_non_admin_cannot_approve_or_reject_provider(): void
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::Patient->value);
        Sanctum::actingAs($user);

        $provider = $this->createProvider(ProviderType::Doctor, ProviderStatus::PendingReview, false);

        $this->postJson('/api/v1/admin/providers/'.$provider->id.'/approve')->assertForbidden();
        $this->postJson('/api/v1/admin/providers/'.$provider->id.'/reject')->assertForbidden();
    }

    public function test_provider_owner_can_update_own_provider_only(): void
    {
        $first = $this->registerDoctor('owner-one@example.com')->json('data');
        $second = $this->registerDoctor('owner-two@example.com')->json('data');

        $token = $first['token'];
        $otherProviderId = $second['provider']['id'];

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/provider/profile', [
                'name_en' => 'Updated Own Provider',
                'phone' => '01000000000',
            ])
            ->assertOk()
            ->assertJsonPath('data.name_en', 'Updated Own Provider');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/provider/profile', [
                'provider_id' => $otherProviderId,
                'name_en' => 'Attempt Other Provider Update',
            ])
            ->assertUnprocessable();

        $this->assertNotSame('Attempt Other Provider Update', Provider::query()->findOrFail($otherProviderId)->name_en);
    }

    public function test_branch_cannot_be_edited_by_non_owner(): void
    {
        $ownerProvider = $this->createProvider(ProviderType::Doctor, ProviderStatus::PendingReview, false);
        $otherProvider = $this->createProvider(ProviderType::Doctor, ProviderStatus::PendingReview, false);
        $branch = $otherProvider->branches()->create(['name_en' => 'Other Branch']);

        Sanctum::actingAs($ownerProvider->owner);

        $this->putJson('/api/v1/provider/branches/'.$branch->id, ['name_en' => 'Hacked Branch'])
            ->assertForbidden();
    }

    public function test_provider_documents_are_private_and_public_response_does_not_expose_raw_path(): void
    {
        Storage::fake('medical_private');

        $registered = $this->registerDoctor('docs-owner@example.com')->json('data');

        $response = $this->withHeader('Authorization', 'Bearer '.$registered['token'])
            ->post('/api/v1/provider/documents', [
                'document_type' => 'license',
                'file' => UploadedFile::fake()->create('license.pdf', 100, 'application/pdf'),
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.file.visibility', 'private')
            ->assertJsonMissingPath('data.file.path')
            ->assertJsonMissingPath('data.file.url');

        $file = UploadedFileModel::query()->firstOrFail();

        $this->assertSame('medical_private', $file->disk);
        $this->assertSame('private', $file->visibility->value);
    }

    public function test_admin_can_create_and_update_specialty(): void
    {
        Sanctum::actingAs($this->adminUser());

        $response = $this->postJson('/api/v1/admin/specialties', [
            'name_ar' => 'قلب',
            'name_en' => 'Cardiology',
        ])->assertCreated();

        $specialtyId = $response->json('data.id');

        $this->putJson('/api/v1/admin/specialties/'.$specialtyId, [
            'name_ar' => 'قلب وأوعية',
            'name_en' => 'Cardiology and Vascular',
            'is_active' => true,
        ])
            ->assertOk()
            ->assertJsonPath('data.name_en', 'Cardiology and Vascular');
    }

    public function test_non_admin_cannot_create_specialty(): void
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::Patient->value);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/admin/specialties', [
            'name_ar' => 'جلدية',
            'name_en' => 'Dermatology',
        ])->assertForbidden();
    }

    private function providerPayload(string $email, string $providerName): array
    {
        return [
            'name' => $providerName.' Owner',
            'email' => $email,
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'provider_name_en' => $providerName,
            'provider_name_ar' => $providerName,
            'phone' => '01012345678',
            'branch_name_en' => $providerName.' Main Branch',
            'address_en' => 'Main address',
        ];
    }

    private function registerDoctor(string $email)
    {
        $specialty = Specialty::query()->create([
            'name_ar' => 'باطنة',
            'name_en' => 'Internal Medicine',
            'slug' => 'internal-medicine-'.Str::random(6),
            'is_active' => true,
        ]);

        return $this->postJson('/api/v1/providers/register-doctor', $this->providerPayload($email, 'Doctor Provider') + [
            'title' => 'Dr',
            'consultation_fee' => 300,
            'years_of_experience' => 10,
            'specialty_ids' => [$specialty->id],
        ]);
    }

    private function createProvider(ProviderType $type, ProviderStatus $status, bool $isActive): Provider
    {
        $role = match ($type) {
            ProviderType::Doctor => UserRole::Doctor,
            ProviderType::Pharmacy => UserRole::PharmacyAdmin,
            ProviderType::Lab => UserRole::LabAdmin,
        };

        $user = User::factory()->create();
        $user->assignRole($role->value);

        $provider = Provider::query()->create([
            'type' => $type,
            'owner_user_id' => $user->id,
            'name_en' => ucfirst($type->value).' Provider '.Str::random(6),
            'status' => $status,
            'is_active' => $isActive,
            'approved_at' => $status === ProviderStatus::Approved ? now() : null,
            'created_by' => $user->id,
        ]);

        $provider->staff()->create([
            'user_id' => $user->id,
            'role' => ProviderStaffRole::Owner,
            'is_owner' => true,
            'status' => 'active',
        ]);

        if ($type === ProviderType::Doctor) {
            DoctorProfile::query()->create([
                'provider_id' => $provider->id,
                'user_id' => $user->id,
            ]);
        }

        if ($type === ProviderType::Pharmacy) {
            PharmacyProfile::query()->create(['provider_id' => $provider->id]);
        }

        if ($type === ProviderType::Lab) {
            LabProfile::query()->create(['provider_id' => $provider->id]);
        }

        return $provider->refresh();
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create();
        Role::findOrCreate(UserRole::SuperAdmin->value);
        $admin->assignRole(UserRole::SuperAdmin->value);

        return $admin;
    }
}
