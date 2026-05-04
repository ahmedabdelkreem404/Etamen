<?php

namespace App\Modules\AuditLogs\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Infrastructure\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    public function log(
        string $action,
        ?Model $target = null,
        ?User $actor = null,
        ?array $before = null,
        ?array $after = null,
        ?array $metadata = null,
        ?Request $request = null,
    ): AuditLog {
        $request ??= request();
        $actor ??= auth()->user();

        return AuditLog::query()->create([
            'actor_id' => $actor?->id,
            'action' => $action,
            'target_type' => $target ? $target::class : null,
            'target_id' => $target?->getKey(),
            'before_values' => $before,
            'after_values' => $after,
            'metadata' => $metadata,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
