<?php

namespace App\Modules\AdminOperations\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportTicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isAdmin = (bool) $request->user()?->isPlatformAdmin();

        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number,
            'category' => $this->category,
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'source' => $this->source,
            'user' => $this->whenLoaded('user', fn () => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ] : null),
            'provider' => $this->whenLoaded('provider', fn () => $this->provider ? [
                'id' => $this->provider->id,
                'type' => $this->provider->type->value,
                'name_ar' => $this->provider->name_ar,
                'name_en' => $this->provider->name_en,
            ] : null),
            'assigned_admin' => $this->when($isAdmin && $this->relationLoaded('assignedAdmin'), fn () => $this->assignedAdmin ? [
                'id' => $this->assignedAdmin->id,
                'name' => $this->assignedAdmin->name,
                'email' => $this->assignedAdmin->email,
            ] : null),
            'messages' => $this->whenLoaded('messages', fn () => $this->messages
                ->filter(fn ($message): bool => $isAdmin || ! $message->is_internal_note)
                ->map(fn ($message) => [
                    'id' => $message->id,
                    'sender_type' => $message->sender_type,
                    'sender' => $message->relationLoaded('sender') && $message->sender ? [
                        'id' => $message->sender->id,
                        'name' => $message->sender->name,
                    ] : null,
                    'message' => $message->message,
                    'is_internal_note' => (bool) $message->is_internal_note,
                    'created_at' => $message->created_at?->toISOString(),
                ])->values()),
            'closed_at' => $this->closed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
