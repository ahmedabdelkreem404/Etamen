<?php

namespace App\Modules\Notifications\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Notifications\Infrastructure\Models\NotificationToken;
use Illuminate\Support\Facades\DB;

class NotificationTokenService
{
    public function __construct(private readonly AuditLogService $auditLogs) {}

    public function register(User $user, array $data): NotificationToken
    {
        return DB::transaction(function () use ($user, $data): NotificationToken {
            $token = NotificationToken::query()->updateOrCreate(
                [
                    'provider' => $data['provider'],
                    'token_hash' => hash('sha256', $data['token']),
                ],
                [
                    'user_id' => $user->id,
                    'token' => $data['token'],
                    'device_type' => $data['device_type'],
                    'device_name' => $data['device_name'] ?? null,
                    'app_version' => $data['app_version'] ?? null,
                    'locale' => $data['locale'] ?? 'ar',
                    'timezone' => $data['timezone'] ?? 'Africa/Cairo',
                    'is_active' => true,
                    'last_seen_at' => now(),
                    'metadata' => $data['metadata'] ?? null,
                ],
            );

            $this->auditLogs->log('notification_token.registered', $token, $user);

            return $token->refresh();
        });
    }

    public function delete(User $user, NotificationToken $token): void
    {
        DB::transaction(function () use ($user, $token): void {
            $before = $token->getAttributes();
            $token->delete();
            $this->auditLogs->log('notification_token.deleted', $token, $user, before: $before);
        });
    }
}
