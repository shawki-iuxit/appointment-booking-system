<?php

namespace App\Domains\Booking\Pipelines\Pipes;

use App\Domains\Booking\Pipelines\BookingContext;
use App\Domains\Booking\Pipelines\Contracts\BookingPipeInterface;
use Closure;
use Illuminate\Validation\ValidationException;

class ValidateTimeSlotAvailablePipe implements BookingPipeInterface
{
    public function handle(BookingContext $context, Closure $next): BookingContext
    {
        if (! $context->timeSlot->is_available) {
            throw ValidationException::withMessages([
                'time_slot_id' => 'Time slot is not available for booking',
            ]);
        }

        return $next($context);
    }
}
