<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radiology_scan_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('radiology_scans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('provider_branches')->nullOnDelete();
            $table->foreignId('radiology_scan_category_id')->constrained('radiology_scan_categories')->restrictOnDelete();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->text('preparation_ar')->nullable();
            $table->text('preparation_en')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->decimal('base_price', 12, 2)->nullable();
            $table->boolean('requires_preparation')->default(false);
            $table->boolean('requires_fasting')->default(false);
            $table->boolean('contrast_required')->default(false);
            $table->boolean('home_available')->default(false);
            $table->boolean('branch_available')->default(true);
            $table->boolean('report_delivery_enabled')->default(true);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['provider_id', 'is_active']);
            $table->index(['radiology_scan_category_id', 'is_active']);
            $table->index(['branch_id', 'is_active']);
        });

        Schema::create('radiology_preparation_instructions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('radiology_scan_category_id')->nullable()->constrained('radiology_scan_categories')->cascadeOnDelete();
            $table->foreignId('radiology_scan_id')->nullable()->constrained('radiology_scans')->cascadeOnDelete();
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->text('body_ar');
            $table->text('body_en')->nullable();
            $table->text('warning_ar')->nullable();
            $table->text('warning_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['radiology_scan_category_id', 'is_active']);
            $table->index(['radiology_scan_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radiology_preparation_instructions');
        Schema::dropIfExists('radiology_scans');
        Schema::dropIfExists('radiology_scan_categories');
    }
};
