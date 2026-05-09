<?php

namespace App\Modules\Fitness\Http\Requests;

use App\Modules\Fitness\Domain\Enums\CoachAvailabilityStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CoachAvailabilitySlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'status' => ['nullable', Rule::in(CoachAvailabilityStatus::values())],
            'provider_id' => ['prohibited'],
        ];
    }
}
