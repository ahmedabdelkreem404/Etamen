<?php

namespace App\Modules\Appointments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorScheduleDayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_schedule_id' => ['prohibited'],
            'day_of_week' => ['sometimes', 'integer', 'between:0,6'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $day = $this->route('day');
            $startTime = $this->input('start_time', $day?->start_time);
            $endTime = $this->input('end_time', $day?->end_time);

            if ($startTime && $endTime && strtotime((string) $startTime) >= strtotime((string) $endTime)) {
                $validator->errors()->add('end_time', 'The end time must be after the start time.');
            }
        });
    }
}
