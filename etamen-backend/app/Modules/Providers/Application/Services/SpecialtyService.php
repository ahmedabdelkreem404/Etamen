<?php

namespace App\Modules\Providers\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Providers\Infrastructure\Models\Specialty;
use Illuminate\Support\Str;

class SpecialtyService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function create(array $data, User $actor): Specialty
    {
        $specialty = Specialty::query()->create([
            ...$data,
            'slug' => $data['slug'] ?? Str::slug($data['name_en']),
        ]);

        $this->auditLogService->log('specialty.created', $specialty, $actor);

        return $specialty;
    }

    public function update(Specialty $specialty, array $data, User $actor): Specialty
    {
        $before = $specialty->getAttributes();
        $specialty->update($data);

        $this->auditLogService->log('specialty.updated', $specialty, $actor, before: $before, after: $specialty->getAttributes());

        return $specialty->refresh();
    }

    public function delete(Specialty $specialty, User $actor): void
    {
        $this->auditLogService->log('specialty.deleted', $specialty, $actor, before: $specialty->getAttributes());
        $specialty->delete();
    }
}
