<?php

use App\Modules\Health\Domain\Enums\AllergySeverity;
use App\Modules\Health\Domain\Enums\BloodType;
use App\Modules\Health\Domain\Enums\Gender;
use App\Modules\Health\Domain\Enums\HealthGoalStatus;
use App\Modules\Health\Domain\Enums\HealthGoalType;
use App\Modules\Health\Domain\Enums\VitalFlag;
use App\Modules\Health\Domain\Enums\VitalSource;
use App\Modules\Health\Domain\Enums\VitalType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_user_id')->unique()->constrained('users')->restrictOnDelete();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', Gender::values())->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->decimal('weight_kg', 6, 2)->nullable();
            $table->enum('blood_type', BloodType::values())->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_chronic_diseases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->date('diagnosed_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['patient_user_id', 'is_active']);
        });

        Schema::create('patient_allergies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->string('allergen');
            $table->string('reaction')->nullable();
            $table->enum('severity', AllergySeverity::values())->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['patient_user_id', 'is_active']);
        });

        Schema::create('patient_current_medications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->string('medication_name');
            $table->string('dosage')->nullable();
            $table->string('frequency_text')->nullable();
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->string('prescribed_by')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['patient_user_id', 'is_active']);
        });

        Schema::create('patient_surgeries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->string('surgery_name');
            $table->date('surgery_date')->nullable();
            $table->string('hospital_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('patient_user_id');
        });

        Schema::create('health_goals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->enum('goal_type', HealthGoalType::values());
            $table->string('title');
            $table->string('target_value')->nullable();
            $table->string('unit')->nullable();
            $table->date('target_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', HealthGoalStatus::values())->default(HealthGoalStatus::Active->value);
            $table->timestamps();

            $table->index(['patient_user_id', 'status']);
        });

        Schema::create('vital_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->enum('vital_type', VitalType::values());
            $table->dateTime('measured_at');
            $table->decimal('value_decimal', 10, 2)->nullable();
            $table->decimal('value_secondary_decimal', 10, 2)->nullable();
            $table->string('unit')->nullable();
            $table->enum('source', VitalSource::values())->default(VitalSource::Manual->value);
            $table->enum('flag', VitalFlag::values())->default(VitalFlag::Unknown->value);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['patient_user_id', 'vital_type', 'measured_at']);
            $table->index(['patient_user_id', 'measured_at']);
        });

        Schema::create('health_access_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['patient_user_id', 'created_at']);
            $table->index(['actor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_access_logs');
        Schema::dropIfExists('vital_records');
        Schema::dropIfExists('health_goals');
        Schema::dropIfExists('patient_surgeries');
        Schema::dropIfExists('patient_current_medications');
        Schema::dropIfExists('patient_allergies');
        Schema::dropIfExists('patient_chronic_diseases');
        Schema::dropIfExists('health_profiles');
    }
};
