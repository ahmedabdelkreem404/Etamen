<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\AuditLogs\Infrastructure\Models\AuditLog;
use App\Modules\MedicalFiles\Application\Services\FileStorageService;
use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use App\Modules\MedicalFiles\Domain\Enums\FileVisibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileStorageFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_file_records_are_private_by_default(): void
    {
        Storage::fake('medical_private');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('proof.jpg');

        $record = app(FileStorageService::class)->storePrivate(
            file: $file,
            category: FileCategory::PaymentProof,
            uploadedBy: $user,
        );

        Storage::disk('medical_private')->assertExists($record->path);

        $this->assertSame('medical_private', $record->disk);
        $this->assertSame(FileCategory::PaymentProof, $record->file_category);
        $this->assertSame(FileVisibility::Private, $record->visibility);
        $this->assertNotNull($record->checksum);
        $this->assertTrue(AuditLog::query()->where('action', 'file.uploaded')->exists());
    }
}
