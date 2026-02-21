<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleRegistry extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_ON_TRIP = 'on_trip';
    public const STATUS_IN_SHOP = 'in_shop';
    public const STATUS_RETIRED = 'retired';

    protected $fillable = [
        'name_model',
        'license_plate',
        'max_load_capacity',
        'load_unit',
        'odometer',
        'status',
        'is_out_of_service',
    ];

    protected $casts = [
        'max_load_capacity' => 'decimal:2',
        'odometer' => 'decimal:2',
        'is_out_of_service' => 'boolean',
    ];

    public static function allowedStatuses(): array
    {
        return [
            self::STATUS_AVAILABLE,
            self::STATUS_ON_TRIP,
            self::STATUS_IN_SHOP,
            self::STATUS_RETIRED,
        ];
    }

    public static function assignableStatuses(): array
    {
        return [
            self::STATUS_AVAILABLE,
        ];
    }

    public function canBeAssigned(): bool
    {
        return in_array((string) $this->status, self::assignableStatuses(), true);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'vehicle_registry_id');
    }
}
