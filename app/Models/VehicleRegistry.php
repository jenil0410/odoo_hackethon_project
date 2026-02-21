<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleRegistry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name_model',
        'license_plate',
        'max_load_capacity',
        'load_unit',
        'odometer',
        'is_out_of_service',
        'is_in_shop',
        'acquisition_cost',
    ];

    protected $casts = [
        'max_load_capacity' => 'decimal:2',
        'odometer' => 'decimal:2',
        'is_out_of_service' => 'boolean',
        'is_in_shop' => 'boolean',
        'acquisition_cost' => 'decimal:2',
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'vehicle_registry_id');
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class, 'vehicle_registry_id');
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class, 'vehicle_registry_id');
    }

    public function getIsDispatchableAttribute(): bool
    {
        return ! $this->is_out_of_service && ! $this->is_in_shop;
    }
}
