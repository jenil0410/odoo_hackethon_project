<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\FuelLog;
use App\Models\MaintenanceLog;
use App\Models\Permission;
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
        $this->call([
            MasterAdminSeeder::class,
            DefaultRolesSeeder::class,
        ]);

        $roles = Role::query()->get(['id', 'name']);
        $assignableRoles = $roles->where('name', '!=', 'Master Admin')->values();

        foreach ($roles as $role) {
            Permission::ensureRolePermissionRows($role->id);
        }

        $this->seedUsers($assignableRoles);
        $vehicles = VehicleRegistry::factory()->count(35)->create();
        $drivers = Driver::factory()->count(35)->create();

        $this->seedTrips($vehicles, $drivers);
        $this->seedMaintenanceLogs($vehicles);
        $this->syncVehicleShopStatus($vehicles);
        $this->seedFuelLogs($vehicles);
        $this->recalculateDriverPerformance($drivers);
    }

    private function seedUsers(Collection $roles): void
    {
        if ($roles->isEmpty()) {
            return;
        }

        $users = User::factory()->count(45)->create();

        foreach ($users as $user) {
            $role = $roles->random();
            $user->syncRoles([$role->id]);
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

        for ($i = 0; $i < 70; $i++) {
            $vehicle = $availableVehicles->random();
            $driver = $availableDrivers->random();

            $maxKg = (float) $vehicle->max_load_capacity * ($vehicle->load_unit === 'tons' ? 1000 : 1);
            $cargoWeight = fake()->randomFloat(2, 50, max(120, $maxKg * 0.88));
            $status = fake()->randomElement(['draft', 'completed', 'completed', 'cancelled']);
            $completedAt = $status === 'completed' ? fake()->dateTimeBetween('-6 months', 'now') : null;

            $baseOdometer = (float) $vehicle->odometer;
            $distance = $status === 'completed' ? fake()->randomFloat(2, 40, 2000) : null;
            $finalOdometer = $distance !== null ? round($baseOdometer + $distance, 2) : null;

            $trip = Trip::create([
                'vehicle_registry_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'origin_address' => fake()->city().' Origin',
                'destination_address' => fake()->city().' Destination',
                'cargo_weight' => $cargoWeight,
                'estimated_fuel_cost' => fake()->randomFloat(2, 500, 15000),
                'actual_distance_km' => $distance,
                'revenue_amount' => $status === 'completed' ? fake()->randomFloat(2, 9000, 200000) : 0,
                'final_odometer' => $finalOdometer,
                'status' => $status,
                'completed_at' => $completedAt,
            ]);

            if ($status === 'completed' && $finalOdometer !== null) {
                $vehicle->odometer = max((float) $vehicle->odometer, $finalOdometer);
                $vehicle->save();
            }
        }

        $maxDispatched = min($availableVehicles->count(), $availableDrivers->count(), 10);
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

    private function seedMaintenanceLogs(Collection $vehicles): void
    {
        $subset = $vehicles->shuffle()->take(min(20, $vehicles->count()));

        foreach ($subset as $vehicle) {
            $count = fake()->numberBetween(1, 3);
            for ($i = 0; $i < $count; $i++) {
                MaintenanceLog::factory()->create([
                    'vehicle_registry_id' => $vehicle->id,
                    'status' => fake()->randomElement(['completed', 'completed', 'in_shop']),
                ]);
            }
        }
    }

    private function syncVehicleShopStatus(Collection $vehicles): void
    {
        foreach ($vehicles as $vehicle) {
            $isInShop = MaintenanceLog::query()
                ->where('vehicle_registry_id', $vehicle->id)
                ->where('status', 'in_shop')
                ->exists();

            $vehicle->update(['is_in_shop' => $isInShop]);
        }
    }

    private function seedFuelLogs(Collection $vehicles): void
    {
        $completedTrips = Trip::query()
            ->where('status', 'completed')
            ->get(['id', 'vehicle_registry_id', 'completed_at']);

        foreach ($completedTrips as $trip) {
            $entries = fake()->numberBetween(1, 2);
            for ($i = 0; $i < $entries; $i++) {
                FuelLog::factory()->forCompletedTrip($trip)->create();
            }
        }

        $standaloneVehicles = $vehicles->shuffle()->take(min(15, $vehicles->count()));
        foreach ($standaloneVehicles as $vehicle) {
            FuelLog::factory()->create([
                'vehicle_registry_id' => $vehicle->id,
                'trip_id' => null,
            ]);
        }
    }

    private function recalculateDriverPerformance(Collection $drivers): void
    {
        foreach ($drivers as $driver) {
            $totalTrips = Trip::query()->where('driver_id', $driver->id)->count();
            $completedTrips = Trip::query()
                ->where('driver_id', $driver->id)
                ->where('status', 'completed')
                ->count();

            $driver->update([
                'total_trips' => $totalTrips,
                'completed_trips' => $completedTrips,
                'safety_score' => fake()->randomFloat(2, 65, 99.5),
            ]);
        }
    }
}
