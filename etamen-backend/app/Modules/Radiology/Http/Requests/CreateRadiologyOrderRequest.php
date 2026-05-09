<?php

namespace App\Modules\Radiology\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRadiologyOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_id' => ['required', 'integer', 'exists:providers,id'],
            'branch_id' => ['nullable', 'integer', 'exists:provider_branches,id'],
            'scans' => ['required', 'array', 'min:1', 'max:10'],
            'scans.*.radiology_scan_id' => ['required', 'integer', 'exists:radiology_scans,id'],
            'scans.*.quantity' => ['nullable', 'integer', 'min:1', 'max:5'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'patient_notes' => ['nullable', 'string', 'max:2000'],
            'subtotal' => ['prohibited'],
            'discount_amount' => ['prohibited'],
            'total_amount' => ['prohibited'],
            'status' => ['prohibited'],
            'payment_id' => ['prohibited'],
        ];
    }
}
