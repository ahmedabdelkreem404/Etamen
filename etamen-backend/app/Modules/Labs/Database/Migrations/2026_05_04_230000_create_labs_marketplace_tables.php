<?php

use App\Modules\Labs\Domain\Enums\LabOrderItemType;
use App\Modules\Labs\Domain\Enums\LabOrderPaymentStatus;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Domain\Enums\LabResultStatus;
use App\Modules\Labs\Domain\Enums\LabSampleCollectionMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_tests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->restrictOnDelete();
            $table->string('name_ar')->nullable();
            $table->string('name_en');
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('code')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('sample_type')->nullable();
            $table->text('preparation_instructions_ar')->nullable();
            $table->text('preparation_instructions_en')->nullable();
            $table->unsignedInteger('result_time_hours')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['provider_id', 'is_active']);
            $table->index('code');
        });

        Schema::create('lab_packages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->restrictOnDelete();
            $table->string('name_ar')->nullable();
            $table->string('name_en');
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->decimal('price', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['provider_id', 'is_active']);
        });

        Schema::create('lab_package_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('package_id')->constrained('lab_packages')->cascadeOnDelete();
            $table->foreignId('test_id')->constrained('lab_tests')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['package_id', 'test_id']);
        });

        Schema::create('lab_orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('lab_provider_id')->constrained('providers')->restrictOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('provider_net_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->string('currency', 3)->default('EGP');
            $table->enum('payment_status', LabOrderPaymentStatus::values())->default(LabOrderPaymentStatus::Unpaid->value);
            $table->enum('order_status', LabOrderStatus::values())->default(LabOrderStatus::LabReview->value);
            $table->enum('sample_collection_method', LabSampleCollectionMethod::values())->default(LabSampleCollectionMethod::BranchVisit->value);
            $table->text('collection_address')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('sample_collected_at')->nullable();
            $table->timestamp('result_ready_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['patient_user_id', 'order_status']);
            $table->index(['lab_provider_id', 'order_status']);
            $table->index('payment_status');
        });

        Schema::create('lab_order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('lab_orders')->cascadeOnDelete();
            $table->enum('item_type', LabOrderItemType::values());
            $table->foreignId('test_id')->nullable()->constrained('lab_tests')->restrictOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('lab_packages')->restrictOnDelete();
            $table->string('item_name');
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();

            $table->index(['item_type', 'test_id', 'package_id']);
        });

        Schema::create('lab_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('lab_orders')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('file_id')->constrained('uploaded_files')->restrictOnDelete();
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', LabResultStatus::values())->default(LabResultStatus::Uploaded->value);
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });

        Schema::create('lab_order_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('lab_orders')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_order_status_histories');
        Schema::dropIfExists('lab_results');
        Schema::dropIfExists('lab_order_items');
        Schema::dropIfExists('lab_orders');
        Schema::dropIfExists('lab_package_items');
        Schema::dropIfExists('lab_packages');
        Schema::dropIfExists('lab_tests');
    }
};
