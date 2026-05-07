<?php

use App\Modules\Appointments\Domain\Enums\AppointmentNoteVisibility;
use App\Modules\Appointments\Domain\Enums\AppointmentSlotStatus;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Domain\Enums\ConsultationType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('doctor_profile_id')->constrained('doctor_profiles')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('provider_branches')->nullOnDelete();
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('slot_duration_minutes')->default(30);
            $table->unsignedSmallInteger('buffer_minutes')->default(0);
            $table->unsignedSmallInteger('max_days_ahead')->default(14);
            $table->timestamps();
        });

        Schema::create('doctor_schedule_days', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('doctor_schedule_id')->constrained('doctor_schedules')->cascadeOnDelete();
            $table->tinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(
                ['doctor_schedule_id', 'day_of_week', 'is_active'],
                'doctor_schedule_days_schedule_day_active_idx',
            );
        });

        Schema::create('doctor_holidays', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('doctor_profile_id')->constrained('doctor_profiles')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->text('reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['doctor_profile_id', 'starts_at', 'ends_at']);
        });

        Schema::create('appointment_slots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('doctor_profile_id')->constrained('doctor_profiles')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('provider_branches')->nullOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->enum('status', AppointmentSlotStatus::values())->default(AppointmentSlotStatus::Available->value);
            $table->timestamp('hold_expires_at')->nullable();
            $table->foreignId('generated_from_schedule_id')->nullable()->constrained('doctor_schedules')->nullOnDelete();
            $table->timestamps();

            $table->unique(['doctor_profile_id', 'starts_at', 'ends_at'], 'appointment_slots_doctor_time_unique');
            $table->index(['doctor_profile_id', 'starts_at']);
            $table->index(['provider_id', 'starts_at']);
            $table->index('status');
        });

        Schema::create('appointments', function (Blueprint $table): void {
            $table->id();
            $table->string('appointment_number')->unique();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('doctor_profile_id')->constrained('doctor_profiles')->restrictOnDelete();
            $table->foreignId('provider_id')->constrained('providers')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('provider_branches')->nullOnDelete();
            $table->foreignId('appointment_slot_id')->nullable()->unique()->constrained('appointment_slots')->nullOnDelete();
            $table->enum('consultation_type', ConsultationType::values());
            $table->text('problem_description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 3)->default('EGP');
            $table->enum('status', AppointmentStatus::values());
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->timestamp('booked_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('no_show_at')->nullable();
            $table->timestamps();

            $table->index(['patient_user_id', 'status']);
            $table->index(['doctor_profile_id', 'status']);
            $table->index(['provider_id', 'status']);
            $table->index('created_at');
        });

        Schema::create('appointment_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->enum('to_status', AppointmentStatus::values());
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('appointment_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->text('note');
            $table->enum('visibility', AppointmentNoteVisibility::values());
            $table->timestamps();
        });

        Schema::create('appointment_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('doctor_profile_id')->constrained('doctor_profiles')->restrictOnDelete();
            $table->tinyInteger('rating');
            $table->text('comment')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['doctor_profile_id', 'is_visible']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_reviews');
        Schema::dropIfExists('appointment_notes');
        Schema::dropIfExists('appointment_status_histories');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('appointment_slots');
        Schema::dropIfExists('doctor_holidays');
        Schema::dropIfExists('doctor_schedule_days');
        Schema::dropIfExists('doctor_schedules');
    }
};
