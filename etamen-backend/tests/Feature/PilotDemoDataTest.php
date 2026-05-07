<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentSlotStatus;
use App\Modules\Appointments\Infrastructure\Models\AppointmentReview;
use App\Modules\Appointments\Infrastructure\Models\AppointmentSlot;
use App\Modules\CarePlans\Domain\Enums\CarePlanStatus;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\Health\Infrastructure\Models\VitalRecord;
use App\Modules\Labs\Infrastructure\Models\LabPackage;
use App\Modules\Labs\Infrastructure\Models\LabResult;
use App\Modules\Labs\Infrastructure\Models\LabTest;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use App\Modules\Notifications\Infrastructure\Models\Notification;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyProduct;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Database\Seeders\PilotDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PilotDemoDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_pilot_demo_seeder_creates_required_walkthrough_data(): void
    {
        Storage::fake('medical_private');

        $this->seed(PilotDemoSeeder::class);
        $this->seed(PilotDemoSeeder::class);

        $patient = User::query()->where('email', 'pilot.patient@example.test')->firstOrFail();
        $doctor = Provider::query()->where('slug', 'pilot-demo-doctor')->firstOrFail();
        $pharmacy = Provider::query()->where('slug', 'pilot-demo-pharmacy')->firstOrFail();
        $lab = Provider::query()->where('slug', 'pilot-demo-lab')->firstOrFail();

        $this->assertTrue($patient->hasRole('patient'));
        $this->assertSame(ProviderType::Doctor, $doctor->type);
        $this->assertSame(ProviderStatus::Approved, $doctor->status);
        $this->assertTrue($doctor->is_active);
        $this->assertSame('legacy-doctorfinder/demo-doctor-avatar-1.png', $doctor->doctorProfile->avatar_path);
        $this->assertGreaterThanOrEqual(3, AppointmentReview::query()
            ->where('doctor_profile_id', $doctor->doctorProfile->id)
            ->where('is_visible', true)
            ->count());
        $this->assertSame(ProviderType::Pharmacy, $pharmacy->type);
        $this->assertSame(ProviderType::Lab, $lab->type);

        $this->assertGreaterThanOrEqual(1, AppointmentSlot::query()
            ->where('doctor_profile_id', $doctor->doctorProfile->id)
            ->where('status', AppointmentSlotStatus::Available)
            ->where('starts_at', '>', now())
            ->count());

        $this->assertTrue(PaymentMethod::query()
            ->where('type', PaymentMethodType::ManualVodafoneCash)
            ->where('is_active', true)
            ->exists());
        $this->assertTrue(PaymentMethod::query()
            ->where('type', PaymentMethodType::ManualInstapay)
            ->where('is_active', true)
            ->exists());

        $this->assertSame(2, PharmacyProduct::query()->where('provider_id', $pharmacy->id)->count());
        $this->assertSame(2, LabTest::query()->where('provider_id', $lab->id)->count());
        $this->assertSame(1, LabPackage::query()->where('provider_id', $lab->id)->count());
        $this->assertGreaterThanOrEqual(1, LabResult::query()->count());
        Storage::disk('medical_private')->assertExists('pilot-demo/lab-result-demo.txt');

        $this->assertSame(3, VitalRecord::query()->where('patient_user_id', $patient->id)->count());
        $this->assertTrue(MedicationReminder::query()->where('patient_user_id', $patient->id)->exists());
        $this->assertTrue(CarePlan::query()
            ->where('patient_user_id', $patient->id)
            ->where('status', CarePlanStatus::Active)
            ->exists());
        $this->assertTrue(Notification::query()
            ->where('user_id', $patient->id)
            ->where('type', 'pilot_demo_welcome')
            ->exists());

        $this->assertSame(1, Provider::query()->where('slug', 'pilot-demo-doctor')->count());
        $this->assertSame(1, User::query()->where('email', 'pilot.patient@example.test')->count());
    }

    public function test_pilot_demo_data_is_visible_through_mobile_endpoints(): void
    {
        Storage::fake('medical_private');
        $this->seed(PilotDemoSeeder::class);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'pilot.patient@example.test',
            'password' => 'Password1234',
        ])->assertOk()->json('data.token');

        $doctorId = $this->getJson('/api/v1/doctors')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'primary_branch_name',
                        'primary_area_name',
                        'primary_city_name',
                        'doctor_profile' => [
                            'avatar_url',
                            'rating_average',
                            'reviews_count',
                        ],
                    ],
                ],
            ])
            ->json('data.0.id');
        $this->assertGreaterThanOrEqual(4, Provider::query()->where('type', ProviderType::Doctor)->count());

        $this->getJson('/api/v1/doctors/'.$doctorId.'/slots')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson('/api/v1/payment-methods')
            ->assertOk()
            ->assertJsonFragment(['type' => PaymentMethodType::ManualVodafoneCash->value])
            ->assertJsonFragment(['type' => PaymentMethodType::ManualInstapay->value]);

        $pharmacyId = $this->getJson('/api/v1/pharmacies')
            ->assertOk()
            ->json('data.0.id');
        $this->assertGreaterThanOrEqual(3, Provider::query()->where('type', ProviderType::Pharmacy)->count());
        $this->getJson('/api/v1/pharmacies/'.$pharmacyId.'/products')
            ->assertOk()
            ->assertJsonPath('success', true);

        $labId = $this->getJson('/api/v1/labs')
            ->assertOk()
            ->json('data.0.id');
        $this->assertGreaterThanOrEqual(3, Provider::query()->where('type', ProviderType::Lab)->count());
        $this->getJson('/api/v1/labs/'.$labId.'/tests')
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->getJson('/api/v1/labs/'.$labId.'/packages')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->withHeader('Authorization', 'Bearer '.$login)->getJson('/api/v1/me')->assertOk();
        $this->withHeader('Authorization', 'Bearer '.$login)->getJson('/api/v1/health/profile')->assertOk();
        $this->withHeader('Authorization', 'Bearer '.$login)->getJson('/api/v1/health/vitals/latest')->assertOk();
        $this->withHeader('Authorization', 'Bearer '.$login)->getJson('/api/v1/medications/today')->assertOk();
        $this->withHeader('Authorization', 'Bearer '.$login)->getJson('/api/v1/care-plans')->assertOk();
        $this->withHeader('Authorization', 'Bearer '.$login)->getJson('/api/v1/notifications')->assertOk();
    }
}
