<?php

use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uploaded_files', function (Blueprint $table): void {
            $table->id();
            $table->string('owner_type')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('disk');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->enum('file_category', FileCategory::values());
            $table->string('visibility')->default('private');
            $table->string('checksum')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['owner_type', 'owner_id']);
            $table->index('file_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploaded_files');
    }
};
