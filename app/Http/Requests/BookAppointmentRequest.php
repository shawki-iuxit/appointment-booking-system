<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'time_slot_id' => 'required|integer|exists:time_slots,id',
            'patient_id' => 'required|integer|exists:patients,id',
        ];
    }

    public function messages(): array
    {
        return [
            'time_slot_id.required' => 'Time slot is required',
            'time_slot_id.exists' => 'Selected time slot does not exist',
            'patient_id.required' => 'Patient is required',
            'patient_id.exists' => 'Selected patient does not exist',
        ];
    }
}
