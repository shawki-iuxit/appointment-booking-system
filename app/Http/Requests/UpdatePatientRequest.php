<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $patientId = $this->route('id');

        return [
            'name' => 'sometimes|string|min:2|max:255',
            'email' => 'sometimes|email|unique:patients,email,'.$patientId,
            'status' => 'sometimes|integer|in:'.Patient::STATUS_ACTIVE.','.Patient::STATUS_INACTIVE,
        ];
    }

    public function messages(): array
    {
        return [
            'name.min' => 'Patient name must be at least 2 characters',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'Email address is already registered',
            'status.in' => 'Invalid status value',
        ];
    }
}
