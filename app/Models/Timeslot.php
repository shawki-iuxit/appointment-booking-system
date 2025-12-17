<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timeslot extends Model
{
    use SoftDeletes;

    protected $table = 'time_slots';

    protected $fillable = [
        'doctor_id',
        'date',
        'start_time',
        'end_time',
        'is_available',
    ];

    protected $casts = [
        'doctor_id' => 'integer',
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_available' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function markAsUnavailable(): void
    {
        $this->is_available = false;
    }

    public function markAsAvailable(): void
    {
        $this->is_available = true;
    }

    public function isPast(): bool
    {
        return $this->date->isPast() || ($this->date->isToday() && $this->end_time->isPast());
    }

    public function getDurationInMinutes(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    public function isOverlapping(string $date, string $startTime, string $endTime): bool
    {
        if ($this->date->format('Y-m-d') !== $date) {
            return false;
        }

        $newStart = Carbon::createFromFormat('H:i', $startTime);
        $newEnd = Carbon::createFromFormat('H:i', $endTime);

        return $newStart->lt($this->end_time) && $newEnd->gt($this->start_time);
    }
}
