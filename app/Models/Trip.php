<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_registry_id',
        'driver_id',
        'origin_address',
        'destination_address',
        'cargo_weight',
        'estimated_fuel_cost',
        'actual_distance_km',
        'revenue_amount',
        'final_odometer',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'cargo_weight' => 'decimal:2',
        'estimated_fuel_cost' => 'decimal:2',
        'actual_distance_km' => 'decimal:2',
        'revenue_amount' => 'decimal:2',
        'final_odometer' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(VehicleRegistry::class, 'vehicle_registry_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class);
    }
}
