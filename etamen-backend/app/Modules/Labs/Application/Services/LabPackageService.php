<?php

namespace App\Modules\Labs\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Labs\Infrastructure\Models\LabPackage;
use App\Modules\Labs\Infrastructure\Models\LabTest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LabPackageService
{
    public function __construct(
        private readonly LabAccessService $accessService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function create(User $actor, array $data): LabPackage
    {
        return DB::transaction(function () use ($actor, $data): LabPackage {
            $lab = $this->accessService->currentLabFor($actor);
            $testIds = $this->assertTestsBelongToLab($data['test_ids'], $lab->id);
            unset($data['test_ids']);

            $package = LabPackage::query()->create([
                'provider_id' => $lab->id,
                ...$data,
            ]);
            $package->tests()->sync($testIds);

            $this->auditLogService->log('lab_package.created', $package, $actor, metadata: ['lab_provider_id' => $lab->id]);

            return $package->refresh()->load('tests');
        });
    }

    public function update(User $actor, LabPackage $package, array $data): LabPackage
    {
        return DB::transaction(function () use ($actor, $package, $data): LabPackage {
            $before = $package->getAttributes();

            if (isset($data['test_ids'])) {
                $testIds = $this->assertTestsBelongToLab($data['test_ids'], $package->provider_id);
                unset($data['test_ids']);
            }

            $package->update($data);

            if (isset($testIds)) {
                $package->tests()->sync($testIds);
            }

            $this->auditLogService->log('lab_package.updated', $package, $actor, before: $before, after: $package->getAttributes());

            return $package->refresh()->load('tests');
        });
    }

    public function deactivate(User $actor, LabPackage $package): LabPackage
    {
        return $this->update($actor, $package, ['is_active' => false]);
    }

    private function assertTestsBelongToLab(array $testIds, int $labProviderId): array
    {
        $testIds = array_values(array_unique(array_map('intval', $testIds)));
        $found = LabTest::query()
            ->whereIn('id', $testIds)
            ->where('provider_id', $labProviderId)
            ->pluck('id')
            ->all();

        if (count($found) !== count($testIds)) {
            throw ValidationException::withMessages([
                'test_ids' => ['All package tests must belong to the same lab.'],
            ]);
        }

        return $testIds;
    }
}
