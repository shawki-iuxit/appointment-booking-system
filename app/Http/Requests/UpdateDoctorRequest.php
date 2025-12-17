<?php

namespace App\Http\Requests;

use App\Models\Doctor;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clinic_id' => 'sometimes|integer|exists:clinics,id',
            'name' => 'sometimes|string|min:2|max:255',
            'specialization' => 'nullable|string|max:255',
            'status' => 'sometimes|integer|in:'.Doctor::STATUS_ACTIVE.','.Doctor::STATUS_INACTIVE,
        ];
    }

    public function messages(): array
    {
        return [
            'clinic_id.exists' => 'Selected clinic does not exist',
            'name.min' => 'Doctor name must be at least 2 characters',
            'status.in' => 'Invalid status value',
        ];
    }
}
