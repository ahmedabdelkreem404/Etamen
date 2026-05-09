<?php

namespace App\Modules\Radiology\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\MedicalFiles\Application\Services\FileStorageService;
use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use App\Modules\Radiology\Domain\Enums\RadiologyOrderStatus;
use App\Modules\Radiology\Domain\Enums\RadiologyResultType;
use App\Modules\Radiology\Infrastructure\Models\RadiologyOrder;
use App\Modules\Radiology\Infrastructure\Models\RadiologyResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RadiologyResultService
{
    public function __construct(
        private readonly FileStorageService $fileStorageService,
        private readonly RadiologyOrderStatusService $statusService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function upload(User $actor, RadiologyOrder $order, UploadedFile $file, array $data): RadiologyResult
    {
        return DB::transaction(function () use ($actor, $order, $file, $data): RadiologyResult {
            $order = RadiologyOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! in_array($order->status, [
                RadiologyOrderStatus::Paid,
                RadiologyOrderStatus::Accepted,
                RadiologyOrderStatus::InProgress,
                RadiologyOrderStatus::ResultReady,
                RadiologyOrderStatus::Completed,
            ], true)) {
                throw ValidationException::withMessages([
                    'order' => ['Radiology results can only be uploaded after payment or acceptance.'],
                ]);
            }

            $uploadedFile = $this->fileStorageService->storePrivate(
                $file,
                FileCategory::MedicalReport,
                $actor,
                $order,
                ['radiology_order_id' => $order->id, 'category' => 'radiology_result'],
            );

            $result = RadiologyResult::query()->create([
                'radiology_order_id' => $order->id,
                'uploaded_file_id' => $uploadedFile->id,
                'uploaded_by' => $actor->id,
                'result_type' => RadiologyResultType::from($data['result_type'] ?? RadiologyResultType::ReportPdf->value),
                'title_ar' => $data['title_ar'] ?? null,
                'title_en' => $data['title_en'] ?? null,
                'notes_ar' => $data['notes_ar'] ?? null,
                'notes_en' => $data['notes_en'] ?? null,
                'is_visible_to_patient' => (bool) ($data['is_visible_to_patient'] ?? false),
                'uploaded_at' => now(),
            ]);

            if (
                $result->is_visible_to_patient
                && in_array($order->status, [RadiologyOrderStatus::Paid, RadiologyOrderStatus::Accepted, RadiologyOrderStatus::InProgress], true)
            ) {
                $this->statusService->transition(
                    $order,
                    RadiologyOrderStatus::ResultReady,
                    $actor,
                    'radiology_order.result_ready',
                    'Radiology result uploaded.',
                    ['result_id' => $result->id],
                );
            }

            $this->auditLogService->log('radiology_result.uploaded', $result, $actor, metadata: [
                'radiology_order_id' => $order->id,
                'uploaded_file_id' => $uploadedFile->id,
                'is_visible_to_patient' => $result->is_visible_to_patient,
            ]);

            return $result->refresh()->load(['file', 'order']);
        });
    }
}
