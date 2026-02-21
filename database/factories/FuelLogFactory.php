<?php

namespace Database\Factories;

use App\Models\FuelLog;
use App\Models\Trip;
use App\Models\VehicleRegistry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FuelLog>
 */
class FuelLogFactory extends Factory
{
    protected $model = FuelLog::class;

    public function definition(): array
    {
        return [
            'vehicle_registry_id' => VehicleRegistry::factory(),
            'trip_id' => null,
            'liters' => fake()->randomFloat(2, 10, 280),
            'cost' => fake()->randomFloat(2, 1200, 35000),
            'logged_on' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
        ];
    }

    public function forCompletedTrip(Trip $trip): static
    {
        return $this->state(fn () => [
            'vehicle_registry_id' => $trip->vehicle_registry_id,
            'trip_id' => $trip->id,
            'logged_on' => optional($trip->completed_at)->toDateString() ?? now()->toDateString(),
        ]);
    }
}
