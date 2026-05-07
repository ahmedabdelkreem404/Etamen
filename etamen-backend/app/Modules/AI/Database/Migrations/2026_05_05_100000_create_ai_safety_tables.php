<?php

use App\Modules\AI\Domain\Enums\AiConversationStatus;
use App\Modules\AI\Domain\Enums\AiLanguage;
use App\Modules\AI\Domain\Enums\AiMessageRole;
use App\Modules\AI\Domain\Enums\AiProvider;
use App\Modules\AI\Domain\Enums\AiSafetyClassification;
use App\Modules\AI\Domain\Enums\AiSafetyEventType;
use App\Modules\AI\Domain\Enums\AiSafetyLevel;
use App\Modules\AI\Domain\Enums\AiSafetySeverity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_conversations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->string('title')->nullable();
            $table->enum('status', AiConversationStatus::values())->default(AiConversationStatus::Active->value);
            $table->enum('provider', AiProvider::values())->default(AiProvider::DeepSeek->value);
            $table->enum('language', AiLanguage::values())->default(AiLanguage::Arabic->value);
            $table->boolean('context_enabled')->default(true);
            $table->enum('safety_level', AiSafetyLevel::values())->default(AiSafetyLevel::Strict->value);
            $table->dateTime('last_message_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['patient_user_id', 'status']);
            $table->index('last_message_at');
        });

        Schema::create('ai_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->enum('role', AiMessageRole::values());
            $table->longText('content');
            $table->enum('safety_classification', AiSafetyClassification::values())->default(AiSafetyClassification::Unknown->value);
            $table->boolean('was_refused')->default(false);
            $table->enum('provider', AiProvider::values())->nullable();
            $table->string('provider_message_id')->nullable();
            $table->unsignedInteger('token_count')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
            $table->index(['patient_user_id', 'created_at']);
            $table->index('safety_classification');
        });

        Schema::create('ai_safety_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->nullable()->constrained('ai_conversations')->cascadeOnDelete();
            $table->foreignId('message_id')->nullable()->constrained('ai_messages')->cascadeOnDelete();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->enum('event_type', AiSafetyEventType::values());
            $table->enum('severity', AiSafetySeverity::values());
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['patient_user_id', 'event_type']);
            $table->index(['severity', 'created_at']);
        });

        Schema::create('ai_usage_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained('ai_conversations')->nullOnDelete();
            $table->enum('provider', AiProvider::values());
            $table->string('model')->nullable();
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->unsignedInteger('total_tokens')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->boolean('success');
            $table->string('error_code')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['patient_user_id', 'created_at']);
            $table->index(['provider', 'created_at']);
        });

        Schema::create('ai_provider_configs', function (Blueprint $table): void {
            $table->id();
            $table->enum('provider', [AiProvider::DeepSeek->value, AiProvider::Gemini->value])->unique();
            $table->boolean('is_active')->default(false);
            $table->string('model')->nullable();
            $table->text('encrypted_config')->nullable();
            $table->enum('safety_level', AiSafetyLevel::values())->default(AiSafetyLevel::Strict->value);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_provider_configs');
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('ai_safety_events');
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
    }
};
