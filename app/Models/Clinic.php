<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clinic extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

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

    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class, 'clinic_id');
    }
}
