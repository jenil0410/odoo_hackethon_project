<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ON_TRIP = 'on_trip';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_OFF_DUTY = 'off_duty';
    public const STATUS_ON_DUTY = 'on_duty';

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

    public static function allowedStatuses(): array
    {
        return [
            self::STATUS_ON_TRIP,
            self::STATUS_SUSPENDED,
            self::STATUS_AVAILABLE,
            self::STATUS_OFF_DUTY,
            self::STATUS_ON_DUTY,
        ];
    }

    public static function assignableStatuses(): array
    {
        return [
            self::STATUS_AVAILABLE,
            self::STATUS_ON_DUTY,
        ];
    }

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
        return in_array((string) $this->status, self::assignableStatuses(), true)
            && ! $this->is_license_expired;
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }
}
