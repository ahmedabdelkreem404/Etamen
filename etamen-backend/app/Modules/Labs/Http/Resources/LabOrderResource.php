<?php

namespace App\Modules\Labs\Http\Resources;

use App\Modules\Labs\Domain\Enums\LabOrderPaymentStatus;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Payments\Http\Resources\PaymentStatusResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $canViewProviderFinancials = $this->canViewProviderFinancials($request);
        $status = $this->order_status;
        $paymentStatus = $this->payment_status;
        $nextAction = $this->nextAction($status, $paymentStatus);

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'patient_user_id' => $this->patient_user_id,
            'lab_provider_id' => $this->lab_provider_id,
            'payment_id' => $this->payment_id,
            'subtotal' => $this->subtotal,
            'discount_total' => $this->discount_total,
            'commission_amount' => $this->when($canViewProviderFinancials, $this->commission_amount),
            'provider_net_amount' => $this->when($canViewProviderFinancials, $this->provider_net_amount),
            'grand_total' => $this->grand_total,
            'currency' => $this->currency,
            'payment_status' => $this->payment_status->value,
            'order_status' => $this->order_status->value,
            'status_label_ar' => $this->statusLabelAr($status),
            'status_label_en' => $this->statusLabelEn($status),
            'payment_status_label_ar' => $this->paymentStatusLabelAr($paymentStatus),
            'payment_status_label_en' => $this->paymentStatusLabelEn($paymentStatus),
            'can_cancel' => $this->canPatientCancel($status, $paymentStatus),
            'can_pay' => $this->canPay($status, $paymentStatus),
            'can_upload_proof' => $this->canUploadProof($status, $paymentStatus),
            'can_view_result_metadata' => $this->canViewResultMetadata($status),
            'next_action_key' => $nextAction['key'],
            'next_action_label_ar' => $nextAction['label_ar'],
            'next_action_label_en' => $nextAction['label_en'],
            'sample_collection_method' => $this->sample_collection_method->value,
            'collection_address' => $this->collection_address,
            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'paid_at' => $this->paid_at?->toISOString(),
            'accepted_at' => $this->accepted_at?->toISOString(),
            'sample_collected_at' => $this->sample_collected_at?->toISOString(),
            'result_ready_at' => $this->result_ready_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'notes' => $this->notes,
            'items' => LabOrderItemResource::collection($this->whenLoaded('items')),
            'lab' => $this->whenLoaded('lab', fn () => [
                'id' => $this->lab?->id,
                'name_ar' => $this->lab?->name_ar,
                'name_en' => $this->lab?->name_en,
            ]),
            'results' => LabResultResource::collection($this->whenLoaded('results')),
            'payment' => new PaymentStatusResource($this->whenLoaded('payment')),
            'status_histories' => $this->whenLoaded('statusHistories', fn () => $this->statusHistories->map(fn ($history) => [
                'from_status' => $history->from_status,
                'to_status' => $history->to_status,
                'actor_id' => $history->actor_id,
                'reason' => $history->reason,
                'created_at' => $history->created_at?->toISOString(),
            ])->values()),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function canViewProviderFinancials(Request $request): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        if ($user->isPlatformAdmin()) {
            return true;
        }

        return $user->ownedProviders()
            ->whereKey($this->lab_provider_id)
            ->exists();
    }

    private function statusLabelAr(LabOrderStatus $status): string
    {
        return match ($status) {
            LabOrderStatus::LabReview => 'مراجعة المعمل',
            LabOrderStatus::Accepted => 'تم قبول الطلب',
            LabOrderStatus::AwaitingPayment => 'في انتظار الدفع',
            LabOrderStatus::Paid => 'تم الدفع',
            LabOrderStatus::SampleScheduled => 'تم جدولة العينة',
            LabOrderStatus::SampleCollected => 'تم جمع العينة',
            LabOrderStatus::Processing => 'جاري التحليل',
            LabOrderStatus::ResultReady => 'النتيجة جاهزة',
            LabOrderStatus::Completed => 'مكتمل',
            LabOrderStatus::Rejected => 'مرفوض',
            LabOrderStatus::Cancelled => 'ملغي',
        };
    }

    private function statusLabelEn(LabOrderStatus $status): string
    {
        return match ($status) {
            LabOrderStatus::LabReview => 'Lab review',
            LabOrderStatus::Accepted => 'Accepted',
            LabOrderStatus::AwaitingPayment => 'Awaiting payment',
            LabOrderStatus::Paid => 'Paid',
            LabOrderStatus::SampleScheduled => 'Sample scheduled',
            LabOrderStatus::SampleCollected => 'Sample collected',
            LabOrderStatus::Processing => 'Processing',
            LabOrderStatus::ResultReady => 'Result ready',
            LabOrderStatus::Completed => 'Completed',
            LabOrderStatus::Rejected => 'Rejected',
            LabOrderStatus::Cancelled => 'Cancelled',
        };
    }

    private function paymentStatusLabelAr(LabOrderPaymentStatus $status): string
    {
        return match ($status) {
            LabOrderPaymentStatus::Unpaid => 'غير مدفوع',
            LabOrderPaymentStatus::PendingPayment => 'في انتظار إثبات الدفع',
            LabOrderPaymentStatus::PendingPaymentReview => 'إثبات الدفع تحت مراجعة الأدمن',
            LabOrderPaymentStatus::Paid => 'مدفوع',
            LabOrderPaymentStatus::Failed => 'فشل الدفع',
            LabOrderPaymentStatus::Refunded => 'تم الاسترداد',
        };
    }

    private function paymentStatusLabelEn(LabOrderPaymentStatus $status): string
    {
        return match ($status) {
            LabOrderPaymentStatus::Unpaid => 'Unpaid',
            LabOrderPaymentStatus::PendingPayment => 'Waiting for payment proof',
            LabOrderPaymentStatus::PendingPaymentReview => 'Payment proof under admin review',
            LabOrderPaymentStatus::Paid => 'Paid',
            LabOrderPaymentStatus::Failed => 'Payment failed',
            LabOrderPaymentStatus::Refunded => 'Refunded',
        };
    }

    private function canPatientCancel(LabOrderStatus $status, LabOrderPaymentStatus $paymentStatus): bool
    {
        return in_array($status, [
            LabOrderStatus::LabReview,
            LabOrderStatus::Accepted,
            LabOrderStatus::AwaitingPayment,
        ], true) && in_array($paymentStatus, [
            LabOrderPaymentStatus::Unpaid,
            LabOrderPaymentStatus::PendingPayment,
            LabOrderPaymentStatus::Failed,
        ], true);
    }

    private function canPay(LabOrderStatus $status, LabOrderPaymentStatus $paymentStatus): bool
    {
        return in_array($status, [
            LabOrderStatus::Accepted,
            LabOrderStatus::AwaitingPayment,
        ], true) && in_array($paymentStatus, [
            LabOrderPaymentStatus::Unpaid,
            LabOrderPaymentStatus::Failed,
        ], true);
    }

    private function canUploadProof(LabOrderStatus $status, LabOrderPaymentStatus $paymentStatus): bool
    {
        return ! in_array($status, [
            LabOrderStatus::Rejected,
            LabOrderStatus::Cancelled,
            LabOrderStatus::Completed,
        ], true) && $paymentStatus === LabOrderPaymentStatus::PendingPayment;
    }

    private function canViewResultMetadata(LabOrderStatus $status): bool
    {
        $hasLoadedResults = $this->relationLoaded('results') && $this->results->isNotEmpty();

        return $hasLoadedResults || in_array($status, [
            LabOrderStatus::ResultReady,
            LabOrderStatus::Completed,
        ], true);
    }

    private function nextAction(LabOrderStatus $status, LabOrderPaymentStatus $paymentStatus): array
    {
        if (in_array($status, [LabOrderStatus::Rejected, LabOrderStatus::Cancelled], true)) {
            return ['key' => 'closed', 'label_ar' => 'الطلب مغلق', 'label_en' => 'Order closed'];
        }

        if ($this->canUploadProof($status, $paymentStatus)) {
            return ['key' => 'upload_proof', 'label_ar' => 'ارفع إثبات الدفع', 'label_en' => 'Upload payment proof'];
        }

        if ($this->canPay($status, $paymentStatus)) {
            return ['key' => 'pay', 'label_ar' => 'اختار طريقة الدفع', 'label_en' => 'Choose payment method'];
        }

        if ($paymentStatus === LabOrderPaymentStatus::PendingPaymentReview) {
            return ['key' => 'wait_admin_review', 'label_ar' => 'الدفع في انتظار مراجعة الأدمن', 'label_en' => 'Payment is under admin review'];
        }

        return match ($status) {
            LabOrderStatus::LabReview => ['key' => 'wait_provider_review', 'label_ar' => 'المعمل بيراجع الطلب', 'label_en' => 'Lab is reviewing the order'],
            LabOrderStatus::Paid, LabOrderStatus::SampleScheduled => ['key' => 'wait_sample', 'label_ar' => 'في انتظار جمع العينة', 'label_en' => 'Waiting for sample collection'],
            LabOrderStatus::SampleCollected, LabOrderStatus::Processing => ['key' => 'wait_processing', 'label_ar' => 'جاري التحليل', 'label_en' => 'Processing'],
            LabOrderStatus::ResultReady => ['key' => 'view_result_metadata', 'label_ar' => 'اعرض بيانات النتيجة', 'label_en' => 'View result metadata'],
            LabOrderStatus::Completed => ['key' => 'completed', 'label_ar' => 'الطلب مكتمل', 'label_en' => 'Order completed'],
            LabOrderStatus::Rejected, LabOrderStatus::Cancelled => ['key' => 'closed', 'label_ar' => 'الطلب مغلق', 'label_en' => 'Order closed'],
            default => ['key' => 'none', 'label_ar' => 'لا يوجد إجراء مطلوب الآن', 'label_en' => 'No action needed now'],
        };
    }
}
