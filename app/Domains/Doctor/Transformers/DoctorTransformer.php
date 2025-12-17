<?php

namespace App\Domains\Doctor\Transformers;

use App\Models\Doctor;

class DoctorTransformer
{
    public static function transform(Doctor $doctor): array
    {
        return [
            'id' => $doctor->id,
            'name' => $doctor->name,
            'specialization' => $doctor->specialization,
            'status' => $doctor->status,
            'status_label' => $doctor->isActive() ? 'Active' : 'Inactive',
            'is_active' => $doctor->isActive(),
            'clinic' => $doctor->clinic ? [
                'id' => $doctor->clinic->id,
                'name' => $doctor->clinic->name,
            ] : null,
            'created_at' => $doctor->created_at?->toISOString(),
            'updated_at' => $doctor->updated_at?->toISOString(),
        ];
    }

    public static function transformCollection($doctors): array
    {
        return array_map(function ($doctor) {
            return self::transform($doctor);
        }, $doctors);
    }

    public static function transformPaginated($paginatedDoctors): array
    {
        return [
            'data' => self::transformCollection($paginatedDoctors->items()),
            'pagination' => [
                'current_page' => $paginatedDoctors->currentPage(),
                'last_page' => $paginatedDoctors->lastPage(),
                'per_page' => $paginatedDoctors->perPage(),
                'total' => $paginatedDoctors->total(),
                'from' => $paginatedDoctors->firstItem(),
                'to' => $paginatedDoctors->lastItem(),
            ],
        ];
    }
}
