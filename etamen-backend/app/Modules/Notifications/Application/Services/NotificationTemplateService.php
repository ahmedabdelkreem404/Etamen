<?php

namespace App\Modules\Notifications\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Notifications\Infrastructure\Models\NotificationTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NotificationTemplateService
{
    public function __construct(private readonly AuditLogService $auditLogs) {}

    public function create(User $admin, array $data): NotificationTemplate
    {
        $this->assertNoSecretVariables($data);

        return DB::transaction(function () use ($admin, $data): NotificationTemplate {
            $template = NotificationTemplate::query()->create($data);
            $this->auditLogs->log('notification_template.created', $template, $admin);

            return $template->refresh();
        });
    }

    public function update(User $admin, NotificationTemplate $template, array $data): NotificationTemplate
    {
        $this->assertNoSecretVariables($data);

        return DB::transaction(function () use ($admin, $template, $data): NotificationTemplate {
            $before = $template->getAttributes();
            $template->fill($data)->save();
            $this->auditLogs->log('notification_template.updated', $template, $admin, before: $before, after: $template->getAttributes());

            return $template->refresh();
        });
    }

    public function render(string $key, array $variables = [], string $locale = 'ar'): array
    {
        $template = NotificationTemplate::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            return [
                'category' => null,
                'channel' => null,
                'title' => $variables['title'] ?? 'Etamen',
                'body' => $variables['body'] ?? 'لديك إشعار جديد من اتطمن.',
            ];
        }

        $title = $locale === 'en' && $template->title_en ? $template->title_en : $template->title_ar;
        $body = $locale === 'en' && $template->body_en ? $template->body_en : $template->body_ar;

        foreach ($variables as $name => $value) {
            if (is_scalar($value)) {
                $title = str_replace('{{'.$name.'}}', (string) $value, $title);
                $body = str_replace('{{'.$name.'}}', (string) $value, $body);
            }
        }

        return [
            'category' => $template->category,
            'channel' => $template->channel,
            'title' => $title,
            'body' => $body,
        ];
    }

    private function assertNoSecretVariables(array $data): void
    {
        $variables = $data['variables'] ?? [];
        $encoded = strtolower(json_encode($variables) ?: '');

        foreach (['secret', 'api_key', 'password', 'server_key', 'token_secret'] as $needle) {
            if (str_contains($encoded, $needle)) {
                throw ValidationException::withMessages([
                    'variables' => ['Template variables cannot contain secrets.'],
                ]);
            }
        }
    }
}
