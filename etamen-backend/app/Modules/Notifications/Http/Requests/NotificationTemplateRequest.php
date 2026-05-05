<?php

namespace App\Modules\Notifications\Http\Requests;

use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Domain\Enums\NotificationChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $templateId = $this->route('template')?->id;

        return [
            'key' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
                'max:255',
                Rule::unique('notification_templates', 'key')->ignore($templateId),
            ],
            'category' => [$this->isMethod('post') ? 'required' : 'sometimes', Rule::in(NotificationCategory::values())],
            'channel' => [$this->isMethod('post') ? 'required' : 'sometimes', Rule::in(NotificationChannel::values())],
            'title_ar' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'body_ar' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:5000'],
            'body_en' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
            'variables' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
