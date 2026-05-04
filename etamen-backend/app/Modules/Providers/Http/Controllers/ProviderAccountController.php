<?php

namespace App\Modules\Providers\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Providers\Application\Services\ProviderBranchService;
use App\Modules\Providers\Application\Services\ProviderDocumentService;
use App\Modules\Providers\Application\Services\ProviderProfileService;
use App\Modules\Providers\Http\Requests\ProviderBranchRequest;
use App\Modules\Providers\Http\Requests\ProviderDocumentRequest;
use App\Modules\Providers\Http\Requests\UpdateProviderProfileRequest;
use App\Modules\Providers\Http\Resources\ProviderBranchResource;
use App\Modules\Providers\Http\Resources\ProviderDocumentResource;
use App\Modules\Providers\Http\Resources\ProviderResource;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;
use Illuminate\Http\Request;

class ProviderAccountController extends ApiController
{
    public function __construct(
        private readonly ProviderProfileService $providerProfileService,
        private readonly ProviderBranchService $branchService,
        private readonly ProviderDocumentService $documentService,
    ) {}

    public function me(Request $request)
    {
        return $this->success(
            new ProviderResource($this->providerProfileService->currentProviderFor($request->user())),
            'Current provider.',
        );
    }

    public function updateProfile(UpdateProviderProfileRequest $request)
    {
        $provider = $this->providerProfileService->updateOwnedProvider($request->user(), $request->validated());

        return $this->success(new ProviderResource($provider), 'Provider profile updated.');
    }

    public function branches(Request $request)
    {
        $provider = $this->providerProfileService->currentProviderFor($request->user());

        return $this->success(
            ProviderBranchResource::collection($provider->branches()->with(['city', 'area'])->get()),
            'Provider branches.',
        );
    }

    public function createBranch(ProviderBranchRequest $request)
    {
        $branch = $this->branchService->createForCurrentProvider($request->user(), $request->validated());

        return $this->success(new ProviderBranchResource($branch->load(['city', 'area'])), 'Provider branch created.', 201);
    }

    public function updateBranch(ProviderBranchRequest $request, ProviderBranch $branch)
    {
        $this->authorize('update', $branch);

        $branch = $this->branchService->update($request->user(), $branch, $request->validated());

        return $this->success(new ProviderBranchResource($branch->load(['city', 'area'])), 'Provider branch updated.');
    }

    public function uploadDocument(ProviderDocumentRequest $request)
    {
        $document = $this->documentService->upload(
            $request->user(),
            $request->file('file'),
            $request->validated('document_type'),
            $request->validated('notes'),
        );

        return $this->success(new ProviderDocumentResource($document), 'Provider document uploaded.', 201);
    }
}
