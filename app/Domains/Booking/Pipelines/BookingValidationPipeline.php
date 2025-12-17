<?php

namespace App\Domains\Booking\Pipelines;

use App\Domains\Booking\Pipelines\Pipes\ValidateTimeSlotAvailablePipe;
use App\Domains\Booking\Pipelines\Pipes\ValidateTimeSlotExistsPipe;
use App\Domains\Booking\Pipelines\Pipes\ValidateTimeSlotNotBookedPipe;
use App\Domains\Booking\Pipelines\Pipes\ValidateTimeSlotNotPastPipe;
use Illuminate\Pipeline\Pipeline;

class BookingValidationPipeline
{
    public function __construct(
        private readonly Pipeline $pipeline,
        private readonly ValidateTimeSlotExistsPipe $validateTimeSlotExistsPipe,
        private readonly ValidateTimeSlotAvailablePipe $validateTimeSlotAvailablePipe,
        private readonly ValidateTimeSlotNotBookedPipe $validateTimeSlotNotBookedPipe,
        private readonly ValidateTimeSlotNotPastPipe $validateTimeSlotNotPastPipe
    ) {}

    public function process(BookingContext $context): BookingContext
    {
        return $this->pipeline
            ->send($context)
            ->through([
                $this->validateTimeSlotExistsPipe,
                $this->validateTimeSlotAvailablePipe,
                $this->validateTimeSlotNotBookedPipe,
                $this->validateTimeSlotNotPastPipe,
            ])
            ->thenReturn();
    }
}
