<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Labs\Domain\Enums\LabOrderItemType;
use App\Modules\Labs\Domain\Enums\LabOrderPaymentStatus;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Domain\Enums\LabResultStatus;
use App\Modules\Labs\Domain\Enums\LabSampleCollectionMethod;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use App\Modules\Labs\Infrastructure\Models\LabPackage;
use App\Modules\Labs\Infrastructure\Models\LabResult;
use App\Modules\Labs\Infrastructure\Models\LabTest;
use App\Modules\Payments\Database\Seeders\PaymentMethodSeeder;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Gateways\PaymobGateway;
use App\Modules\Payments\Infrastructure\Models\Invoice;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Domain\Enums\ServiceType;
use App\Modules\Providers\Infrastructure\Models\LabProfile;
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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LabsMarketplaceSprint6Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PaymentMethodSeeder::class);
    }

    public function test_provider_can_create_own_lab_test_and_cannot_manage_another_lab_test(): void
    {
        ['user' => $labUser, 'provider' => $provider] = $this->createLabProvider();
        ['user' => $otherUser, 'provider' => $otherProvider] = $this->createLabProvider(email: 'other-lab@example.com');
        $otherTest = $this->createLabTest($otherProvider);

        Sanctum::actingAs($labUser);
        $testId = $this->postJson('/api/v1/provider/lab/tests', [
            'provider_id' => $otherProvider->id,
            'name_en' => 'CBC',
            'price' => 120,
        ])->assertUnprocessable();

        $testId = $this->postJson('/api/v1/provider/lab/tests', [
            'name_en' => 'CBC',
            'price' => 120,
            'sample_type' => 'blood',
        ])
            ->assertCreated()
            ->assertJsonPath('data.provider_id', $provider->id)
            ->json('data.id');

        $this->patchJson('/api/v1/provider/lab/tests/'.$testId, [
            'name_en' => 'CBC Updated',
            'price' => 130,
        ])->assertOk()->assertJsonPath('data.price', '130.00');

        $this->patchJson('/api/v1/provider/lab/tests/'.$otherTest->id, [
            'name_en' => 'Should fail',
            'price' => 1,
        ])->assertForbidden();

        Sanctum::actingAs($otherUser);
        $this->getJson('/api/v1/provider/lab/tests/'.$testId)->assertForbidden();
    }

    public function test_public_lab_tests_and_packages_show_only_active_items_for_approved_lab(): void
    {
        ['provider' => $provider] = $this->createLabProvider();
        ['provider' => $pendingProvider] = $this->createLabProvider(status: ProviderStatus::PendingReview, email: 'pending-lab@example.com');
        $active = $this->createLabTest($provider, ['name_en' => 'Active Test']);
        $inactive = $this->createLabTest($provider, ['name_en' => 'Inactive Test', 'is_active' => false]);
        $pendingTest = $this->createLabTest($pendingProvider, ['name_en' => 'Pending Test']);
        $package = $this->createLabPackage($provider, [$active], ['name_en' => 'Active Package']);
        $inactivePackage = $this->createLabPackage($provider, [$active], ['name_en' => 'Inactive Package', 'is_active' => false]);

        $this->getJson('/api/v1/labs/'.$provider->id.'/tests')
            ->assertOk()
            ->assertJsonFragment(['id' => $active->id])
            ->assertJsonMissing(['id' => $inactive->id]);

        $this->getJson('/api/v1/labs/'.$provider->id.'/packages')
            ->assertOk()
            ->assertJsonFragment(['id' => $package->id])
            ->assertJsonMissing(['id' => $inactivePackage->id]);

        $this->getJson('/api/v1/labs/'.$pendingProvider->id.'/tests')->assertNotFound();
        $this->assertNotNull($pendingTest);
    }

    public function test_lab_package_items_must_belong_to_same_lab(): void
    {
        ['user' => $labUser, 'provider' => $provider] = $this->createLabProvider();
        ['provider' => $otherProvider] = $this->createLabProvider(email: 'foreign-package-lab@example.com');
        $ownTest = $this->createLabTest($provider);
        $otherTest = $this->createLabTest($otherProvider);

        Sanctum::actingAs($labUser);
        $this->postJson('/api/v1/provider/lab/packages', [
            'name_en' => 'Mixed Package',
            'price' => 200,
            'test_ids' => [$ownTest->id, $otherTest->id],
        ])->assertUnprocessable();
    }

    public function test_patient_can_create_lab_order_with_backend_totals_and_financial_privacy(): void
    {
        $this->createCommissionRule(percentage: 10);
        $patient = $this->patientUser();
        $admin = $this->adminUser();
        ['user' => $labUser, 'provider' => $provider] = $this->createLabProvider();
        $test = $this->createLabTest($provider, ['name_en' => 'CBC', 'price' => 100]);
        $package = $this->createLabPackage($provider, [$test], ['name_en' => 'Full Checkup', 'price' => 250]);

        Sanctum::actingAs($patient);
        $orderId = $this->postJson('/api/v1/lab/orders', [
            'lab_provider_id' => $provider->id,
            'sample_collection_method' => LabSampleCollectionMethod::BranchVisit->value,
            'subtotal' => 1,
            'grand_total' => 1,
            'payment_status' => LabOrderPaymentStatus::Paid->value,
            'items' => [
                ['item_type' => LabOrderItemType::Test->value, 'test_id' => $test->id, 'quantity' => 1, 'unit_price' => 1],
            ],
        ])->assertUnprocessable();

        $orderId = $this->postJson('/api/v1/lab/orders', [
            'lab_provider_id' => $provider->id,
            'sample_collection_method' => LabSampleCollectionMethod::BranchVisit->value,
            'items' => [
                ['item_type' => LabOrderItemType::Test->value, 'test_id' => $test->id, 'quantity' => 2],
                ['item_type' => LabOrderItemType::Package->value, 'package_id' => $package->id, 'quantity' => 1],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('data.subtotal', '450.00')
            ->assertJsonPath('data.grand_total', '450.00')
            ->assertJsonPath('data.payment_status', LabOrderPaymentStatus::Unpaid->value)
            ->assertJsonPath('data.order_status', LabOrderStatus::LabReview->value)
            ->assertJsonMissingPath('data.commission_amount')
            ->assertJsonMissingPath('data.provider_net_amount')
            ->json('data.id');

        $order = LabOrder::query()->with('items')->findOrFail($orderId);
        $this->assertSame('100.00', $order->items->firstWhere('test_id', $test->id)->unit_price);

        $test->update(['price' => 999]);
        $this->assertSame('100.00', $order->items()->where('test_id', $test->id)->firstOrFail()->unit_price);

        Sanctum::actingAs($labUser);
        $this->getJson('/api/v1/provider/lab/orders/'.$order->id)
            ->assertOk()
            ->assertJsonPath('data.provider_net_amount', '405.00');

        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/admin/lab-orders/'.$order->id)
            ->assertOk()
            ->assertJsonPath('data.commission_amount', '45.00')
            ->assertJsonPath('data.provider_net_amount', '405.00');
    }

    public function test_home_collection_requires_address_and_unapproved_lab_cannot_receive_orders(): void
    {
        $patient = $this->patientUser();
        ['provider' => $provider] = $this->createLabProvider();
        ['provider' => $pendingProvider] = $this->createLabProvider(status: ProviderStatus::PendingReview, email: 'unapproved-order-lab@example.com');
        $test = $this->createLabTest($provider);
        $pendingTest = $this->createLabTest($pendingProvider);

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/lab/orders', [
            'lab_provider_id' => $provider->id,
            'sample_collection_method' => LabSampleCollectionMethod::HomeCollection->value,
            'items' => [
                ['item_type' => LabOrderItemType::Test->value, 'test_id' => $test->id, 'quantity' => 1],
            ],
        ])->assertUnprocessable();

        $this->postJson('/api/v1/lab/orders', [
            'lab_provider_id' => $pendingProvider->id,
            'sample_collection_method' => LabSampleCollectionMethod::BranchVisit->value,
            'items' => [
                ['item_type' => LabOrderItemType::Test->value, 'test_id' => $pendingTest->id, 'quantity' => 1],
            ],
        ])->assertUnprocessable();
    }

    public function test_lab_order_access_is_scoped_to_patient_provider_and_admin(): void
    {
        $patient = $this->patientUser();
        $otherPatient = $this->patientUser('other-lab-patient@example.com');
        $admin = $this->adminUser();
        ['user' => $labUser, 'provider' => $provider] = $this->createLabProvider();
        ['user' => $otherLabUser] = $this->createLabProvider(email: 'other-order-lab@example.com');
        $test = $this->createLabTest($provider);
        $order = $this->createLabOrder($patient, $provider, [
            ['item_type' => LabOrderItemType::Test->value, 'test_id' => $test->id, 'quantity' => 1],
        ]);

        Sanctum::actingAs($otherPatient);
        $this->getJson('/api/v1/lab/orders/'.$order->id)->assertForbidden();

        Sanctum::actingAs($otherLabUser);
        $this->getJson('/api/v1/provider/lab/orders/'.$order->id)->assertForbidden();

        Sanctum::actingAs($labUser);
        $this->getJson('/api/v1/provider/lab/orders/'.$order->id)->assertOk();

        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/admin/lab-orders/'.$order->id)->assertOk();

        Sanctum::actingAs($patient);
        $this->getJson('/api/v1/admin/lab-orders')->assertForbidden();
    }

    public function test_payment_cannot_be_created_before_lab_accepts_order_then_manual_verification_marks_order_paid(): void
    {
        Storage::fake('medical_private');
        $this->createCommissionRule(percentage: 10);
        $context = $this->createAcceptedLabOrderWithPayment(grandTotal: 300, createPayment: false);

        Sanctum::actingAs($context['patient']);
        $this->postJson('/api/v1/lab/orders/'.$context['order']->id.'/pay')->assertOk();
        $payment = $context['order']->refresh()->payment;

        $payment = $this->verifyManualPaymentForOrder($context['patient'], $payment);
        $order = $context['order']->refresh();

        $this->assertSame(PaymentStatus::Verified, $payment->status);
        $this->assertSame(LabOrderPaymentStatus::Paid, $order->payment_status);
        $this->assertSame(LabOrderStatus::Paid, $order->order_status);
        $this->assertSame(1, Invoice::query()->where('payment_id', $payment->id)->count());
        $this->assertSame(2, WalletTransaction::query()->count());
    }

    public function test_payment_cannot_be_created_before_accepted(): void
    {
        $patient = $this->patientUser();
        ['provider' => $provider] = $this->createLabProvider();
        $test = $this->createLabTest($provider);
        $order = $this->createLabOrder($patient, $provider, [
            ['item_type' => LabOrderItemType::Test->value, 'test_id' => $test->id, 'quantity' => 1],
        ]);

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/lab/orders/'.$order->id.'/pay')->assertUnprocessable();
    }

    public function test_patient_can_cancel_own_unpaid_lab_order_before_payment_flow(): void
    {
        $patient = $this->patientUser();
        $otherPatient = $this->patientUser('other-cancel-lab@example.com');
        ['user' => $labUser, 'provider' => $provider] = $this->createLabProvider();
        $test = $this->createLabTest($provider);
        $order = $this->createLabOrder($patient, $provider, [
            ['item_type' => LabOrderItemType::Test->value, 'test_id' => $test->id, 'quantity' => 1],
        ]);

        Sanctum::actingAs($labUser);
        $this->patchJson('/api/v1/provider/lab/orders/'.$order->id.'/status', [
            'status' => LabOrderStatus::Accepted->value,
        ])->assertOk();

        Sanctum::actingAs($otherPatient);
        $this->postJson('/api/v1/lab/orders/'.$order->id.'/cancel', [
            'reason' => 'Not mine.',
        ])->assertForbidden();

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/lab/orders/'.$order->id.'/cancel', [
            'reason' => 'Will reschedule.',
        ])
            ->assertOk()
            ->assertJsonPath('data.order_status', LabOrderStatus::Cancelled->value)
            ->assertJsonPath('data.payment_status', LabOrderPaymentStatus::Unpaid->value);

        $this->assertNotNull($order->refresh()->cancelled_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'lab_order.cancelled_by_patient']);
    }

    public function test_patient_cannot_cancel_lab_order_after_payment_flow_started(): void
    {
        $context = $this->createAcceptedLabOrderWithPayment(grandTotal: 300);

        Sanctum::actingAs($context['patient']);
        $this->postJson('/api/v1/lab/orders/'.$context['order']->id.'/cancel', [
            'reason' => 'Too late.',
        ])->assertUnprocessable();

        $this->assertSame(LabOrderStatus::AwaitingPayment, $context['order']->refresh()->order_status);
        $this->assertSame(LabOrderPaymentStatus::PendingPayment, $context['order']->payment_status);
    }

    public function test_paymob_verification_marks_lab_order_paid_and_is_idempotent(): void
    {
        $this->createCommissionRule(percentage: 10);
        $context = $this->createAcceptedLabOrderWithPayment(grandTotal: 300);
        $payment = $this->makePaymentPendingPaymob($context['payment']);
        $payload = $this->paymobPayload($payment);
        $payload['hmac'] = app(PaymobGateway::class)->calculateHmac($payload);

        $this->postJson('/api/v1/payments/paymob/callback', $payload)
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::Verified->value)
            ->assertJsonPath('data.lab_order.payment_status', LabOrderPaymentStatus::Paid->value)
            ->assertJsonPath('data.lab_order.order_status', LabOrderStatus::Paid->value);

        $order = $context['order']->refresh();
        $wallet = $this->walletForProvider($context['provider']);
        $historyCount = $order->statusHistories()->where('to_status', LabOrderStatus::Paid->value)->count();
        $invoiceCount = Invoice::query()->where('payment_id', $payment->id)->count();
        $transactionCount = $wallet->transactions()->count();

        $this->postJson('/api/v1/payments/paymob/callback', $payload)->assertOk();

        $this->assertSame($historyCount, $order->statusHistories()->where('to_status', LabOrderStatus::Paid->value)->count());
        $this->assertSame($invoiceCount, Invoice::query()->where('payment_id', $payment->id)->count());
        $this->assertSame($transactionCount, $wallet->transactions()->count());
    }

    public function test_failed_payment_and_cancelled_order_do_not_mark_lab_order_paid(): void
    {
        $context = $this->createAcceptedLabOrderWithPayment(grandTotal: 300);
        $payment = $this->makePaymentPendingPaymob($context['payment']);
        $payload = $this->paymobPayload($payment, success: false);
        $payload['hmac'] = app(PaymobGateway::class)->calculateHmac($payload);

        $this->postJson('/api/v1/payments/paymob/webhook', $payload)
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::Failed->value)
            ->assertJsonPath('data.lab_order.payment_status', LabOrderPaymentStatus::PendingPayment->value);

        $this->assertSame(LabOrderStatus::AwaitingPayment, $context['order']->refresh()->order_status);

        $context = $this->createAcceptedLabOrderWithPayment(grandTotal: 300);
        $payment = $this->prepareManualPaymentProofForOrder($context['patient'], $context['payment']);
        $context['order']->forceFill([
            'order_status' => LabOrderStatus::Cancelled,
            'payment_status' => LabOrderPaymentStatus::PendingPaymentReview,
        ])->save();

        Sanctum::actingAs($this->adminUser());
        $this->postJson('/api/v1/admin/payments/'.$payment->id.'/accept')->assertUnprocessable();
        $this->assertNotSame(PaymentStatus::Verified, $payment->refresh()->status);
    }

    public function test_completed_lab_order_releases_earning_once_and_can_be_settled_once(): void
    {
        $this->createCommissionRule(percentage: 10);
        $context = $this->createAcceptedLabOrderWithPayment(grandTotal: 300);
        $this->verifyManualPaymentForOrder($context['patient'], $context['payment']);

        Sanctum::actingAs($context['lab_user']);
        $this->patchJson('/api/v1/provider/lab/orders/'.$context['order']->id.'/status', [
            'status' => LabOrderStatus::Completed->value,
        ])->assertUnprocessable();

        $this->uploadResult($context['lab_user'], $context['order']);

        $this->patchJson('/api/v1/provider/lab/orders/'.$context['order']->id.'/status', [
            'status' => LabOrderStatus::Completed->value,
        ])->assertOk()->assertJsonPath('data.order_status', LabOrderStatus::Completed->value);

        $completedOrder = $context['order']->refresh();
        $this->assertNotNull($completedOrder->completed_at);
        $this->assertTrue($completedOrder->statusHistories()->where('to_status', LabOrderStatus::ResultReady->value)->exists());
        $this->assertTrue($completedOrder->statusHistories()->where('to_status', LabOrderStatus::Completed->value)->exists());

        $this->patchJson('/api/v1/provider/lab/orders/'.$context['order']->id.'/status', [
            'status' => LabOrderStatus::Completed->value,
        ])->assertOk();

        $wallet = $this->walletForProvider($context['provider']);
        $this->assertSame(1, $wallet->transactions()->where('type', WalletTransactionType::Release)->count());

        Sanctum::actingAs($this->adminUser());
        $settlementId = $this->postJson('/api/v1/admin/settlements', [
            'provider_id' => $context['provider']->id,
            'provider_type' => ProviderType::Lab->value,
        ])
            ->assertCreated()
            ->assertJsonPath('data.total_net', '270.00')
            ->assertJsonPath('data.status', SettlementStatus::Draft->value)
            ->json('data.id');

        $this->assertSame(1, SettlementItem::query()->where('settlement_id', $settlementId)->count());

        $this->postJson('/api/v1/admin/settlements', [
            'provider_id' => $context['provider']->id,
            'provider_type' => ProviderType::Lab->value,
        ])->assertUnprocessable();
    }

    public function test_lab_result_upload_and_download_are_private_and_authorized(): void
    {
        Storage::fake('medical_private');
        $this->createCommissionRule(percentage: 0);
        $context = $this->createAcceptedLabOrderWithPayment(grandTotal: 300);
        $this->verifyManualPaymentForOrder($context['patient'], $context['payment']);
        $otherPatient = $this->patientUser('other-result-patient@example.com');
        ['user' => $otherLabUser] = $this->createLabProvider(email: 'other-result-lab@example.com');

        Sanctum::actingAs($context['patient']);
        $this->post('/api/v1/provider/lab/orders/'.$context['order']->id.'/results', [
            'file' => UploadedFile::fake()->create('result.pdf', 100, 'application/pdf'),
        ])->assertForbidden();

        Sanctum::actingAs($otherLabUser);
        $this->post('/api/v1/provider/lab/orders/'.$context['order']->id.'/results', [
            'file' => UploadedFile::fake()->create('result.pdf', 100, 'application/pdf'),
        ])->assertForbidden();

        $resultId = $this->uploadResult($context['lab_user'], $context['order']);
        $result = LabResult::query()->with('file')->findOrFail($resultId);

        $this->assertSame(LabResultStatus::VisibleToPatient, $result->status);
        $this->assertSame('private', $result->file->visibility->value);

        Sanctum::actingAs($context['patient']);
        $this->getJson('/api/v1/lab/orders/'.$context['order']->id.'/results')
            ->assertOk()
            ->assertJsonMissingPath('data.0.file.path')
            ->assertJsonMissingPath('data.0.file.url');
        $this->get('/api/v1/lab/results/'.$result->id.'/download')->assertOk();

        Sanctum::actingAs($otherPatient);
        $this->getJson('/api/v1/lab/results/'.$result->id.'/download')->assertForbidden();
    }

    public function test_unpaid_order_cannot_be_processed_and_admin_can_update_status(): void
    {
        $patient = $this->patientUser();
        $admin = $this->adminUser();
        ['user' => $labUser, 'provider' => $provider] = $this->createLabProvider();
        $test = $this->createLabTest($provider);
        $order = $this->createLabOrder($patient, $provider, [
            ['item_type' => LabOrderItemType::Test->value, 'test_id' => $test->id, 'quantity' => 1],
        ]);

        Sanctum::actingAs($labUser);
        $this->patchJson('/api/v1/provider/lab/orders/'.$order->id.'/status', [
            'status' => LabOrderStatus::Processing->value,
        ])->assertUnprocessable();

        Sanctum::actingAs($admin);
        $this->patchJson('/api/v1/admin/lab-orders/'.$order->id.'/status', [
            'status' => LabOrderStatus::Accepted->value,
            'reason' => 'Admin accepted.',
        ])->assertOk()->assertJsonPath('data.order_status', LabOrderStatus::Accepted->value);

        Sanctum::actingAs($patient);
        $this->patchJson('/api/v1/admin/lab-orders/'.$order->id.'/status', [
            'status' => LabOrderStatus::Cancelled->value,
        ])->assertForbidden();
    }

    private function createAcceptedLabOrderWithPayment(int|float $grandTotal, bool $createPayment = true): array
    {
        $patient = $this->patientUser('lab-patient-'.Str::random(10).'@example.com');
        ['user' => $labUser, 'provider' => $provider] = $this->createLabProvider();
        $test = $this->createLabTest($provider, ['price' => $grandTotal]);
        $order = $this->createLabOrder($patient, $provider, [
            ['item_type' => LabOrderItemType::Test->value, 'test_id' => $test->id, 'quantity' => 1],
        ]);

        Sanctum::actingAs($labUser);
        $this->patchJson('/api/v1/provider/lab/orders/'.$order->id.'/status', [
            'status' => LabOrderStatus::Accepted->value,
        ])->assertOk();

        if ($createPayment) {
            Sanctum::actingAs($patient);
            $this->postJson('/api/v1/lab/orders/'.$order->id.'/pay')
                ->assertOk()
                ->assertJsonPath('data.order_status', LabOrderStatus::AwaitingPayment->value)
                ->assertJsonPath('data.payment_status', LabOrderPaymentStatus::PendingPayment->value);
        }

        return [
            'patient' => $patient,
            'lab_user' => $labUser,
            'provider' => $provider,
            'test' => $test,
            'order' => $order->refresh(),
            'payment' => $order->refresh()->payment,
        ];
    }

    private function verifyManualPaymentForOrder(User $patient, Payment $payment): Payment
    {
        $payment = $this->prepareManualPaymentProofForOrder($patient, $payment);
        Sanctum::actingAs($this->adminUser());

        $this->postJson('/api/v1/admin/payments/'.$payment->id.'/accept')
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::Verified->value)
            ->assertJsonPath('data.lab_order.payment_status', LabOrderPaymentStatus::Paid->value);

        return $payment->refresh();
    }

    private function prepareManualPaymentProofForOrder(User $patient, Payment $payment): Payment
    {
        $method = $this->paymentMethod(PaymentMethodType::ManualVodafoneCash);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/payments/'.$payment->id.'/manual/select', [
            'payment_method_id' => $method->id,
        ])->assertOk();

        $this->post('/api/v1/payments/'.$payment->id.'/proofs', [
            'file' => UploadedFile::fake()->image('proof.jpg'),
            'reference_number' => 'VC-123',
            'sender_phone' => '01012345678',
        ])->assertCreated();

        return $payment->refresh();
    }

    private function makePaymentPendingPaymob(Payment $payment): Payment
    {
        $method = $this->paymentMethod(PaymentMethodType::Paymob);
        $payment->update([
            'payment_method_id' => $method->id,
            'status' => PaymentStatus::PendingGateway,
        ]);

        $payment->attempts()->create([
            'method_type' => PaymentMethodType::Paymob,
            'gateway_reference' => 'paymob-txn-'.$payment->id,
            'status' => 'created',
        ]);

        Config::set('paymob.hmac_secret', 'test-hmac-secret');

        return $payment->refresh();
    }

    private function paymobPayload(Payment $payment, bool $success = true): array
    {
        return [
            'amount_cents' => (string) ((int) round((float) $payment->amount * 100)),
            'created_at' => '2026-05-05T12:00:00',
            'currency' => 'EGP',
            'error_occured' => $success ? 'false' : 'true',
            'has_parent_transaction' => 'false',
            'id' => 'paymob-txn-'.$payment->id,
            'integration_id' => '123456',
            'is_3d_secure' => 'true',
            'is_auth' => 'false',
            'is_capture' => 'false',
            'is_refunded' => 'false',
            'is_standalone_payment' => 'true',
            'is_voided' => 'false',
            'order' => [
                'id' => 'order-'.$payment->id,
                'merchant_order_id' => 'ETAMEN-PAY-'.$payment->id,
            ],
            'owner' => '1',
            'pending' => 'false',
            'source_data' => [
                'pan' => '2346',
                'sub_type' => 'MasterCard',
                'type' => 'card',
            ],
            'success' => $success ? 'true' : 'false',
        ];
    }

    private function uploadResult(User $labUser, LabOrder $order): int
    {
        Sanctum::actingAs($labUser);

        return $this->post('/api/v1/provider/lab/orders/'.$order->id.'/results', [
            'file' => UploadedFile::fake()->create('result.pdf', 100, 'application/pdf'),
            'title_en' => 'Result',
        ])
            ->assertCreated()
            ->assertJsonPath('data.file.visibility', 'private')
            ->json('data.id');
    }

    private function createLabOrder(User $patient, Provider $provider, array $items): LabOrder
    {
        Sanctum::actingAs($patient);

        $orderId = $this->postJson('/api/v1/lab/orders', [
            'lab_provider_id' => $provider->id,
            'sample_collection_method' => LabSampleCollectionMethod::BranchVisit->value,
            'items' => $items,
        ])
            ->assertCreated()
            ->json('data.id');

        return LabOrder::query()->with(['items', 'payment'])->findOrFail($orderId);
    }

    private function createCommissionRule(int|float $percentage, int|float|null $fixedAmount = null): CommissionRule
    {
        return CommissionRule::query()->create([
            'provider_type' => ProviderType::Lab,
            'service_type' => ServiceType::LabOrder,
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
            ->where('owner_type', WalletOwnerType::Lab)
            ->where('owner_id', $provider->id)
            ->where('currency', 'EGP')
            ->firstOrFail();
    }

    private function createLabTest(Provider $provider, array $overrides = []): LabTest
    {
        return LabTest::query()->create([
            'provider_id' => $provider->id,
            'name_en' => 'Lab Test '.Str::random(5),
            'price' => 100,
            'sample_type' => 'blood',
            'is_active' => true,
            ...$overrides,
        ]);
    }

    private function createLabPackage(Provider $provider, array $tests, array $overrides = []): LabPackage
    {
        $package = LabPackage::query()->create([
            'provider_id' => $provider->id,
            'name_en' => 'Lab Package '.Str::random(5),
            'price' => 200,
            'is_active' => true,
            ...$overrides,
        ]);

        $package->tests()->sync(collect($tests)->pluck('id')->all());

        return $package->refresh()->load('tests');
    }

    private function patientUser(string $email = 'lab-patient@example.com'): User
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

    private function createLabProvider(
        ProviderStatus $status = ProviderStatus::Approved,
        bool $isActive = true,
        ?string $email = null,
    ): array {
        $user = User::factory()->create(['email' => $email ?? 'lab-'.Str::random(8).'@example.com']);
        $user->assignRole(UserRole::LabAdmin->value);

        $provider = Provider::query()->create([
            'type' => ProviderType::Lab,
            'owner_user_id' => $user->id,
            'name_en' => 'Lab Provider '.Str::random(6),
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

        LabProfile::query()->create([
            'provider_id' => $provider->id,
            'license_number' => 'LAB-'.Str::upper(Str::random(6)),
            'home_collection_available' => true,
        ]);

        return [
            'user' => $user,
            'provider' => $provider->refresh(),
        ];
    }
}
