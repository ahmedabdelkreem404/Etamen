<?php

namespace App\Modules\Providers\Http\Requests;

use App\Modules\Providers\Domain\Enums\ProviderContractStatus;
use App\Modules\Providers\Domain\Enums\ProviderContractType;
use App\Modules\Providers\Domain\Enums\ProviderSettlementCycle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminProviderContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPlatformAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'contract_type' => ['required', Rule::in(ProviderContractType::values())],
            'commission_rate' => ['nullable', 'numeric', 'between:0,100'],
            'fixed_commission_amount' => ['nullable', 'numeric', 'min:0'],
            'subscription_plan_id' => ['nullable', 'integer', 'exists:subscription_plans,id'],
            'settlement_cycle' => ['required', Rule::in(ProviderSettlementCycle::values())],
            'pay_at_branch_allowed' => ['nullable', 'boolean'],
            'online_payment_required' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'status' => ['required', Rule::in(ProviderContractStatus::values())],
        ];
    }
}
