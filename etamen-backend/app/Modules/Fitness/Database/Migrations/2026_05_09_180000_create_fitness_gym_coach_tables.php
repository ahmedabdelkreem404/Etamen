<?php

use App\Modules\Fitness\Domain\Enums\CoachAvailabilityStatus;
use App\Modules\Fitness\Domain\Enums\CoachBookingStatus;
use App\Modules\Fitness\Domain\Enums\CoachSessionMode;
use App\Modules\Fitness\Domain\Enums\GymBookingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gym_membership_plans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('provider_branches')->nullOnDelete();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->unsignedInteger('duration_days');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('sessions_count')->nullable();
            $table->boolean('includes_classes')->default(false);
            $table->boolean('includes_personal_training')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['provider_id', 'is_active']);
            $table->index(['branch_id', 'is_active']);
        });

        Schema::create('gym_classes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('provider_branches')->nullOnDelete();
            $table->foreignId('coach_provider_id')->nullable()->constrained('providers')->nullOnDelete();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedInteger('capacity')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['provider_id', 'is_active']);
            $table->index(['provider_id', 'starts_at']);
        });

        Schema::create('gym_bookings', function (Blueprint $table): void {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('provider_id')->constrained('providers')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('provider_branches')->nullOnDelete();
            $table->foreignId('membership_plan_id')->nullable()->constrained('gym_membership_plans')->restrictOnDelete();
            $table->foreignId('gym_class_id')->nullable()->constrained('gym_classes')->restrictOnDelete();
            $table->enum('status', GymBookingStatus::values())->default(GymBookingStatus::PendingPayment->value);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['patient_user_id', 'status']);
            $table->index(['provider_id', 'status']);
            $table->index(['provider_id', 'starts_at']);
            $table->index('payment_id');
        });

        Schema::create('gym_booking_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gym_booking_id')->constrained('gym_bookings')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->enum('to_status', GymBookingStatus::values());
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['gym_booking_id', 'to_status']);
        });

        Schema::create('coach_session_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->restrictOnDelete();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->unsignedInteger('duration_minutes');
            $table->decimal('price', 12, 2);
            $table->enum('session_mode', CoachSessionMode::values());
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['provider_id', 'is_active']);
        });

        Schema::create('coach_availability_slots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->restrictOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->enum('status', CoachAvailabilityStatus::values())->default(CoachAvailabilityStatus::Available->value);
            $table->timestamps();

            $table->index(['provider_id', 'status']);
            $table->index(['provider_id', 'starts_at']);
        });

        Schema::create('coach_bookings', function (Blueprint $table): void {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('coach_provider_id')->constrained('providers')->restrictOnDelete();
            $table->foreignId('session_type_id')->constrained('coach_session_types')->restrictOnDelete();
            $table->foreignId('availability_slot_id')->nullable()->constrained('coach_availability_slots')->nullOnDelete();
            $table->enum('status', CoachBookingStatus::values())->default(CoachBookingStatus::PendingPayment->value);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->text('patient_goal')->nullable();
            $table->text('coach_notes')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['patient_user_id', 'status']);
            $table->index(['coach_provider_id', 'status']);
            $table->index('payment_id');
        });

        Schema::create('coach_booking_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('coach_booking_id')->constrained('coach_bookings')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->enum('to_status', CoachBookingStatus::values());
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['coach_booking_id', 'to_status']);
        });

        Schema::create('coach_packages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->restrictOnDelete();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->unsignedInteger('sessions_count');
            $table->unsignedInteger('duration_days')->nullable();
            $table->decimal('price', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['provider_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_packages');
        Schema::dropIfExists('coach_booking_status_histories');
        Schema::dropIfExists('coach_bookings');
        Schema::dropIfExists('coach_availability_slots');
        Schema::dropIfExists('coach_session_types');
        Schema::dropIfExists('gym_booking_status_histories');
        Schema::dropIfExists('gym_bookings');
        Schema::dropIfExists('gym_classes');
        Schema::dropIfExists('gym_membership_plans');
    }
};
