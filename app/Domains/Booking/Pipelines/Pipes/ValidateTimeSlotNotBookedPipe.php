<?php

namespace App\Domains\Booking\Pipelines\Pipes;

use App\Domains\Booking\Contracts\BookingRepositoryInterface;
use App\Domains\Booking\Pipelines\BookingContext;
use App\Domains\Booking\Pipelines\Contracts\BookingPipeInterface;
use Closure;
use Illuminate\Validation\ValidationException;

class ValidateTimeSlotNotBookedPipe implements BookingPipeInterface
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository
    ) {}

    public function handle(BookingContext $context, Closure $next): BookingContext
    {
        if ($this->bookingRepository->isTimeSlotBooked($context->timeSlotId)) {
            throw ValidationException::withMessages([
                'time_slot_id' => 'Time slot has already been booked by another patient',
            ]);
        }

        return $next($context);
    }
}
