<?php

namespace Database\Factories;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Driver>
 */
class DriverFactory extends Factory
{
    protected $model = Driver::class;

    public function definition(): array
    {
        $totalTrips = fake()->numberBetween(0, 600);
        $completedTrips = $totalTrips > 0 ? fake()->numberBetween(0, $totalTrips) : 0;

        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->numerify('##########'),
            'license_number' => strtoupper(fake()->unique()->bothify('DL-####-??')),
            'license_expiry_date' => fake()->dateTimeBetween('-6 months', '+2 years')->format('Y-m-d'),
            'total_trips' => $totalTrips,
            'completed_trips' => $completedTrips,
            'safety_score' => fake()->randomFloat(2, 0, 100),
            'monthly_salary' => fake()->randomFloat(2, 18000, 95000),
            'status' => fake()->randomElement(['on_duty', 'on_duty', 'off_duty', 'suspended']),
        ];
    }
}
