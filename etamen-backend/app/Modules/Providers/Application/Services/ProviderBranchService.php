<?php

namespace App\Modules\Providers\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;

class ProviderBranchService
{
    public function __construct(
        private readonly ProviderProfileService $providerProfileService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function createForCurrentProvider(User $user, array $data): ProviderBranch
    {
        $provider = $this->providerProfileService->currentProviderFor($user);
        $branch = $provider->branches()->create($data);

        $this->auditLogService->log('provider_branch.created', $branch, $user, metadata: ['provider_id' => $provider->id]);

        return $branch;
    }

    public function update(User $user, ProviderBranch $branch, array $data): ProviderBranch
    {
        $before = $branch->getAttributes();
        $branch->update($data);

        $this->auditLogService->log('provider_branch.updated', $branch, $user, before: $before, after: $branch->getAttributes());

        return $branch->refresh();
    }
}
