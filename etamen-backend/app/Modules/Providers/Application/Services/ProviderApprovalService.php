<?php

namespace App\Modules\Providers\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Providers\Domain\Enums\ApprovalRequestStatus;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Infrastructure\Models\Provider;

class ProviderApprovalService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function approve(Provider $provider, User $admin, ?string $notes = null): Provider
    {
        $before = $provider->getAttributes();

        $provider->update([
            'status' => ProviderStatus::Approved,
            'is_active' => true,
            'approved_at' => now(),
            'rejected_at' => null,
            'suspended_at' => null,
            'reviewed_by' => $admin->id,
        ]);

        $this->updatePendingRequests($provider, ApprovalRequestStatus::Approved, $admin, $notes);
        $this->auditLogService->log('provider.approved', $provider, $admin, before: $before, after: $provider->getAttributes());

        return $provider->refresh();
    }

    public function reject(Provider $provider, User $admin, ?string $notes = null): Provider
    {
        $before = $provider->getAttributes();

        $provider->update([
            'status' => ProviderStatus::Rejected,
            'is_active' => false,
            'rejected_at' => now(),
            'reviewed_by' => $admin->id,
        ]);

        $this->updatePendingRequests($provider, ApprovalRequestStatus::NeedsChanges, $admin, $notes);
        $this->auditLogService->log('provider.rejected', $provider, $admin, before: $before, after: $provider->getAttributes(), metadata: ['notes' => $notes]);

        return $provider->refresh();
    }

    public function requestChanges(Provider $provider, User $admin, ?string $notes = null): Provider
    {
        $before = $provider->getAttributes();

        $provider->update([
            'status' => ProviderStatus::NeedsChanges,
            'is_active' => false,
            'reviewed_by' => $admin->id,
        ]);

        $this->updatePendingRequests($provider, ApprovalRequestStatus::Rejected, $admin, $notes);
        $this->auditLogService->log('provider.needs_changes', $provider, $admin, before: $before, after: $provider->getAttributes(), metadata: ['notes' => $notes]);

        return $provider->refresh();
    }

    public function suspend(Provider $provider, User $admin, ?string $notes = null): Provider
    {
        $before = $provider->getAttributes();

        $provider->update([
            'status' => ProviderStatus::Suspended,
            'is_active' => false,
            'suspended_at' => now(),
            'reviewed_by' => $admin->id,
        ]);

        $this->auditLogService->log('provider.suspended', $provider, $admin, before: $before, after: $provider->getAttributes(), metadata: ['notes' => $notes]);

        return $provider->refresh();
    }

    public function reactivate(Provider $provider, User $admin): Provider
    {
        $before = $provider->getAttributes();

        $provider->update([
            'status' => ProviderStatus::Approved,
            'is_active' => true,
            'suspended_at' => null,
            'reviewed_by' => $admin->id,
        ]);

        $this->auditLogService->log('provider.reactivated', $provider, $admin, before: $before, after: $provider->getAttributes());

        return $provider->refresh();
    }

    private function updatePendingRequests(Provider $provider, ApprovalRequestStatus $status, User $admin, ?string $notes): void
    {
        $provider->approvalRequests()
            ->where('status', ApprovalRequestStatus::Pending)
            ->update([
                'status' => $status,
                'reviewed_by' => $admin->id,
                'review_notes' => $notes,
                'reviewed_at' => now(),
            ]);
    }
}
