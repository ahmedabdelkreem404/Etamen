<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use App\Modules\MedicalFiles\Domain\Enums\FileVisibility;
use App\Modules\MedicalFiles\Infrastructure\Models\UploadedFile as UploadedFileModel;
use App\Modules\Providers\Domain\Enums\ProviderDocumentStatus;
use App\Modules\Providers\Domain\Enums\ProviderDocumentType;
use App\Modules\Providers\Domain\Enums\ProviderDocumentVisibility;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;
use App\Modules\Providers\Infrastructure\Models\ProviderDocument;
use App\Modules\Providers\Infrastructure\Models\RadiologyProfile;
use App\Modules\Radiology\Database\Seeders\RadiologyScanCategorySeeder;
use App\Modules\Radiology\Infrastructure\Models\RadiologyPreparationInstruction;
use App\Modules\Radiology\Infrastructure\Models\RadiologyScan;
use App\Modules\Radiology\Infrastructure\Models\RadiologyScanCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RadiologyCatalogFoundationSprint37Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(RadiologyScanCategorySeeder::class);
    }

    public function test_radiology_categories_are_seeded_with_arabic_first_labels(): void
    {
        $this->assertSame(10, RadiologyScanCategory::query()->count());
        $this->assertDatabaseHas('radiology_scan_categories', [
            'code' => 'mri',
            'name_ar' => 'رنين مغناطيسي',
            'name_en' => 'MRI',
            'is_active' => true,
        ]);
    }

    public function test_only_radiology_provider_can_create_own_scan(): void
    {
        $radiology = $this->createProvider(ProviderType::Radiology, ProviderStatus::Approved, true);
        $doctor = $this->createProvider(ProviderType::Doctor, ProviderStatus::Approved, true);
        $category = RadiologyScanCategory::query()->where('code', 'x_ray')->firstOrFail();

        Sanctum::actingAs($radiology->owner);
        $this->postJson('/api/v1/provider/radiology/scans', [
            'radiology_scan_category_id' => $category->id,
            'name_ar' => 'أشعة صدر',
            'name_en' => 'Chest X-Ray',
            'base_price' => 250,
        ])->assertCreated()
            ->assertJsonPath('data.name_en', 'Chest X-Ray');

        Sanctum::actingAs($doctor->owner);
        $this->postJson('/api/v1/provider/radiology/scans', [
            'radiology_scan_category_id' => $category->id,
            'name_ar' => 'محاولة طبيب',
        ])->assertNotFound();
    }

    public function test_provider_cannot_create_scan_for_another_provider_or_branch(): void
    {
        $first = $this->createProvider(ProviderType::Radiology, ProviderStatus::Approved, true);
        $second = $this->createProvider(ProviderType::Radiology, ProviderStatus::Approved, true);
        $category = RadiologyScanCategory::query()->where('code', 'ultrasound')->firstOrFail();
        $otherBranch = $second->branches()->create(['name_en' => 'Other Radiology Branch']);

        Sanctum::actingAs($first->owner);
        $this->postJson('/api/v1/provider/radiology/scans', [
            'provider_id' => $second->id,
            'radiology_scan_category_id' => $category->id,
            'name_ar' => 'محاولة Provider ID',
        ])->assertUnprocessable();

        $this->postJson('/api/v1/provider/radiology/scans', [
            'branch_id' => $otherBranch->id,
            'radiology_scan_category_id' => $category->id,
            'name_ar' => 'محاولة فرع خاطئ',
        ])->assertUnprocessable();
    }

    public function test_patient_cannot_create_or_update_radiology_scans(): void
    {
        $patient = User::factory()->create();
        $patient->assignRole(UserRole::Patient->value);
        $scan = $this->createRadiologyScan();

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/provider/radiology/scans', [
            'radiology_scan_category_id' => $scan->radiology_scan_category_id,
            'name_ar' => 'محاولة مريض',
        ])->assertForbidden();

        $this->patchJson('/api/v1/provider/radiology/scans/'.$scan->id, [
            'name_ar' => 'تعديل مريض',
        ])->assertForbidden();
    }

    public function test_admin_can_manage_categories_scans_and_preparation_instructions(): void
    {
        $admin = $this->adminUser();
        $provider = $this->createProvider(ProviderType::Radiology, ProviderStatus::Approved, true);
        $branch = $provider->branches()->create(['name_en' => 'Admin Branch']);

        Sanctum::actingAs($admin);
        $categoryResponse = $this->postJson('/api/v1/admin/radiology-scan-categories', [
            'code' => 'pet_ct',
            'name_ar' => 'تصوير مقطعي بالإصدار البوزيتروني',
            'name_en' => 'PET CT',
        ])->assertCreated();

        $categoryId = $categoryResponse->json('data.id');

        $scanResponse = $this->postJson('/api/v1/admin/radiology-scans', [
            'provider_id' => $provider->id,
            'branch_id' => $branch->id,
            'radiology_scan_category_id' => $categoryId,
            'name_ar' => 'PET CT تجريبي',
            'name_en' => 'Demo PET CT',
            'base_price' => 4000,
            'requires_preparation' => true,
        ])->assertCreated()
            ->assertJsonPath('data.base_price', '4000.00');

        $this->postJson('/api/v1/admin/radiology-preparation-instructions', [
            'radiology_scan_id' => $scanResponse->json('data.id'),
            'title_ar' => 'تعليمات عامة',
            'body_ar' => 'يرجى تأكيد التعليمات مع المركز قبل الحضور.',
        ])->assertCreated()
            ->assertJsonPath('data.warning_ar', RadiologyPreparationInstruction::DISCLAIMER_AR);
    }

    public function test_admin_scan_branch_must_belong_to_same_radiology_provider(): void
    {
        $admin = $this->adminUser();
        $first = $this->createProvider(ProviderType::Radiology, ProviderStatus::Approved, true);
        $second = $this->createProvider(ProviderType::Radiology, ProviderStatus::Approved, true);
        $otherBranch = $second->branches()->create(['name_en' => 'Wrong Branch']);
        $category = RadiologyScanCategory::query()->where('code', 'ct_scan')->firstOrFail();

        Sanctum::actingAs($admin);
        $this->postJson('/api/v1/admin/radiology-scans', [
            'provider_id' => $first->id,
            'branch_id' => $otherBranch->id,
            'radiology_scan_category_id' => $category->id,
            'name_ar' => 'فرع غير صحيح',
        ])->assertUnprocessable();
    }

    public function test_inactive_scans_and_unapproved_radiology_providers_are_hidden_from_safe_public_listing(): void
    {
        $visible = $this->createRadiologyScan(isActive: true, providerStatus: ProviderStatus::Approved, providerActive: true);
        $this->createRadiologyScan(isActive: false, providerStatus: ProviderStatus::Approved, providerActive: true, name: 'Inactive MRI');
        $this->createRadiologyScan(isActive: true, providerStatus: ProviderStatus::PendingReview, providerActive: false, name: 'Pending MRI');

        $this->getJson('/api/v1/radiology/scans')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $visible->id)
            ->assertJsonMissing(['Inactive MRI'])
            ->assertJsonMissing(['Pending MRI']);
    }

    public function test_preparation_instructions_include_safe_disclaimers_only(): void
    {
        $category = RadiologyScanCategory::query()->where('code', 'mri')->firstOrFail();

        $instruction = RadiologyPreparationInstruction::query()->create([
            'radiology_scan_category_id' => $category->id,
            'title_ar' => 'قبل الرنين',
            'title_en' => 'Before MRI',
            'body_ar' => 'أبلغ المركز إذا كان لديك معدن مزروع.',
            'body_en' => 'Tell the center if you have implanted metal.',
            'warning_ar' => 'لا تبدأ أو توقف أي دواء بناء على هذه التعليمات.',
            'warning_en' => 'Do not start or stop medication based on these instructions.',
            'is_active' => true,
        ]);

        $this->assertStringContainsString(RadiologyPreparationInstruction::DISCLAIMER_AR, $instruction->warning_ar);
        $this->assertStringContainsString(RadiologyPreparationInstruction::DISCLAIMER_EN, $instruction->warning_en);
        $this->assertStringNotContainsString('diagnosis', strtolower($instruction->body_en));
    }

    public function test_public_radiology_catalog_does_not_expose_private_document_paths(): void
    {
        $provider = $this->createProvider(ProviderType::Radiology, ProviderStatus::Approved, true);
        $this->createRadiologyScan(provider: $provider);

        $file = UploadedFileModel::query()->create([
            'owner_type' => Provider::class,
            'owner_id' => $provider->id,
            'uploaded_by' => $provider->owner_user_id,
            'disk' => 'medical_private',
            'path' => 'private/provider-documents/radiology-license.pdf',
            'original_name' => 'radiology-license.pdf',
            'mime_type' => 'application/pdf',
            'size' => 100,
            'file_category' => FileCategory::ProviderDocument,
            'visibility' => FileVisibility::Private,
        ]);
        ProviderDocument::query()->create([
            'provider_id' => $provider->id,
            'file_id' => $file->id,
            'uploaded_by' => $provider->owner_user_id,
            'document_type' => ProviderDocumentType::RadiologyLicense->value,
            'status' => ProviderDocumentStatus::Approved,
            'visibility' => ProviderDocumentVisibility::AdminOnly,
        ]);

        $this->getJson('/api/v1/radiology/scans')
            ->assertOk()
            ->assertJsonMissing(['private/provider-documents/radiology-license.pdf'])
            ->assertJsonMissing(['medical_private'])
            ->assertJsonMissingPath('data.0.provider.documents');
    }

    private function createRadiologyScan(
        ?Provider $provider = null,
        bool $isActive = true,
        ProviderStatus $providerStatus = ProviderStatus::Approved,
        bool $providerActive = true,
        string $name = 'Demo MRI',
    ): RadiologyScan {
        $provider ??= $this->createProvider(ProviderType::Radiology, $providerStatus, $providerActive);
        $category = RadiologyScanCategory::query()->where('code', 'mri')->firstOrFail();

        return RadiologyScan::query()->create([
            'provider_id' => $provider->id,
            'radiology_scan_category_id' => $category->id,
            'name_ar' => $name,
            'name_en' => $name,
            'base_price' => 1200,
            'duration_minutes' => 30,
            'is_active' => $isActive,
        ]);
    }

    private function createProvider(ProviderType $type, ProviderStatus $status, bool $isActive): Provider
    {
        $user = User::factory()->create();
        $user->assignRole(match ($type) {
            ProviderType::Doctor => UserRole::Doctor->value,
            ProviderType::Pharmacy => UserRole::PharmacyAdmin->value,
            ProviderType::Lab => UserRole::LabAdmin->value,
            default => UserRole::ProviderAdmin->value,
        });

        $provider = Provider::query()->create([
            'type' => $type,
            'owner_user_id' => $user->id,
            'name_ar' => 'مزود تجريبي',
            'name_en' => Str::headline($type->value).' Provider '.Str::random(6),
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

        ProviderBranch::query()->create([
            'provider_id' => $provider->id,
            'name_en' => 'Main Branch',
            'is_main' => true,
            'is_active' => true,
        ]);

        if ($type === ProviderType::Radiology) {
            RadiologyProfile::query()->create([
                'provider_id' => $provider->id,
                'license_number' => 'RAD-'.Str::random(6),
                'is_active' => true,
            ]);
        }

        if ($type === ProviderType::Doctor) {
            DoctorProfile::query()->create([
                'provider_id' => $provider->id,
                'user_id' => $user->id,
            ]);
        }

        return $provider->refresh()->load('owner');
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create();
        Role::findOrCreate(UserRole::SuperAdmin->value);
        $admin->assignRole(UserRole::SuperAdmin->value);

        return $admin;
    }
}
