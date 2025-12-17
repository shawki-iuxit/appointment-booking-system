<?php

namespace App\Http\Controllers\V1;

use App\Domains\Booking\Services\BookingService;
use App\Domains\Booking\Transformers\AppointmentTransformer;
use App\Http\Controllers\BaseController;
use App\Http\Requests\BookAppointmentRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class BookingController extends BaseController
{
    public function __construct(
        private readonly BookingService $bookingService
    ) {}

    public function bookAppointment(BookAppointmentRequest $request): JsonResponse
    {
        try {
            $appointment = $this->bookingService->bookAppointment(
                $request->validated()['time_slot_id'],
                $request->validated()['patient_id']
            );

            return $this->createdResponse(
                AppointmentTransformer::transform($appointment),
                'Appointment booked successfully'
            );
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to book appointment',
                $e->getMessage()
            );
        }
    }

    public function getPatientAppointments(int $patientId): JsonResponse
    {
        try {
            $appointments = $this->bookingService->getPatientAppointments($patientId);

            return $this->successResponse(
                AppointmentTransformer::transformCollection($appointments),
                'Patient appointments retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve appointments',
                $e->getMessage()
            );
        }
    }

    public function checkTimeSlotAvailability(int $timeSlotId): JsonResponse
    {
        try {
            $isAvailable = $this->bookingService->isTimeSlotAvailable($timeSlotId);

            return $this->successResponse(
                ['available' => $isAvailable],
                'Time slot availability checked successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to check availability',
                $e->getMessage()
            );
        }
    }
}
