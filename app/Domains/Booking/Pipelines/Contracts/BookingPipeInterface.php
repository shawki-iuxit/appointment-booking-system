<?php

namespace App\Domains\Booking\Pipelines\Contracts;

use App\Domains\Booking\Pipelines\BookingContext;
use Closure;

interface BookingPipeInterface
{
    public function handle(BookingContext $context, Closure $next): BookingContext;
}
