<?php

namespace App\Modules\Pharmacies\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\MedicalFiles\Application\Services\FileStorageService;
use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyPrescription;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PharmacyPrescriptionService
{
    public function __construct(
        private readonly FileStorageService $fileStorageService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function upload(User $patient, UploadedFile $file, array $data): PharmacyPrescription
    {
        if (! $patient->hasRole(UserRole::Patient->value)) {
            throw new AuthorizationException('Only patients can upload pharmacy prescriptions.');
        }

        return DB::transaction(function () use ($patient, $file, $data): PharmacyPrescription {
            $pharmacy = Provider::query()
                ->whereKey($data['pharmacy_provider_id'])
                ->where('type', ProviderType::Pharmacy)
                ->publiclyVisible()
                ->firstOrFail();

            $uploadedFile = $this->fileStorageService->storePrivate(
                $file,
                FileCategory::Prescription,
                $patient,
                $pharmacy,
                ['pharmacy_provider_id' => $pharmacy->id],
            );

            $prescription = PharmacyPrescription::query()->create([
                'patient_user_id' => $patient->id,
                'pharmacy_provider_id' => $pharmacy->id,
                'uploaded_file_id' => $uploadedFile->id,
                'notes' => $data['notes'] ?? null,
                'metadata' => ['source' => 'pharmacy_marketplace'],
            ]);

            $this->auditLogService->log('pharmacy_prescription.uploaded', $prescription, $patient, metadata: [
                'uploaded_file_id' => $uploadedFile->id,
                'pharmacy_provider_id' => $pharmacy->id,
            ]);

            return $prescription->refresh()->load(['uploadedFile', 'pharmacy']);
        });
    }

    public function assertUsableForOrder(PharmacyPrescription $prescription, User $patient, int $pharmacyProviderId): void
    {
        if ((int) $prescription->patient_user_id !== (int) $patient->id || (int) $prescription->pharmacy_provider_id !== (int) $pharmacyProviderId) {
            throw ValidationException::withMessages([
                'prescription_id' => ['The selected prescription is not valid for this pharmacy order.'],
            ]);
        }
    }
}
