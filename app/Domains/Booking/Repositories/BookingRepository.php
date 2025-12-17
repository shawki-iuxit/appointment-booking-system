<?php

namespace App\Domains\Booking\Repositories;

use App\Domains\Booking\Contracts\BookingRepositoryInterface;
use App\Models\Appointment;
use App\Models\Timeslot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingRepository implements BookingRepositoryInterface
{
    public function __construct(
        private readonly Appointment $appointment,
        private readonly Timeslot $timeslot
    ) {}

    public function findAppointmentByTimeSlot(int $timeSlotId): ?Appointment
    {
        return $this->appointment->where('time_slot_id', $timeSlotId)->first();
    }

    public function createAppointmentWithTimeSlotUpdate(int $timeSlotId, int $patientId): Appointment
    {
        return DB::transaction(function () use ($timeSlotId, $patientId) {
            $timeSlot = $this->timeslot->lockForUpdate()->findOrFail($timeSlotId);

            $timeSlot->markAsUnavailable();
            $timeSlot->save();

            return $this->appointment->create([
                'time_slot_id' => $timeSlotId,
                'patient_id' => $patientId,
                'booked_at' => Carbon::now(),
            ]);
        });
    }

    public function getPatientAppointments(int $patientId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->appointment
            ->with(['timeslot.doctor'])
            ->where('patient_id', $patientId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function isTimeSlotBooked(int $timeSlotId): bool
    {
        return $this->appointment->where('time_slot_id', $timeSlotId)->exists();
    }
}
