<?php

namespace App\Modules\AdminOperations\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminProviderReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'phone' => $this->phone,
            'email' => $this->email,
            'status' => $this->status->value,
            'is_active' => (bool) $this->is_active,
            'owner' => $this->whenLoaded('owner', fn () => $this->owner ? [
                'id' => $this->owner->id,
                'name' => $this->owner->name,
                'email' => $this->owner->email,
            ] : null),
            'approval_requests' => $this->whenLoaded('approvalRequests', fn () => $this->approvalRequests->map(fn ($request) => [
                'id' => $request->id,
                'status' => $request->status->value,
                'notes' => $request->notes,
                'review_notes' => $request->review_notes,
                'reviewed_at' => $request->reviewed_at?->toISOString(),
                'created_at' => $request->created_at?->toISOString(),
            ])->values()),
            'document_checklist' => $this->whenLoaded('documents', fn () => $this->documents->map(fn ($document) => [
                'id' => $document->id,
                'document_type' => $document->document_type,
                'status' => $document->status->value,
                'visibility' => $document->visibility->value,
                'uploaded_at' => $document->created_at?->toISOString(),
                'verified_at' => $document->reviewed_at?->toISOString(),
            ])->values()),
            'created_at' => $this->created_at?->toISOString(),
            'approved_at' => $this->approved_at?->toISOString(),
            'rejected_at' => $this->rejected_at?->toISOString(),
            'suspended_at' => $this->suspended_at?->toISOString(),
        ];
    }
}
