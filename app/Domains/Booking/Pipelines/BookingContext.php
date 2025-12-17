<?php

namespace App\Domains\Booking\Pipelines;

use App\Models\Timeslot;

class BookingContext
{
    public function __construct(
        public readonly int $timeSlotId,
        public readonly int $patientId,
        public ?Timeslot $timeSlot = null
    ) {}

    public function setTimeSlot(Timeslot $timeSlot): self
    {
        return new self($this->timeSlotId, $this->patientId, $timeSlot);
    }
}
