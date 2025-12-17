<?php

namespace App\Domains\Timeslot\Transformers;

use App\Models\Timeslot;

class TimeslotTransformer
{
    public static function transform(Timeslot $timeslot): array
    {
        return [
            'id' => $timeslot->id,
            'date' => $timeslot->date->format('Y-m-d'),
            'start_time' => $timeslot->start_time->format('H:i'),
            'end_time' => $timeslot->end_time->format('H:i'),
            'duration_minutes' => $timeslot->getDurationInMinutes(),
            'is_available' => $timeslot->is_available,
            'is_past' => $timeslot->isPast(),
            'doctor' => $timeslot->doctor ? [
                'id' => $timeslot->doctor->id,
                'name' => $timeslot->doctor->name,
                'specialization' => $timeslot->doctor->specialization,
                'clinic' => $timeslot->doctor->clinic ? [
                    'id' => $timeslot->doctor->clinic->id,
                    'name' => $timeslot->doctor->clinic->name,
                ] : null,
            ] : null,
            'created_at' => $timeslot->created_at?->toISOString(),
            'updated_at' => $timeslot->updated_at?->toISOString(),
        ];
    }

    public static function transformCollection($timeslots): array
    {
        // Handle both arrays and collections
        if (is_array($timeslots)) {
            return array_map(function ($timeslot) {
                return self::transform($timeslot);
            }, $timeslots);
        }

        // For collections, convert to array first then transform
        return $timeslots->map(function ($timeslot) {
            return self::transform($timeslot);
        })->toArray();
    }

    public static function transformPaginated($paginatedTimeslots): array
    {
        return [
            'data' => self::transformCollection($paginatedTimeslots->items()),
            'pagination' => [
                'current_page' => $paginatedTimeslots->currentPage(),
                'last_page' => $paginatedTimeslots->lastPage(),
                'per_page' => $paginatedTimeslots->perPage(),
                'total' => $paginatedTimeslots->total(),
                'from' => $paginatedTimeslots->firstItem(),
                'to' => $paginatedTimeslots->lastItem(),
            ],
        ];
    }
}
