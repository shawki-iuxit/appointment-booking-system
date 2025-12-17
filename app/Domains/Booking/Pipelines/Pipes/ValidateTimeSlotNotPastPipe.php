<?php

namespace App\Domains\Booking\Pipelines\Pipes;

use App\Domains\Booking\Pipelines\BookingContext;
use App\Domains\Booking\Pipelines\Contracts\BookingPipeInterface;
use Closure;
use Illuminate\Validation\ValidationException;

class ValidateTimeSlotNotPastPipe implements BookingPipeInterface
{
    public function handle(BookingContext $context, Closure $next): BookingContext
    {
        if ($context->timeSlot->isPast()) {
            throw ValidationException::withMessages([
                'time_slot_id' => 'Cannot book a past time slot',
            ]);
        }

        return $next($context);
    }
}
