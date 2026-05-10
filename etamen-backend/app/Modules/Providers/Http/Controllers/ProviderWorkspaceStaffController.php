<?php

namespace App\Modules\Providers\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Providers\Application\Services\ProviderWorkspaceService;
use App\Modules\Providers\Domain\Enums\ProviderPermission;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderStaff;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProviderWorkspaceStaffController extends ApiController
{
    public function __construct(private readonly ProviderWorkspaceService $workspaceService) {}

    public function index(Request $request, Provider $provider)
    {
        try {
            return $this->success(
                $this->workspaceService->staffList($request->user(), $provider),
                'Provider staff.',
            );
        } catch (AuthorizationException $exception) {
            return $this->error($exception->getMessage(), status: 403);
        }
    }

    public function store(Request $request, Provider $provider)
    {
        $data = $request->validate($this->rules());

        try {
            return $this->success(
                $this->workspaceService->addStaff($request->user(), $provider, $data),
                'Provider staff member added.',
                201,
            );
        } catch (AuthorizationException $exception) {
            return $this->error($exception->getMessage(), status: 403);
        } catch (ValidationException $exception) {
            return $this->error('Validation error.', $exception->errors(), 422);
        }
    }

    public function update(Request $request, Provider $provider, ProviderStaff $staff)
    {
        $data = $request->validate($this->rules(requiredEmail: false, allowStatus: true));

        try {
            return $this->success(
                $this->workspaceService->updateStaff($request->user(), $provider, $staff, $data),
                'Provider staff member updated.',
            );
        } catch (AuthorizationException $exception) {
            return $this->error($exception->getMessage(), status: 403);
        } catch (ValidationException $exception) {
            return $this->error('Validation error.', $exception->errors(), 422);
        }
    }

    public function destroy(Request $request, Provider $provider, ProviderStaff $staff)
    {
        try {
            return $this->success(
                $this->workspaceService->deactivateStaff($request->user(), $provider, $staff),
                'Provider staff member deactivated.',
            );
        } catch (AuthorizationException $exception) {
            return $this->error($exception->getMessage(), status: 403);
        } catch (ValidationException $exception) {
            return $this->error('Validation error.', $exception->errors(), 422);
        }
    }

    private function rules(bool $requiredEmail = true, bool $allowStatus = false): array
    {
        $rules = [
            'role' => ['sometimes', 'string', Rule::in([
                ProviderStaffRole::Admin->value,
                ProviderStaffRole::Staff->value,
            ])],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::in(ProviderPermission::values())],
        ];

        if ($requiredEmail) {
            $rules['email'] = ['required', 'email'];
        }

        if ($allowStatus) {
            $rules['status'] = ['sometimes', 'string', Rule::in(['active', 'inactive'])];
        }

        return $rules;
    }
}
