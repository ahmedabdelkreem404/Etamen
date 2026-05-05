<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Payments\Database\Seeders\PaymentMethodSeeder;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Invoice;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use App\Modules\Pharmacies\Domain\Enums\PharmacyDeliveryMethod;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderPaymentStatus;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyPrescription;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyProduct;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Domain\Enums\ServiceType;
use App\Modules\Providers\Infrastructure\Models\PharmacyProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Wallets\Domain\Enums\SettlementStatus;
use App\Modules\Wallets\Domain\Enums\WalletOwnerType;
use App\Modules\Wallets\Domain\Enums\WalletTransactionType;
use App\Modules\Wallets\Infrastructure\Models\CommissionRule;
use App\Modules\Wallets\Infrastructure\Models\SettlementItem;
use App\Modules\Wallets\Infrastructure\Models\Wallet;
use App\Modules\Wallets\Infrastructure\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PharmacyMarketplaceSprint5Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PaymentMethodSeeder::class);
    }

    public function test_pharmacy_provider_can_create_and_update_own_product_only(): void
    {
        ['user' => $pharmacyUser, 'provider' => $provider] = $this->createPharmacyProvider();
        ['user' => $otherUser, 'provider' => $otherProvider] = $this->createPharmacyProvider(email: 'other-pharmacy@example.com');
        $otherProduct = $this->createProduct($otherProvider);
        Sanctum::actingAs($pharmacyUser);

        $productId = $this->postJson('/api/v1/provider/pharmacy/products', [
            'provider_id' => $otherProvider->id,
            'name_en' => 'Vitamin C',
            'price' => 120,
            'stock_quantity' => 20,
        ])->assertUnprocessable();

        $productId = $this->postJson('/api/v1/provider/pharmacy/products', [
            'name_en' => 'Vitamin C',
            'price' => 120,
            'stock_quantity' => 20,
            'requires_prescription' => false,
        ])
            ->assertCreated()
            ->assertJsonPath('data.provider_id', $provider->id)
            ->json('data.id');

        $this->patchJson('/api/v1/provider/pharmacy/products/'.$productId, [
            'price' => 130,
            'stock_quantity' => 25,
        ])
            ->assertOk()
            ->assertJsonPath('data.price', '130.00');

        $this->patchJson('/api/v1/provider/pharmacy/products/'.$otherProduct->id, [
            'price' => 1,
        ])->assertForbidden();

        Sanctum::actingAs($otherUser);
        $this->getJson('/api/v1/provider/pharmacy/products/'.$productId)->assertForbidden();
    }

    public function test_patient_can_view_only_active_public_pharmacy_products(): void
    {
        ['provider' => $provider] = $this->createPharmacyProvider();
        $active = $this->createProduct($provider, ['name_en' => 'Active Medicine', 'is_active' => true]);
        $inactive = $this->createProduct($provider, ['name_en' => 'Inactive Medicine', 'is_active' => false]);

        $response = $this->getJson('/api/v1/pharmacies/'.$provider->id.'/products')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($active->id));
        $this->assertFalse($ids->contains($inactive->id));
    }

    public function test_prescription_upload_validation_and_private_authorized_download(): void
    {
        Storage::fake('medical_private');

        $patient = $this->patientUser();
        $otherPatient = $this->patientUser('other-patient@example.com');
        ['user' => $pharmacyUser, 'provider' => $provider] = $this->createPharmacyProvider();
        ['user' => $otherPharmacyUser] = $this->createPharmacyProvider(email: 'different-pharmacy@example.com');
        $admin = $this->adminUser();

        Sanctum::actingAs($patient);
        $this->post('/api/v1/pharmacy/prescriptions', [
            'pharmacy_provider_id' => $provider->id,
            'file' => UploadedFile::fake()->create('malware.exe', 10, 'application/x-msdownload'),
        ])->assertUnprocessable();

        $this->post('/api/v1/pharmacy/prescriptions', [
            'pharmacy_provider_id' => $provider->id,
            'file' => UploadedFile::fake()->create('large.pdf', 11000, 'application/pdf'),
        ])->assertUnprocessable();

        $prescriptionId = $this->post('/api/v1/pharmacy/prescriptions', [
            'pharmacy_provider_id' => $provider->id,
            'file' => UploadedFile::fake()->create('prescription.pdf', 100, 'application/pdf'),
            'notes' => 'Please verify this prescription.',
        ])
            ->assertCreated()
            ->assertJsonMissingPath('data.file.path')
            ->assertJsonMissingPath('data.file.url')
            ->assertJsonPath('data.file.visibility', 'private')
            ->json('data.id');

        $prescription = PharmacyPrescription::query()->with('uploadedFile')->findOrFail($prescriptionId);
        $this->assertSame('prescription', $prescription->uploadedFile->file_category->value);
        Storage::disk('medical_private')->assertExists($prescription->uploadedFile->path);

        $this->get('/api/v1/pharmacy/prescriptions/'.$prescriptionId.'/download')->assertOk();
        $this->assertContains($this->get('/storage/'.$prescription->uploadedFile->path)->status(), [403, 404]);

        Sanctum::actingAs($pharmacyUser);
        $this->get('/api/v1/pharmacy/prescriptions/'.$prescriptionId.'/download')->assertOk();

        Sanctum::actingAs($admin);
        $this->get('/api/v1/pharmacy/prescriptions/'.$prescriptionId.'/download')->assertOk();

        Sanctum::actingAs($otherPatient);
        $this->getJson('/api/v1/pharmacy/prescriptions/'.$prescriptionId.'/download')->assertForbidden();

        Sanctum::actingAs($otherPharmacyUser);
        $this->getJson('/api/v1/pharmacy/prescriptions/'.$prescriptionId.'/download')->assertForbidden();
    }

    public function test_patient_can_create_order_with_snapshots_and_prescription_rule_is_enforced(): void
    {
        Storage::fake('medical_private');

        $patient = $this->patientUser();
        ['provider' => $provider] = $this->createPharmacyProvider();
        $normal = $this->createProduct($provider, ['name_en' => 'Panadol', 'price' => 50, 'stock_quantity' => 10]);
        $rx = $this->createProduct($provider, ['name_en' => 'Prescription Drug', 'price' => 80, 'stock_quantity' => 10, 'requires_prescription' => true]);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/pharmacy/orders', [
            'pharmacy_provider_id' => $provider->id,
            'delivery_method' => PharmacyDeliveryMethod::Pickup->value,
            'items' => [
                ['product_id' => $rx->id, 'quantity' => 1],
            ],
        ])->assertUnprocessable();

        $prescriptionId = $this->uploadPrescription($patient, $provider);

        $orderId = $this->postJson('/api/v1/pharmacy/orders', [
            'pharmacy_provider_id' => $provider->id,
            'prescription_id' => $prescriptionId,
            'delivery_method' => PharmacyDeliveryMethod::Delivery->value,
            'delivery_address' => '12 Nile St, Cairo',
            'items' => [
                ['product_id' => $normal->id, 'quantity' => 2],
                ['product_id' => $rx->id, 'quantity' => 1],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('data.subtotal', '180.00')
            ->assertJsonPath('data.grand_total', '180.00')
            ->assertJsonPath('data.order_status', PharmacyOrderStatus::PharmacyReview->value)
            ->assertJsonPath('data.payment_status', PharmacyOrderPaymentStatus::Unpaid->value)
            ->json('data.id');

        $order = PharmacyOrder::query()->with('items')->findOrFail($orderId);
        $this->assertSame('Panadol', $order->items->firstWhere('product_id', $normal->id)->product_name);
        $this->assertSame('50.00', $order->items->firstWhere('product_id', $normal->id)->unit_price);

        $normal->update(['price' => 999]);
        $this->assertSame('50.00', $order->items()->where('product_id', $normal->id)->firstOrFail()->unit_price);
    }

    public function test_pharmacy_order_permissions_and_status_rules(): void
    {
        $patient = $this->patientUser();
        $otherPatient = $this->patientUser('not-owner@example.com');
        ['user' => $pharmacyUser, 'provider' => $provider] = $this->createPharmacyProvider();
        ['user' => $otherPharmacyUser] = $this->createPharmacyProvider(email: 'other-order-pharmacy@example.com');
        $product = $this->createProduct($provider);
        $order = $this->createOrder($patient, $provider, [['product_id' => $product->id, 'quantity' => 1]]);

        Sanctum::actingAs($otherPatient);
        $this->getJson('/api/v1/pharmacy/orders/'.$order->id)->assertForbidden();

        Sanctum::actingAs($otherPharmacyUser);
        $this->getJson('/api/v1/provider/pharmacy/orders/'.$order->id)->assertForbidden();
        $this->patchJson('/api/v1/provider/pharmacy/orders/'.$order->id.'/status', [
            'status' => PharmacyOrderStatus::Accepted->value,
        ])->assertForbidden();

        Sanctum::actingAs($pharmacyUser);
        $this->patchJson('/api/v1/provider/pharmacy/orders/'.$order->id.'/status', [
            'status' => PharmacyOrderStatus::Rejected->value,
        ])->assertUnprocessable();

        $this->patchJson('/api/v1/provider/pharmacy/orders/'.$order->id.'/status', [
            'status' => PharmacyOrderStatus::Accepted->value,
        ])
            ->assertOk()
            ->assertJsonPath('data.order_status', PharmacyOrderStatus::Accepted->value);

        Sanctum::actingAs($patient);
        $this->getJson('/api/v1/pharmacy/orders/'.$order->id)
            ->assertOk()
            ->assertJsonPath('data.id', $order->id);
    }

    public function test_verified_pharmacy_payment_posts_hold_once_and_delivery_releases_once(): void
    {
        $this->createCommissionRule(percentage: 10, fixedAmount: 5);

        $context = $this->createAcceptedOrderWithPayment(grandTotalProductPrice: 200);
        $payment = $this->verifyManualPaymentForOrder($context['patient'], $context['payment']);
        $order = $context['order']->refresh();

        $this->assertSame(PaymentStatus::Verified, $payment->status);
        $this->assertSame(PharmacyOrderPaymentStatus::Paid, $order->payment_status);
        $this->assertSame(PharmacyOrderStatus::Paid, $order->order_status);

        $wallet = $this->walletForProvider($context['provider']);
        $hold = $wallet->transactions()->where('type', WalletTransactionType::Hold)->firstOrFail();
        $commission = $wallet->transactions()->where('type', WalletTransactionType::Commission)->firstOrFail();

        $this->assertSame(WalletOwnerType::Pharmacy, $wallet->owner_type);
        $this->assertSame('200.00', $hold->gross_amount);
        $this->assertSame('25.00', $hold->commission_amount);
        $this->assertSame('175.00', $hold->net_amount);
        $this->assertSame('25.00', $commission->commission_amount);

        Sanctum::actingAs($this->adminUser());
        $this->postJson('/api/v1/admin/payments/'.$payment->id.'/accept')->assertOk();
        $this->assertSame(1, $wallet->transactions()->where('type', WalletTransactionType::Hold)->count());
        $this->assertSame(1, Invoice::query()->where('payment_id', $payment->id)->count());

        Sanctum::actingAs($context['pharmacy_user']);
        $this->getJson('/api/v1/provider/wallet')
            ->assertOk()
            ->assertJsonPath('data.balances.pending_balance', 175)
            ->assertJsonPath('data.balances.available_balance', 0);

        $this->patchJson('/api/v1/provider/pharmacy/orders/'.$order->id.'/status', [
            'status' => PharmacyOrderStatus::Delivered->value,
        ])
            ->assertOk()
            ->assertJsonPath('data.order_status', PharmacyOrderStatus::Delivered->value);
        $this->patchJson('/api/v1/provider/pharmacy/orders/'.$order->id.'/status', [
            'status' => PharmacyOrderStatus::Delivered->value,
        ])->assertOk();

        $this->assertSame(1, $wallet->transactions()->where('type', WalletTransactionType::Release)->count());

        $this->getJson('/api/v1/provider/wallet')
            ->assertOk()
            ->assertJsonPath('data.balances.pending_balance', 0)
            ->assertJsonPath('data.balances.available_balance', 175);

        $this->assertDatabaseHas('audit_logs', ['action' => 'wallet.pharmacy_order_hold_posted']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'wallet.pharmacy_order_earning_released']);
    }

    public function test_rejected_unpaid_order_does_not_create_wallet_transactions(): void
    {
        $patient = $this->patientUser();
        ['user' => $pharmacyUser, 'provider' => $provider] = $this->createPharmacyProvider();
        $product = $this->createProduct($provider);
        $order = $this->createOrder($patient, $provider, [['product_id' => $product->id, 'quantity' => 1]]);

        Sanctum::actingAs($pharmacyUser);
        $this->patchJson('/api/v1/provider/pharmacy/orders/'.$order->id.'/status', [
            'status' => PharmacyOrderStatus::Rejected->value,
            'reason' => 'Unavailable.',
        ])->assertOk();

        $this->assertSame(0, WalletTransaction::query()->count());
    }

    public function test_pharmacy_release_can_be_settled_once(): void
    {
        $this->createCommissionRule(percentage: 10);
        $context = $this->createAcceptedOrderWithPayment(grandTotalProductPrice: 200);
        $this->verifyManualPaymentForOrder($context['patient'], $context['payment']);

        Sanctum::actingAs($context['pharmacy_user']);
        $this->patchJson('/api/v1/provider/pharmacy/orders/'.$context['order']->id.'/status', [
            'status' => PharmacyOrderStatus::Delivered->value,
        ])->assertOk();

        $admin = $this->adminUser();
        Sanctum::actingAs($admin);
        $settlementId = $this->postJson('/api/v1/admin/settlements', [
            'provider_id' => $context['provider']->id,
            'provider_type' => ProviderType::Pharmacy->value,
        ])
            ->assertCreated()
            ->assertJsonPath('data.total_net', '180.00')
            ->assertJsonPath('data.status', SettlementStatus::Draft->value)
            ->json('data.id');

        $this->assertSame(1, SettlementItem::query()->where('settlement_id', $settlementId)->count());

        $this->postJson('/api/v1/admin/settlements', [
            'provider_id' => $context['provider']->id,
            'provider_type' => ProviderType::Pharmacy->value,
        ])->assertUnprocessable();
    }

    private function createAcceptedOrderWithPayment(int|float $grandTotalProductPrice): array
    {
        $patient = $this->patientUser();
        ['user' => $pharmacyUser, 'provider' => $provider] = $this->createPharmacyProvider();
        $product = $this->createProduct($provider, ['price' => $grandTotalProductPrice, 'stock_quantity' => 10]);
        $order = $this->createOrder($patient, $provider, [['product_id' => $product->id, 'quantity' => 1]]);

        Sanctum::actingAs($pharmacyUser);
        $this->patchJson('/api/v1/provider/pharmacy/orders/'.$order->id.'/status', [
            'status' => PharmacyOrderStatus::Accepted->value,
        ])->assertOk();

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/pharmacy/orders/'.$order->id.'/pay')
            ->assertOk()
            ->assertJsonPath('data.order_status', PharmacyOrderStatus::AwaitingPayment->value)
            ->assertJsonPath('data.payment_status', PharmacyOrderPaymentStatus::PendingPayment->value);

        return [
            'patient' => $patient,
            'pharmacy_user' => $pharmacyUser,
            'provider' => $provider,
            'order' => $order->refresh(),
            'payment' => $order->refresh()->payment,
        ];
    }

    private function verifyManualPaymentForOrder(User $patient, Payment $payment): Payment
    {
        Storage::fake('medical_private');

        $method = $this->paymentMethod(PaymentMethodType::ManualVodafoneCash);
        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/payments/'.$payment->id.'/manual/select', [
            'payment_method_id' => $method->id,
        ])->assertOk();

        $this->post('/api/v1/payments/'.$payment->id.'/proofs', [
            'file' => UploadedFile::fake()->create('proof.jpg', 20, 'image/jpeg'),
            'reference_number' => 'VC-'.Str::upper(Str::random(6)),
            'sender_phone' => '01012345678',
        ])->assertCreated();

        $admin = $this->adminUser();
        Sanctum::actingAs($admin);
        $this->postJson('/api/v1/admin/payments/'.$payment->id.'/accept')
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::Verified->value)
            ->assertJsonPath('data.pharmacy_order.payment_status', PharmacyOrderPaymentStatus::Paid->value);

        return $payment->refresh();
    }

    private function createOrder(User $patient, Provider $provider, array $items): PharmacyOrder
    {
        Sanctum::actingAs($patient);

        $orderId = $this->postJson('/api/v1/pharmacy/orders', [
            'pharmacy_provider_id' => $provider->id,
            'delivery_method' => PharmacyDeliveryMethod::Pickup->value,
            'items' => $items,
        ])
            ->assertCreated()
            ->json('data.id');

        return PharmacyOrder::query()->with(['items', 'payment'])->findOrFail($orderId);
    }

    private function uploadPrescription(User $patient, Provider $provider): int
    {
        Storage::fake('medical_private');
        Sanctum::actingAs($patient);

        return $this->post('/api/v1/pharmacy/prescriptions', [
            'pharmacy_provider_id' => $provider->id,
            'file' => UploadedFile::fake()->create('prescription.pdf', 100, 'application/pdf'),
        ])
            ->assertCreated()
            ->json('data.id');
    }

    private function createCommissionRule(
        int|float $percentage,
        int|float|null $fixedAmount = null,
    ): CommissionRule {
        return CommissionRule::query()->create([
            'provider_type' => ProviderType::Pharmacy,
            'service_type' => ServiceType::PharmacyOrder,
            'percentage' => $percentage,
            'fixed_amount' => $fixedAmount,
            'starts_at' => now()->subDay(),
            'is_active' => true,
        ]);
    }

    private function paymentMethod(PaymentMethodType $type): PaymentMethod
    {
        $method = PaymentMethod::query()->where('type', $type)->firstOrFail();
        $method->update(['is_active' => true]);

        return $method->refresh();
    }

    private function walletForProvider(Provider $provider): Wallet
    {
        return Wallet::query()
            ->where('owner_type', WalletOwnerType::Pharmacy)
            ->where('owner_id', $provider->id)
            ->where('currency', 'EGP')
            ->firstOrFail();
    }

    private function createProduct(Provider $provider, array $overrides = []): PharmacyProduct
    {
        return PharmacyProduct::query()->create([
            'provider_id' => $provider->id,
            'name_en' => 'Product '.Str::random(5),
            'price' => 100,
            'stock_quantity' => 10,
            'requires_prescription' => false,
            'is_active' => true,
            ...$overrides,
        ]);
    }

    private function patientUser(string $email = 'patient@example.com'): User
    {
        $user = User::factory()->create(['email' => $email]);
        $user->assignRole(UserRole::Patient->value);

        return $user;
    }

    private function adminUser(): User
    {
        $user = User::factory()->create(['email' => 'admin-'.Str::random(6).'@example.com']);
        $user->assignRole(UserRole::SuperAdmin->value);

        return $user;
    }

    private function createPharmacyProvider(
        ProviderStatus $status = ProviderStatus::Approved,
        bool $isActive = true,
        ?string $email = null,
    ): array {
        $user = User::factory()->create(['email' => $email ?? 'pharmacy-'.Str::random(8).'@example.com']);
        $user->assignRole(UserRole::PharmacyAdmin->value);

        $provider = Provider::query()->create([
            'type' => ProviderType::Pharmacy,
            'owner_user_id' => $user->id,
            'name_en' => 'Pharmacy Provider '.Str::random(6),
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

        PharmacyProfile::query()->create([
            'provider_id' => $provider->id,
            'license_number' => 'PH-'.Str::upper(Str::random(6)),
            'delivery_available' => true,
        ]);

        return [
            'user' => $user,
            'provider' => $provider->refresh(),
        ];
    }
}
