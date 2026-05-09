<?php

namespace App\Modules\Fitness\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelFitnessBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
