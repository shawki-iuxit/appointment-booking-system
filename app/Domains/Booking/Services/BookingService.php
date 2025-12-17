<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\Contracts\BookingRepositoryInterface;
use App\Domains\Booking\Pipelines\BookingContext;
use App\Domains\Booking\Pipelines\BookingValidationPipeline;
use App\Models\Appointment;

class BookingService
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly BookingValidationPipeline $appointmentAvailabilityPipeline
    ) {}

    public function bookAppointment(int $timeSlotId, int $patientId): Appointment
    {
        $context = new BookingContext($timeSlotId, $patientId);

        // Integrated the Pipeline Pattern to check the appointment availability
        $this->appointmentAvailabilityPipeline->process($context);

        return $this->bookingRepository->createAppointmentWithTimeSlotUpdate($timeSlotId, $patientId);
    }

    public function getPatientAppointments(int $patientId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->bookingRepository->getPatientAppointments($patientId);
    }

    public function isTimeSlotAvailable(int $timeSlotId): bool
    {
        return ! $this->bookingRepository->isTimeSlotBooked($timeSlotId);
    }
}
