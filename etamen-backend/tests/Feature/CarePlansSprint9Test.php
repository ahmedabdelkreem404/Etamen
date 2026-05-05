<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Domain\Enums\ConsultationType;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\CarePlans\Domain\Enums\CarePlanFoodCategory;
use App\Modules\CarePlans\Domain\Enums\CarePlanInstructionType;
use App\Modules\CarePlans\Domain\Enums\CarePlanMealType;
use App\Modules\CarePlans\Domain\Enums\CarePlanSource;
use App\Modules\CarePlans\Domain\Enums\CarePlanStatus;
use App\Modules\CarePlans\Domain\Enums\CarePlanType;
use App\Modules\CarePlans\Domain\Enums\CarePlanVisibility;
use App\Modules\CarePlans\Domain\Enums\MealLogStatus;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanDay;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanMeal;
use App\Modules\CarePlans\Infrastructure\Models\MealLog;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\MedicalFiles\Infrastructure\Models\UploadedFile as UploadedFileModel;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CarePlansSprint9Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_patient_can_create_update_own_plan_and_cannot_force_sensitive_fields(): void
    {
        $patient = $this->patientUser();
        $other = $this->patientUser('care-other@example.com');
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/care-plans', [
            ...$this->planPayload(),
            'patient_user_id' => $other->id,
        ])->assertUnprocessable();

        $this->postJson('/api/v1/care-plans', [
            ...$this->planPayload(),
            'source' => CarePlanSource::ProviderAssigned->value,
            'status' => CarePlanStatus::Active->value,
            'provider_id' => 123,
        ])->assertUnprocessable();

        $id = $this->postJson('/api/v1/care-plans', $this->planPayload())
            ->assertCreated()
            ->assertJsonPath('data.patient_user_id', $patient->id)
            ->assertJsonPath('data.source', CarePlanSource::PatientCreated->value)
            ->assertJsonPath('data.status', CarePlanStatus::Draft->value)
            ->assertJsonPath('data.safety_disclaimer', CarePlan::SAFETY_DISCLAIMER)
            ->json('data.id');

        $this->putJson('/api/v1/care-plans/'.$id, [
            'title' => 'Updated follow-up plan',
        ])->assertOk()->assertJsonPath('data.title', 'Updated follow-up plan');

        Sanctum::actingAs($other);
        $this->putJson('/api/v1/care-plans/'.$id, ['title' => 'Nope'])->assertForbidden();

        $this->assertDatabaseHas('audit_logs', ['action' => 'care_plan.created']);
    }

    public function test_status_transitions_are_controlled_and_completed_plan_rejects_tracking(): void
    {
        $patient = $this->patientUser();
        $plan = $this->patientPlan($patient);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/care-plans/'.$plan->id.'/complete')->assertUnprocessable();
        $this->postJson('/api/v1/care-plans/'.$plan->id.'/activate')->assertOk()->assertJsonPath('data.status', 'active');
        $this->postJson('/api/v1/care-plans/'.$plan->id.'/pause')->assertOk()->assertJsonPath('data.status', 'paused');
        $this->postJson('/api/v1/care-plans/'.$plan->id.'/resume')->assertOk()->assertJsonPath('data.status', 'active');
        $this->postJson('/api/v1/care-plans/'.$plan->id.'/complete')->assertOk()->assertJsonPath('data.status', 'completed');

        $this->postJson('/api/v1/care-plans/'.$plan->id.'/checkins', [
            'checkin_date' => '2026-05-05',
            'commitment_score' => 80,
        ])->assertUnprocessable();

        $this->postJson('/api/v1/care-plans/'.$plan->id.'/meal-logs', [
            'logged_at' => '2026-05-05 12:00:00',
            'status' => MealLogStatus::Followed->value,
        ])->assertUnprocessable();

        $this->assertDatabaseHas('audit_logs', ['action' => 'care_plan.status_changed']);
    }

    public function test_provider_can_assign_plan_only_to_related_patient_and_cannot_access_other_provider_plan(): void
    {
        $patient = $this->patientUser();
        $unrelated = $this->patientUser('unrelated-care@example.com');
        [$doctorUser, $provider, $doctorProfile] = $this->doctorProvider();
        [$otherDoctorUser] = $this->doctorProvider('other-doctor-care@example.com');
        $this->appointment($patient, $provider, $doctorProfile, AppointmentStatus::Confirmed);

        Sanctum::actingAs($doctorUser);
        $planId = $this->postJson('/api/v1/provider/care-plans/assign', [
            ...$this->planPayload(),
            'patient_user_id' => $patient->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.source', CarePlanSource::ProviderAssigned->value)
            ->assertJsonPath('data.provider_id', $provider->id)
            ->json('data.id');

        $this->postJson('/api/v1/provider/care-plans/assign', [
            ...$this->planPayload(['title' => 'Bad assignment']),
            'patient_user_id' => $unrelated->id,
        ])->assertUnprocessable();

        Sanctum::actingAs($otherDoctorUser);
        $this->getJson('/api/v1/provider/care-plans/'.$planId)->assertForbidden();

        $this->assertDatabaseHas('audit_logs', ['action' => 'care_plan.provider_assigned']);
    }

    public function test_patient_cannot_edit_provider_assigned_plan_structure_but_can_track_it_when_active(): void
    {
        $patient = $this->patientUser();
        [$doctorUser, $provider, $doctorProfile] = $this->doctorProvider();
        $this->appointment($patient, $provider, $doctorProfile, AppointmentStatus::Accepted);
        Sanctum::actingAs($doctorUser);
        $planId = $this->postJson('/api/v1/provider/care-plans/assign', [
            ...$this->planPayload(),
            'patient_user_id' => $patient->id,
        ])->assertCreated()->json('data.id');
        $this->postJson('/api/v1/provider/care-plans/'.$planId.'/activate')->assertOk();

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/care-plans/'.$planId.'/days', [
            'day_number' => 1,
        ])->assertForbidden();

        $this->postJson('/api/v1/care-plans/'.$planId.'/checkins', [
            'checkin_date' => '2026-05-05',
            'commitment_score' => 75,
        ])->assertCreated()->assertJsonPath('data.commitment_score', 75);
    }

    public function test_patient_plan_structure_food_and_instruction_validation(): void
    {
        $patient = $this->patientUser();
        $plan = $this->patientPlan($patient);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/care-plans/'.$plan->id.'/days', ['title' => 'No day marker'])->assertUnprocessable();
        $dayId = $this->postJson('/api/v1/care-plans/'.$plan->id.'/days', [
            'day_number' => 1,
            'title' => 'Day one',
        ])->assertCreated()->json('data.id');

        $mealId = $this->postJson('/api/v1/care-plans/'.$plan->id.'/meals', [
            'care_plan_day_id' => $dayId,
            'meal_type' => CarePlanMealType::Breakfast->value,
            'title' => 'Breakfast',
            'calories' => 400,
            'is_required' => true,
        ])->assertCreated()->json('data.id');

        $this->postJson('/api/v1/care-plans/'.$plan->id.'/foods', [
            'category' => 'medical_ban',
            'name' => 'Unsafe',
        ])->assertUnprocessable();

        $this->postJson('/api/v1/care-plans/'.$plan->id.'/foods', [
            'category' => CarePlanFoodCategory::Limited->value,
            'name' => 'Dessert',
        ])->assertCreated()->assertJsonPath('data.safety_note', 'هذه الأطعمة مرتبطة بالخطة فقط وليست منعًا طبيًا عامًا.');

        $this->postJson('/api/v1/care-plans/'.$plan->id.'/instructions', [
            'instruction_type' => CarePlanInstructionType::General->value,
            'body' => 'General wellness note.',
        ])->assertCreated();

        $this->deleteJson('/api/v1/care-plans/'.$plan->id.'/meals/'.$mealId)->assertOk();
    }

    public function test_checkins_are_idempotent_date_bounded_and_owner_scoped(): void
    {
        $patient = $this->patientUser();
        $other = $this->patientUser('checkin-other@example.com');
        $plan = $this->patientPlan($patient, ['start_date' => '2026-05-01', 'end_date' => '2026-05-10', 'status' => CarePlanStatus::Active]);

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/care-plans/'.$plan->id.'/checkins', [
            'checkin_date' => '2026-05-05',
            'commitment_score' => 80,
            'mood' => 'good',
        ])->assertCreated()->assertJsonPath('data.commitment_score', 80);

        $this->postJson('/api/v1/care-plans/'.$plan->id.'/checkins', [
            'checkin_date' => '2026-05-05',
            'commitment_score' => 90,
        ])->assertCreated()->assertJsonPath('data.commitment_score', 90);

        $this->assertDatabaseCount('care_plan_checkins', 1);

        $this->postJson('/api/v1/care-plans/'.$plan->id.'/checkins', [
            'checkin_date' => '2026-05-11',
        ])->assertUnprocessable();

        Sanctum::actingAs($other);
        $this->postJson('/api/v1/care-plans/'.$plan->id.'/checkins', [
            'checkin_date' => '2026-05-05',
        ])->assertForbidden();
    }

    public function test_meal_logs_are_private_bounded_and_do_not_expose_raw_photo_path(): void
    {
        Storage::fake('medical_private');
        $patient = $this->patientUser();
        $other = $this->patientUser('meal-other@example.com');
        $plan = $this->patientPlan($patient, ['status' => CarePlanStatus::Active, 'start_date' => '2026-05-01', 'end_date' => '2026-05-10']);
        $day = CarePlanDay::query()->create(['care_plan_id' => $plan->id, 'day_number' => 1]);
        $meal = CarePlanMeal::query()->create([
            'care_plan_day_id' => $day->id,
            'meal_type' => CarePlanMealType::Lunch,
            'title' => 'Lunch',
            'is_required' => true,
        ]);
        $otherPlan = $this->patientPlan($other, ['status' => CarePlanStatus::Active]);
        $otherDay = CarePlanDay::query()->create(['care_plan_id' => $otherPlan->id, 'day_number' => 1]);
        $otherMeal = CarePlanMeal::query()->create(['care_plan_day_id' => $otherDay->id, 'meal_type' => CarePlanMealType::Dinner]);

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/care-plans/'.$plan->id.'/meal-logs', [
            'care_plan_meal_id' => $otherMeal->id,
            'logged_at' => '2026-05-05 13:00:00',
            'status' => MealLogStatus::Followed->value,
        ])->assertUnprocessable();

        $response = $this->post('/api/v1/care-plans/'.$plan->id.'/meal-logs', [
            'care_plan_meal_id' => $meal->id,
            'logged_at' => '2026-05-05 13:00:00',
            'status' => MealLogStatus::Followed->value,
            'photo' => UploadedFile::fake()->image('meal.jpg'),
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.photo.visibility', 'private')
            ->assertJsonMissingPath('data.photo.path')
            ->assertJsonMissingPath('data.photo.url');

        $file = UploadedFileModel::query()->firstOrFail();
        $this->assertSame('meal_photo', $file->file_category->value);
        $this->assertSame('medical_private', $file->disk);

        $this->postJson('/api/v1/care-plans/'.$plan->id.'/meal-logs', [
            'logged_at' => '2026-05-11 13:00:00',
            'status' => MealLogStatus::Skipped->value,
        ])->assertUnprocessable();

        Sanctum::actingAs($other);
        $this->getJson('/api/v1/care-plans/'.$plan->id.'/meal-logs/'.MealLog::query()->firstOrFail()->id)->assertForbidden();
    }

    public function test_progress_summary_is_patient_scoped_and_safe(): void
    {
        $patient = $this->patientUser();
        $other = $this->patientUser('progress-other@example.com');
        $plan = $this->patientPlan($patient, ['status' => CarePlanStatus::Active, 'start_date' => '2026-05-01', 'end_date' => '2026-05-10']);
        $day = CarePlanDay::query()->create(['care_plan_id' => $plan->id, 'day_number' => 1]);
        $meal = CarePlanMeal::query()->create(['care_plan_day_id' => $day->id, 'meal_type' => CarePlanMealType::Lunch, 'is_required' => true]);

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/care-plans/'.$plan->id.'/checkins', [
            'checkin_date' => '2026-05-05',
            'commitment_score' => 80,
        ])->assertCreated();
        $this->postJson('/api/v1/care-plans/'.$plan->id.'/meal-logs', [
            'care_plan_meal_id' => $meal->id,
            'logged_at' => '2026-05-05 13:00:00',
            'status' => MealLogStatus::PartiallyFollowed->value,
        ])->assertCreated();

        $content = $this->getJson('/api/v1/care-plans/'.$plan->id.'/progress?from=2026-05-01&to=2026-05-10')
            ->assertOk()
            ->assertJsonPath('data.average_commitment_score', 80)
            ->assertJsonPath('data.partially_followed_count', 1)
            ->assertJsonPath('data.adherence_percentage', 50)
            ->getContent();

        $this->assertStringNotContainsString('diagnosis', strtolower($content));
        $this->assertStringNotContainsString('treatment success', strtolower($content));
        $this->assertStringNotContainsString('medication advice', strtolower($content));

        $this->getJson('/api/v1/care-plans/summary')
            ->assertOk()
            ->assertJsonPath('data.total_plans', 1)
            ->assertJsonPath('data.active_plans', 1);

        Sanctum::actingAs($other);
        $this->getJson('/api/v1/care-plans/'.$plan->id.'/progress')->assertForbidden();
    }

    public function test_admin_can_manage_and_non_admin_cannot_access_admin_routes(): void
    {
        $patient = $this->patientUser();
        $admin = $this->adminUser();
        Sanctum::actingAs($admin);

        $planId = $this->postJson('/api/v1/admin/care-plans', [
            ...$this->planPayload(['title' => 'Admin plan']),
            'patient_user_id' => $patient->id,
        ])->assertCreated()->assertJsonPath('data.source', CarePlanSource::AdminCreated->value)->json('data.id');

        $this->putJson('/api/v1/admin/care-plans/'.$planId, ['title' => 'Admin updated'])->assertOk();
        $this->postJson('/api/v1/admin/care-plans/'.$planId.'/activate')->assertOk();
        $this->getJson('/api/v1/admin/care-plans')->assertOk()->assertJsonFragment(['title' => 'Admin updated']);
        $this->getJson('/api/v1/admin/care-plan-checkins')->assertOk();
        $this->getJson('/api/v1/admin/meal-logs')->assertOk();

        Sanctum::actingAs($patient);
        $this->getJson('/api/v1/admin/care-plans')->assertForbidden();
        $this->getJson('/api/v1/care-plans')->assertOk();

        $this->getJson('/api/v1/care-plans-public')->assertNotFound();
    }

    private function planPayload(array $overrides = []): array
    {
        return [
            'plan_type' => CarePlanType::Nutrition->value,
            'title' => 'Nutrition follow-up',
            'description' => 'General organization plan.',
            'goal_text' => 'Improve commitment tracking.',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-31',
            ...$overrides,
        ];
    }

    private function patientPlan(User $patient, array $overrides = []): CarePlan
    {
        return CarePlan::query()->create([
            'patient_user_id' => $patient->id,
            'plan_type' => $overrides['plan_type'] ?? CarePlanType::Nutrition,
            'title' => $overrides['title'] ?? 'Patient plan',
            'start_date' => $overrides['start_date'] ?? '2026-05-01',
            'end_date' => $overrides['end_date'] ?? '2026-05-31',
            'status' => $overrides['status'] ?? CarePlanStatus::Draft,
            'visibility' => CarePlanVisibility::PatientOnly,
            'source' => CarePlanSource::PatientCreated,
            'safety_disclaimer' => CarePlan::SAFETY_DISCLAIMER,
        ]);
    }

    private function patientUser(string $email = 'care-patient@example.com'): User
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

    private function doctorProvider(string $email = 'care-doctor@example.com'): array
    {
        $user = User::factory()->create(['email' => $email]);
        $user->assignRole(UserRole::Doctor->value);
        $provider = Provider::query()->create([
            'type' => ProviderType::Doctor,
            'owner_user_id' => $user->id,
            'name_en' => 'Care Doctor '.Str::random(5),
            'status' => ProviderStatus::Approved,
            'is_active' => true,
            'approved_at' => now(),
        ]);
        $provider->staff()->create([
            'user_id' => $user->id,
            'role' => ProviderStaffRole::Owner,
            'is_owner' => true,
            'status' => 'active',
        ]);
        $doctorProfile = DoctorProfile::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $user->id,
        ]);

        return [$user, $provider->refresh(), $doctorProfile];
    }

    private function appointment(User $patient, Provider $provider, DoctorProfile $doctorProfile, AppointmentStatus $status): Appointment
    {
        return Appointment::query()->create([
            'appointment_number' => 'APT-CARE-'.Str::upper(Str::random(8)),
            'patient_user_id' => $patient->id,
            'doctor_profile_id' => $doctorProfile->id,
            'provider_id' => $provider->id,
            'consultation_type' => ConsultationType::Clinic,
            'price' => 0,
            'currency' => 'EGP',
            'status' => $status,
            'booked_at' => now(),
        ]);
    }
}
