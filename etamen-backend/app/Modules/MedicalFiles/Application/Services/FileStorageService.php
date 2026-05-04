<?php

namespace App\Modules\MedicalFiles\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use App\Modules\MedicalFiles\Domain\Enums\FileVisibility;
use App\Modules\MedicalFiles\Infrastructure\Models\UploadedFile as UploadedFileModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FileStorageService
{
    private const MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024;

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/pdf',
    ];

    public function storePrivate(
        UploadedFile $file,
        FileCategory $category,
        ?User $uploadedBy = null,
        ?Model $owner = null,
        array $metadata = [],
    ): UploadedFileModel {
        $this->validate($file);

        $disk = 'medical_private';
        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';
        $path = sprintf(
            '%s/%s/%s.%s',
            $category->value,
            now()->format('Y/m'),
            (string) Str::uuid(),
            strtolower($extension),
        );

        Storage::disk($disk)->put($path, $file->getContent());

        $record = UploadedFileModel::query()->create([
            'owner_type' => $owner ? $owner::class : null,
            'owner_id' => $owner?->getKey(),
            'uploaded_by' => $uploadedBy?->id,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'file_category' => $category,
            'visibility' => FileVisibility::Private,
            'checksum' => hash_file('sha256', $file->getRealPath()),
            'metadata' => $metadata,
        ]);

        app(AuditLogService::class)->log(
            'file.uploaded',
            $record,
            actor: $uploadedBy,
            metadata: ['category' => $category->value],
        );

        return $record;
    }

    private function validate(UploadedFile $file): void
    {
        $errors = [];

        if ($file->getSize() > self::MAX_FILE_SIZE_BYTES) {
            $errors['file'][] = 'The file may not be greater than 10 MB.';
        }

        if (! in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES, true)) {
            $errors['file'][] = 'The file type is not allowed.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
