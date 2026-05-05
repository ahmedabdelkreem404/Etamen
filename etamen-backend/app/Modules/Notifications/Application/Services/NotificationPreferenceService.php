<?php

namespace App\Modules\Notifications\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Domain\Enums\NotificationChannel;
use App\Modules\Notifications\Infrastructure\Models\NotificationPreference;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NotificationPreferenceService
{
    public function __construct(private readonly AuditLogService $auditLogs) {}

    public function defaultsFor(User $user): Collection
    {
        foreach (NotificationChannel::cases() as $channel) {
            foreach (NotificationCategory::cases() as $category) {
                NotificationPreference::query()->firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'channel' => $channel,
                        'category' => $category,
                    ],
                    [
                        'is_enabled' => true,
                        'timezone' => 'Africa/Cairo',
                    ],
                );
            }
        }

        return NotificationPreference::query()->where('user_id', $user->id)->orderBy('category')->orderBy('channel')->get();
    }

    public function update(User $user, array $preferences): Collection
    {
        return DB::transaction(function () use ($user, $preferences): Collection {
            foreach ($preferences as $item) {
                NotificationPreference::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'channel' => $item['channel'],
                        'category' => $item['category'],
                    ],
                    [
                        'is_enabled' => (bool) $item['is_enabled'],
                        'quiet_hours_start' => $item['quiet_hours_start'] ?? null,
                        'quiet_hours_end' => $item['quiet_hours_end'] ?? null,
                        'timezone' => $item['timezone'] ?? 'Africa/Cairo',
                        'metadata' => $item['metadata'] ?? null,
                    ],
                );
            }

            $this->auditLogs->log('notification_preferences.updated', actor: $user, metadata: [
                'count' => count($preferences),
            ]);

            return $this->defaultsFor($user);
        });
    }

    public function isEnabled(User $user, NotificationChannel $channel, NotificationCategory $category): bool
    {
        $preference = NotificationPreference::query()
            ->where('user_id', $user->id)
            ->where('channel', $channel)
            ->where('category', $category)
            ->first();

        return $preference?->is_enabled ?? true;
    }

    public function isQuietNow(User $user, NotificationChannel $channel, NotificationCategory $category): bool
    {
        $preference = NotificationPreference::query()
            ->where('user_id', $user->id)
            ->where('channel', $channel)
            ->where('category', $category)
            ->first();

        if (! $preference?->quiet_hours_start || ! $preference->quiet_hours_end) {
            return false;
        }

        $timezone = $preference->timezone ?: 'Africa/Cairo';
        $now = CarbonImmutable::now($timezone)->format('H:i');
        $start = substr((string) $preference->quiet_hours_start, 0, 5);
        $end = substr((string) $preference->quiet_hours_end, 0, 5);

        if ($start <= $end) {
            return $now >= $start && $now <= $end;
        }

        return $now >= $start || $now <= $end;
    }
}
