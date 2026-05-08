<?php

use App\Modules\Providers\Domain\Enums\ApprovalRequestStatus;
use App\Modules\Providers\Domain\Enums\CoachType;
use App\Modules\Providers\Domain\Enums\ProviderContractStatus;
use App\Modules\Providers\Domain\Enums\ProviderContractType;
use App\Modules\Providers\Domain\Enums\ProviderDocumentVisibility;
use App\Modules\Providers\Domain\Enums\ProviderSettlementCycle;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->widenExistingEnumsForMysql();
        $this->hardenProviderBranches();
        $this->hardenProviderDocuments();
        $this->createProfileTables();
        $this->createHospitalFoundationTables();
        $this->createServiceCatalogTables();
        $this->createBookingAndContractTables();
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_contracts');
        Schema::dropIfExists('provider_booking_settings');
        Schema::dropIfExists('provider_services');
        Schema::dropIfExists('service_categories');
        Schema::dropIfExists('hospital_doctors');
        Schema::dropIfExists('hospital_departments');
        Schema::dropIfExists('home_healthcare_profiles');
        Schema::dropIfExists('physiotherapy_profiles');
        Schema::dropIfExists('coach_profiles');
        Schema::dropIfExists('gym_profiles');
        Schema::dropIfExists('radiology_profiles');
        Schema::dropIfExists('medical_center_profiles');
        Schema::dropIfExists('clinic_profiles');
        Schema::dropIfExists('hospital_profiles');

        Schema::table('provider_documents', function (Blueprint $table): void {
            foreach (['visibility', 'approved_public_at'] as $column) {
                if (Schema::hasColumn('provider_documents', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('provider_branches', function (Blueprint $table): void {
            foreach ([
                'address_line_1',
                'address_line_2',
                'district',
                'whatsapp',
                'working_hours_json',
                'is_24_hours',
                'home_service_radius_km',
                'delivery_radius_km',
            ] as $column) {
                if (Schema::hasColumn('provider_branches', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function widenExistingEnumsForMysql(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $providerTypes = $this->enumSql(ProviderType::values());
        $providerStatuses = $this->enumSql(ProviderStatus::values());
        $approvalRequestStatuses = $this->enumSql(ApprovalRequestStatus::values());

        DB::statement("ALTER TABLE providers MODIFY type ENUM({$providerTypes}) NOT NULL");
        DB::statement("ALTER TABLE providers MODIFY status ENUM({$providerStatuses}) NOT NULL DEFAULT '".ProviderStatus::Draft->value."'");
        DB::statement("ALTER TABLE provider_approval_requests MODIFY status ENUM({$approvalRequestStatuses}) NOT NULL DEFAULT '".ApprovalRequestStatus::Pending->value."'");

        foreach (['commission_rules', 'subscription_plans', 'settlements'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'provider_type')) {
                DB::statement("ALTER TABLE {$table} MODIFY provider_type ENUM({$providerTypes}) NOT NULL");
            }
        }
    }

    private function enumSql(array $values): string
    {
        return collect($values)
            ->map(fn (string $value): string => "'".str_replace("'", "''", $value)."'")
            ->implode(',');
    }

    private function hardenProviderBranches(): void
    {
        Schema::table('provider_branches', function (Blueprint $table): void {
            if (! Schema::hasColumn('provider_branches', 'address_line_1')) {
                $table->string('address_line_1')->nullable();
            }

            if (! Schema::hasColumn('provider_branches', 'address_line_2')) {
                $table->string('address_line_2')->nullable();
            }

            if (! Schema::hasColumn('provider_branches', 'district')) {
                $table->string('district')->nullable();
            }

            if (! Schema::hasColumn('provider_branches', 'whatsapp')) {
                $table->string('whatsapp', 30)->nullable();
            }

            if (! Schema::hasColumn('provider_branches', 'working_hours_json')) {
                $table->json('working_hours_json')->nullable();
            }

            if (! Schema::hasColumn('provider_branches', 'is_24_hours')) {
                $table->boolean('is_24_hours')->default(false);
            }

            if (! Schema::hasColumn('provider_branches', 'home_service_radius_km')) {
                $table->decimal('home_service_radius_km', 8, 2)->nullable();
            }

            if (! Schema::hasColumn('provider_branches', 'delivery_radius_km')) {
                $table->decimal('delivery_radius_km', 8, 2)->nullable();
            }
        });
    }

    private function hardenProviderDocuments(): void
    {
        Schema::table('provider_documents', function (Blueprint $table): void {
            if (! Schema::hasColumn('provider_documents', 'visibility')) {
                $table->enum('visibility', ProviderDocumentVisibility::values())
                    ->default(ProviderDocumentVisibility::AdminOnly->value);
            }

            if (! Schema::hasColumn('provider_documents', 'approved_public_at')) {
                $table->timestamp('approved_public_at')->nullable();
            }
        });
    }

    private function createProfileTables(): void
    {
        Schema::create('hospital_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->unique()->constrained('providers')->cascadeOnDelete();
            $table->string('license_number')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->boolean('emergency_available')->default(false);
            $table->boolean('has_inpatient')->default(false);
            $table->boolean('has_outpatient')->default(true);
            $table->boolean('has_icu')->default(false);
            $table->boolean('has_ambulance')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('clinic_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->unique()->constrained('providers')->cascadeOnDelete();
            $table->string('clinic_type')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('medical_center_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->unique()->constrained('providers')->cascadeOnDelete();
            $table->string('center_type')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('radiology_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->unique()->constrained('providers')->cascadeOnDelete();
            $table->string('license_number')->nullable();
            $table->boolean('home_service_enabled')->default(false);
            $table->boolean('report_delivery_enabled')->default(true);
            $table->boolean('dicom_supported')->default(false);
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('gym_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->unique()->constrained('providers')->cascadeOnDelete();
            $table->boolean('men_allowed')->default(true);
            $table->boolean('women_allowed')->default(true);
            $table->boolean('ladies_only_hours')->default(false);
            $table->boolean('has_classes')->default(false);
            $table->boolean('has_personal_training')->default(false);
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('coach_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->unique()->constrained('providers')->cascadeOnDelete();
            $table->enum('coach_type', CoachType::values());
            $table->unsignedInteger('experience_years')->nullable();
            $table->decimal('session_price', 12, 2)->nullable();
            $table->decimal('monthly_followup_price', 12, 2)->nullable();
            $table->boolean('online_coaching_enabled')->default(false);
            $table->boolean('gym_visit_enabled')->default(false);
            $table->boolean('home_training_enabled')->default(false);
            $table->text('certifications_summary')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('physiotherapy_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->unique()->constrained('providers')->cascadeOnDelete();
            $table->boolean('home_visit_enabled')->default(false);
            $table->boolean('center_visit_enabled')->default(true);
            $table->decimal('session_price', 12, 2)->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('home_healthcare_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->unique()->constrained('providers')->cascadeOnDelete();
            $table->boolean('nursing_enabled')->default(false);
            $table->boolean('injections_enabled')->default(false);
            $table->boolean('wound_care_enabled')->default(false);
            $table->boolean('elderly_care_enabled')->default(false);
            $table->boolean('physiotherapy_home_enabled')->default(false);
            $table->decimal('service_radius_km', 8, 2)->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    private function createHospitalFoundationTables(): void
    {
        Schema::create('hospital_departments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hospital_provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('specialty_id')->nullable()->constrained('specialties')->nullOnDelete();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['hospital_provider_id', 'is_active']);
        });

        Schema::create('hospital_doctors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hospital_provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('doctor_provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('hospital_department_id')->nullable()->constrained('hospital_departments')->nullOnDelete();
            $table->decimal('consultation_fee', 12, 2)->nullable();
            $table->boolean('online_consultation_enabled')->default(false);
            $table->boolean('clinic_consultation_enabled')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['hospital_provider_id', 'doctor_provider_id', 'hospital_department_id'], 'hospital_doctor_department_unique');
            $table->index(['hospital_provider_id', 'is_active']);
            $table->index(['doctor_provider_id', 'is_active']);
        });
    }

    private function createServiceCatalogTables(): void
    {
        Schema::create('service_categories', function (Blueprint $table): void {
            $table->id();
            $table->enum('provider_type', ProviderType::values())->nullable();
            $table->string('code')->unique();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['provider_type', 'is_active']);
        });

        Schema::create('provider_services', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('provider_branches')->nullOnDelete();
            $table->foreignId('service_category_id')->nullable()->constrained('service_categories')->nullOnDelete();
            $table->string('service_type');
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->decimal('base_price', 12, 2)->nullable();
            $table->boolean('online_available')->default(false);
            $table->boolean('home_available')->default(false);
            $table->boolean('branch_available')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['provider_id', 'is_active']);
            $table->index(['service_type', 'is_active']);
        });
    }

    private function createBookingAndContractTables(): void
    {
        Schema::create('provider_booking_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->unique()->constrained('providers')->cascadeOnDelete();
            $table->boolean('clinic_visit_enabled')->default(true);
            $table->boolean('online_video_enabled')->default(false);
            $table->boolean('home_visit_enabled')->default(false);
            $table->boolean('branch_visit_enabled')->default(true);
            $table->boolean('booking_requires_payment')->default(true);
            $table->boolean('pay_at_branch_enabled')->default(false);
            $table->unsignedInteger('default_slot_duration_minutes')->nullable();
            $table->text('cancellation_policy_ar')->nullable();
            $table->text('cancellation_policy_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('provider_contracts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->enum('contract_type', ProviderContractType::values())->default(ProviderContractType::CommissionOnly->value);
            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->decimal('fixed_commission_amount', 12, 2)->nullable();
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->enum('settlement_cycle', ProviderSettlementCycle::values())->default(ProviderSettlementCycle::Monthly->value);
            $table->boolean('pay_at_branch_allowed')->default(false);
            $table->boolean('online_payment_required')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->enum('status', ProviderContractStatus::values())->default(ProviderContractStatus::Draft->value);
            $table->timestamps();

            $table->index(['provider_id', 'status']);
        });
    }
};
