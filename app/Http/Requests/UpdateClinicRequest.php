<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Clinic;

class UpdateClinicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clinicId = $this->route('id');
        
        return [
            'name' => 'sometimes|string|min:2|max:255|unique:clinics,name,' . $clinicId,
            'address' => 'nullable|string|max:500',
            'status' => 'sometimes|integer|in:' . Clinic::STATUS_ACTIVE . ',' . Clinic::STATUS_INACTIVE,
        ];
    }

    public function messages(): array
    {
        return [
            'name.min' => 'Clinic name must be at least 2 characters',
            'name.unique' => 'Clinic name already exists',
            'status.in' => 'Invalid status value',
        ];
    }
}