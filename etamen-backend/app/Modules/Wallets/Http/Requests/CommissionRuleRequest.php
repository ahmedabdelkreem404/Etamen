<?php

namespace App\Modules\Wallets\Http\Requests;

use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Domain\Enums\ServiceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommissionRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_type' => [$this->isMethod('post') ? 'required' : 'sometimes', Rule::in(ProviderType::values())],
            'service_type' => [$this->isMethod('post') ? 'required' : 'sometimes', Rule::in(ServiceType::values())],
            'percentage' => [$this->isMethod('post') ? 'required' : 'sometimes', 'numeric', 'between:0,100'],
            'fixed_amount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => [$this->isMethod('post') ? 'required' : 'sometimes', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
