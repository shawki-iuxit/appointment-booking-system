<?php

namespace App\Http\Requests;

use App\Models\Doctor;
use Illuminate\Foundation\Http\FormRequest;

class CreateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clinic_id' => 'required|integer|exists:clinics,id',
            'name' => 'required|string|min:2|max:255',
            'specialization' => 'nullable|string|max:255',
            'status' => 'nullable|integer|in:'.Doctor::STATUS_ACTIVE.','.Doctor::STATUS_INACTIVE,
        ];
    }

    public function messages(): array
    {
        return [
            'clinic_id.required' => 'Clinic is required',
            'clinic_id.exists' => 'Selected clinic does not exist',
            'name.required' => 'Doctor name is required',
            'name.min' => 'Doctor name must be at least 2 characters',
            'status.in' => 'Invalid status value',
        ];
    }
}
