<?php

namespace App\Modules\Radiology\Http\Resources;

use App\Modules\Payments\Http\Resources\PaymentStatusResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RadiologyOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $canViewProviderNotes = $this->canViewProviderNotes($request);

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'patient_user_id' => $this->patient_user_id,
            'provider_id' => $this->provider_id,
            'provider' => $this->whenLoaded('provider', fn () => [
                'id' => $this->provider->id,
                'name_ar' => $this->provider->name_ar,
                'name_en' => $this->provider->name_en,
                'type' => $this->provider->type->value,
            ]),
            'branch_id' => $this->branch_id,
            'branch' => $this->whenLoaded('branch', fn () => $this->branch ? [
                'id' => $this->branch->id,
                'name_ar' => $this->branch->name_ar,
                'name_en' => $this->branch->name_en,
                'address_ar' => $this->branch->address_ar,
                'address_en' => $this->branch->address_en,
                'address_line_1' => $this->branch->address_line_1,
                'latitude' => $this->branch->latitude,
                'longitude' => $this->branch->longitude,
            ] : null),
            'status' => $this->status->value,
            'status_label_ar' => $this->statusLabelAr(),
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'payment_id' => $this->payment_id,
            'payment' => new PaymentStatusResource($this->whenLoaded('payment')),
            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'patient_notes' => $this->patient_notes,
            'provider_notes' => $this->when($canViewProviderNotes, $this->provider_notes),
            'accepted_at' => $this->accepted_at?->toISOString(),
            'rejected_at' => $this->rejected_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            'items' => RadiologyOrderItemResource::collection($this->whenLoaded('items')),
            'results' => RadiologyResultResource::collection($this->whenLoaded('results')),
            'status_histories' => $this->whenLoaded('statusHistories', fn () => $this->statusHistories->map(fn ($history) => [
                'from_status' => $history->from_status,
                'to_status' => $history->to_status->value,
                'changed_by' => $history->changed_by,
                'reason' => $history->reason,
                'created_at' => $history->created_at?->toISOString(),
            ])->values()),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function canViewProviderNotes(Request $request): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        return $user->isPlatformAdmin()
            || $user->ownedProviders()->whereKey($this->provider_id)->exists();
    }

    private function statusLabelAr(): string
    {
        return match ($this->status->value) {
            'pending_payment' => 'في انتظار الدفع',
            'pending_payment_review' => 'جاري مراجعة الدفع',
            'paid' => 'تم الدفع',
            'accepted' => 'تم قبول الطلب',
            'in_progress' => 'قيد التنفيذ',
            'result_ready' => 'النتيجة جاهزة',
            'completed' => 'مكتمل',
            'cancelled_by_patient' => 'ملغي من المريض',
            'cancelled_by_provider' => 'ملغي من المركز',
            'rejected' => 'مرفوض',
            default => $this->status->value,
        };
    }
}
