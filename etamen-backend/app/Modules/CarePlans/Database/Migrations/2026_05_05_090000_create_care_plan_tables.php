<?php

use App\Modules\CarePlans\Domain\Enums\CarePlanFoodCategory;
use App\Modules\CarePlans\Domain\Enums\CarePlanInstructionType;
use App\Modules\CarePlans\Domain\Enums\CarePlanMealType;
use App\Modules\CarePlans\Domain\Enums\CarePlanMood;
use App\Modules\CarePlans\Domain\Enums\CarePlanSource;
use App\Modules\CarePlans\Domain\Enums\CarePlanStatus;
use App\Modules\CarePlans\Domain\Enums\CarePlanType;
use App\Modules\CarePlans\Domain\Enums\CarePlanVisibility;
use App\Modules\CarePlans\Domain\Enums\MealLogStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('care_plans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained('providers')->nullOnDelete();
            $table->enum('plan_type', CarePlanType::values());
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('goal_text')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', CarePlanStatus::values())->default(CarePlanStatus::Draft->value);
            $table->enum('visibility', CarePlanVisibility::values())->default(CarePlanVisibility::PatientOnly->value);
            $table->enum('source', CarePlanSource::values())->default(CarePlanSource::PatientCreated->value);
            $table->text('notes')->nullable();
            $table->text('safety_disclaimer')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['patient_user_id', 'status']);
            $table->index(['provider_id', 'status']);
            $table->index(['plan_type', 'status']);
            $table->index('start_date');
        });

        Schema::create('care_plan_days', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('care_plan_id')->constrained('care_plans')->cascadeOnDelete();
            $table->unsignedInteger('day_number')->nullable();
            $table->date('day_date')->nullable();
            $table->string('title')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['care_plan_id', 'day_number']);
            $table->index(['care_plan_id', 'day_date']);
        });

        Schema::create('care_plan_meals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('care_plan_day_id')->constrained('care_plan_days')->cascadeOnDelete();
            $table->enum('meal_type', CarePlanMealType::values());
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('calories')->nullable();
            $table->decimal('protein_g', 8, 2)->nullable();
            $table->decimal('carbs_g', 8, 2)->nullable();
            $table->decimal('fat_g', 8, 2)->nullable();
            $table->text('instructions')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->timestamps();

            $table->index(['care_plan_day_id', 'meal_type']);
        });

        Schema::create('care_plan_food_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('care_plan_id')->constrained('care_plans')->cascadeOnDelete();
            $table->enum('category', CarePlanFoodCategory::values());
            $table->string('name');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['care_plan_id', 'category']);
        });

        Schema::create('care_plan_instructions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('care_plan_id')->constrained('care_plans')->cascadeOnDelete();
            $table->enum('instruction_type', CarePlanInstructionType::values());
            $table->string('title')->nullable();
            $table->text('body');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['care_plan_id', 'instruction_type']);
        });

        Schema::create('care_plan_checkins', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('care_plan_id')->constrained('care_plans')->cascadeOnDelete();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->date('checkin_date');
            $table->unsignedTinyInteger('commitment_score')->nullable();
            $table->unsignedTinyInteger('energy_level')->nullable();
            $table->unsignedTinyInteger('hunger_level')->nullable();
            $table->unsignedTinyInteger('sleep_quality')->nullable();
            $table->enum('mood', CarePlanMood::values())->nullable();
            $table->text('symptoms_notes')->nullable();
            $table->text('general_notes')->nullable();
            $table->timestamps();

            $table->unique(['care_plan_id', 'patient_user_id', 'checkin_date']);
            $table->index(['patient_user_id', 'checkin_date']);
        });

        Schema::create('meal_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('care_plan_id')->constrained('care_plans')->cascadeOnDelete();
            $table->foreignId('care_plan_meal_id')->nullable()->constrained('care_plan_meals')->nullOnDelete();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('logged_at');
            $table->enum('meal_type', CarePlanMealType::values())->nullable();
            $table->enum('status', MealLogStatus::values());
            $table->text('description')->nullable();
            $table->foreignId('photo_file_id')->nullable()->constrained('uploaded_files')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['patient_user_id', 'logged_at']);
            $table->index(['care_plan_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_logs');
        Schema::dropIfExists('care_plan_checkins');
        Schema::dropIfExists('care_plan_instructions');
        Schema::dropIfExists('care_plan_food_items');
        Schema::dropIfExists('care_plan_meals');
        Schema::dropIfExists('care_plan_days');
        Schema::dropIfExists('care_plans');
    }
};
