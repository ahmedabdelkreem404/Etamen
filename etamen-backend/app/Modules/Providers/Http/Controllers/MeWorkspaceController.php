<?php

namespace App\Modules\Providers\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Providers\Application\Services\ProviderWorkspaceService;
use Illuminate\Http\Request;

class MeWorkspaceController extends ApiController
{
    public function __construct(private readonly ProviderWorkspaceService $workspaceService) {}

    public function index(Request $request)
    {
        return $this->success(
            $this->workspaceService->workspacesFor($request->user()),
            'Available workspaces.',
        );
    }
}
