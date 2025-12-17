<?php

namespace App\Domains\Booking\Pipelines\Pipes;

use App\Domains\Booking\Pipelines\BookingContext;
use App\Domains\Booking\Pipelines\Contracts\BookingPipeInterface;
use App\Domains\Timeslot\Contracts\TimeslotRepositoryInterface;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ValidateTimeSlotExistsPipe implements BookingPipeInterface
{
    public function __construct(
        private readonly TimeslotRepositoryInterface $timeslotRepository
    ) {}

    public function handle(BookingContext $context, Closure $next): BookingContext
    {
        $timeSlot = $this->timeslotRepository->find($context->timeSlotId);

        if (! $timeSlot) {
            throw new ModelNotFoundException('Time slot not found');
        }

        return $next($context->setTimeSlot($timeSlot));
    }
}
