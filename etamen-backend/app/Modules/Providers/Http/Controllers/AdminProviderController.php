<?php

namespace App\Modules\Providers\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Providers\Application\Services\ProviderApprovalService;
use App\Modules\Providers\Domain\Enums\ApprovalRequestStatus;
use App\Modules\Providers\Domain\Enums\ProviderDocumentStatus;
use App\Modules\Providers\Domain\Enums\ProviderDocumentVisibility;
use App\Modules\Providers\Http\Requests\AdminProviderContractRequest;
use App\Modules\Providers\Http\Requests\AdminProviderDecisionRequest;
use App\Modules\Providers\Http\Resources\ProviderDocumentResource;
use App\Modules\Providers\Http\Resources\ProviderResource;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderDocument;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminProviderController extends ApiController
{
    public function __construct(
        private readonly ProviderApprovalService $approvalService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function index()
    {
        return $this->success(
            ProviderResource::collection(Provider::query()->with($this->adminProviderRelations())->latest()->get()),
            'Providers.',
        );
    }

    public function pendingApprovals()
    {
        return $this->success(
            ProviderResource::collection(
                Provider::query()
                    ->whereHas('approvalRequests', fn ($query) => $query->where('status', ApprovalRequestStatus::Pending))
                    ->with($this->adminProviderRelations())
                    ->latest()
                    ->get(),
            ),
            'Providers pending approval.',
        );
    }

    public function approve(AdminProviderDecisionRequest $request, Provider $provider)
    {
        $this->authorize('approve', $provider);

        return $this->success(
            new ProviderResource($this->approvalService->approve($provider, $request->user(), $request->validated('notes'))),
            'Provider approved.',
        );
    }

    public function reject(AdminProviderDecisionRequest $request, Provider $provider)
    {
        $this->authorize('reject', $provider);

        return $this->success(
            new ProviderResource($this->approvalService->reject($provider, $request->user(), $request->validated('notes'))),
            'Provider rejected.',
        );
    }

    public function requestChanges(AdminProviderDecisionRequest $request, Provider $provider)
    {
        $this->authorize('requestChanges', $provider);

        return $this->success(
            new ProviderResource($this->approvalService->requestChanges($provider, $request->user(), $request->validated('notes'))),
            'Provider changes requested.',
        );
    }

    public function suspend(AdminProviderDecisionRequest $request, Provider $provider)
    {
        $this->authorize('suspend', $provider);

        return $this->success(
            new ProviderResource($this->approvalService->suspend($provider, $request->user(), $request->validated('notes'))),
            'Provider suspended.',
        );
    }

    public function reactivate(Request $request, Provider $provider)
    {
        $this->authorize('reactivate', $provider);

        return $this->success(
            new ProviderResource($this->approvalService->reactivate($provider, $request->user())),
            'Provider reactivated.',
        );
    }

    public function approveDocument(Request $request, ProviderDocument $document)
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string'],
            'visibility' => ['nullable', Rule::in(ProviderDocumentVisibility::values())],
        ]);

        $before = $document->getAttributes();
        $document->update([
            'status' => ProviderDocumentStatus::Approved,
            'visibility' => $data['visibility'] ?? $document->visibility,
            'notes' => $data['notes'] ?? $document->notes,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $this->auditLogService->log('provider_document.approved', $document, $request->user(), before: $before, after: $document->getAttributes());

        return $this->success(new ProviderDocumentResource($document->refresh()->load('file')), 'Provider document approved.');
    }

    public function rejectDocument(Request $request, ProviderDocument $document)
    {
        $data = $request->validate([
            'notes' => ['required', 'string'],
        ]);

        $before = $document->getAttributes();
        $document->update([
            'status' => ProviderDocumentStatus::Rejected,
            'visibility' => ProviderDocumentVisibility::AdminOnly,
            'notes' => $data['notes'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $this->auditLogService->log('provider_document.rejected', $document, $request->user(), before: $before, after: $document->getAttributes());

        return $this->success(new ProviderDocumentResource($document->refresh()->load('file')), 'Provider document rejected.');
    }

    public function storeContract(AdminProviderContractRequest $request, Provider $provider)
    {
        $contract = $provider->contracts()->create($request->validated());

        $this->auditLogService->log('provider_contract.created', $contract, $request->user(), metadata: ['provider_id' => $provider->id]);

        return $this->success([
            'id' => $contract->id,
            'provider_id' => $contract->provider_id,
            'contract_type' => $contract->contract_type->value,
            'settlement_cycle' => $contract->settlement_cycle->value,
            'pay_at_branch_allowed' => $contract->pay_at_branch_allowed,
            'online_payment_required' => $contract->online_payment_required,
            'status' => $contract->status->value,
        ], 'Provider contract created.', 201);
    }

    private function adminProviderRelations(): array
    {
        return [
            'doctorProfile.specialties',
            'pharmacyProfile',
            'labProfile',
            'hospitalProfile',
            'clinicProfile',
            'medicalCenterProfile',
            'radiologyProfile',
            'gymProfile',
            'coachProfile',
            'physiotherapyProfile',
            'homeHealthcareProfile',
            'bookingSettings',
            'activeContract',
            'branches.city',
            'branches.area',
            'publicDocuments.file',
            'publicServices.category',
            'approvalRequests',
        ];
    }
}
