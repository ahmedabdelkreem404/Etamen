<?php

namespace App\Modules\Notifications\Application\Services;

use App\Models\User;
use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Domain\Enums\NotificationChannel;
use App\Modules\Notifications\Domain\Enums\NotificationDispatchStatus;
use App\Modules\Notifications\Domain\Enums\NotificationPriority;
use App\Modules\Notifications\Infrastructure\Models\Notification;
use App\Modules\Notifications\Infrastructure\Models\NotificationDispatch;
use App\Modules\Notifications\Infrastructure\Models\NotificationToken;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function __construct(
        private readonly NotificationTemplateService $templates,
        private readonly NotificationPreferenceService $preferences,
        private readonly NotificationPayloadSanitizer $sanitizer,
    ) {}

    public function sendToUser(User $user, string $templateKey, array $variables = [], array $options = []): ?Notification
    {
        $idempotencyKey = $options['idempotency_key'] ?? null;

        if ($idempotencyKey) {
            $existing = NotificationDispatch::query()->where('idempotency_key', $idempotencyKey.':in_app')->first();

            if ($existing?->notification) {
                return $existing->notification;
            }
        }

        return DB::transaction(function () use ($user, $templateKey, $variables, $options, $idempotencyKey): ?Notification {
            $locale = $options['locale'] ?? 'ar';
            $rendered = $this->templates->render($templateKey, $variables, $locale);
            $category = $options['category'] ?? $rendered['category'] ?? NotificationCategory::System;
            $category = $category instanceof NotificationCategory ? $category : NotificationCategory::from((string) $category);
            $priority = $options['priority'] ?? NotificationPriority::Normal;
            $priority = $priority instanceof NotificationPriority ? $priority : NotificationPriority::from((string) $priority);
            $type = $options['type'] ?? $templateKey;
            $data = $this->sanitizer->sanitize($options['data'] ?? $variables, NotificationChannel::InApp);
            $critical = (bool) ($options['critical'] ?? false) || $priority === NotificationPriority::Urgent;

            $notification = null;

            if ($critical || $this->preferences->isEnabled($user, NotificationChannel::InApp, $category)) {
                $notification = Notification::query()->create([
                    'user_id' => $user->id,
                    'category' => $category,
                    'type' => $type,
                    'title' => $rendered['title'],
                    'body' => $rendered['body'],
                    'data' => $data,
                    'priority' => $priority,
                    'action_url' => $options['action_url'] ?? null,
                ]);

                $this->createDispatch(
                    user: $user,
                    notification: $notification,
                    channel: NotificationChannel::InApp,
                    category: $category,
                    type: $type,
                    title: $rendered['title'],
                    body: $rendered['body'],
                    payload: ['notification_id' => $notification->id, 'category' => $category->value, 'type' => $type],
                    idempotencyKey: $idempotencyKey ? $idempotencyKey.':in_app' : null,
                    status: NotificationDispatchStatus::Sent,
                    scheduledFor: null,
                );
            }

            foreach ([NotificationChannel::Push, NotificationChannel::Email, NotificationChannel::Sms, NotificationChannel::WhatsApp] as $channel) {
                $this->queueChannelDispatch($user, $notification, $channel, $category, $type, $rendered['title'], $rendered['body'], $data, $priority, $critical, $idempotencyKey);
            }

            return $notification;
        });
    }

    public function markRead(User $user, Notification $notification): Notification
    {
        $notification->forceFill(['read_at' => $notification->read_at ?? now()])->save();

        return $notification->refresh();
    }

    public function markAllRead(User $user): int
    {
        return Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    private function queueChannelDispatch(
        User $user,
        ?Notification $notification,
        NotificationChannel $channel,
        NotificationCategory $category,
        string $type,
        string $title,
        string $body,
        array $data,
        NotificationPriority $priority,
        bool $critical,
        ?string $baseIdempotencyKey,
    ): void {
        if (! $this->preferences->isEnabled($user, $channel, $category)) {
            if ($baseIdempotencyKey) {
                $this->createSkippedDispatch($user, $notification, $channel, $category, $type, 'Preference disabled.', $baseIdempotencyKey.':'.$channel->value);
            }

            return;
        }

        if (! $critical && $this->preferences->isQuietNow($user, $channel, $category)) {
            if ($baseIdempotencyKey) {
                $this->createSkippedDispatch($user, $notification, $channel, $category, $type, 'Quiet hours active.', $baseIdempotencyKey.':'.$channel->value);
            }

            return;
        }

        $tokens = $channel === NotificationChannel::Push
            ? NotificationToken::query()->where('user_id', $user->id)->where('is_active', true)->get()
            : collect();

        if ($channel === NotificationChannel::Push && $tokens->isEmpty()) {
            return;
        }

        if ($channel !== NotificationChannel::Push) {
            return;
        }

        foreach ($tokens as $token) {
            $payload = $this->sanitizer->sanitize([
                'notification_id' => $notification?->id,
                'category' => $category->value,
                'type' => $type,
                'title' => $title,
                'body' => $this->sanitizer->safeBody($body, $channel),
                'data' => $data,
            ], $channel);

            $this->createDispatch(
                user: $user,
                notification: $notification,
                channel: $channel,
                category: $category,
                type: $type,
                title: $title,
                body: $this->sanitizer->safeBody($body, $channel),
                payload: $payload,
                idempotencyKey: $baseIdempotencyKey ? $baseIdempotencyKey.':'.$channel->value.':'.$token->id : null,
                status: NotificationDispatchStatus::Pending,
                scheduledFor: null,
                recipient: $token->token,
                provider: $token->provider->value,
            );
        }
    }

    private function createDispatch(
        User $user,
        ?Notification $notification,
        NotificationChannel $channel,
        NotificationCategory $category,
        string $type,
        ?string $title,
        ?string $body,
        array $payload,
        ?string $idempotencyKey,
        NotificationDispatchStatus $status,
        ?\DateTimeInterface $scheduledFor,
        ?string $recipient = null,
        ?string $provider = null,
    ): NotificationDispatch {
        if ($idempotencyKey === null) {
            return NotificationDispatch::query()->create([
                'notification_id' => $notification?->id,
                'user_id' => $user->id,
                'channel' => $channel,
                'provider' => $provider,
                'category' => $category,
                'type' => $type,
                'recipient' => $recipient,
                'title' => $title,
                'body' => $body,
                'payload' => $payload,
                'status' => $status,
                'scheduled_for' => $scheduledFor,
                'attempted_at' => $status === NotificationDispatchStatus::Sent ? now() : null,
                'sent_at' => $status === NotificationDispatchStatus::Sent ? now() : null,
            ]);
        }

        return NotificationDispatch::query()->firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            [
                'notification_id' => $notification?->id,
                'user_id' => $user->id,
                'channel' => $channel,
                'provider' => $provider,
                'category' => $category,
                'type' => $type,
                'recipient' => $recipient,
                'title' => $title,
                'body' => $body,
                'payload' => $payload,
                'status' => $status,
                'scheduled_for' => $scheduledFor,
                'attempted_at' => $status === NotificationDispatchStatus::Sent ? now() : null,
                'sent_at' => $status === NotificationDispatchStatus::Sent ? now() : null,
            ],
        );
    }

    private function createSkippedDispatch(
        User $user,
        ?Notification $notification,
        NotificationChannel $channel,
        NotificationCategory $category,
        string $type,
        string $reason,
        string $idempotencyKey,
    ): void {
        NotificationDispatch::query()->firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            [
                'notification_id' => $notification?->id,
                'user_id' => $user->id,
                'channel' => $channel,
                'category' => $category,
                'type' => $type,
                'status' => NotificationDispatchStatus::Skipped,
                'failure_reason' => $reason,
            ],
        );
    }
}
