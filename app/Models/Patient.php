<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'name',
        'email',
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
}
