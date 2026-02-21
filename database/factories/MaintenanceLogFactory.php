<?php

namespace Database\Factories;

use App\Models\MaintenanceLog;
use App\Models\VehicleRegistry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceLog>
 */
class MaintenanceLogFactory extends Factory
{
    protected $model = MaintenanceLog::class;

    public function definition(): array
    {
        $status = fake()->randomElement(['completed', 'completed', 'in_shop']);

        return [
            'vehicle_registry_id' => VehicleRegistry::factory(),
            'title' => fake()->randomElement([
                'Oil Change',
                'Brake Pad Replacement',
                'Tyre Rotation',
                'Engine Diagnostics',
                'Suspension Service',
            ]),
            'description' => fake()->optional()->sentence(8),
            'service_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'cost' => fake()->randomFloat(2, 500, 45000),
            'status' => $status,
        ];
    }
}
