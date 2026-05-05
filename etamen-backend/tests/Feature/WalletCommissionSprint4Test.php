<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentSlotStatus;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Domain\Enums\ConsultationType;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\Appointments\Infrastructure\Models\AppointmentSlot;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Payments\Database\Seeders\PaymentMethodSeeder;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Invoice;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Domain\Enums\ServiceType;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Wallets\Domain\Enums\SettlementStatus;
use App\Modules\Wallets\Domain\Enums\WalletOwnerType;
use App\Modules\Wallets\Domain\Enums\WalletTransactionType;
use App\Modules\Wallets\Domain\Enums\WithdrawalRequestStatus;
use App\Modules\Wallets\Infrastructure\Models\CommissionRule;
use App\Modules\Wallets\Infrastructure\Models\Settlement;
use App\Modules\Wallets\Infrastructure\Models\SettlementItem;
use App\Modules\Wallets\Infrastructure\Models\Wallet;
use App\Modules\Wallets\Infrastructure\Models\WalletTransaction;
use App\Modules\Wallets\Infrastructure\Models\WithdrawalRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WalletCommissionSprint4Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PaymentMethodSeeder::class);
    }

    public function test_verified_paid_appointment_posts_wallet_hold_and_commission_from_active_rule(): void
    {
        $this->createCommissionRule(percentage: 10, fixedAmount: 5);

        $context = $this->verifyPaidAppointment(fee: 300);
        $wallet = $this->walletForProvider($context['provider']);

        $this->assertSame(WalletOwnerType::Doctor, $wallet->owner_type);
        $this->assertSame($context['provider']->id, $wallet->owner_id);

        $hold = $wallet->transactions()->where('type', WalletTransactionType::Hold)->firstOrFail();
        $commission = $wallet->transactions()->where('type', WalletTransactionType::Commission)->firstOrFail();

        $this->assertSame('300.00', $hold->gross_amount);
        $this->assertSame('35.00', $hold->commission_amount);
        $this->assertSame('265.00', $hold->net_amount);
        $this->assertSame('35.00', $commission->commission_amount);
        $this->assertSame('0.00', $commission->net_amount);
        $this->assertSame('payment:'.$context['payment']->id.':provider_hold', $hold->idempotency_key);
        $this->assertDatabaseHas('audit_logs', ['action' => 'wallet.payment_hold_posted']);
    }

    public function test_missing_commission_rule_posts_zero_commission_safely(): void
    {
        $context = $this->verifyPaidAppointment(fee: 300);
        $wallet = $this->walletForProvider($context['provider']);
        $hold = $wallet->transactions()->where('type', WalletTransactionType::Hold)->firstOrFail();

        $this->assertSame('300.00', $hold->gross_amount);
        $this->assertSame('0.00', $hold->commission_amount);
        $this->assertSame('300.00', $hold->net_amount);
        $this->assertTrue($hold->metadata['missing_commission_rule']);
    }

    public function test_duplicate_manual_accept_does_not_duplicate_wallet_transactions_or_invoice(): void
    {
        $this->createCommissionRule(percentage: 10);
        $context = $this->prepareManualReviewPayment(fee: 300);
        $admin = $this->adminUser();
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/admin/payments/'.$context['payment']->id.'/accept')->assertOk();
        $this->postJson('/api/v1/admin/payments/'.$context['payment']->id.'/accept')->assertOk();

        $wallet = $this->walletForProvider($context['provider']);

        $this->assertSame(1, $wallet->transactions()->where('type', WalletTransactionType::Hold)->count());
        $this->assertSame(1, $wallet->transactions()->where('type', WalletTransactionType::Commission)->count());
        $this->assertSame(1, Invoice::query()->where('payment_id', $context['payment']->id)->count());
    }

    public function test_free_appointment_does_not_create_wallet_transaction(): void
    {
        $patient = $this->patientUser();
        $appointment = $this->bookAppointmentThroughApi($patient, fee: 0);

        $this->assertSame(AppointmentStatus::Confirmed, $appointment->status);
        $this->assertSame(0, WalletTransaction::query()->count());
    }

    public function test_completion_releases_paid_earning_once_and_wallet_balance_updates(): void
    {
        $this->createCommissionRule(percentage: 10);
        $context = $this->verifyPaidAppointment(fee: 300);
        $wallet = $this->walletForProvider($context['provider']);

        Sanctum::actingAs($context['doctor_user']);
        $this->getJson('/api/v1/provider/wallet')
            ->assertOk()
            ->assertJsonPath('data.balances.pending_balance', 270)
            ->assertJsonPath('data.balances.available_balance', 0);

        $this->postJson('/api/v1/provider/appointments/'.$context['appointment']->id.'/accept')->assertOk();
        $this->postJson('/api/v1/provider/appointments/'.$context['appointment']->id.'/complete')->assertOk();
        $this->postJson('/api/v1/provider/appointments/'.$context['appointment']->id.'/complete')->assertUnprocessable();

        $this->assertSame(1, $wallet->transactions()->where('type', WalletTransactionType::Release)->count());

        $this->getJson('/api/v1/provider/wallet')
            ->assertOk()
            ->assertJsonPath('data.balances.pending_balance', 0)
            ->assertJsonPath('data.balances.available_balance', 270);

        $this->assertDatabaseHas('audit_logs', ['action' => 'wallet.appointment_earning_released']);
    }

    public function test_completion_before_verified_payment_does_not_release_earning(): void
    {
        $patient = $this->patientUser();
        $context = $this->bookPaidAppointmentContext($patient, fee: 300);
        $context['appointment']->update([
            'status' => AppointmentStatus::Accepted,
            'confirmed_at' => now(),
            'accepted_at' => now(),
        ]);

        Sanctum::actingAs($context['doctor_user']);
        $this->postJson('/api/v1/provider/appointments/'.$context['appointment']->id.'/complete')->assertOk();

        $this->assertSame(0, WalletTransaction::query()->where('type', WalletTransactionType::Release)->count());
        $this->assertSame(PaymentStatus::AwaitingMethod, $context['payment']->refresh()->status);
    }

    public function test_provider_withdrawal_flow_and_available_balance_reduction(): void
    {
        $this->createCommissionRule(percentage: 10);
        $context = $this->verifyAndCompletePaidAppointment(fee: 300);
        $admin = $this->adminUser();

        Sanctum::actingAs($context['doctor_user']);
        $withdrawalId = $this->postJson('/api/v1/provider/withdrawals', ['amount' => 100])
            ->assertCreated()
            ->assertJsonPath('data.status', WithdrawalRequestStatus::Pending->value)
            ->json('data.id');

        $this->postJson('/api/v1/provider/withdrawals', ['amount' => 10000])->assertUnprocessable();

        Sanctum::actingAs($this->patientUser('wallet-patient-denied@example.com'));
        $this->postJson('/api/v1/provider/withdrawals', ['amount' => 10])->assertForbidden();

        Sanctum::actingAs($admin);
        $this->postJson('/api/v1/admin/withdrawals/'.$withdrawalId.'/approve')
            ->assertOk()
            ->assertJsonPath('data.status', WithdrawalRequestStatus::Approved->value);
        $this->postJson('/api/v1/admin/withdrawals/'.$withdrawalId.'/mark-paid')
            ->assertOk()
            ->assertJsonPath('data.status', WithdrawalRequestStatus::Paid->value);
        $this->postJson('/api/v1/admin/withdrawals/'.$withdrawalId.'/mark-paid')->assertOk();

        $wallet = $this->walletForProvider($context['provider']);
        $this->assertSame(1, $wallet->transactions()->where('type', WalletTransactionType::Withdrawal)->count());

        Sanctum::actingAs($context['doctor_user']);
        $this->getJson('/api/v1/provider/wallet')
            ->assertOk()
            ->assertJsonPath('data.balances.available_balance', 170)
            ->assertJsonPath('data.balances.withdrawn_balance', 100);
    }

    public function test_admin_can_reject_withdrawal_and_non_admin_cannot_use_admin_wallet_routes(): void
    {
        $context = $this->verifyAndCompletePaidAppointment(fee: 300);
        $admin = $this->adminUser();

        Sanctum::actingAs($context['doctor_user']);
        $withdrawalId = $this->postJson('/api/v1/provider/withdrawals', ['amount' => 50])
            ->assertCreated()
            ->json('data.id');

        $wallet = $this->walletForProvider($context['provider']);
        $this->getJson('/api/v1/admin/wallets/'.$wallet->id)->assertForbidden();

        Sanctum::actingAs($admin);
        $this->postJson('/api/v1/admin/withdrawals/'.$withdrawalId.'/reject')
            ->assertUnprocessable();
        $this->postJson('/api/v1/admin/withdrawals/'.$withdrawalId.'/reject', [
            'reason' => 'Provider bank details are missing.',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', WithdrawalRequestStatus::Rejected->value);

        $this->assertSame(0, WalletTransaction::query()->where('type', WalletTransactionType::Withdrawal)->count());
        $this->assertSame('Provider bank details are missing.', WithdrawalRequest::query()->findOrFail($withdrawalId)->rejection_reason);
    }

    public function test_commission_rule_admin_management_and_latest_active_rule_selection(): void
    {
        $patient = $this->patientUser();
        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/admin/commission-rules', [
            'provider_type' => ProviderType::Doctor->value,
            'service_type' => ServiceType::Appointment->value,
            'percentage' => 5,
            'starts_at' => now()->subDay()->toISOString(),
        ])->assertForbidden();

        $admin = $this->adminUser();
        Sanctum::actingAs($admin);
        $ruleId = $this->postJson('/api/v1/admin/commission-rules', [
            'provider_type' => ProviderType::Doctor->value,
            'service_type' => ServiceType::Appointment->value,
            'percentage' => 5,
            'fixed_amount' => 0,
            'starts_at' => now()->subDays(5)->toISOString(),
            'is_active' => true,
        ])
            ->assertCreated()
            ->json('data.id');

        $this->putJson('/api/v1/admin/commission-rules/'.$ruleId, [
            'percentage' => 7,
        ])
            ->assertOk()
            ->assertJsonPath('data.percentage', '7.00');

        $this->createCommissionRule(percentage: 80, startsAt: now()->subDays(10), endsAt: now()->subDay());
        $this->createCommissionRule(percentage: 90, startsAt: now()->subHour(), active: false);
        $this->createCommissionRule(percentage: 20, startsAt: now()->subMinute());

        $context = $this->verifyPaidAppointment($patient, fee: 300);
        $wallet = $this->walletForProvider($context['provider']);
        $hold = $wallet->transactions()->where('type', WalletTransactionType::Hold)->firstOrFail();

        $this->assertSame('60.00', $hold->commission_amount);
        $this->assertSame('240.00', $hold->net_amount);
        $this->assertDatabaseHas('audit_logs', ['action' => 'commission_rule.created']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'commission_rule.updated']);
    }

    public function test_settlement_flow_uses_released_transactions_once(): void
    {
        $this->createCommissionRule(percentage: 10);
        $context = $this->verifyAndCompletePaidAppointment(fee: 300);
        $admin = $this->adminUser();

        Sanctum::actingAs($context['doctor_user']);
        $this->postJson('/api/v1/admin/settlements', [
            'provider_id' => $context['provider']->id,
            'provider_type' => ProviderType::Doctor->value,
        ])->assertForbidden();

        Sanctum::actingAs($admin);
        $settlementId = $this->postJson('/api/v1/admin/settlements', [
            'provider_id' => $context['provider']->id,
            'provider_type' => ProviderType::Doctor->value,
        ])
            ->assertCreated()
            ->assertJsonPath('data.total_net', '270.00')
            ->assertJsonPath('data.status', SettlementStatus::Draft->value)
            ->json('data.id');

        $this->assertSame(1, SettlementItem::query()->where('settlement_id', $settlementId)->count());

        $this->postJson('/api/v1/admin/settlements', [
            'provider_id' => $context['provider']->id,
            'provider_type' => ProviderType::Doctor->value,
        ])->assertUnprocessable();

        $this->postJson('/api/v1/admin/settlements/'.$settlementId.'/mark-paid')
            ->assertOk()
            ->assertJsonPath('data.status', SettlementStatus::Paid->value);
        $this->postJson('/api/v1/admin/settlements/'.$settlementId.'/mark-paid')->assertOk();

        $this->assertSame(SettlementStatus::Paid, Settlement::query()->findOrFail($settlementId)->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'settlement.created']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'settlement.paid']);
    }

    public function test_wallet_transactions_are_append_only_through_public_api_surface(): void
    {
        $context = $this->verifyPaidAppointment(fee: 300);
        $wallet = $this->walletForProvider($context['provider']);
        Sanctum::actingAs($context['doctor_user']);

        $updateResponse = $this->putJson('/api/v1/provider/wallet/transactions/'.$wallet->transactions()->firstOrFail()->id, [
            'net_amount' => 999999,
        ]);

        $this->assertContains($updateResponse->status(), [404, 405]);

        $this->postJson('/api/v1/provider/wallet/transactions', [
            'type' => WalletTransactionType::Release->value,
        ])->assertMethodNotAllowed();
    }

    private function verifyAndCompletePaidAppointment(int|float $fee = 300): array
    {
        $context = $this->verifyPaidAppointment(fee: $fee);

        Sanctum::actingAs($context['doctor_user']);
        $this->postJson('/api/v1/provider/appointments/'.$context['appointment']->id.'/accept')->assertOk();
        $this->postJson('/api/v1/provider/appointments/'.$context['appointment']->id.'/complete')->assertOk();

        return [
            ...$context,
            'appointment' => $context['appointment']->refresh(),
        ];
    }

    private function verifyPaidAppointment(?User $patient = null, int|float $fee = 300): array
    {
        $context = $this->prepareManualReviewPayment($patient ?? $this->patientUser(), $fee);
        $admin = $this->adminUser();
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/admin/payments/'.$context['payment']->id.'/accept')
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::Verified->value)
            ->assertJsonPath('data.appointment.status', AppointmentStatus::Confirmed->value);

        return [
            ...$context,
            'payment' => $context['payment']->refresh(),
            'appointment' => $context['appointment']->refresh(),
        ];
    }

    private function prepareManualReviewPayment(?User $patient = null, int|float $fee = 300): array
    {
        Storage::fake('medical_private');

        $patient ??= $this->patientUser();
        $context = $this->bookPaidAppointmentContext($patient, $fee);
        $method = $this->paymentMethod(PaymentMethodType::ManualVodafoneCash);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/payments/'.$context['payment']->id.'/manual/select', [
            'payment_method_id' => $method->id,
        ])->assertOk();

        $this->post('/api/v1/payments/'.$context['payment']->id.'/proofs', [
            'file' => UploadedFile::fake()->image('proof.jpg'),
            'reference_number' => 'VC-'.Str::upper(Str::random(6)),
            'sender_phone' => '01012345678',
        ])->assertCreated();

        return [
            ...$context,
            'payment' => $context['payment']->refresh(),
            'appointment' => $context['appointment']->refresh(),
        ];
    }

    private function bookPaidAppointmentContext(User $patient, int|float $fee = 300): array
    {
        $providerContext = $this->createDoctorProvider(fee: $fee);
        $slot = $this->createSlot($providerContext['doctor']);
        Sanctum::actingAs($patient);

        $appointmentId = $this->postJson('/api/v1/appointments', [
            'doctor_profile_id' => $providerContext['doctor']->id,
            'appointment_slot_id' => $slot->id,
            'consultation_type' => ConsultationType::Clinic->value,
            'problem_description' => 'Need consultation.',
        ])
            ->assertCreated()
            ->json('data.id');

        $appointment = Appointment::query()->with('payment')->findOrFail($appointmentId);

        return [
            ...$providerContext,
            'patient' => $patient,
            'appointment' => $appointment,
            'payment' => $appointment->payment,
        ];
    }

    private function bookAppointmentThroughApi(User $patient, int|float $fee = 300): Appointment
    {
        return $this->bookPaidAppointmentContext($patient, $fee)['appointment'];
    }

    private function paymentMethod(PaymentMethodType $type, bool $active = true): PaymentMethod
    {
        $method = PaymentMethod::query()->where('type', $type)->firstOrFail();
        $method->update(['is_active' => $active]);

        return $method->refresh();
    }

    private function createCommissionRule(
        int|float $percentage,
        int|float|null $fixedAmount = null,
        mixed $startsAt = null,
        mixed $endsAt = null,
        bool $active = true,
    ): CommissionRule {
        return CommissionRule::query()->create([
            'provider_type' => ProviderType::Doctor,
            'service_type' => ServiceType::Appointment,
            'percentage' => $percentage,
            'fixed_amount' => $fixedAmount,
            'starts_at' => $startsAt ?? now()->subDay(),
            'ends_at' => $endsAt,
            'is_active' => $active,
        ]);
    }

    private function walletForProvider(Provider $provider): Wallet
    {
        return Wallet::query()
            ->where('owner_type', WalletOwnerType::Doctor)
            ->where('owner_id', $provider->id)
            ->where('currency', 'EGP')
            ->firstOrFail();
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

    private function createDoctorProvider(
        ProviderStatus $status = ProviderStatus::Approved,
        bool $isActive = true,
        int|float $fee = 300,
        ?string $email = null,
    ): array {
        $user = User::factory()->create(['email' => $email ?? 'doctor-'.Str::random(8).'@example.com']);
        $user->assignRole(UserRole::Doctor->value);

        $provider = Provider::query()->create([
            'type' => ProviderType::Doctor,
            'owner_user_id' => $user->id,
            'name_en' => 'Doctor Provider '.Str::random(6),
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

        $doctor = DoctorProfile::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $user->id,
            'consultation_fee' => $fee,
        ]);

        return [
            'user' => $user,
            'doctor_user' => $user,
            'provider' => $provider->refresh(),
            'doctor' => $doctor->refresh(),
        ];
    }

    private function createSlot(DoctorProfile $doctor): AppointmentSlot
    {
        $startsAt = now()->addDays(3)->startOfDay()->setTime(9, 0);

        while (AppointmentSlot::query()->where('doctor_profile_id', $doctor->id)->where('starts_at', $startsAt)->exists()) {
            $startsAt = $startsAt->addMinutes(30);
        }

        return AppointmentSlot::query()->create([
            'doctor_profile_id' => $doctor->id,
            'provider_id' => $doctor->provider_id,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMinutes(30),
            'status' => AppointmentSlotStatus::Available,
        ]);
    }
}
