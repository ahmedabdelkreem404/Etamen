<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Fitness\Domain\Enums\CoachAvailabilityStatus;
use App\Modules\Fitness\Domain\Enums\CoachBookingStatus;
use App\Modules\Fitness\Domain\Enums\CoachSessionMode;
use App\Modules\Fitness\Domain\Enums\GymBookingStatus;
use App\Modules\Fitness\Infrastructure\Models\CoachAvailabilitySlot;
use App\Modules\Fitness\Infrastructure\Models\CoachBooking;
use App\Modules\Fitness\Infrastructure\Models\CoachPackage;
use App\Modules\Fitness\Infrastructure\Models\CoachSessionType;
use App\Modules\Fitness\Infrastructure\Models\GymBooking;
use App\Modules\Fitness\Infrastructure\Models\GymClassModel;
use App\Modules\Fitness\Infrastructure\Models\GymMembershipPlan;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Payments\Database\Seeders\PaymentMethodSeeder;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Invoice;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use App\Modules\Providers\Domain\Enums\CoachType;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\CoachProfile;
use App\Modules\Providers\Infrastructure\Models\GymProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GymCoachBackendSprint46Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PaymentMethodSeeder::class);
    }

    public function test_public_gym_discovery_and_membership_plans_are_safe(): void
    {
        ['gym' => $gym, 'plan' => $plan] = $this->createGymCatalog();
        $pending = $this->createGymProvider(status: ProviderStatus::PendingReview, active: false)['gym'];
        GymMembershipPlan::query()->create([
            'provider_id' => $pending->id,
            'name_ar' => 'Hidden plan',
            'duration_days' => 30,
            'price' => 100,
            'is_active' => true,
        ]);

        $this->getJson('/api/v1/gyms')
            ->assertOk()
            ->assertJsonPath('data.0.id', $gym->id)
            ->assertJsonMissing(['medical_private', 'storage/private']);

        $this->getJson('/api/v1/gyms/'.$gym->id.'/membership-plans')
            ->assertOk()
            ->assertJsonPath('data.0.id', $plan->id)
            ->assertJsonMissing(['config', 'path', 'medical_private']);
    }

    public function test_patient_can_create_gym_booking_with_backend_price_and_payment_acceptance(): void
    {
        Storage::fake('medical_private');

        $patient = $this->patientUser();
        ['plan' => $plan] = $this->createGymCatalog(price: 900);
        Sanctum::actingAs($patient);

        $response = $this->postJson('/api/v1/gym/bookings', [
            'provider_id' => $plan->provider_id,
            'membership_plan_id' => $plan->id,
            'total_amount' => 1,
            'status' => GymBookingStatus::Completed->value,
        ])->assertUnprocessable();

        $this->assertSame(0, GymBooking::query()->count());

        $bookingId = $this->postJson('/api/v1/gym/bookings', [
            'provider_id' => $plan->provider_id,
            'membership_plan_id' => $plan->id,
            'notes' => 'Demo gym booking.',
        ])->assertCreated()
            ->assertJsonPath('data.total_amount', '900.00')
            ->assertJsonPath('data.status', GymBookingStatus::PendingPayment->value)
            ->json('data.id');

        $booking = GymBooking::query()->with('payment')->findOrFail($bookingId);
        $this->assertSame(PaymentStatus::AwaitingMethod, $booking->payment->status);
        $this->assertSame(GymBooking::class, $booking->payment->payable_type);

        $this->completeManualPayment($patient, $booking->payment_id, 'gym-proof.jpg')
            ->assertJsonPath('data.status', PaymentStatus::PendingReview->value)
            ->assertJsonMissing(['medical_private', 'storage/private']);

        $this->assertSame(GymBookingStatus::PendingPaymentReview, $booking->refresh()->status);

        Sanctum::actingAs($this->adminUser());
        $this->postJson('/api/v1/admin/payments/'.$booking->payment_id.'/accept')
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::Verified->value)
            ->assertJsonPath('data.gym_booking.status', GymBookingStatus::Confirmed->value);

        $this->assertSame(GymBookingStatus::Confirmed, $booking->refresh()->status);
        $this->assertSame(1, Invoice::query()->where('payment_id', $booking->payment_id)->count());
    }

    public function test_gym_patient_and_provider_scoping_rules(): void
    {
        $patient = $this->patientUser();
        $otherPatient = $this->patientUser('other-gym-patient@example.com');
        ['gym' => $gym, 'owner' => $owner, 'plan' => $plan] = $this->createGymCatalog();
        ['owner' => $otherOwner] = $this->createGymCatalog();

        Sanctum::actingAs($patient);
        $bookingId = $this->postJson('/api/v1/gym/bookings', [
            'provider_id' => $gym->id,
            'membership_plan_id' => $plan->id,
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($otherPatient);
        $this->getJson('/api/v1/gym/bookings/'.$bookingId)->assertUnprocessable();

        Sanctum::actingAs($owner);
        $this->getJson('/api/v1/provider/gym/membership-plans')->assertOk()
            ->assertJsonPath('data.0.provider_id', $gym->id);
        $createdPlanId = $this->postJson('/api/v1/provider/gym/membership-plans', [
            'name_ar' => 'Provider owned plan',
            'duration_days' => 10,
            'price' => 300,
            'is_active' => true,
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($otherOwner);
        $this->deleteJson('/api/v1/provider/gym/membership-plans/'.$createdPlanId)->assertForbidden();
    }

    public function test_public_coach_discovery_session_types_and_packages_are_safe(): void
    {
        ['coach' => $coach, 'sessionType' => $sessionType] = $this->createCoachCatalog();

        $this->getJson('/api/v1/coaches')
            ->assertOk()
            ->assertJsonPath('data.0.id', $coach->id)
            ->assertJsonMissing(['medical_private', 'storage/private']);

        $this->getJson('/api/v1/coaches/'.$coach->id.'/session-types')
            ->assertOk()
            ->assertJsonPath('data.0.id', $sessionType->id)
            ->assertJsonMissing(['config', 'path', 'medical_private']);

        $this->getJson('/api/v1/coaches/'.$coach->id.'/packages')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_patient_can_create_coach_booking_and_admin_accepts_payment(): void
    {
        Storage::fake('medical_private');

        $patient = $this->patientUser();
        ['coach' => $coach, 'sessionType' => $sessionType, 'slot' => $slot] = $this->createCoachCatalog(price: 350);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/coach/bookings', [
            'coach_provider_id' => $coach->id,
            'session_type_id' => $sessionType->id,
            'availability_slot_id' => $slot->id,
            'total_amount' => 1,
            'status' => CoachBookingStatus::Completed->value,
        ])->assertUnprocessable();

        $bookingId = $this->postJson('/api/v1/coach/bookings', [
            'coach_provider_id' => $coach->id,
            'session_type_id' => $sessionType->id,
            'availability_slot_id' => $slot->id,
            'patient_goal' => 'Improve fitness safely.',
        ])->assertCreated()
            ->assertJsonPath('data.total_amount', '350.00')
            ->assertJsonPath('data.status', CoachBookingStatus::PendingPayment->value)
            ->json('data.id');

        $booking = CoachBooking::query()->with('payment')->findOrFail($bookingId);
        $this->assertSame(CoachAvailabilityStatus::Booked, $slot->refresh()->status);
        $this->assertSame(CoachBooking::class, $booking->payment->payable_type);

        $this->completeManualPayment($patient, $booking->payment_id, 'coach-proof.jpg')
            ->assertJsonPath('data.status', PaymentStatus::PendingReview->value);

        $this->assertSame(CoachBookingStatus::PendingPaymentReview, $booking->refresh()->status);

        Sanctum::actingAs($this->adminUser());
        $this->postJson('/api/v1/admin/payments/'.$booking->payment_id.'/accept')
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::Verified->value)
            ->assertJsonPath('data.coach_booking.status', CoachBookingStatus::Confirmed->value);

        $this->assertSame(CoachBookingStatus::Confirmed, $booking->refresh()->status);
    }

    public function test_coach_unavailable_slot_and_provider_scoping_rules(): void
    {
        $patient = $this->patientUser();
        ['coach' => $coach, 'owner' => $owner, 'sessionType' => $sessionType, 'slot' => $slot] = $this->createCoachCatalog();
        ['owner' => $otherOwner] = $this->createCoachCatalog();
        $slot->update(['status' => CoachAvailabilityStatus::Blocked]);

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/coach/bookings', [
            'coach_provider_id' => $coach->id,
            'session_type_id' => $sessionType->id,
            'availability_slot_id' => $slot->id,
        ])->assertUnprocessable();

        Sanctum::actingAs($owner);
        $createdId = $this->postJson('/api/v1/provider/coach/session-types', [
            'name_ar' => 'Owner session',
            'duration_minutes' => 30,
            'price' => 100,
            'session_mode' => CoachSessionMode::Online->value,
            'is_active' => true,
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($otherOwner);
        $this->deleteJson('/api/v1/provider/coach/session-types/'.$createdId)->assertForbidden();
    }

    private function completeManualPayment(User $patient, int $paymentId, string $fileName)
    {
        $method = PaymentMethod::query()->where('type', PaymentMethodType::ManualVodafoneCash)->firstOrFail();
        $method->update(['is_active' => true]);

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/payments/'.$paymentId.'/manual/select', [
            'payment_method_id' => $method->id,
        ])->assertOk()
            ->assertJsonPath('data.payment.status', PaymentStatus::AwaitingProof->value);

        return $this->post('/api/v1/payments/'.$paymentId.'/proofs', [
            'file' => UploadedFile::fake()->image($fileName),
            'reference_number' => 'FIT-'.Str::upper(Str::random(6)),
        ])->assertCreated();
    }

    private function createGymCatalog(int|float $price = 800, ProviderStatus $status = ProviderStatus::Approved, bool $active = true): array
    {
        $data = $this->createGymProvider($status, $active);
        $plan = GymMembershipPlan::query()->create([
            'provider_id' => $data['gym']->id,
            'branch_id' => $data['branch']->id,
            'name_ar' => 'اشتراك تجريبي',
            'name_en' => 'Demo Plan '.Str::random(5),
            'duration_days' => 30,
            'price' => $price,
            'is_active' => true,
        ]);
        $class = GymClassModel::query()->create([
            'provider_id' => $data['gym']->id,
            'branch_id' => $data['branch']->id,
            'name_ar' => 'كارديو تجريبي',
            'name_en' => 'Demo Class '.Str::random(5),
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(2)->addHour(),
            'price' => 150,
            'is_active' => true,
        ]);

        return $data + ['plan' => $plan, 'class' => $class];
    }

    private function createGymProvider(ProviderStatus $status = ProviderStatus::Approved, bool $active = true): array
    {
        $owner = User::factory()->create(['email' => 'gym-owner-'.Str::random(8).'@example.com']);
        $owner->assignRole(UserRole::ProviderAdmin->value);

        $gym = Provider::query()->create([
            'type' => ProviderType::Gym,
            'owner_user_id' => $owner->id,
            'name_ar' => 'جيم تجريبي',
            'name_en' => 'Demo Gym '.Str::random(6),
            'status' => $status,
            'is_active' => $active,
            'approved_at' => $status === ProviderStatus::Approved ? now() : null,
            'created_by' => $owner->id,
        ]);

        $this->attachOwner($gym, $owner);

        GymProfile::query()->create([
            'provider_id' => $gym->id,
            'has_classes' => true,
            'has_personal_training' => true,
            'is_active' => true,
        ]);

        $branch = ProviderBranch::query()->create([
            'provider_id' => $gym->id,
            'name_ar' => 'فرع تجريبي',
            'name_en' => 'Demo Branch',
            'is_main' => true,
            'is_active' => true,
        ]);

        return ['gym' => $gym, 'owner' => $owner, 'branch' => $branch];
    }

    private function createCoachCatalog(int|float $price = 300, ProviderType $type = ProviderType::FitnessCoach): array
    {
        $owner = User::factory()->create(['email' => 'coach-owner-'.Str::random(8).'@example.com']);
        $owner->assignRole(UserRole::ProviderAdmin->value);

        $coach = Provider::query()->create([
            'type' => $type,
            'owner_user_id' => $owner->id,
            'name_ar' => 'مدرب تجريبي',
            'name_en' => 'Demo Coach '.Str::random(6),
            'status' => ProviderStatus::Approved,
            'is_active' => true,
            'approved_at' => now(),
            'created_by' => $owner->id,
        ]);

        $this->attachOwner($coach, $owner);

        CoachProfile::query()->create([
            'provider_id' => $coach->id,
            'coach_type' => $type === ProviderType::NutritionCoach ? CoachType::Nutrition : CoachType::Fitness,
            'experience_years' => 5,
            'session_price' => $price,
            'online_coaching_enabled' => true,
            'is_active' => true,
        ]);

        $sessionType = CoachSessionType::query()->create([
            'provider_id' => $coach->id,
            'name_ar' => 'جلسة تجريبية',
            'name_en' => 'Demo Session',
            'duration_minutes' => 45,
            'price' => $price,
            'session_mode' => CoachSessionMode::Online,
            'is_active' => true,
        ]);

        $slot = CoachAvailabilitySlot::query()->create([
            'provider_id' => $coach->id,
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(2)->addHour(),
            'status' => CoachAvailabilityStatus::Available,
        ]);

        CoachPackage::query()->create([
            'provider_id' => $coach->id,
            'name_ar' => 'باقة تجريبية',
            'name_en' => 'Demo Package',
            'sessions_count' => 4,
            'duration_days' => 30,
            'price' => 900,
            'is_active' => true,
        ]);

        return ['coach' => $coach, 'owner' => $owner, 'sessionType' => $sessionType, 'slot' => $slot];
    }

    private function attachOwner(Provider $provider, User $owner): void
    {
        $provider->staff()->create([
            'user_id' => $owner->id,
            'role' => ProviderStaffRole::Owner,
            'is_owner' => true,
            'status' => 'active',
        ]);
    }

    private function patientUser(string $email = 'fitness-patient@example.com'): User
    {
        $user = User::factory()->create(['email' => $email]);
        $user->assignRole(UserRole::Patient->value);

        return $user;
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create(['email' => 'fitness-admin-'.Str::random(8).'@example.com']);
        Role::findOrCreate(UserRole::SuperAdmin->value);
        $admin->assignRole(UserRole::SuperAdmin->value);

        return $admin;
    }
}
