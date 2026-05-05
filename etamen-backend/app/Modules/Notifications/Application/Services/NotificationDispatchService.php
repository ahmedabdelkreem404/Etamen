<?php

namespace App\Modules\Notifications\Application\Services;

use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Notifications\Application\Providers\ProviderUnavailableException;
use App\Modules\Notifications\Domain\Enums\NotificationDispatchStatus;
use App\Modules\Notifications\Infrastructure\Models\NotificationDispatch;

class NotificationDispatchService
{
    public function __construct(
        private readonly NotificationProviderManager $providers,
        private readonly AuditLogService $auditLogs,
    ) {}

    public function processDue(int $limit = 100): array
    {
        $processed = 0;
        $failed = 0;

        NotificationDispatch::query()
            ->whereIn('status', [NotificationDispatchStatus::Pending, NotificationDispatchStatus::Queued])
            ->where(fn ($query) => $query->whereNull('scheduled_for')->orWhere('scheduled_for', '<=', now()))
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->each(function (NotificationDispatch $dispatch) use (&$processed, &$failed): void {
                $processed++;

                if ($dispatch->channel->value === 'in_app') {
                    $dispatch->forceFill([
                        'status' => NotificationDispatchStatus::Sent,
                        'attempted_at' => now(),
                        'sent_at' => now(),
                    ])->save();

                    return;
                }

                try {
                    $dispatch->forceFill([
                        'status' => NotificationDispatchStatus::Queued,
                        'attempted_at' => now(),
                    ])->save();
                    $this->providers->providerFor($dispatch->channel, $dispatch->provider)->send($dispatch);
                    $dispatch->forceFill([
                        'status' => NotificationDispatchStatus::Sent,
                        'sent_at' => now(),
                        'failure_reason' => null,
                    ])->save();
                } catch (ProviderUnavailableException $exception) {
                    $failed++;
                    $dispatch->forceFill([
                        'status' => NotificationDispatchStatus::Failed,
                        'failure_reason' => str($exception->getMessage())->limit(500)->toString(),
                    ])->save();
                    $this->auditLogs->log('notification_dispatch.failed', $dispatch, metadata: [
                        'reason' => $dispatch->failure_reason,
                    ]);
                }
            });

        return ['processed' => $processed, 'failed' => $failed];
    }

    public function cancelDuplicate(string $idempotencyKey): ?NotificationDispatch
    {
        return NotificationDispatch::query()->where('idempotency_key', $idempotencyKey)->first();
    }
}
