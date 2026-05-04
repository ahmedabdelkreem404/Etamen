<?php

namespace App\Modules\Providers\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Providers\Application\Services\ProviderApprovalService;
use App\Modules\Providers\Domain\Enums\ApprovalRequestStatus;
use App\Modules\Providers\Http\Requests\AdminProviderDecisionRequest;
use App\Modules\Providers\Http\Resources\ProviderResource;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Http\Request;

class AdminProviderController extends ApiController
{
    public function __construct(private readonly ProviderApprovalService $approvalService) {}

    public function index()
    {
        return $this->success(
            ProviderResource::collection(Provider::query()->with(['doctorProfile.specialties', 'pharmacyProfile', 'labProfile', 'approvalRequests'])->latest()->get()),
            'Providers.',
        );
    }

    public function pendingApprovals()
    {
        return $this->success(
            ProviderResource::collection(
                Provider::query()
                    ->whereHas('approvalRequests', fn ($query) => $query->where('status', ApprovalRequestStatus::Pending))
                    ->with(['doctorProfile.specialties', 'pharmacyProfile', 'labProfile', 'approvalRequests'])
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
}
