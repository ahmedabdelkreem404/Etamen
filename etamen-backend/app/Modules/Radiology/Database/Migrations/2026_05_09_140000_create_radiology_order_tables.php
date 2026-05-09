<?php

use App\Modules\Radiology\Domain\Enums\RadiologyOrderStatus;
use App\Modules\Radiology\Domain\Enums\RadiologyResultType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radiology_orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('provider_id')->constrained('providers')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('provider_branches')->nullOnDelete();
            $table->enum('status', RadiologyOrderStatus::values())->default(RadiologyOrderStatus::PendingPayment->value);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->timestamp('scheduled_at')->nullable();
            $table->text('patient_notes')->nullable();
            $table->text('provider_notes')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['patient_user_id', 'status']);
            $table->index(['provider_id', 'status']);
            $table->index(['provider_id', 'scheduled_at']);
            $table->index('payment_id');
        });

        Schema::create('radiology_order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('radiology_order_id')->constrained('radiology_orders')->cascadeOnDelete();
            $table->foreignId('radiology_scan_id')->constrained('radiology_scans')->restrictOnDelete();
            $table->string('scan_name_ar');
            $table->string('scan_name_en')->nullable();
            $table->string('category_name_ar')->nullable();
            $table->string('category_name_en')->nullable();
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('total_price', 12, 2);
            $table->text('preparation_snapshot_ar')->nullable();
            $table->text('preparation_snapshot_en')->nullable();
            $table->timestamps();

            $table->index(['radiology_order_id', 'radiology_scan_id'], 'rad_order_items_order_scan_idx');
        });

        Schema::create('radiology_order_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('radiology_order_id')->constrained('radiology_orders')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->enum('to_status', RadiologyOrderStatus::values());
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['radiology_order_id', 'to_status'], 'rad_order_hist_order_status_idx');
        });

        Schema::create('radiology_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('radiology_order_id')->constrained('radiology_orders')->cascadeOnDelete();
            $table->foreignId('uploaded_file_id')->constrained('uploaded_files')->restrictOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->enum('result_type', RadiologyResultType::values())->default(RadiologyResultType::ReportPdf->value);
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();
            $table->text('notes_ar')->nullable();
            $table->text('notes_en')->nullable();
            $table->boolean('is_visible_to_patient')->default(false);
            $table->timestamp('uploaded_at');
            $table->timestamps();

            $table->index(['radiology_order_id', 'is_visible_to_patient'], 'rad_results_order_visible_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radiology_results');
        Schema::dropIfExists('radiology_order_status_histories');
        Schema::dropIfExists('radiology_order_items');
        Schema::dropIfExists('radiology_orders');
    }
};
