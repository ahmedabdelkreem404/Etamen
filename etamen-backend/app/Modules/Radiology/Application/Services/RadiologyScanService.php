<?php

namespace App\Modules\Radiology\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Radiology\Infrastructure\Models\RadiologyScan;
use Illuminate\Support\Facades\DB;

class RadiologyScanService
{
    public function __construct(
        private readonly RadiologyAccessService $accessService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function createForProvider(User $actor, array $data): RadiologyScan
    {
        $provider = $this->accessService->currentRadiologyFor($actor);

        return $this->createForRadiologyProvider($actor, $provider, $data);
    }

    public function createForRadiologyProvider(User $actor, Provider $provider, array $data): RadiologyScan
    {
        $this->accessService->assertRadiologyCanManageCatalog($provider);

        return DB::transaction(function () use ($actor, $provider, $data): RadiologyScan {
            $scan = RadiologyScan::query()->create([
                ...$data,
                'provider_id' => $provider->id,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $this->auditLogService->log('radiology_scan.created', $scan, $actor, metadata: ['provider_id' => $provider->id]);

            return $scan->refresh()->load(['provider', 'branch', 'category']);
        });
    }

    public function update(User $actor, RadiologyScan $scan, array $data): RadiologyScan
    {
        return DB::transaction(function () use ($actor, $scan, $data): RadiologyScan {
            $before = $scan->getAttributes();
            $scan->update([
                ...$data,
                'updated_by' => $actor->id,
            ]);

            $this->auditLogService->log('radiology_scan.updated', $scan, $actor, before: $before, after: $scan->getAttributes());

            return $scan->refresh()->load(['provider', 'branch', 'category']);
        });
    }

    public function deactivate(User $actor, RadiologyScan $scan): RadiologyScan
    {
        return $this->update($actor, $scan, ['is_active' => false]);
    }
}
