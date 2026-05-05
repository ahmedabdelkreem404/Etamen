<?php

namespace App\Modules\Notifications\Application\Services;

use App\Modules\Notifications\Domain\Enums\NotificationChannel;

class NotificationPayloadSanitizer
{
    public function sanitize(array $payload, NotificationChannel $channel): array
    {
        $sanitized = $this->stripSensitive($payload);

        if ($channel !== NotificationChannel::InApp) {
            unset(
                $sanitized['patient_health_details'],
                $sanitized['problem_description'],
                $sanitized['lab_result_notes'],
                $sanitized['provider_notes'],
                $sanitized['ai_prompt'],
                $sanitized['ai_response'],
            );
        }

        return $sanitized;
    }

    public function safeBody(string $body, NotificationChannel $channel): string
    {
        if ($channel === NotificationChannel::InApp) {
            return $body;
        }

        $patterns = [
            '/[A-Z]:\\\\[^\\s]+/i',
            '/\\b(private|storage|medical_private|uploads)\\/[^\s]+/i',
            '/provider_net_amount[:=]\s*\d+(\.\d+)?/i',
            '/commission_amount[:=]\s*\d+(\.\d+)?/i',
        ];

        return trim((string) preg_replace($patterns, '[hidden]', $body));
    }

    private function stripSensitive(array $payload): array
    {
        foreach ($payload as $key => $value) {
            $normalized = strtolower((string) $key);

            if ($this->isSensitiveKey($normalized)) {
                unset($payload[$key]);

                continue;
            }

            if (is_array($value)) {
                $payload[$key] = $this->stripSensitive($value);

                continue;
            }

            if (is_string($value) && $this->looksLikePrivatePath($value)) {
                unset($payload[$key]);
            }
        }

        return $payload;
    }

    private function isSensitiveKey(string $key): bool
    {
        foreach ([
            'secret',
            'api_key',
            'server_key',
            'password',
            'token_secret',
            'raw',
            'path',
            'file_path',
            'storage_path',
            'provider_net_amount',
            'commission_amount',
            'wallet_metadata',
            'ai_prompt',
            'provider_raw_response',
            'raw_response',
        ] as $needle) {
            if (str_contains($key, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikePrivatePath(string $value): bool
    {
        return str_contains($value, 'medical_private')
            || str_contains($value, 'storage/')
            || str_contains($value, 'private/')
            || preg_match('/[A-Z]:\\\\/i', $value) === 1;
    }
}
