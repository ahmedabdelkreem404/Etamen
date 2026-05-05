<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\AuditLogs\Infrastructure\Models\AuditLog;
use App\Modules\Health\Domain\Enums\AllergySeverity;
use App\Modules\Health\Domain\Enums\BloodType;
use App\Modules\Health\Domain\Enums\Gender;
use App\Modules\Health\Domain\Enums\HealthGoalStatus;
use App\Modules\Health\Domain\Enums\HealthGoalType;
use App\Modules\Health\Domain\Enums\VitalFlag;
use App\Modules\Health\Domain\Enums\VitalType;
use App\Modules\Health\Infrastructure\Models\HealthProfile;
use App\Modules\Health\Infrastructure\Models\VitalRecord;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HealthProfileVitalsSprint7Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_patient_can_create_update_own_health_profile_and_cannot_force_patient_user_id(): void
    {
        $patient = $this->patientUser();
        Sanctum::actingAs($patient);

        $this->putJson('/api/v1/health/profile', [
            'patient_user_id' => 999,
            'height_cm' => 170,
        ])->assertUnprocessable();

        $this->putJson('/api/v1/health/profile', [
            'date_of_birth' => '1990-01-01',
            'gender' => Gender::Female->value,
            'height_cm' => 170,
            'weight_kg' => 70,
            'blood_type' => BloodType::OPositive->value,
            'emergency_contact_name' => 'Ahmed',
            'emergency_contact_phone' => '+201012345678',
        ])
            ->assertOk()
            ->assertJsonPath('data.patient_user_id', $patient->id)
            ->assertJsonPath('data.height_cm', '170.00');

        $this->assertDatabaseHas('audit_logs', ['action' => 'health_profile.updated']);
    }

    public function test_admin_can_view_health_profiles_and_read_creates_access_log(): void
    {
        $patient = $this->patientUser();
        $admin = $this->adminUser();
        $profile = HealthProfile::query()->create([
            'patient_user_id' => $patient->id,
            'height_cm' => 180,
        ]);

        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/admin/health/profiles')->assertOk()->assertJsonFragment(['patient_user_id' => $patient->id]);
        $this->getJson('/api/v1/admin/health/profiles/'.$profile->id)->assertOk();

        $this->assertDatabaseHas('health_access_logs', [
            'patient_user_id' => $patient->id,
            'actor_id' => $admin->id,
            'action' => 'admin.health_profile.viewed',
        ]);
    }

    public function test_chronic_disease_allergy_medication_surgery_and_goal_crud_are_owner_scoped(): void
    {
        $patient = $this->patientUser();
        $other = $this->patientUser('other-health-owner@example.com');

        Sanctum::actingAs($patient);
        $diseaseId = $this->postJson('/api/v1/health/chronic-diseases', [
            'name' => 'Hypertension',
            'diagnosed_at' => '2020-01-01',
        ])->assertCreated()->json('data.id');

        $allergyId = $this->postJson('/api/v1/health/allergies', [
            'allergen' => 'Penicillin',
            'severity' => AllergySeverity::Severe->value,
        ])->assertCreated()->json('data.id');

        $this->postJson('/api/v1/health/allergies', [
            'allergen' => 'Dust',
            'severity' => 'dangerous',
        ])->assertUnprocessable();

        $medicationId = $this->postJson('/api/v1/health/current-medications', [
            'medication_name' => 'Profile-only medicine',
            'dosage' => '10mg',
            'frequency_text' => 'once daily',
            'reminder_times' => ['08:00'],
        ])->assertUnprocessable();

        $medicationId = $this->postJson('/api/v1/health/current-medications', [
            'medication_name' => 'Profile-only medicine',
            'dosage' => '10mg',
            'frequency_text' => 'once daily',
        ])->assertCreated()->json('data.id');

        $surgeryId = $this->postJson('/api/v1/health/surgeries', [
            'surgery_name' => 'Appendectomy',
            'surgery_date' => '2015-01-01',
        ])->assertCreated()->json('data.id');

        $goalId = $this->postJson('/api/v1/health/goals', [
            'goal_type' => HealthGoalType::Fitness->value,
            'title' => 'Walk more',
            'status' => HealthGoalStatus::Active->value,
        ])->assertCreated()->json('data.id');

        $this->putJson('/api/v1/health/goals/'.$goalId, [
            'goal_type' => HealthGoalType::Fitness->value,
            'title' => 'Walk more',
            'status' => 'invalid',
        ])->assertUnprocessable();

        Sanctum::actingAs($other);
        $this->putJson('/api/v1/health/chronic-diseases/'.$diseaseId, ['name' => 'Nope'])->assertForbidden();
        $this->deleteJson('/api/v1/health/allergies/'.$allergyId)->assertForbidden();
        $this->putJson('/api/v1/health/current-medications/'.$medicationId, ['medication_name' => 'Nope'])->assertForbidden();
        $this->deleteJson('/api/v1/health/surgeries/'.$surgeryId)->assertForbidden();
        $this->deleteJson('/api/v1/health/goals/'.$goalId)->assertForbidden();

        $this->assertGreaterThanOrEqual(5, AuditLog::query()->where('action', 'like', 'health.%created')->count());
    }

    public function test_vital_records_calculate_flags_and_reject_forced_fields(): void
    {
        $patient = $this->patientUser();
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/health/vitals', [
            'vital_type' => VitalType::BloodPressure->value,
            'measured_at' => now()->toDateTimeString(),
            'value_decimal' => 180,
            'value_secondary_decimal' => 120,
            'flag' => VitalFlag::Normal->value,
        ])->assertUnprocessable();

        $this->postJson('/api/v1/health/vitals', [
            'vital_type' => VitalType::BloodPressure->value,
            'measured_at' => now()->toDateTimeString(),
            'value_decimal' => 180,
            'value_secondary_decimal' => 120,
            'notes' => 'After walking',
        ])
            ->assertCreated()
            ->assertJsonPath('data.unit', 'mmHg')
            ->assertJsonPath('data.flag', VitalFlag::VeryHigh->value)
            ->assertJsonPath('data.source', 'manual');

        $this->postJson('/api/v1/health/vitals', [
            'vital_type' => VitalType::BloodSugar->value,
            'measured_at' => now()->subHour()->toDateTimeString(),
            'value_decimal' => 130,
            'metadata' => ['context' => 'fasting'],
        ])
            ->assertCreated()
            ->assertJsonPath('data.flag', VitalFlag::High->value);

        $this->postJson('/api/v1/health/vitals', [
            'vital_type' => VitalType::OxygenSaturation->value,
            'measured_at' => now()->subHours(2)->toDateTimeString(),
            'value_decimal' => 89,
        ])
            ->assertCreated()
            ->assertJsonPath('data.flag', VitalFlag::VeryLow->value);

        $this->postJson('/api/v1/health/vitals', [
            'vital_type' => 'diagnosis',
            'measured_at' => now()->toDateTimeString(),
            'value_decimal' => 1,
        ])->assertUnprocessable();

        $this->postJson('/api/v1/health/vitals', [
            'vital_type' => VitalType::HeartRate->value,
            'measured_at' => now()->addDays(2)->toDateTimeString(),
            'value_decimal' => 80,
        ])->assertUnprocessable();
    }

    public function test_vital_records_are_owner_scoped_for_view_update_and_delete(): void
    {
        $patient = $this->patientUser();
        $other = $this->patientUser('other-vitals@example.com');
        $vital = VitalRecord::query()->create([
            'patient_user_id' => $patient->id,
            'vital_type' => VitalType::HeartRate,
            'measured_at' => now(),
            'value_decimal' => 80,
            'unit' => 'bpm',
            'source' => 'manual',
            'flag' => VitalFlag::Normal,
        ]);

        Sanctum::actingAs($other);
        $this->getJson('/api/v1/health/vitals/'.$vital->id)->assertForbidden();
        $this->putJson('/api/v1/health/vitals/'.$vital->id, [
            'vital_type' => VitalType::HeartRate->value,
            'measured_at' => now()->toDateTimeString(),
            'value_decimal' => 90,
        ])->assertForbidden();
        $this->deleteJson('/api/v1/health/vitals/'.$vital->id)->assertForbidden();
    }

    public function test_weight_bmi_latest_trends_and_summary_are_patient_scoped_and_bounded(): void
    {
        $patient = $this->patientUser();
        $other = $this->patientUser('trend-other@example.com');
        HealthProfile::query()->create(['patient_user_id' => $patient->id, 'height_cm' => 180]);
        HealthProfile::query()->create(['patient_user_id' => $other->id, 'height_cm' => 150]);

        VitalRecord::query()->create([
            'patient_user_id' => $other->id,
            'vital_type' => VitalType::Weight,
            'measured_at' => now(),
            'value_decimal' => 200,
            'unit' => 'kg',
            'source' => 'manual',
            'flag' => VitalFlag::Unknown,
        ]);

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/health/vitals', [
            'vital_type' => VitalType::Weight->value,
            'measured_at' => now()->subDays(2)->toDateTimeString(),
            'value_decimal' => 81,
        ])->assertCreated();
        $this->postJson('/api/v1/health/vitals', [
            'vital_type' => VitalType::Weight->value,
            'measured_at' => now()->subDay()->toDateTimeString(),
            'value_decimal' => 80,
        ])->assertCreated();

        $this->getJson('/api/v1/health/vitals/latest')
            ->assertOk()
            ->assertJsonPath('data.0.value_decimal', '80.00');

        $this->getJson('/api/v1/health/vitals/trends?vital_type=weight&from='.now()->subYears(2)->toDateString().'&to='.now()->toDateString().'&group_by=month')
            ->assertOk()
            ->assertJsonPath('data.vital_type', VitalType::Weight->value)
            ->assertJsonMissing(['value_decimal' => '200.00']);

        $this->getJson('/api/v1/health/summary')
            ->assertOk()
            ->assertJsonPath('data.bmi.value', 24.69)
            ->assertJsonPath('data.bmi.category', 'normal')
            ->assertJsonPath('data.active_chronic_diseases_count', 0);
    }

    public function test_sleep_mood_and_symptom_basic_records_are_supported(): void
    {
        $patient = $this->patientUser();
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/health/vitals', [
            'vital_type' => VitalType::Sleep->value,
            'measured_at' => now()->subDay()->toDateTimeString(),
            'value_decimal' => 7.5,
            'metadata' => ['quality' => 'good'],
        ])->assertCreated()->assertJsonPath('data.flag', VitalFlag::Unknown->value);

        $this->postJson('/api/v1/health/vitals', [
            'vital_type' => VitalType::Mood->value,
            'measured_at' => now()->subHours(3)->toDateTimeString(),
            'metadata' => ['mood' => 'neutral'],
        ])->assertCreated();

        $this->postJson('/api/v1/health/vitals', [
            'vital_type' => VitalType::Symptom->value,
            'measured_at' => now()->subHours(2)->toDateTimeString(),
        ])->assertUnprocessable();

        $this->postJson('/api/v1/health/vitals', [
            'vital_type' => VitalType::Symptom->value,
            'measured_at' => now()->subHours(2)->toDateTimeString(),
            'notes' => 'Headache',
        ])->assertCreated();
    }

    public function test_security_boundaries_for_health_endpoints(): void
    {
        $provider = User::factory()->create(['email' => 'provider-'.Str::random(6).'@example.com']);
        $provider->assignRole(UserRole::Doctor->value);
        $patient = $this->patientUser();

        $this->getJson('/api/v1/health/profile')->assertUnauthorized();

        Sanctum::actingAs($provider);
        $this->getJson('/api/v1/health/profile')->assertForbidden();

        Sanctum::actingAs($patient);
        $this->getJson('/api/v1/admin/health/profiles')->assertForbidden();
        $this->postJson('/api/v1/admin/health/profiles')->assertMethodNotAllowed();
    }

    public function test_admin_can_read_vitals_and_access_logs_but_patient_cannot_access_admin_health_apis(): void
    {
        $patient = $this->patientUser();
        $admin = $this->adminUser();
        $vital = VitalRecord::query()->create([
            'patient_user_id' => $patient->id,
            'vital_type' => VitalType::Temperature,
            'measured_at' => now(),
            'value_decimal' => 38.5,
            'unit' => 'C',
            'source' => 'manual',
            'flag' => VitalFlag::High,
        ]);

        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/admin/health/vitals')->assertOk()->assertJsonFragment(['patient_user_id' => $patient->id]);
        $this->getJson('/api/v1/admin/health/vitals/'.$vital->id)->assertOk();
        $this->getJson('/api/v1/admin/health/access-logs')->assertOk();

        $this->assertDatabaseHas('health_access_logs', [
            'patient_user_id' => $patient->id,
            'actor_id' => $admin->id,
            'action' => 'admin.vital_record.viewed',
        ]);
    }

    public function test_responses_do_not_include_unsafe_medical_advice_keywords(): void
    {
        $patient = $this->patientUser();
        Sanctum::actingAs($patient);

        $content = $this->postJson('/api/v1/health/vitals', [
            'vital_type' => VitalType::BloodPressure->value,
            'measured_at' => now()->toDateTimeString(),
            'value_decimal' => 150,
            'value_secondary_decimal' => 95,
        ])->assertCreated()->getContent();

        $this->assertStringNotContainsString('prescribe', strtolower($content));
        $this->assertStringNotContainsString('change medication', strtolower($content));
        $this->assertStringNotContainsString('stop medication', strtolower($content));
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
}
