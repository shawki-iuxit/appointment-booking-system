<?php

namespace App\Domains\Patient\Transformers;

use App\Models\Patient;

class PatientTransformer
{
    public static function transform(Patient $patient): array
    {
        return [
            'id' => $patient->id,
            'name' => $patient->name,
            'email' => $patient->email,
            'status' => $patient->isActive() ? 'Active' : 'Inactive',
            'created_at' => $patient->created_at?->toISOString(),
            'updated_at' => $patient->updated_at?->toISOString(),
        ];
    }

    public static function transformCollection($patients): array
    {
        // Handle both arrays and collections
        if (is_array($patients)) {
            return array_map(function ($patient) {
                return self::transform($patient);
            }, $patients);
        }

        return $patients->map(function ($patient) {
            return self::transform($patient);
        })->toArray();
    }

    public static function transformPaginated($paginatedPatients): array
    {
        return [
            'data' => self::transformCollection($paginatedPatients->items()),
            'pagination' => [
                'current_page' => $paginatedPatients->currentPage(),
                'last_page' => $paginatedPatients->lastPage(),
                'per_page' => $paginatedPatients->perPage(),
                'total' => $paginatedPatients->total(),
                'from' => $paginatedPatients->firstItem(),
                'to' => $paginatedPatients->lastItem(),
            ],
        ];
    }
}
