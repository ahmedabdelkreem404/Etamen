<?php

use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Domain\Enums\NotificationChannel;
use App\Modules\Notifications\Domain\Enums\NotificationDeviceType;
use App\Modules\Notifications\Domain\Enums\NotificationDispatchStatus;
use App\Modules\Notifications\Domain\Enums\NotificationPriority;
use App\Modules\Notifications\Domain\Enums\NotificationTokenProvider;
use App\Modules\Notifications\Domain\Enums\SchedulerRunStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('token');
            $table->string('token_hash', 64);
            $table->enum('provider', NotificationTokenProvider::values());
            $table->enum('device_type', NotificationDeviceType::values())->default(NotificationDeviceType::Unknown->value);
            $table->string('device_name')->nullable();
            $table->string('app_version', 100)->nullable();
            $table->string('locale', 10)->default('ar');
            $table->string('timezone')->default('Africa/Cairo');
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_seen_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'token_hash'], 'notification_tokens_provider_hash_unique');
            $table->index(['user_id', 'is_active']);
        });

        Schema::create('notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('channel', NotificationChannel::values());
            $table->enum('category', NotificationCategory::values());
            $table->boolean('is_enabled')->default(true);
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();
            $table->string('timezone')->default('Africa/Cairo');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'channel', 'category'], 'notification_preferences_unique');
        });

        Schema::create('notification_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->enum('category', NotificationCategory::values());
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->text('body_ar');
            $table->text('body_en')->nullable();
            $table->enum('channel', NotificationChannel::values());
            $table->boolean('is_active')->default(true);
            $table->json('variables')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('category', NotificationCategory::values());
            $table->string('type');
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable();
            $table->enum('priority', NotificationPriority::values())->default(NotificationPriority::Normal->value);
            $table->dateTime('read_at')->nullable();
            $table->string('action_url')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('category');
        });

        Schema::create('notification_dispatches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('notification_id')->nullable()->constrained('notifications')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('channel', NotificationChannel::values());
            $table->string('provider')->nullable();
            $table->enum('category', NotificationCategory::values());
            $table->string('type');
            $table->string('recipient')->nullable();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->json('payload')->nullable();
            $table->enum('status', NotificationDispatchStatus::values())->default(NotificationDispatchStatus::Pending->value);
            $table->string('idempotency_key')->nullable()->unique();
            $table->dateTime('scheduled_for')->nullable();
            $table->dateTime('attempted_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['scheduled_for', 'status']);
            $table->index(['category', 'status']);
        });

        Schema::create('scheduler_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('job_name');
            $table->enum('status', SchedulerRunStatus::values())->default(SchedulerRunStatus::Started->value);
            $table->dateTime('started_at');
            $table->dateTime('finished_at')->nullable();
            $table->unsignedInteger('processed_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->json('metadata')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['job_name', 'started_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduler_runs');
        Schema::dropIfExists('notification_dispatches');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notification_tokens');
    }
};
