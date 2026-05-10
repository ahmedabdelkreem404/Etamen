<?php

namespace App\Modules\Providers\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Providers\Application\Services\ProviderWorkspaceService;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class ProviderWorkspaceDashboardController extends ApiController
{
    public function __construct(private readonly ProviderWorkspaceService $workspaceService) {}

    public function show(Request $request, Provider $provider)
    {
        try {
            return $this->success(
                $this->workspaceService->dashboardFor($request->user(), $provider),
                'Provider workspace dashboard.',
            );
        } catch (AuthorizationException $exception) {
            return $this->error($exception->getMessage(), status: 403);
        }
    }
}
