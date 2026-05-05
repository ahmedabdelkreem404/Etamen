<?php

use App\Modules\Pharmacies\Domain\Enums\PharmacyDeliveryMethod;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderPaymentStatus;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->restrictOnDelete();
            $table->string('name_ar')->nullable();
            $table->string('name_en');
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('price', 12, 2);
            $table->foreignId('image_file_id')->nullable()->constrained('uploaded_files')->nullOnDelete();
            $table->boolean('requires_prescription')->default(false);
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['provider_id', 'is_active']);
            $table->index('sku');
        });

        Schema::create('pharmacy_prescriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('pharmacy_provider_id')->constrained('providers')->restrictOnDelete();
            $table->foreignId('uploaded_file_id')->constrained('uploaded_files')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['patient_user_id', 'pharmacy_provider_id']);
        });

        Schema::create('pharmacy_orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('pharmacy_provider_id')->constrained('providers')->restrictOnDelete();
            $table->foreignId('prescription_id')->nullable()->constrained('pharmacy_prescriptions')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('provider_net_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->string('currency', 3)->default('EGP');
            $table->enum('payment_status', PharmacyOrderPaymentStatus::values())->default(PharmacyOrderPaymentStatus::Unpaid->value);
            $table->enum('order_status', PharmacyOrderStatus::values())->default(PharmacyOrderStatus::PharmacyReview->value);
            $table->enum('delivery_method', PharmacyDeliveryMethod::values())->default(PharmacyDeliveryMethod::Pickup->value);
            $table->text('delivery_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['patient_user_id', 'order_status']);
            $table->index(['pharmacy_provider_id', 'order_status']);
            $table->index('payment_status');
        });

        Schema::create('pharmacy_order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('pharmacy_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('pharmacy_products')->restrictOnDelete();
            $table->string('product_name');
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 12, 2);
            $table->timestamps();

            $table->index('product_id');
        });

        Schema::create('pharmacy_order_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('pharmacy_orders')->cascadeOnDelete();
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
        Schema::dropIfExists('pharmacy_order_status_histories');
        Schema::dropIfExists('pharmacy_order_items');
        Schema::dropIfExists('pharmacy_orders');
        Schema::dropIfExists('pharmacy_prescriptions');
        Schema::dropIfExists('pharmacy_products');
    }
};
