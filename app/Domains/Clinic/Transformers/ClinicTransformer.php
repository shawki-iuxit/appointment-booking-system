<?php

namespace App\Domains\Clinic\Transformers;

use App\Models\Clinic;

class ClinicTransformer
{
    public static function transform(Clinic $clinic): array
    {
        return [
            'id' => $clinic->id,
            'name' => $clinic->name,
            'address' => $clinic->address,
            'status' => $clinic->status,
            'status_label' => $clinic->isActive() ? 'Active' : 'Inactive',
            'created_at' => $clinic->created_at?->toISOString(),
            'updated_at' => $clinic->updated_at?->toISOString(),
        ];
    }

    public static function transformCollection($clinics): array
    {
        // if (is_array($clinics)) {
            return array_map(function ($clinic) {
                return self::transform($clinic);
            }, $clinics);
        // }
    }

    public static function transformPaginated($paginatedClinics): array
    {
        return [
            'data' => self::transformCollection($paginatedClinics->items()),
            'pagination' => [
                'current_page' => $paginatedClinics->currentPage(),
                'last_page' => $paginatedClinics->lastPage(),
                'per_page' => $paginatedClinics->perPage(),
                'total' => $paginatedClinics->total()
            ]
        ];
    }
}