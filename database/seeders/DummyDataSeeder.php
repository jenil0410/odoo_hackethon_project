<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Role;
use App\Models\Trip;
use App\Models\User;
use App\Models\VehicleRegistry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Keep core roles/admin seeders intact and ensure base roles exist.
        $this->call([
            MasterAdminSeeder::class,
            DefaultRolesSeeder::class,
        ]);

        $assignableRoleIds = Role::query()
            ->where('name', '!=', 'Master Admin')
            ->pluck('id');

        $this->seedUsers($assignableRoleIds);
        $vehicles = VehicleRegistry::factory()->count(20)->create();
        $drivers = Driver::factory()->count(20)->create();

        $this->seedTrips($vehicles, $drivers);
    }

    private function seedUsers(Collection $roleIds): void
    {
        if ($roleIds->isEmpty()) {
            return;
        }

        $users = User::factory()->count(20)->create();

        foreach ($users as $user) {
            $roleId = $roleIds->random();
            $user->syncRoles([$roleId]);
        }
    }

    private function seedTrips(Collection $vehicles, Collection $drivers): void
    {
        $availableVehicles = $vehicles->where('is_out_of_service', false)->values();
        $availableDrivers = $drivers
            ->filter(fn (Driver $driver) => $driver->status === 'on_duty' && ! $driver->is_license_expired)
            ->values();

        if ($availableVehicles->isEmpty() || $availableDrivers->isEmpty()) {
            return;
        }

        // Seed non-dispatched trips first (can reuse same assets).
        for ($i = 0; $i < 25; $i++) {
            $vehicle = $availableVehicles->random();
            $driver = $availableDrivers->random();

            $maxKg = (float) $vehicle->max_load_capacity * ($vehicle->load_unit === 'tons' ? 1000 : 1);
            $cargoWeight = fake()->randomFloat(2, 50, max(50, $maxKg * 0.9));

            Trip::create([
                'vehicle_registry_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'origin_address' => fake()->city().' Origin',
                'destination_address' => fake()->city().' Destination',
                'cargo_weight' => $cargoWeight,
                'estimated_fuel_cost' => fake()->randomFloat(2, 500, 15000),
                'status' => fake()->randomElement(['draft', 'completed', 'cancelled']),
            ]);
        }

        // Seed dispatched trips with unique pairs to keep "availability" realistic.
        $maxDispatched = min($availableVehicles->count(), $availableDrivers->count(), 6);
        $vehiclePool = $availableVehicles->shuffle()->take($maxDispatched)->values();
        $driverPool = $availableDrivers->shuffle()->take($maxDispatched)->values();

        for ($i = 0; $i < $maxDispatched; $i++) {
            $vehicle = $vehiclePool[$i];
            $driver = $driverPool[$i];

            $maxKg = (float) $vehicle->max_load_capacity * ($vehicle->load_unit === 'tons' ? 1000 : 1);
            $cargoWeight = fake()->randomFloat(2, 50, max(50, $maxKg * 0.9));

            Trip::create([
                'vehicle_registry_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'origin_address' => fake()->city().' Origin',
                'destination_address' => fake()->city().' Destination',
                'cargo_weight' => $cargoWeight,
                'estimated_fuel_cost' => fake()->randomFloat(2, 500, 15000),
                'status' => 'dispatched',
            ]);
        }
    }
}
