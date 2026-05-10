<?php

namespace App\Modules\AdminOperations\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\AdminOperations\Http\Resources\SupportTicketResource;
use App\Modules\AdminOperations\Infrastructure\Models\SupportTicket;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportTicketController extends ApiController
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function index(Request $request)
    {
        $providerIds = $this->activeProviderIds($request);

        $tickets = SupportTicket::query()
            ->with(['provider', 'user'])
            ->where(function ($query) use ($request, $providerIds): void {
                $query->where('user_id', $request->user()->id);

                if ($providerIds !== []) {
                    $query->orWhereIn('provider_id', $providerIds);
                }
            })
            ->latest()
            ->paginate($this->perPage($request, 30));

        return $this->success(SupportTicketResource::collection($tickets), 'Support tickets.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'provider_id' => ['nullable', 'integer', 'exists:providers,id'],
            'category' => ['required', Rule::in(SupportTicket::categories())],
            'subject' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:4000'],
            'priority' => ['nullable', Rule::in(SupportTicket::priorities())],
            'source' => ['nullable', 'string', 'max:50'],
        ]);

        $ticket = SupportTicket::query()->create([
            'user_id' => $request->user()->id,
            'provider_id' => $data['provider_id'] ?? null,
            'category' => $data['category'],
            'subject' => $data['subject'],
            'description' => $data['description'],
            'priority' => $data['priority'] ?? 'normal',
            'source' => $data['source'] ?? 'app',
            'status' => SupportTicket::STATUS_OPEN,
        ]);

        $senderType = $this->isActiveStaffForProvider($request, $ticket->provider_id) ? 'provider' : 'patient';
        $ticket->messages()->create([
            'sender_user_id' => $request->user()->id,
            'sender_type' => $senderType,
            'message' => $data['description'],
            'is_internal_note' => false,
        ]);

        $this->auditLogService->log('support.ticket.created', $ticket, $request->user(), metadata: [
            'category' => $ticket->category,
            'provider_id' => $ticket->provider_id,
        ]);

        return $this->success(new SupportTicketResource($ticket->load(['provider', 'user', 'messages.sender'])), 'Support ticket created.', 201);
    }

    public function show(Request $request, SupportTicket $ticket)
    {
        $this->authorizeTicketAccess($request, $ticket);

        return $this->success(new SupportTicketResource($ticket->load(['provider', 'user', 'messages.sender'])), 'Support ticket details.');
    }

    public function message(Request $request, SupportTicket $ticket)
    {
        $this->authorizeTicketAccess($request, $ticket);

        $data = $request->validate(['message' => ['required', 'string', 'max:4000']]);
        $senderType = $this->isActiveStaffForProvider($request, $ticket->provider_id) ? 'provider' : 'patient';

        $ticket->messages()->create([
            'sender_user_id' => $request->user()->id,
            'sender_type' => $senderType,
            'message' => $data['message'],
            'is_internal_note' => false,
        ]);

        $ticket->update(['status' => SupportTicket::STATUS_PENDING_ADMIN]);
        $this->auditLogService->log('support.ticket.replied', $ticket, $request->user(), metadata: [
            'sender_type' => $senderType,
        ]);

        return $this->success(new SupportTicketResource($ticket->refresh()->load(['provider', 'user', 'messages.sender'])), 'Support ticket message added.');
    }

    private function authorizeTicketAccess(Request $request, SupportTicket $ticket): void
    {
        if ((int) $ticket->user_id === (int) $request->user()->id) {
            return;
        }

        if ($this->isActiveStaffForProvider($request, $ticket->provider_id)) {
            return;
        }

        abort(403, 'You cannot access this support ticket.');
    }

    private function isActiveStaffForProvider(Request $request, ?int $providerId): bool
    {
        if (! $providerId) {
            return false;
        }

        return $request->user()
            ->providerStaffRecords()
            ->where('provider_id', $providerId)
            ->where('status', 'active')
            ->exists();
    }

    private function activeProviderIds(Request $request): array
    {
        return $request->user()
            ->providerStaffRecords()
            ->where('status', 'active')
            ->pluck('provider_id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }
}
