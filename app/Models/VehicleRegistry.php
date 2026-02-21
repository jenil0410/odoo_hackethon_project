<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    ];

    protected $casts = [
        'max_load_capacity' => 'decimal:2',
        'odometer' => 'decimal:2',
        'is_out_of_service' => 'boolean',
    ];
}
