<?php

namespace App\Modules\Labs\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Labs\Domain\Enums\LabOrderPaymentStatus;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Domain\Enums\LabResultStatus;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use App\Modules\Labs\Infrastructure\Models\LabResult;
use App\Modules\MedicalFiles\Application\Services\FileStorageService;
use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LabResultService
{
    public function __construct(
        private readonly FileStorageService $fileStorageService,
        private readonly LabOrderStatusService $statusService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function upload(User $actor, LabOrder $order, UploadedFile $file, array $data): LabResult
    {
        return DB::transaction(function () use ($actor, $order, $file, $data): LabResult {
            $order = LabOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->payment_status !== LabOrderPaymentStatus::Paid) {
                throw ValidationException::withMessages([
                    'order' => ['Lab results can only be uploaded for paid orders.'],
                ]);
            }

            if (! in_array($order->order_status, [
                LabOrderStatus::Paid,
                LabOrderStatus::SampleScheduled,
                LabOrderStatus::SampleCollected,
                LabOrderStatus::Processing,
                LabOrderStatus::ResultReady,
                LabOrderStatus::Completed,
            ], true)) {
                throw ValidationException::withMessages([
                    'order' => ['This lab order cannot receive a result in its current status.'],
                ]);
            }

            $uploadedFile = $this->fileStorageService->storePrivate(
                $file,
                FileCategory::LabResult,
                $actor,
                $order,
                ['lab_order_id' => $order->id],
            );

            $result = LabResult::query()->create([
                'order_id' => $order->id,
                'uploaded_by' => $actor->id,
                'file_id' => $uploadedFile->id,
                'title_ar' => $data['title_ar'] ?? null,
                'title_en' => $data['title_en'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => LabResultStatus::VisibleToPatient,
            ]);

            if (! in_array($order->order_status, [LabOrderStatus::ResultReady, LabOrderStatus::Completed], true)) {
                $this->statusService->transition(
                    $order,
                    LabOrderStatus::ResultReady,
                    $actor,
                    'lab_order.result_ready',
                    'Lab result uploaded.',
                    ['result_id' => $result->id],
                );
            }

            $this->auditLogService->log('lab_result.uploaded', $result, $actor, metadata: [
                'lab_order_id' => $order->id,
                'file_id' => $uploadedFile->id,
            ]);

            return $result->refresh()->load(['file', 'order']);
        });
    }
}
