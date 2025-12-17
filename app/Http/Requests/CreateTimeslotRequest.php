<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTimeslotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => 'required|integer|exists:doctors,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'duration_minutes' => 'required|integer|min:15|max:480',
        ];
    }

    public function messages(): array
    {
        return [
            'doctor_id.required' => 'Doctor is required',
            'doctor_id.exists' => 'Selected doctor does not exist',
            'date.required' => 'Date is required',
            'date.after_or_equal' => 'Date cannot be in the past',
            'start_time.required' => 'Start time is required',
            'start_time.date_format' => 'Start time must be in HH:MM format',
            'end_time.required' => 'End time is required',
            'end_time.date_format' => 'End time must be in HH:MM format',
            'end_time.after' => 'End time must be after start time',
            'duration_minutes.required' => 'Duration in minutes is required',
            'duration_minutes.min' => 'Duration must be at least 15 minutes',
            'duration_minutes.max' => 'Duration cannot exceed 480 minutes (8 hours)',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->start_time && $this->end_time && $this->duration_minutes) {
                $start = \Carbon\Carbon::createFromFormat('H:i', $this->start_time);
                $end = \Carbon\Carbon::createFromFormat('H:i', $this->end_time);

                $totalDuration = $start->diffInMinutes($end);
                $slotDuration = $this->duration_minutes;

                if ($slotDuration > $totalDuration) {
                    $validator->errors()->add('duration_minutes', 'Duration cannot be longer than the total time range');
                }

                if ($totalDuration % $slotDuration !== 0) {
                    $validator->errors()->add('duration_minutes', 'The time range must be evenly divisible by the duration');
                }
            }
        });
    }
}
