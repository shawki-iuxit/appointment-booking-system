<?php

namespace App\Domains\Booking\Commands;

use App\Domains\Booking\Contracts\BookingRepositoryInterface;
use App\Domains\Timeslot\Contracts\TimeslotRepositoryInterface;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookAppointmentCommand
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly TimeslotRepositoryInterface $timeslotRepository
    ) {}

    public function execute(int $timeSlotId, int $patientId): Appointment
    {
        return DB::transaction(function () use ($timeSlotId, $patientId) {
            $timeSlot = $this->timeslotRepository->find($timeSlotId);

            if (! $timeSlot) {
                throw new ModelNotFoundException('Time slot not found');
            }

            if (! $timeSlot->is_available) {
                throw ValidationException::withMessages([
                    'time_slot_id' => 'Time slot is not available for booking',
                ]);
            }

            if ($this->bookingRepository->isTimeSlotBooked($timeSlotId)) {
                throw ValidationException::withMessages([
                    'time_slot_id' => 'Time slot has already been booked by another patient',
                ]);
            }

            if ($timeSlot->isPast()) {
                throw ValidationException::withMessages([
                    'time_slot_id' => 'Cannot book a past time slot',
                ]);
            }

            $timeSlot->markAsUnavailable();
            $timeSlot->save();

            return $this->bookingRepository->createAppointment($timeSlotId, $patientId);
        });
    }
}
