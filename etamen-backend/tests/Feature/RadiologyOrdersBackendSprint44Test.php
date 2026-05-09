<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\MedicalFiles\Infrastructure\Models\UploadedFile as UploadedFileModel;
use App\Modules\Payments\Database\Seeders\PaymentMethodSeeder;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Domain\Enums\PaymentProofStatus;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Invoice;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;
use App\Modules\Providers\Infrastructure\Models\RadiologyProfile;
use App\Modules\Radiology\Database\Seeders\RadiologyScanCategorySeeder;
use App\Modules\Radiology\Domain\Enums\RadiologyOrderStatus;
use App\Modules\Radiology\Infrastructure\Models\RadiologyOrder;
use App\Modules\Radiology\Infrastructure\Models\RadiologyResult;
use App\Modules\Radiology\Infrastructure\Models\RadiologyScan;
use App\Modules\Radiology\Infrastructure\Models\RadiologyScanCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RadiologyOrdersBackendSprint44Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PaymentMethodSeeder::class);
        $this->seed(RadiologyScanCategorySeeder::class);
    }

    public function test_patient_can_create_radiology_order_with_backend_total_and_payment(): void
    {
        $patient = $this->patientUser();
        ['provider' => $provider, 'branch' => $branch, 'scan' => $scan] = $this->createRadiologyCatalog(price: 600);
        Sanctum::actingAs($patient);

        $response = $this->postJson('/api/v1/radiology/orders', [
            'provider_id' => $provider->id,
            'branch_id' => $branch->id,
            'scans' => [
                ['radiology_scan_id' => $scan->id, 'quantity' => 2],
            ],
            'scheduled_at' => now()->addDays(2)->toISOString(),
            'patient_notes' => 'Need a scan appointment.',
        ])->assertCreated()
            ->assertJsonPath('data.total_amount', '1200.00')
            ->assertJsonPath('data.status', RadiologyOrderStatus::PendingPayment->value)
            ->assertJsonPath('data.items.0.unit_price', '600.00')
            ->assertJsonMissingPath('data.items.0.scan.price');

        $order = RadiologyOrder::query()->with('payment')->findOrFail($response->json('data.id'));

        $this->assertNotNull($order->payment);
        $this->assertSame(PaymentStatus::AwaitingMethod, $order->payment->status);
        $this->assertSame($order->id, $order->payment->payable_id);
        $this->assertSame(RadiologyOrder::class, $order->payment->payable_type);
        $this->assertSame('radiology', $order->payment->provider_type);
    }

    public function test_frontend_cannot_force_radiology_total_status_or_payment(): void
    {
        $patient = $this->patientUser();
        ['provider' => $provider, 'scan' => $scan] = $this->createRadiologyCatalog(price: 700);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/radiology/orders', [
            'provider_id' => $provider->id,
            'scans' => [['radiology_scan_id' => $scan->id, 'quantity' => 1]],
            'total_amount' => 1,
            'status' => RadiologyOrderStatus::Completed->value,
            'payment_id' => 999,
        ])->assertUnprocessable();

        $this->assertSame(0, RadiologyOrder::query()->count());
    }

    public function test_inactive_scan_unapproved_provider_and_wrong_branch_are_rejected(): void
    {
        $patient = $this->patientUser();
        ['provider' => $provider, 'scan' => $scan] = $this->createRadiologyCatalog();
        ['branch' => $otherBranch] = $this->createRadiologyCatalog();
        $inactive = $this->createScan($provider, isActive: false, name: 'Inactive CT');
        $pending = $this->createRadiologyProvider(status: ProviderStatus::PendingReview, active: false);
        $pendingScan = $this->createScan($pending['provider']);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/radiology/orders', [
            'provider_id' => $provider->id,
            'scans' => [['radiology_scan_id' => $inactive->id]],
        ])->assertUnprocessable();

        $this->postJson('/api/v1/radiology/orders', [
            'provider_id' => $pending['provider']->id,
            'scans' => [['radiology_scan_id' => $pendingScan->id]],
        ])->assertUnprocessable();

        $this->postJson('/api/v1/radiology/orders', [
            'provider_id' => $provider->id,
            'branch_id' => $otherBranch->id,
            'scans' => [['radiology_scan_id' => $scan->id]],
        ])->assertUnprocessable();
    }

    public function test_manual_payment_proof_and_admin_accept_update_radiology_order_status(): void
    {
        Storage::fake('medical_private');

        $patient = $this->patientUser();
        $order = $this->createOrderThroughApi($patient);
        $payment = $order->payment;
        $method = $this->paymentMethod(PaymentMethodType::ManualVodafoneCash);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/payments/'.$payment->id.'/manual/select', [
            'payment_method_id' => $method->id,
        ])->assertOk()
            ->assertJsonPath('data.payment.status', PaymentStatus::AwaitingProof->value);

        $this->post('/api/v1/payments/'.$payment->id.'/proofs', [
            'file' => UploadedFile::fake()->image('radiology-proof.jpg'),
            'reference_number' => 'RAD-VC-123',
        ])->assertCreated()
            ->assertJsonPath('data.status', PaymentStatus::PendingReview->value)
            ->assertJsonMissingPath('data.proofs.0.file.path');

        $this->assertSame(RadiologyOrderStatus::PendingPaymentReview, $order->refresh()->status);
        $this->assertSame(PaymentProofStatus::PendingReview, $payment->proofs()->firstOrFail()->status);

        $admin = $this->adminUser();
        Sanctum::actingAs($admin);
        $this->postJson('/api/v1/admin/payments/'.$payment->id.'/accept')
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::Verified->value)
            ->assertJsonPath('data.radiology_order.status', RadiologyOrderStatus::Paid->value);

        $this->assertSame(RadiologyOrderStatus::Paid, $order->refresh()->status);
        $this->assertSame(1, Invoice::query()->where('payment_id', $payment->id)->count());

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/admin/payments/'.$payment->id.'/accept')->assertForbidden();
    }

    public function test_provider_access_is_scoped_to_own_radiology_orders(): void
    {
        $patient = $this->patientUser();
        ['provider' => $provider, 'owner' => $owner] = $this->createRadiologyCatalog();
        ['owner' => $otherOwner] = $this->createRadiologyCatalog();
        $order = $this->createOrderThroughApi($patient, $provider);

        Sanctum::actingAs($owner);
        $this->getJson('/api/v1/provider/radiology/orders')
            ->assertOk()
            ->assertJsonPath('data.0.id', $order->id);
        $this->getJson('/api/v1/provider/radiology/orders/'.$order->id)->assertOk();

        Sanctum::actingAs($otherOwner);
        $this->getJson('/api/v1/provider/radiology/orders/'.$order->id)->assertForbidden();

        $doctorOwner = $this->createNonRadiologyProviderOwner();
        Sanctum::actingAs($doctorOwner);
        $this->getJson('/api/v1/provider/radiology/orders')->assertNotFound();
    }

    public function test_provider_uploads_private_result_and_patient_sees_only_visible_metadata(): void
    {
        Storage::fake('medical_private');

        $patient = $this->patientUser();
        ['provider' => $provider, 'owner' => $owner] = $this->createRadiologyCatalog();
        $order = $this->createVerifiedPaidOrder($patient, $provider);

        Sanctum::actingAs($owner);
        $this->post('/api/v1/provider/radiology/orders/'.$order->id.'/results', [
            'file' => UploadedFile::fake()->create('hidden-result.pdf', 64, 'application/pdf'),
            'result_type' => 'report_pdf',
            'title_ar' => 'نتيجة داخلية',
            'is_visible_to_patient' => false,
        ])->assertCreated();

        $hidden = RadiologyResult::query()->firstOrFail();
        $this->assertFalse($hidden->is_visible_to_patient);
        $this->assertSame('medical_private', $hidden->file->disk);

        Sanctum::actingAs($patient);
        $this->getJson('/api/v1/radiology/orders/'.$order->id.'/results')
            ->assertOk()
            ->assertJsonCount(0, 'data');
        $this->get('/api/v1/radiology/results/'.$hidden->id.'/download')->assertForbidden();

        Sanctum::actingAs($owner);
        $this->post('/api/v1/provider/radiology/orders/'.$order->id.'/results', [
            'file' => UploadedFile::fake()->create('visible-result.pdf', 64, 'application/pdf'),
            'result_type' => 'report_pdf',
            'title_ar' => 'تقرير الأشعة',
            'is_visible_to_patient' => true,
        ])->assertCreated()
            ->assertJsonMissingPath('data.file.path')
            ->assertJsonMissing(['medical_private']);

        $visible = RadiologyResult::query()->where('is_visible_to_patient', true)->firstOrFail();
        $this->assertSame(RadiologyOrderStatus::ResultReady, $order->refresh()->status);

        Sanctum::actingAs($patient);
        $this->getJson('/api/v1/radiology/orders/'.$order->id.'/results')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $visible->id)
            ->assertJsonMissingPath('data.0.file.path')
            ->assertJsonMissing(['medical_private']);
        $this->get('/api/v1/radiology/results/'.$visible->id.'/download')->assertOk();

        $otherPatient = $this->patientUser('other-radiology-patient@example.com');
        Sanctum::actingAs($otherPatient);
        $this->get('/api/v1/radiology/results/'.$visible->id.'/download')->assertForbidden();
    }

    public function test_status_history_and_admin_filters_work(): void
    {
        $patient = $this->patientUser();
        ['provider' => $provider] = $this->createRadiologyCatalog();
        $order = $this->createOrderThroughApi($patient, $provider);
        $admin = $this->adminUser();
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/radiology-orders?provider_id='.$provider->id.'&status='.RadiologyOrderStatus::PendingPayment->value)
            ->assertOk()
            ->assertJsonPath('data.0.id', $order->id)
            ->assertJsonMissing(['medical_private']);

        $this->getJson('/api/v1/admin/radiology-orders/'.$order->id.'/status-history')
            ->assertOk()
            ->assertJsonPath('data.0.to_status', RadiologyOrderStatus::PendingPayment->value);
    }

    private function createOrderThroughApi(User $patient, ?Provider $provider = null): RadiologyOrder
    {
        $catalog = $provider ? ['provider' => $provider, 'scan' => $this->createScan($provider)] : $this->createRadiologyCatalog();
        Sanctum::actingAs($patient);

        $orderId = $this->postJson('/api/v1/radiology/orders', [
            'provider_id' => $catalog['provider']->id,
            'scans' => [['radiology_scan_id' => $catalog['scan']->id, 'quantity' => 1]],
        ])->assertCreated()->json('data.id');

        return RadiologyOrder::query()->with('payment')->findOrFail($orderId);
    }

    private function createVerifiedPaidOrder(User $patient, Provider $provider): RadiologyOrder
    {
        $order = $this->createOrderThroughApi($patient, $provider);
        $payment = $order->payment;
        $method = $this->paymentMethod(PaymentMethodType::ManualVodafoneCash);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/payments/'.$payment->id.'/manual/select', ['payment_method_id' => $method->id])->assertOk();
        $this->post('/api/v1/payments/'.$payment->id.'/proofs', ['file' => UploadedFile::fake()->image('proof.jpg')])->assertCreated();

        Sanctum::actingAs($this->adminUser());
        $this->postJson('/api/v1/admin/payments/'.$payment->id.'/accept')->assertOk();

        return $order->refresh()->load('payment');
    }

    private function createRadiologyCatalog(
        int|float $price = 500,
        ProviderStatus $status = ProviderStatus::Approved,
        bool $active = true,
    ): array {
        $providerData = $this->createRadiologyProvider($status, $active);
        $scan = $this->createScan($providerData['provider'], price: $price, branch: $providerData['branch']);

        return $providerData + ['scan' => $scan];
    }

    private function createRadiologyProvider(ProviderStatus $status = ProviderStatus::Approved, bool $active = true): array
    {
        $owner = User::factory()->create(['email' => 'radiology-owner-'.Str::random(8).'@example.com']);
        $owner->assignRole(UserRole::ProviderAdmin->value);

        $provider = Provider::query()->create([
            'type' => ProviderType::Radiology,
            'owner_user_id' => $owner->id,
            'name_ar' => 'مركز أشعة تجريبي',
            'name_en' => 'Demo Radiology '.Str::random(6),
            'status' => $status,
            'is_active' => $active,
            'approved_at' => $status === ProviderStatus::Approved ? now() : null,
            'created_by' => $owner->id,
        ]);

        $provider->staff()->create([
            'user_id' => $owner->id,
            'role' => ProviderStaffRole::Owner,
            'is_owner' => true,
            'status' => 'active',
        ]);

        RadiologyProfile::query()->create([
            'provider_id' => $provider->id,
            'license_number' => 'RAD-'.Str::random(6),
            'is_active' => true,
        ]);

        $branch = ProviderBranch::query()->create([
            'provider_id' => $provider->id,
            'name_ar' => 'فرع مدينة نصر',
            'name_en' => 'Nasr City Branch',
            'is_main' => true,
            'is_active' => true,
        ]);

        return ['provider' => $provider, 'owner' => $owner, 'branch' => $branch];
    }

    private function createScan(?Provider $provider = null, int|float $price = 500, bool $isActive = true, string $name = 'Chest X-Ray', ?ProviderBranch $branch = null): RadiologyScan
    {
        $provider ??= $this->createRadiologyProvider()['provider'];
        $category = RadiologyScanCategory::query()->where('code', 'x_ray')->firstOrFail();

        return RadiologyScan::query()->create([
            'provider_id' => $provider->id,
            'branch_id' => $branch?->id,
            'radiology_scan_category_id' => $category->id,
            'name_ar' => 'أشعة صدر',
            'name_en' => $name,
            'preparation_ar' => 'هذه تعليمات عامة ولا تغني عن تعليمات المركز أو الطبيب.',
            'preparation_en' => 'These are general instructions and do not replace the center or doctor instructions.',
            'base_price' => $price,
            'is_active' => $isActive,
        ]);
    }

    private function createNonRadiologyProviderOwner(): User
    {
        $owner = User::factory()->create(['email' => 'doctor-owner-'.Str::random(8).'@example.com']);
        $owner->assignRole(UserRole::Doctor->value);

        $provider = Provider::query()->create([
            'type' => ProviderType::Doctor,
            'owner_user_id' => $owner->id,
            'name_en' => 'Doctor Provider '.Str::random(6),
            'status' => ProviderStatus::Approved,
            'is_active' => true,
            'approved_at' => now(),
            'created_by' => $owner->id,
        ]);

        $provider->staff()->create([
            'user_id' => $owner->id,
            'role' => ProviderStaffRole::Owner,
            'is_owner' => true,
            'status' => 'active',
        ]);

        return $owner;
    }

    private function patientUser(string $email = 'radiology-patient@example.com'): User
    {
        $user = User::factory()->create(['email' => $email]);
        $user->assignRole(UserRole::Patient->value);

        return $user;
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create(['email' => 'radiology-admin-'.Str::random(8).'@example.com']);
        Role::findOrCreate(UserRole::SuperAdmin->value);
        $admin->assignRole(UserRole::SuperAdmin->value);

        return $admin;
    }

    private function paymentMethod(PaymentMethodType $type): PaymentMethod
    {
        $method = PaymentMethod::query()->where('type', $type)->firstOrFail();
        $method->update(['is_active' => true]);

        return $method->refresh();
    }
}
