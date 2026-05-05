<?php

namespace App\Modules\Wallets\Http\Requests;

use App\Modules\Providers\Domain\Enums\ProviderType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSettlementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_id' => ['required', 'integer', 'exists:providers,id'],
            'provider_type' => ['required', Rule::in(ProviderType::values())],
        ];
    }
}
