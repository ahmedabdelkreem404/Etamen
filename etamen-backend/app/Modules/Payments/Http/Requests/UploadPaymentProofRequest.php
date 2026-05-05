<?php

namespace App\Modules\Payments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadPaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'sender_phone' => ['nullable', 'regex:/^(\\+?20|0)?1[0125][0-9]{8}$/'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'user_id' => ['prohibited'],
            'status' => ['prohibited'],
        ];
    }
}
