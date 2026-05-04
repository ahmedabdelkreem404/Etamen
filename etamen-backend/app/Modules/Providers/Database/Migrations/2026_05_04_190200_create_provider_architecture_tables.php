<?php

use App\Modules\Providers\Domain\Enums\ApprovalRequestStatus;
use App\Modules\Providers\Domain\Enums\ProviderDocumentStatus;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table): void {
            $table->id();
            $table->enum('type', ProviderType::values());
            $table->foreignId('owner_user_id')->constrained('users')->restrictOnDelete();
            $table->string('name_ar')->nullable();
            $table->string('name_en');
            $table->string('slug')->unique();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->enum('status', ProviderStatus::values())->default(ProviderStatus::Draft->value);
            $table->boolean('is_active')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['type', 'status', 'is_active']);
            $table->index('owner_user_id');
        });

        Schema::create('provider_branches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->string('name_ar')->nullable();
            $table->string('name_en');
            $table->string('phone')->nullable();
            $table->text('address_ar')->nullable();
            $table->text('address_en')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_main')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('provider_staff', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ProviderStaffRole::values())->default(ProviderStaffRole::Staff->value);
            $table->boolean('is_owner')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['provider_id', 'user_id']);
            $table->index(['user_id', 'is_owner', 'status']);
        });

        Schema::create('provider_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('file_id')->constrained('uploaded_files')->restrictOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('document_type');
            $table->enum('status', ProviderDocumentStatus::values())->default(ProviderDocumentStatus::Pending->value);
            $table->text('notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('provider_approval_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ApprovalRequestStatus::values())->default(ApprovalRequestStatus::Pending->value);
            $table->text('notes')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('specialties', function (Blueprint $table): void {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('doctor_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->unique()->constrained('providers')->cascadeOnDelete();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('bio_ar')->nullable();
            $table->text('bio_en')->nullable();
            $table->decimal('consultation_fee', 12, 2)->nullable();
            $table->unsignedInteger('years_of_experience')->nullable();
            $table->timestamps();
        });

        Schema::create('doctor_specialties', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('doctor_profile_id')->constrained('doctor_profiles')->cascadeOnDelete();
            $table->foreignId('specialty_id')->constrained('specialties')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['doctor_profile_id', 'specialty_id']);
        });

        Schema::create('pharmacy_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->unique()->constrained('providers')->cascadeOnDelete();
            $table->string('license_number')->nullable();
            $table->boolean('delivery_available')->default(false);
            $table->timestamps();
        });

        Schema::create('lab_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->unique()->constrained('providers')->cascadeOnDelete();
            $table->string('license_number')->nullable();
            $table->boolean('home_collection_available')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_profiles');
        Schema::dropIfExists('pharmacy_profiles');
        Schema::dropIfExists('doctor_specialties');
        Schema::dropIfExists('doctor_profiles');
        Schema::dropIfExists('specialties');
        Schema::dropIfExists('provider_approval_requests');
        Schema::dropIfExists('provider_documents');
        Schema::dropIfExists('provider_staff');
        Schema::dropIfExists('provider_branches');
        Schema::dropIfExists('providers');
    }
};
