<?php

namespace App\Modules\Providers\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\MedicalFiles\Application\Services\FileStorageService;
use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use App\Modules\Providers\Domain\Enums\ProviderDocumentStatus;
use App\Modules\Providers\Infrastructure\Models\ProviderDocument;
use Illuminate\Http\UploadedFile;

class ProviderDocumentService
{
    public function __construct(
        private readonly ProviderProfileService $providerProfileService,
        private readonly FileStorageService $fileStorageService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function upload(User $user, UploadedFile $file, string $documentType, ?string $notes = null): ProviderDocument
    {
        $provider = $this->providerProfileService->currentProviderFor($user);
        $uploadedFile = $this->fileStorageService->storePrivate(
            file: $file,
            category: FileCategory::ProviderDocument,
            uploadedBy: $user,
            owner: $provider,
        );

        $document = $provider->documents()->create([
            'file_id' => $uploadedFile->id,
            'uploaded_by' => $user->id,
            'document_type' => $documentType,
            'status' => ProviderDocumentStatus::Pending,
            'notes' => $notes,
        ]);

        $this->auditLogService->log('provider_document.uploaded', $document, $user, metadata: ['provider_id' => $provider->id]);

        return $document->load('file');
    }
}
