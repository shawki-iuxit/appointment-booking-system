<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'time_slot_id',
        'patient_id',
        'booked_at',
    ];

    protected $casts = [
        'time_slot_id' => 'integer',
        'patient_id' => 'integer',
        'booked_at' => 'datetime',
    ];

    public function timeslot(): BelongsTo
    {
        return $this->belongsTo(Timeslot::class, 'time_slot_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
