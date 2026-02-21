<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_registry_id',
        'trip_id',
        'liters',
        'cost',
        'logged_on',
    ];

    protected $casts = [
        'liters' => 'decimal:2',
        'cost' => 'decimal:2',
        'logged_on' => 'date',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(VehicleRegistry::class, 'vehicle_registry_id');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
