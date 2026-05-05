<?php

namespace App\Modules\Wallets\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
            'wallet_id' => ['prohibited'],
            'status' => ['prohibited'],
        ];
    }
}
