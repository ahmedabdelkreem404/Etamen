<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Domain\Enums\AppointmentSlotStatus;
use App\Modules\Appointments\Domain\Enums\ConsultationType;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\Appointments\Infrastructure\Models\AppointmentReview;
use App\Modules\Appointments\Infrastructure\Models\AppointmentSlot;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Locations\Infrastructure\Models\Area;
use App\Modules\Locations\Infrastructure\Models\City;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\Specialty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DoctorVisualContractSprint32Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_public_doctor_listing_exposes_safe_avatar_and_rating_summary(): void
    {
        $doctor = $this->createApprovedDoctor('legacy-doctorfinder/demo-doctor-avatar-1.png');
        $this->createReview($doctor, 5, true, 'VISUAL-1');
        $this->createReview($doctor, 3, false, 'VISUAL-2');

        $response = $this->getJson('/api/v1/doctors')
            ->assertOk()
            ->assertJsonPath('data.0.doctor_profile.reviews_count', 1)
            ->assertJsonPath('data.0.doctor_profile.rating_average', 5)
            ->assertJsonPath('data.0.primary_city_name', 'القاهرة');

        $profile = $response->json('data.0.doctor_profile');

        $this->assertArrayHasKey('avatar_url', $profile);
        $this->assertStringContainsString('/legacy-doctorfinder/demo-doctor-avatar-1.png', $profile['avatar_url']);
        $this->assertArrayNotHasKey('avatar_path', $profile);
        $this->assertStringNotContainsString('medical_private', $profile['avatar_url']);
        $this->assertStringNotContainsString('medical-private', $profile['avatar_url']);
    }

    public function test_private_or_unsafe_avatar_paths_are_not_exposed(): void
    {
        $this->createApprovedDoctor('medical_private/provider-documents/secret.png');

        $this->getJson('/api/v1/doctors')
            ->assertOk()
            ->assertJsonPath('data.0.doctor_profile.avatar_url', null);
    }

    public function test_doctor_registration_cannot_force_visual_or_rating_fields(): void
    {
        Specialty::query()->create([
            'name_ar' => 'باطنة',
            'name_en' => 'Internal Medicine',
            'slug' => 'internal-medicine',
            'is_active' => true,
        ]);

        $this->postJson('/api/v1/providers/register-doctor', [
            'name' => 'Doctor Visual Owner',
            'email' => 'visual-owner@example.test',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'provider_name_en' => 'Visual Clinic',
            'avatar_path' => 'legacy-doctorfinder/demo-doctor-avatar-1.png',
            'rating_average' => 5,
        ])->assertUnprocessable();
    }

    public function test_provider_profile_update_cannot_force_visual_or_rating_fields(): void
    {
        $doctor = $this->createApprovedDoctor(null);
        $owner = $doctor->provider->owner;
        Sanctum::actingAs($owner);

        $this->putJson('/api/v1/provider/profile', [
            'name_en' => 'Updated Visual Clinic',
            'profile' => [
                'avatar_path' => 'legacy-doctorfinder/demo-doctor-avatar-1.png',
                'rating_average' => 5,
            ],
        ])->assertUnprocessable();

        $doctor->refresh();
        $this->assertNull($doctor->avatar_path);
    }

    public function test_booking_request_contract_does_not_accept_visual_fields(): void
    {
        $doctor = $this->createApprovedDoctor('legacy-doctorfinder/demo-doctor-avatar-1.png');
        $slot = AppointmentSlot::query()->create([
            'doctor_profile_id' => $doctor->id,
            'provider_id' => $doctor->provider_id,
            'branch_id' => $doctor->provider->branches()->first()?->id,
            'starts_at' => now()->addDay()->setTime(10, 0),
            'ends_at' => now()->addDay()->setTime(10, 30),
            'status' => AppointmentSlotStatus::Available,
        ]);
        $patient = User::factory()->create();
        $patient->assignRole(UserRole::Patient->value);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/appointments', [
            'doctor_profile_id' => $doctor->id,
            'appointment_slot_id' => $slot->id,
            'consultation_type' => ConsultationType::Clinic->value,
            'avatar_url' => 'https://example.test/fake.png',
            'rating_average' => 5,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['avatar_url', 'rating_average']);
    }

    private function createApprovedDoctor(?string $avatarPath): DoctorProfile
    {
        $owner = User::factory()->create();
        $owner->assignRole(UserRole::Doctor->value);

        $provider = Provider::query()->create([
            'type' => ProviderType::Doctor,
            'owner_user_id' => $owner->id,
            'name_ar' => 'د. اختبار بصري',
            'name_en' => 'Dr Visual Test',
            'slug' => 'visual-test-'.strtolower((string) str()->random(8)),
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

        $city = City::query()->create([
            'name_ar' => 'القاهرة',
            'name_en' => 'Cairo',
            'slug' => 'cairo-'.strtolower((string) str()->random(8)),
            'is_active' => true,
        ]);
        $area = Area::query()->create([
            'city_id' => $city->id,
            'name_ar' => 'مدينة نصر',
            'name_en' => 'Nasr City',
            'slug' => 'nasr-'.strtolower((string) str()->random(8)),
            'is_active' => true,
        ]);

        $provider->branches()->create([
            'city_id' => $city->id,
            'area_id' => $area->id,
            'name_ar' => 'عيادة بصرية',
            'name_en' => 'Visual Clinic',
            'is_main' => true,
            'is_active' => true,
        ]);

        $specialty = Specialty::query()->create([
            'name_ar' => 'قلب',
            'name_en' => 'Cardiology',
            'slug' => 'cardiology-'.strtolower((string) str()->random(8)),
            'is_active' => true,
        ]);

        $doctor = DoctorProfile::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $owner->id,
            'title' => 'استشاري',
            'avatar_path' => $avatarPath,
            'consultation_fee' => 300,
            'years_of_experience' => 8,
        ]);
        $doctor->specialties()->attach($specialty->id);

        return $doctor->refresh()->load('provider.owner');
    }

    private function createReview(DoctorProfile $doctor, int $rating, bool $visible, string $suffix): void
    {
        $patient = User::factory()->create();
        $patient->assignRole(UserRole::Patient->value);

        $appointment = Appointment::query()->create([
            'appointment_number' => 'APT-'.$suffix,
            'patient_user_id' => $patient->id,
            'doctor_profile_id' => $doctor->id,
            'provider_id' => $doctor->provider_id,
            'branch_id' => $doctor->provider->branches()->first()?->id,
            'consultation_type' => ConsultationType::Clinic,
            'price' => 300,
            'currency' => 'EGP',
            'status' => AppointmentStatus::Completed,
            'booked_at' => now(),
            'confirmed_at' => now(),
            'accepted_at' => now(),
            'completed_at' => now(),
        ]);

        AppointmentReview::query()->create([
            'appointment_id' => $appointment->id,
            'patient_user_id' => $patient->id,
            'doctor_profile_id' => $doctor->id,
            'rating' => $rating,
            'comment' => 'Visible summary test.',
            'is_visible' => $visible,
        ]);
    }
}
