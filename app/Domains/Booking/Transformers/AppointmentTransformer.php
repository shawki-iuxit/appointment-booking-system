<?php

namespace App\Domains\Booking\Transformers;

use App\Models\Appointment;

class AppointmentTransformer
{
    public static function transform(Appointment $appointment): array
    {
        return [
            'id' => $appointment->id,
            'time_slot_id' => $appointment->time_slot_id,
            'patient_id' => $appointment->patient_id,
            'booked_at' => $appointment->booked_at?->toISOString(),
            'time_slot' => [
                'id' => $appointment->timeSlot?->id,
                'date' => $appointment->timeSlot?->date?->format('Y-m-d'),
                'start_time' => $appointment->timeSlot?->start_time?->format('H:i'),
                'end_time' => $appointment->timeSlot?->end_time?->format('H:i'),
                'doctor' => [
                    'id' => $appointment->timeSlot?->doctor?->id,
                    'name' => $appointment->timeSlot?->doctor?->name,
                ],
            ],
            'patient' => [
                'id' => $appointment->patient?->id,
                'name' => $appointment->patient?->name,
                'email' => $appointment->patient?->email,
            ],
            'created_at' => $appointment->created_at?->toISOString(),
            'updated_at' => $appointment->updated_at?->toISOString(),
        ];
    }

    public static function transformCollection($appointments): array
    {
        if (is_array($appointments)) {
            return array_map(function ($appointment) {
                return self::transform($appointment);
            }, $appointments);
        }

        return $appointments->map(function ($appointment) {
            return self::transform($appointment);
        })->toArray();
    }
}
