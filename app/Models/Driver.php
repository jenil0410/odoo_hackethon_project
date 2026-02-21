<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'full_name',
        'email',
        'phone_number',
        'license_number',
        'license_expiry_date',
        'total_trips',
        'completed_trips',
        'safety_score',
        'status',
    ];

    protected $casts = [
        'license_expiry_date' => 'date',
        'total_trips' => 'integer',
        'completed_trips' => 'integer',
        'safety_score' => 'decimal:2',
    ];

    public function getTripCompletionRateAttribute(): float
    {
        if ((int) $this->total_trips === 0) {
            return 0;
        }

        return round(((int) $this->completed_trips / (int) $this->total_trips) * 100, 2);
    }

    public function getIsLicenseExpiredAttribute(): bool
    {
        return $this->license_expiry_date?->isPast() ?? false;
    }

    public function canBeAssigned(): bool
    {
        return ! $this->is_license_expired;
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }
}
