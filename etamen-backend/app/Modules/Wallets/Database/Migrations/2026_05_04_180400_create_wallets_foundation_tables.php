<?php

use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Domain\Enums\ServiceType;
use App\Modules\Wallets\Domain\Enums\SubscriptionStatus;
use App\Modules\Wallets\Domain\Enums\WalletOwnerType;
use App\Modules\Wallets\Domain\Enums\WalletStatus;
use App\Modules\Wallets\Domain\Enums\WalletTransactionStatus;
use App\Modules\Wallets\Domain\Enums\WalletTransactionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table): void {
            $table->id();
            $table->enum('owner_type', WalletOwnerType::values());
            $table->unsignedBigInteger('owner_id');
            $table->string('currency', 3)->default('EGP');
            $table->enum('status', WalletStatus::values())->default(WalletStatus::Active->value);
            $table->timestamps();

            $table->unique(['owner_type', 'owner_id']);
        });

        Schema::create('wallet_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->restrictOnDelete();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->enum('type', WalletTransactionType::values());
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            $table->decimal('balance_after_snapshot', 12, 2)->nullable();
            $table->enum('status', WalletTransactionStatus::values())->default(WalletTransactionStatus::Pending->value);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
        });

        Schema::create('commission_rules', function (Blueprint $table): void {
            $table->id();
            $table->enum('provider_type', ProviderType::values());
            $table->enum('service_type', ServiceType::values());
            $table->decimal('percentage', 5, 2);
            $table->decimal('fixed_amount', 12, 2)->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['provider_type', 'service_type', 'is_active']);
        });

        Schema::create('subscription_plans', function (Blueprint $table): void {
            $table->id();
            $table->enum('provider_type', ProviderType::values());
            $table->string('name_ar');
            $table->string('name_en');
            $table->unsignedInteger('duration_days');
            $table->decimal('price', 12, 2);
            $table->string('currency', 3)->default('EGP');
            $table->json('benefits')->nullable();
            $table->integer('visibility_priority')->default(0);
            $table->json('feature_limits')->nullable();
            $table->boolean('has_free_trial')->default(false);
            $table->unsignedInteger('trial_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('provider_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->foreignId('plan_id')->constrained('subscription_plans')->restrictOnDelete();
            $table->enum('status', SubscriptionStatus::values());
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->boolean('auto_renew')->default(false);
            $table->timestamps();

            $table->index(['provider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_subscriptions');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('commission_rules');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }
};
