<?php

namespace App\Modules\Providers\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Providers\Application\Services\ProviderProfileService;
use App\Modules\Providers\Http\Requests\ProviderServiceRequest;
use App\Modules\Providers\Http\Resources\ProviderServiceResource;
use App\Modules\Providers\Infrastructure\Models\ProviderService;
use Illuminate\Http\Request;

class ProviderServiceController extends ApiController
{
    public function __construct(private readonly ProviderProfileService $providerProfileService) {}

    public function index(Request $request)
    {
        $provider = $this->providerProfileService->currentProviderFor($request->user());

        return $this->success(
            ProviderServiceResource::collection($provider->services()->with('category')->latest()->get()),
            'Provider services.',
        );
    }

    public function store(ProviderServiceRequest $request)
    {
        $provider = $this->providerProfileService->currentProviderFor($request->user());
        $service = $provider->services()->create($request->validated());

        return $this->success(
            new ProviderServiceResource($service->load('category')),
            'Provider service created.',
            201,
        );
    }

    public function update(ProviderServiceRequest $request, ProviderService $service)
    {
        $provider = $this->providerProfileService->currentProviderFor($request->user());
        abort_if((int) $service->provider_id !== (int) $provider->id, 403);

        $service->update($request->validated());

        return $this->success(
            new ProviderServiceResource($service->refresh()->load('category')),
            'Provider service updated.',
        );
    }
}
