<?php

namespace App\Modules\Pharmacies\Http\Resources;

use App\Modules\Payments\Http\Resources\PaymentStatusResource;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderPaymentStatus;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyOrderResource extends JsonResource
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
            'pharmacy_provider_id' => $this->pharmacy_provider_id,
            'prescription_id' => $this->prescription_id,
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
            'can_view_result_metadata' => false,
            'next_action_key' => $nextAction['key'],
            'next_action_label_ar' => $nextAction['label_ar'],
            'next_action_label_en' => $nextAction['label_en'],
            'delivery_method' => $this->delivery_method->value,
            'delivery_address' => $this->delivery_address,
            'notes' => $this->notes,
            'paid_at' => $this->paid_at?->toISOString(),
            'delivered_at' => $this->delivered_at?->toISOString(),
            'stock_reserved_at' => $this->stock_reserved_at?->toISOString(),
            'stock_released_at' => $this->stock_released_at?->toISOString(),
            'items' => PharmacyOrderItemResource::collection($this->whenLoaded('items')),
            'pharmacy' => $this->whenLoaded('pharmacy', fn () => [
                'id' => $this->pharmacy?->id,
                'name_ar' => $this->pharmacy?->name_ar,
                'name_en' => $this->pharmacy?->name_en,
            ]),
            'prescription' => new PharmacyPrescriptionResource($this->whenLoaded('prescription')),
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
            ->whereKey($this->pharmacy_provider_id)
            ->exists();
    }

    private function statusLabelAr(PharmacyOrderStatus $status): string
    {
        return match ($status) {
            PharmacyOrderStatus::Pending, PharmacyOrderStatus::PharmacyReview => 'في انتظار مراجعة الصيدلية',
            PharmacyOrderStatus::Accepted => 'تم قبول الطلب',
            PharmacyOrderStatus::AwaitingPayment => 'في انتظار الدفع',
            PharmacyOrderStatus::Paid => 'تم الدفع',
            PharmacyOrderStatus::Preparing => 'تحت التجهيز',
            PharmacyOrderStatus::ReadyForPickup => 'جاهز للاستلام',
            PharmacyOrderStatus::OutForDelivery => 'في التوصيل',
            PharmacyOrderStatus::Delivered => 'مكتمل',
            PharmacyOrderStatus::Rejected => 'مرفوض',
            PharmacyOrderStatus::Cancelled => 'ملغي',
        };
    }

    private function statusLabelEn(PharmacyOrderStatus $status): string
    {
        return match ($status) {
            PharmacyOrderStatus::Pending, PharmacyOrderStatus::PharmacyReview => 'Waiting for pharmacy review',
            PharmacyOrderStatus::Accepted => 'Accepted',
            PharmacyOrderStatus::AwaitingPayment => 'Awaiting payment',
            PharmacyOrderStatus::Paid => 'Paid',
            PharmacyOrderStatus::Preparing => 'Preparing',
            PharmacyOrderStatus::ReadyForPickup => 'Ready',
            PharmacyOrderStatus::OutForDelivery => 'Out for delivery',
            PharmacyOrderStatus::Delivered => 'Completed',
            PharmacyOrderStatus::Rejected => 'Rejected',
            PharmacyOrderStatus::Cancelled => 'Cancelled',
        };
    }

    private function paymentStatusLabelAr(PharmacyOrderPaymentStatus $status): string
    {
        return match ($status) {
            PharmacyOrderPaymentStatus::Unpaid => 'غير مدفوع',
            PharmacyOrderPaymentStatus::PendingPayment => 'في انتظار إثبات الدفع',
            PharmacyOrderPaymentStatus::PendingPaymentReview => 'إثبات الدفع تحت مراجعة الأدمن',
            PharmacyOrderPaymentStatus::Paid => 'مدفوع',
            PharmacyOrderPaymentStatus::Rejected => 'إثبات الدفع مرفوض',
            PharmacyOrderPaymentStatus::Failed => 'فشل الدفع',
            PharmacyOrderPaymentStatus::Refunded => 'تم الاسترداد',
        };
    }

    private function paymentStatusLabelEn(PharmacyOrderPaymentStatus $status): string
    {
        return match ($status) {
            PharmacyOrderPaymentStatus::Unpaid => 'Unpaid',
            PharmacyOrderPaymentStatus::PendingPayment => 'Waiting for payment proof',
            PharmacyOrderPaymentStatus::PendingPaymentReview => 'Payment proof under admin review',
            PharmacyOrderPaymentStatus::Paid => 'Paid',
            PharmacyOrderPaymentStatus::Rejected => 'Payment proof rejected',
            PharmacyOrderPaymentStatus::Failed => 'Payment failed',
            PharmacyOrderPaymentStatus::Refunded => 'Refunded',
        };
    }

    private function canPatientCancel(PharmacyOrderStatus $status, PharmacyOrderPaymentStatus $paymentStatus): bool
    {
        return in_array($status, [
            PharmacyOrderStatus::Pending,
            PharmacyOrderStatus::PharmacyReview,
            PharmacyOrderStatus::Accepted,
            PharmacyOrderStatus::AwaitingPayment,
        ], true) && in_array($paymentStatus, [
            PharmacyOrderPaymentStatus::Unpaid,
            PharmacyOrderPaymentStatus::PendingPayment,
            PharmacyOrderPaymentStatus::Rejected,
            PharmacyOrderPaymentStatus::Failed,
        ], true);
    }

    private function canPay(PharmacyOrderStatus $status, PharmacyOrderPaymentStatus $paymentStatus): bool
    {
        return in_array($status, [
            PharmacyOrderStatus::Accepted,
            PharmacyOrderStatus::AwaitingPayment,
        ], true) && in_array($paymentStatus, [
            PharmacyOrderPaymentStatus::Unpaid,
            PharmacyOrderPaymentStatus::Rejected,
            PharmacyOrderPaymentStatus::Failed,
        ], true);
    }

    private function canUploadProof(PharmacyOrderStatus $status, PharmacyOrderPaymentStatus $paymentStatus): bool
    {
        return ! in_array($status, [
            PharmacyOrderStatus::Rejected,
            PharmacyOrderStatus::Cancelled,
            PharmacyOrderStatus::Delivered,
        ], true) && in_array($paymentStatus, [
            PharmacyOrderPaymentStatus::PendingPayment,
            PharmacyOrderPaymentStatus::Rejected,
        ], true);
    }

    private function nextAction(PharmacyOrderStatus $status, PharmacyOrderPaymentStatus $paymentStatus): array
    {
        if (in_array($status, [PharmacyOrderStatus::Rejected, PharmacyOrderStatus::Cancelled], true)) {
            return ['key' => 'closed', 'label_ar' => 'الطلب مغلق', 'label_en' => 'Order closed'];
        }

        if ($this->canUploadProof($status, $paymentStatus)) {
            return ['key' => 'upload_proof', 'label_ar' => 'ارفع إثبات الدفع', 'label_en' => 'Upload payment proof'];
        }

        if ($this->canPay($status, $paymentStatus)) {
            return ['key' => 'pay', 'label_ar' => 'اختار طريقة الدفع', 'label_en' => 'Choose payment method'];
        }

        if ($paymentStatus === PharmacyOrderPaymentStatus::PendingPaymentReview) {
            return ['key' => 'wait_admin_review', 'label_ar' => 'الدفع في انتظار مراجعة الأدمن', 'label_en' => 'Payment is under admin review'];
        }

        return match ($status) {
            PharmacyOrderStatus::Pending, PharmacyOrderStatus::PharmacyReview => ['key' => 'wait_provider_review', 'label_ar' => 'الصيدلية بتراجع الطلب', 'label_en' => 'Pharmacy is reviewing the order'],
            PharmacyOrderStatus::Paid, PharmacyOrderStatus::Preparing => ['key' => 'wait_preparing', 'label_ar' => 'الطلب تحت التجهيز', 'label_en' => 'Order is being prepared'],
            PharmacyOrderStatus::ReadyForPickup => ['key' => 'ready', 'label_ar' => 'الطلب جاهز للاستلام', 'label_en' => 'Order is ready'],
            PharmacyOrderStatus::OutForDelivery => ['key' => 'out_for_delivery', 'label_ar' => 'الطلب في التوصيل', 'label_en' => 'Order is out for delivery'],
            PharmacyOrderStatus::Delivered => ['key' => 'completed', 'label_ar' => 'الطلب مكتمل', 'label_en' => 'Order completed'],
            PharmacyOrderStatus::Rejected, PharmacyOrderStatus::Cancelled => ['key' => 'closed', 'label_ar' => 'الطلب مغلق', 'label_en' => 'Order closed'],
            default => ['key' => 'none', 'label_ar' => 'لا يوجد إجراء مطلوب الآن', 'label_en' => 'No action needed now'],
        };
    }
}
