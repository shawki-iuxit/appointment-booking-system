<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;

class CreatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|unique:patients,email',
            'status' => 'nullable|integer|in:'.Patient::STATUS_ACTIVE.','.Patient::STATUS_INACTIVE,
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Patient name is required',
            'name.min' => 'Patient name must be at least 2 characters',
            'email.required' => 'Email address is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'Email address is already registered',
            'status.in' => 'Invalid status value',
        ];
    }
}
