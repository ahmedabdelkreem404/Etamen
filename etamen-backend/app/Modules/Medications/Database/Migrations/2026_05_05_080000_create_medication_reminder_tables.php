<?php

use App\Modules\Medications\Domain\Enums\MedicationFrequencyType;
use App\Modules\Medications\Domain\Enums\MedicationLogAction;
use App\Modules\Medications\Domain\Enums\MedicationNotificationChannel;
use App\Modules\Medications\Domain\Enums\MedicationNotificationStatus;
use App\Modules\Medications\Domain\Enums\MedicationNotificationType;
use App\Modules\Medications\Domain\Enums\MedicationRefillEventType;
use App\Modules\Medications\Domain\Enums\MedicationReminderSource;
use App\Modules\Medications\Domain\Enums\MedicationReminderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_reminders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->string('medication_name');
            $table->string('dosage')->nullable();
            $table->string('dosage_unit', 100)->nullable();
            $table->text('instructions')->nullable();
            $table->enum('frequency_type', MedicationFrequencyType::values());
            $table->unsignedTinyInteger('interval_hours')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('timezone')->nullable()->default('Africa/Cairo');
            $table->enum('status', MedicationReminderStatus::values())->default(MedicationReminderStatus::Active->value);
            $table->string('prescribed_by')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('refill_enabled')->default(false);
            $table->unsignedInteger('refill_quantity')->nullable();
            $table->unsignedInteger('refill_threshold')->nullable();
            $table->date('refill_reminder_date')->nullable();
            $table->enum('source', MedicationReminderSource::values())->default(MedicationReminderSource::PatientEntered->value);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['patient_user_id', 'status']);
            $table->index(['patient_user_id', 'start_date']);
            $table->index('refill_reminder_date');
        });

        Schema::create('medication_reminder_times', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('medication_reminder_id')->constrained('medication_reminders')->cascadeOnDelete();
            $table->time('time_of_day');
            $table->string('label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['medication_reminder_id', 'is_active']);
        });

        Schema::create('medication_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('medication_reminder_id')->constrained('medication_reminders')->cascadeOnDelete();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('scheduled_for');
            $table->enum('action', MedicationLogAction::values());
            $table->dateTime('taken_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['medication_reminder_id', 'scheduled_for']);
            $table->index(['patient_user_id', 'scheduled_for']);
            $table->index(['medication_reminder_id', 'action']);
        });

        Schema::create('medication_refill_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('medication_reminder_id')->constrained('medication_reminders')->cascadeOnDelete();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->enum('event_type', MedicationRefillEventType::values());
            $table->date('event_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['patient_user_id', 'event_date']);
        });

        Schema::create('medication_notification_queue', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('medication_reminder_id')->constrained('medication_reminders')->cascadeOnDelete();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('scheduled_for');
            $table->enum('notification_type', MedicationNotificationType::values());
            $table->enum('status', MedicationNotificationStatus::values())->default(MedicationNotificationStatus::Pending->value);
            $table->enum('channel', MedicationNotificationChannel::values())->default(MedicationNotificationChannel::Local->value);
            $table->json('payload')->nullable();
            $table->dateTime('attempted_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['patient_user_id', 'scheduled_for']);
            $table->index(['medication_reminder_id', 'status']);
            $table->unique(['medication_reminder_id', 'scheduled_for', 'notification_type', 'channel'], 'med_notification_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_notification_queue');
        Schema::dropIfExists('medication_refill_events');
        Schema::dropIfExists('medication_logs');
        Schema::dropIfExists('medication_reminder_times');
        Schema::dropIfExists('medication_reminders');
    }
};
