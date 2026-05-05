<?php

namespace App\Modules\Labs\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Labs\Infrastructure\Models\LabTest;
use Illuminate\Support\Facades\DB;

class LabTestService
{
    public function __construct(
        private readonly LabAccessService $accessService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function create(User $actor, array $data): LabTest
    {
        return DB::transaction(function () use ($actor, $data): LabTest {
            $lab = $this->accessService->currentLabFor($actor);

            $test = LabTest::query()->create([
                'provider_id' => $lab->id,
                ...$data,
            ]);

            $this->auditLogService->log('lab_test.created', $test, $actor, metadata: ['lab_provider_id' => $lab->id]);

            return $test->refresh()->load('provider');
        });
    }

    public function update(User $actor, LabTest $test, array $data): LabTest
    {
        return DB::transaction(function () use ($actor, $test, $data): LabTest {
            $before = $test->getAttributes();
            $test->update($data);

            $this->auditLogService->log('lab_test.updated', $test, $actor, before: $before, after: $test->getAttributes());

            return $test->refresh()->load('provider');
        });
    }

    public function deactivate(User $actor, LabTest $test): LabTest
    {
        return $this->update($actor, $test, ['is_active' => false]);
    }
}
