<?php

namespace App\Domains\Booking\Contracts;

use App\Models\Appointment;

interface BookingRepositoryInterface
{
    public function findAppointmentByTimeSlot(int $timeSlotId): ?Appointment;

    public function createAppointmentWithTimeSlotUpdate(int $timeSlotId, int $patientId): Appointment;

    public function getPatientAppointments(int $patientId): \Illuminate\Database\Eloquent\Collection;

    public function isTimeSlotBooked(int $timeSlotId): bool;
}
