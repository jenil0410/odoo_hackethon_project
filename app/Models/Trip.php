<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'status',
    ];

    protected $casts = [
        'cargo_weight' => 'decimal:2',
        'estimated_fuel_cost' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(VehicleRegistry::class, 'vehicle_registry_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
