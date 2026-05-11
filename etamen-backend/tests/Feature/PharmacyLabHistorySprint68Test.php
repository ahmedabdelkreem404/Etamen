<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Labs\Domain\Enums\LabOrderItemType;
use App\Modules\Labs\Domain\Enums\LabOrderPaymentStatus;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Domain\Enums\LabSampleCollectionMethod;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use App\Modules\Labs\Infrastructure\Models\LabTest;
use App\Modules\Pharmacies\Domain\Enums\PharmacyDeliveryMethod;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderPaymentStatus;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyProduct;
use App\Modules\Providers\Domain\Enums\ProviderPermission;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderStaff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PharmacyLabHistorySprint68Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_patient_pharmacy_and_lab_history_filters_are_scoped_and_safe(): void
    {
        $patient = $this->patientUser();
        $otherPatient = $this->patientUser('other-sprint68-patient@example.com');
        [, $pharmacy] = $this->providerWithOwner(ProviderType::Pharmacy);
        [, $lab] = $this->providerWithOwner(ProviderType::Lab);

        $ownPharmacyOrder = $this->pharmacyOrder($patient, $pharmacy, PharmacyOrderStatus::Preparing, PharmacyOrderPaymentStatus::Paid);
        $this->pharmacyOrder($otherPatient, $pharmacy, PharmacyOrderStatus::Preparing, PharmacyOrderPaymentStatus::Paid);
        $ownLabOrder = $this->labOrder($patient, $lab, LabOrderStatus::Processing, LabOrderPaymentStatus::Paid);
        $this->labOrder($otherPatient, $lab, LabOrderStatus::Processing, LabOrderPaymentStatus::Paid);

        Sanctum::actingAs($patient);

        $pharmacyResponse = $this->getJson('/api/v1/pharmacy/orders?status=preparing&payment_status=paid&per_page=200')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownPharmacyOrder->id)
            ->assertJsonPath('data.0.status_label_ar', 'تحت التجهيز')
            ->assertJsonPath('data.0.can_cancel', false)
            ->assertJsonPath('data.0.can_pay', false)
            ->assertJsonPath('data.0.can_upload_proof', false);

        $this->assertSafeHistoryResponse($pharmacyResponse->content());

        $labResponse = $this->getJson('/api/v1/lab/orders?status=processing&payment_status=paid&visit_type=branch&per_page=200')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownLabOrder->id)
            ->assertJsonPath('data.0.status_label_ar', 'جاري التحليل')
            ->assertJsonPath('data.0.can_cancel', false)
            ->assertJsonPath('data.0.can_view_result_metadata', false);

        $this->assertSafeHistoryResponse($labResponse->content());

        $this->getJson('/api/v1/pharmacy/orders?status=invalid')->assertUnprocessable();
        $this->getJson('/api/v1/lab/orders?visit_type=invalid')->assertUnprocessable();
    }

    public function test_provider_pharmacy_and_lab_history_filters_are_scoped_and_limited_staff_manage_actions_are_blocked(): void
    {
        [$pharmacyOwner, $pharmacy] = $this->providerWithOwner(ProviderType::Pharmacy);
        [$otherPharmacyOwner, $otherPharmacy] = $this->providerWithOwner(ProviderType::Pharmacy);
        [$labOwner, $lab] = $this->providerWithOwner(ProviderType::Lab);
        [$otherLabOwner, $otherLab] = $this->providerWithOwner(ProviderType::Lab);
        $patient = $this->patientUser();

        $ownPharmacyOrder = $this->pharmacyOrder($patient, $pharmacy, PharmacyOrderStatus::ReadyForPickup, PharmacyOrderPaymentStatus::Paid);
        $otherPharmacyOrder = $this->pharmacyOrder($patient, $otherPharmacy, PharmacyOrderStatus::ReadyForPickup, PharmacyOrderPaymentStatus::Paid);
        $ownLabOrder = $this->labOrder($patient, $lab, LabOrderStatus::ResultReady, LabOrderPaymentStatus::Paid);
        $otherLabOrder = $this->labOrder($patient, $otherLab, LabOrderStatus::ResultReady, LabOrderPaymentStatus::Paid);

        Sanctum::actingAs($pharmacyOwner);
        $pharmacyResponse = $this->getJson('/api/v1/provider/workspace/'.$pharmacy->id.'/pharmacy/orders?status=ready_for_pickup&per_page=200')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $ownPharmacyOrder->id)
            ->assertJsonPath('data.items.0.status_label_ar', 'جاهز للاستلام');
        $this->assertSafeHistoryResponse($pharmacyResponse->content());

        Sanctum::actingAs($otherPharmacyOwner);
        $this->getJson('/api/v1/provider/workspace/'.$otherPharmacy->id.'/pharmacy/orders/'.$ownPharmacyOrder->id)->assertNotFound();
        $this->getJson('/api/v1/provider/workspace/'.$otherPharmacy->id.'/pharmacy/orders/'.$otherPharmacyOrder->id)->assertOk();

        Sanctum::actingAs($labOwner);
        $labResponse = $this->getJson('/api/v1/provider/workspace/'.$lab->id.'/lab/orders?status=result_ready&per_page=200')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $ownLabOrder->id)
            ->assertJsonPath('data.items.0.status_label_ar', 'النتيجة جاهزة')
            ->assertJsonPath('data.items.0.can_view_result_metadata', true);
        $this->assertSafeHistoryResponse($labResponse->content());

        Sanctum::actingAs($otherLabOwner);
        $this->getJson('/api/v1/provider/workspace/'.$otherLab->id.'/lab/orders/'.$ownLabOrder->id)->assertNotFound();
        $this->getJson('/api/v1/provider/workspace/'.$otherLab->id.'/lab/orders/'.$otherLabOrder->id)->assertOk();

        $limitedPharmacyStaff = $this->staff($pharmacy, [ProviderPermission::ViewPharmacyOrders->value]);
        Sanctum::actingAs($limitedPharmacyStaff);
        $this->getJson('/api/v1/provider/workspace/'.$pharmacy->id.'/pharmacy/orders')->assertOk();
        $this->postJson('/api/v1/provider/workspace/'.$pharmacy->id.'/pharmacy/orders/'.$ownPharmacyOrder->id.'/complete')->assertForbidden();

        $limitedLabStaff = $this->staff($lab, [ProviderPermission::ViewLabOrders->value]);
        Sanctum::actingAs($limitedLabStaff);
        $this->getJson('/api/v1/provider/workspace/'.$lab->id.'/lab/orders')->assertOk();
        $this->postJson('/api/v1/provider/workspace/'.$lab->id.'/lab/orders/'.$ownLabOrder->id.'/complete')->assertForbidden();
    }

    private function pharmacyOrder(User $patient, Provider $provider, PharmacyOrderStatus $status, PharmacyOrderPaymentStatus $paymentStatus): PharmacyOrder
    {
        $product = PharmacyProduct::query()->firstOrCreate(
            ['provider_id' => $provider->id, 'sku' => 'SPRINT68-'.$provider->id],
            [
                'name_ar' => 'منتج تجريبي',
                'name_en' => 'Sprint 68 Product',
                'price' => 75,
                'stock_quantity' => 20,
                'requires_prescription' => false,
                'is_active' => true,
            ],
        );

        $order = PharmacyOrder::query()->create([
            'order_number' => 'PH68-'.Str::upper(Str::random(8)),
            'patient_user_id' => $patient->id,
            'pharmacy_provider_id' => $provider->id,
            'subtotal' => 75,
            'discount_total' => 0,
            'commission_amount' => 0,
            'provider_net_amount' => 75,
            'grand_total' => 75,
            'currency' => 'EGP',
            'payment_status' => $paymentStatus,
            'order_status' => $status,
            'delivery_method' => PharmacyDeliveryMethod::Pickup,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name_en,
            'unit_price' => 75,
            'quantity' => 1,
            'line_total' => 75,
        ]);

        return $order;
    }

    private function labOrder(User $patient, Provider $provider, LabOrderStatus $status, LabOrderPaymentStatus $paymentStatus): LabOrder
    {
        $test = LabTest::query()->firstOrCreate(
            ['provider_id' => $provider->id, 'code' => 'SPRINT68-'.$provider->id],
            [
                'name_ar' => 'تحليل تجريبي',
                'name_en' => 'Sprint 68 Lab Test',
                'price' => 120,
                'sample_type' => 'blood',
                'is_active' => true,
            ],
        );

        $order = LabOrder::query()->create([
            'order_number' => 'LAB68-'.Str::upper(Str::random(8)),
            'patient_user_id' => $patient->id,
            'lab_provider_id' => $provider->id,
            'subtotal' => 120,
            'discount_total' => 0,
            'commission_amount' => 0,
            'provider_net_amount' => 120,
            'grand_total' => 120,
            'currency' => 'EGP',
            'payment_status' => $paymentStatus,
            'order_status' => $status,
            'sample_collection_method' => LabSampleCollectionMethod::BranchVisit,
        ]);

        $order->items()->create([
            'item_type' => LabOrderItemType::Test,
            'test_id' => $test->id,
            'item_name' => $test->name_en,
            'unit_price' => 120,
            'quantity' => 1,
            'line_total' => 120,
        ]);

        return $order;
    }

    private function patientUser(string $email = 'sprint68-patient@example.com'): User
    {
        $user = User::factory()->create(['email' => $email]);
        $user->assignRole(UserRole::Patient->value);

        return $user;
    }

    private function providerWithOwner(ProviderType $type): array
    {
        $owner = User::factory()->create();
        $provider = Provider::query()->create([
            'type' => $type,
            'owner_user_id' => $owner->id,
            'name_ar' => 'Sprint 68 '.$type->value,
            'name_en' => 'Sprint 68 '.Str::headline($type->value),
            'status' => ProviderStatus::Approved,
            'is_active' => true,
            'approved_at' => now(),
        ]);

        ProviderStaff::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $owner->id,
            'role' => ProviderStaffRole::Owner,
            'is_owner' => true,
            'status' => 'active',
        ]);

        return [$owner, $provider];
    }

    private function staff(Provider $provider, array $permissions): User
    {
        $staff = User::factory()->create();
        ProviderStaff::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $staff->id,
            'role' => ProviderStaffRole::Staff,
            'is_owner' => false,
            'status' => 'active',
            'permissions' => $permissions,
        ]);

        return $staff;
    }

    private function assertSafeHistoryResponse(string $content): void
    {
        $this->assertStringNotContainsString('storage/private', $content);
        $this->assertStringNotContainsString('medical_private', $content);
        $this->assertStringNotContainsString('raw_path', $content);
        $this->assertStringNotContainsString('payment_config', $content);
        $this->assertStringNotContainsString('APP_KEY', $content);
        $this->assertStringNotContainsString('DB_PASSWORD', $content);
        $this->assertStringNotContainsString('diagnosis', $content);
        $this->assertStringNotContainsString('interpretation', $content);
    }
}
