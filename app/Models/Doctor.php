<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    protected $fillable = [
        'clinic_id',
        'name',
        'specialization',
        'status',
    ];

    protected $casts = [
        'clinic_id' => 'integer',
        'status' => 'integer',
    ];

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function activate(): void
    {
        $this->status = self::STATUS_ACTIVE;
    }

    public function deactivate(): void
    {
        $this->status = self::STATUS_INACTIVE;
    }

    public function timeslots(): HasMany
    {
        return $this->hasMany(Timeslot::class);
    }
}
