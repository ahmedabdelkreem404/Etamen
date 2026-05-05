<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\AuditLogs\Infrastructure\Models\AuditLog;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Medications\Application\Jobs\MarkMissedMedicationDosesJob;
use App\Modules\Medications\Application\Jobs\QueueMedicationReminderNotificationsJob;
use App\Modules\Medications\Application\Services\MedicationLogService;
use App\Modules\Medications\Application\Services\MedicationNotificationService;
use App\Modules\Medications\Application\Services\MedicationScheduleService;
use App\Modules\Medications\Domain\Enums\MedicationFrequencyType;
use App\Modules\Medications\Domain\Enums\MedicationLogAction;
use App\Modules\Medications\Domain\Enums\MedicationReminderStatus;
use App\Modules\Medications\Infrastructure\Models\MedicationLog;
use App\Modules\Medications\Infrastructure\Models\MedicationNotificationQueue;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MedicationRemindersSprint8Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        Carbon::setTestNow(Carbon::parse('2026-05-05 10:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_patient_can_create_update_status_and_cannot_force_ownership_source_or_status(): void
    {
        $patient = $this->patientUser();
        $other = $this->patientUser('other-med-owner@example.com');
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/medications/reminders', [
            'patient_user_id' => $other->id,
            'medication_name' => 'Unsafe override',
            'frequency_type' => MedicationFrequencyType::OnceDaily->value,
            'start_date' => now()->toDateString(),
            'times' => [['time_of_day' => '08:00']],
        ])->assertUnprocessable();

        $this->postJson('/api/v1/medications/reminders', [
            'source' => 'provider_entered',
            'status' => MedicationReminderStatus::Cancelled->value,
            'medication_name' => 'Unsafe source',
            'frequency_type' => MedicationFrequencyType::OnceDaily->value,
            'start_date' => now()->toDateString(),
            'times' => [['time_of_day' => '08:00']],
        ])->assertUnprocessable();

        $id = $this->postJson('/api/v1/medications/reminders', $this->reminderPayload())
            ->assertCreated()
            ->assertJsonPath('data.patient_user_id', $patient->id)
            ->assertJsonPath('data.source', 'patient_entered')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.times.0.time_of_day', '08:00')
            ->json('data.id');

        $this->putJson('/api/v1/medications/reminders/'.$id, [
            'medication_name' => 'Updated medicine',
            'notes' => 'Updated safely',
        ])->assertOk()->assertJsonPath('data.medication_name', 'Updated medicine');

        $this->postJson('/api/v1/medications/reminders/'.$id.'/pause')->assertOk()->assertJsonPath('data.status', 'paused');
        $this->postJson('/api/v1/medications/reminders/'.$id.'/resume')->assertOk()->assertJsonPath('data.status', 'active');
        $this->postJson('/api/v1/medications/reminders/'.$id.'/cancel')->assertOk()->assertJsonPath('data.status', 'cancelled');

        Sanctum::actingAs($other);
        $this->putJson('/api/v1/medications/reminders/'.$id, ['medication_name' => 'Nope'])->assertForbidden();

        $this->assertDatabaseHas('audit_logs', ['action' => 'medication_reminder.created']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'medication_reminder.cancelled']);
    }

    public function test_frequency_validation_rules_are_enforced(): void
    {
        $patient = $this->patientUser();
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/medications/reminders', $this->reminderPayload([
            'frequency_type' => MedicationFrequencyType::OnceDaily->value,
            'times' => [],
        ]))->assertUnprocessable();

        $this->postJson('/api/v1/medications/reminders', $this->reminderPayload([
            'frequency_type' => MedicationFrequencyType::TwiceDaily->value,
            'times' => [['time_of_day' => '08:00']],
        ]))->assertUnprocessable();

        $this->postJson('/api/v1/medications/reminders', $this->reminderPayload([
            'frequency_type' => MedicationFrequencyType::ThreeTimesDaily->value,
            'times' => [['time_of_day' => '08:00'], ['time_of_day' => '14:00']],
        ]))->assertUnprocessable();

        $this->postJson('/api/v1/medications/reminders', $this->reminderPayload([
            'frequency_type' => MedicationFrequencyType::CustomTimes->value,
            'times' => collect(range(1, 11))->map(fn (int $hour) => ['time_of_day' => sprintf('%02d:00', $hour)])->all(),
        ]))->assertUnprocessable();

        $this->postJson('/api/v1/medications/reminders', $this->reminderPayload([
            'frequency_type' => MedicationFrequencyType::EveryXHours->value,
            'times' => [['time_of_day' => '06:00']],
            'interval_hours' => null,
        ]))->assertUnprocessable();

        $this->postJson('/api/v1/medications/reminders', $this->reminderPayload([
            'frequency_type' => MedicationFrequencyType::SpecificDays->value,
            'times' => [['time_of_day' => '08:00']],
            'metadata' => [],
        ]))->assertUnprocessable();

        $this->postJson('/api/v1/medications/reminders', $this->reminderPayload([
            'frequency_type' => MedicationFrequencyType::AsNeeded->value,
            'times' => [],
        ]))->assertCreated();
    }

    public function test_schedule_today_upcoming_and_as_needed_behavior_are_patient_scoped(): void
    {
        $patient = $this->patientUser();
        $other = $this->patientUser('schedule-other@example.com');
        Sanctum::actingAs($patient);

        $scheduledId = $this->postJson('/api/v1/medications/reminders', $this->reminderPayload([
            'times' => [['time_of_day' => '11:00']],
        ]))->assertCreated()->json('data.id');

        $this->postJson('/api/v1/medications/reminders', $this->reminderPayload([
            'frequency_type' => MedicationFrequencyType::AsNeeded->value,
            'medication_name' => 'As needed only',
            'times' => [],
        ]))->assertCreated();

        Sanctum::actingAs($other);
        $this->getJson('/api/v1/medications/reminders/'.$scheduledId.'/schedule')->assertForbidden();

        Sanctum::actingAs($patient);
        $this->getJson('/api/v1/medications/today')
            ->assertOk()
            ->assertJsonPath('data.0.medication_name', 'Test medicine');

        $this->getJson('/api/v1/medications/upcoming?days=90')
            ->assertOk()
            ->assertJsonMissing(['medication_name' => 'As needed only']);

        $this->getJson('/api/v1/medications/reminders/'.$scheduledId.'/schedule?from=2026-05-05&to=2026-05-07')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_reminder_times_can_be_added_updated_and_deactivated_by_owner_only(): void
    {
        $patient = $this->patientUser();
        $other = $this->patientUser('time-other@example.com');
        $reminder = $this->createReminder($patient);

        Sanctum::actingAs($patient);
        $timeId = $this->postJson('/api/v1/medications/reminders/'.$reminder->id.'/times', [
            'time_of_day' => '20:30',
            'label' => 'Evening',
        ])->assertCreated()->assertJsonPath('data.time_of_day', '20:30')->json('data.id');

        $this->putJson('/api/v1/medications/reminders/'.$reminder->id.'/times/'.$timeId, [
            'time_of_day' => '21:00',
            'label' => 'Night',
        ])->assertOk()->assertJsonPath('data.label', 'Night');

        $this->deleteJson('/api/v1/medications/reminders/'.$reminder->id.'/times/'.$timeId)
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        Sanctum::actingAs($other);
        $this->postJson('/api/v1/medications/reminders/'.$reminder->id.'/times', [
            'time_of_day' => '22:00',
        ])->assertForbidden();
    }

    public function test_logs_are_owner_scoped_idempotent_and_validate_active_dates(): void
    {
        $patient = $this->patientUser();
        $other = $this->patientUser('log-other@example.com');
        $reminder = $this->createReminder($patient, ['start_date' => '2026-05-05', 'end_date' => '2026-05-06']);

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/medications/reminders/'.$reminder->id.'/taken', [
            'scheduled_for' => '2026-05-05 08:00:00',
        ])
            ->assertOk()
            ->assertJsonPath('data.action', 'taken')
            ->assertJsonStructure(['data' => ['taken_at']]);

        $this->postJson('/api/v1/medications/reminders/'.$reminder->id.'/taken', [
            'scheduled_for' => '2026-05-05 08:00:00',
            'notes' => 'Duplicate updates same log',
        ])->assertOk()->assertJsonPath('data.notes', 'Duplicate updates same log');
        $this->assertSame(1, MedicationLog::query()->where('medication_reminder_id', $reminder->id)->count());

        $this->postJson('/api/v1/medications/reminders/'.$reminder->id.'/logs', [
            'action' => MedicationLogAction::Skipped->value,
            'scheduled_for' => '2026-05-06 08:00:00',
            'notes' => 'Skipped safely',
        ])->assertCreated()->assertJsonPath('data.action', 'skipped');

        $this->postJson('/api/v1/medications/reminders/'.$reminder->id.'/taken', [
            'scheduled_for' => '2026-05-04 08:00:00',
        ])->assertUnprocessable();

        $this->postJson('/api/v1/medications/reminders/'.$reminder->id.'/taken', [
            'scheduled_for' => '2026-05-07 08:00:00',
        ])->assertUnprocessable();

        $this->postJson('/api/v1/medications/reminders/'.$reminder->id.'/cancel')->assertOk();
        $this->postJson('/api/v1/medications/reminders/'.$reminder->id.'/taken', [
            'scheduled_for' => '2026-05-05 09:00:00',
        ])->assertUnprocessable();

        Sanctum::actingAs($other);
        $this->postJson('/api/v1/medications/reminders/'.$reminder->id.'/skipped', [
            'scheduled_for' => '2026-05-05 10:00:00',
        ])->assertForbidden();
    }

    public function test_missed_job_is_idempotent_and_existing_logs_prevent_missed_logs(): void
    {
        $patient = $this->patientUser();
        $reminder = $this->createReminder($patient, [
            'start_date' => '2026-05-04',
            'times' => [['time_of_day' => '07:00']],
        ]);
        MedicationLog::query()->create([
            'medication_reminder_id' => $reminder->id,
            'patient_user_id' => $patient->id,
            'scheduled_for' => '2026-05-05 07:00:00',
            'action' => MedicationLogAction::Taken,
            'taken_at' => '2026-05-05 07:10:00',
        ]);

        $job = new MarkMissedMedicationDosesJob(2);
        $job->handle(app(MedicationScheduleService::class), app(MedicationLogService::class));
        $job->handle(app(MedicationScheduleService::class), app(MedicationLogService::class));

        $this->assertDatabaseHas('medication_logs', [
            'medication_reminder_id' => $reminder->id,
            'scheduled_for' => '2026-05-04 07:00:00',
            'action' => MedicationLogAction::Missed->value,
        ]);
        $this->assertSame(1, MedicationLog::query()->where('action', MedicationLogAction::Missed->value)->count());
    }

    public function test_adherence_summary_counts_scheduled_taken_skipped_and_missed_without_medical_advice(): void
    {
        $patient = $this->patientUser();
        $reminder = $this->createReminder($patient, [
            'start_date' => '2026-05-03',
            'end_date' => '2026-05-05',
            'times' => [['time_of_day' => '08:00']],
        ]);
        MedicationLog::query()->create([
            'medication_reminder_id' => $reminder->id,
            'patient_user_id' => $patient->id,
            'scheduled_for' => '2026-05-03 08:00:00',
            'action' => MedicationLogAction::Taken,
            'taken_at' => '2026-05-03 08:01:00',
        ]);
        MedicationLog::query()->create([
            'medication_reminder_id' => $reminder->id,
            'patient_user_id' => $patient->id,
            'scheduled_for' => '2026-05-04 08:00:00',
            'action' => MedicationLogAction::Skipped,
        ]);

        Sanctum::actingAs($patient);
        $response = $this->getJson('/api/v1/medications/adherence?from=2026-05-03&to=2026-05-05')
            ->assertOk()
            ->assertJsonPath('data.total_scheduled', 3)
            ->assertJsonPath('data.taken_count', 1)
            ->assertJsonPath('data.skipped_count', 1)
            ->assertJsonPath('data.missed_count', 1)
            ->assertJsonPath('data.adherence_percentage', 33.33)
            ->getContent();

        $this->assertStringNotContainsString('diagnosis', strtolower($response));
        $this->assertStringNotContainsString('prescribe', strtolower($response));
    }

    public function test_refill_events_are_owner_scoped(): void
    {
        $patient = $this->patientUser();
        $other = $this->patientUser('refill-other@example.com');
        $reminder = $this->createReminder($patient, ['refill_enabled' => true, 'refill_reminder_date' => '2026-05-10']);

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/medications/reminders/'.$reminder->id.'/refill-done', [
            'event_date' => '2026-05-05',
        ])->assertCreated()->assertJsonPath('data.event_type', 'refill_done');

        $this->postJson('/api/v1/medications/reminders/'.$reminder->id.'/refill-skipped', [
            'event_date' => '2026-05-06',
            'notes' => 'Will buy later',
        ])->assertCreated()->assertJsonPath('data.event_type', 'refill_skipped');

        $this->getJson('/api/v1/medications/refills')->assertOk()->assertJsonCount(2, 'data');

        Sanctum::actingAs($other);
        $this->postJson('/api/v1/medications/reminders/'.$reminder->id.'/refill-done')->assertForbidden();
    }

    public function test_notification_queue_job_creates_pending_local_records_without_sending(): void
    {
        $patient = $this->patientUser();
        $this->createReminder($patient, [
            'times' => [['time_of_day' => '11:00']],
            'refill_enabled' => true,
            'refill_reminder_date' => '2026-05-05',
        ]);

        $job = new QueueMedicationReminderNotificationsJob(24);
        $job->handle(app(MedicationNotificationService::class));
        $job->handle(app(MedicationNotificationService::class));

        $this->assertSame(2, MedicationNotificationQueue::query()->count());
        $this->assertDatabaseHas('medication_notification_queue', [
            'patient_user_id' => $patient->id,
            'notification_type' => 'medication_reminder',
            'status' => 'pending',
            'channel' => 'local',
            'sent_at' => null,
        ]);
        $this->assertDatabaseHas('medication_notification_queue', [
            'patient_user_id' => $patient->id,
            'notification_type' => 'refill_reminder',
            'status' => 'pending',
            'channel' => 'local',
            'sent_at' => null,
        ]);
    }

    public function test_admin_read_only_routes_and_auth_boundaries(): void
    {
        $patient = $this->patientUser();
        $admin = $this->adminUser();
        $reminder = $this->createReminder($patient);

        $this->getJson('/api/v1/medications/reminders')->assertUnauthorized();

        Sanctum::actingAs($patient);
        $this->getJson('/api/v1/admin/medications/reminders')->assertForbidden();
        $this->postJson('/api/v1/admin/medications/reminders')->assertMethodNotAllowed();

        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/admin/medications/reminders')->assertOk()->assertJsonFragment(['medication_name' => $reminder->medication_name]);
        $this->getJson('/api/v1/admin/medications/reminders/'.$reminder->id)->assertOk();
        $this->getJson('/api/v1/admin/medications/logs')->assertOk();
        $this->getJson('/api/v1/admin/medications/refill-events')->assertOk();
        $this->getJson('/api/v1/admin/medications/notification-queue')->assertOk();
    }

    public function test_patient_cannot_access_another_patients_log_and_audit_logs_are_created(): void
    {
        $patient = $this->patientUser();
        $other = $this->patientUser('log-view-other@example.com');
        $reminder = $this->createReminder($patient);
        $log = MedicationLog::query()->create([
            'medication_reminder_id' => $reminder->id,
            'patient_user_id' => $patient->id,
            'scheduled_for' => '2026-05-05 08:00:00',
            'action' => MedicationLogAction::Taken,
            'taken_at' => '2026-05-05 08:00:00',
        ]);

        Sanctum::actingAs($other);
        $this->putJson('/api/v1/medications/logs/'.$log->id, [
            'action' => MedicationLogAction::Skipped->value,
            'scheduled_for' => '2026-05-05 08:00:00',
        ])->assertForbidden();

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/medications/reminders/'.$reminder->id.'/skipped', [
            'scheduled_for' => '2026-05-05 09:00:00',
        ])->assertOk();

        $this->assertGreaterThan(0, AuditLog::query()->where('action', 'like', 'medication_%')->count());
    }

    private function reminderPayload(array $overrides = []): array
    {
        return [
            'medication_name' => 'Test medicine',
            'dosage' => '10',
            'dosage_unit' => 'mg',
            'instructions' => 'Take after food',
            'frequency_type' => MedicationFrequencyType::OnceDaily->value,
            'start_date' => now()->toDateString(),
            'timezone' => 'Africa/Cairo',
            'times' => [['time_of_day' => '08:00']],
            ...$overrides,
        ];
    }

    private function createReminder(User $patient, array $overrides = []): MedicationReminder
    {
        $times = $overrides['times'] ?? [['time_of_day' => '08:00']];
        unset($overrides['times']);

        $reminder = MedicationReminder::query()->create([
            'patient_user_id' => $patient->id,
            'medication_name' => $overrides['medication_name'] ?? 'Seeded medicine',
            'frequency_type' => $overrides['frequency_type'] ?? MedicationFrequencyType::OnceDaily->value,
            'start_date' => $overrides['start_date'] ?? now()->toDateString(),
            'end_date' => $overrides['end_date'] ?? null,
            'timezone' => 'Africa/Cairo',
            'status' => $overrides['status'] ?? MedicationReminderStatus::Active->value,
            'refill_enabled' => $overrides['refill_enabled'] ?? false,
            'refill_reminder_date' => $overrides['refill_reminder_date'] ?? null,
            'metadata' => $overrides['metadata'] ?? null,
        ]);

        foreach ($times as $time) {
            $reminder->times()->create([
                'time_of_day' => $time['time_of_day'],
                'label' => $time['label'] ?? null,
                'is_active' => $time['is_active'] ?? true,
            ]);
        }

        return $reminder->refresh();
    }

    private function patientUser(string $email = 'med-patient@example.com'): User
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
