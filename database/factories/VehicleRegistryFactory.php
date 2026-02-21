<?php

namespace Database\Factories;

use App\Models\VehicleRegistry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleRegistry>
 */
class VehicleRegistryFactory extends Factory
{
    protected $model = VehicleRegistry::class;

    public function definition(): array
    {
        $unit = fake()->randomElement(['kg', 'tons']);
        $capacity = $unit === 'kg'
            ? fake()->numberBetween(800, 35000)
            : fake()->randomFloat(2, 1, 40);

        return [
            'name_model' => fake()->randomElement([
                'Volvo FH16',
                'Tata Prima',
                'Ashok Leyland U-Truck',
                'Eicher Pro 3015',
                'BharatBenz 2823',
            ]).' '.fake()->bothify('##'),
            'license_plate' => strtoupper(fake()->bothify('??##??####')),
            'max_load_capacity' => $capacity,
            'load_unit' => $unit,
            'odometer' => fake()->randomFloat(2, 1000, 500000),
            'is_out_of_service' => fake()->boolean(15),
        ];
    }
}
