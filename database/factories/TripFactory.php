<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\Trip;
use App\Models\VehicleRegistry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trip>
 */
class TripFactory extends Factory
{
    protected $model = Trip::class;

    public function definition(): array
    {
        return [
            'vehicle_registry_id' => VehicleRegistry::factory(),
            'driver_id' => Driver::factory(),
            'origin_address' => fake()->city().' Warehouse',
            'destination_address' => fake()->city().' Hub',
            'cargo_weight' => fake()->randomFloat(2, 100, 15000),
            'estimated_fuel_cost' => fake()->randomFloat(2, 500, 15000),
            'status' => fake()->randomElement(['draft', 'dispatched', 'completed', 'cancelled']),
        ];
    }
}
