<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Role;
use App\Models\Trip;
use App\Models\User;
use App\Models\VehicleRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FuelLogModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private VehicleRegistry $vehicle;
    private Trip $trip;

    protected function setUp(): void
    {
        parent::setUp();

        $masterRole = Role::create([
            'name' => 'Master Admin',
            'guard_name' => 'web',
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Master Admin User',
            'email' => 'masteradmin@example.com',
        ]);
        $this->admin->assignRole($masterRole);

        $this->vehicle = VehicleRegistry::create([
            'name_model' => 'Ashok Leyland',
            'license_plate' => 'GJ00FL1234',
            'max_load_capacity' => 1000,
            'load_unit' => 'kg',
            'odometer' => 1000,
            'is_out_of_service' => false,
        ]);

        $driver = Driver::create([
            'full_name' => 'Driver A',
            'license_number' => 'DL-1000',
            'license_expiry_date' => now()->addYear()->toDateString(),
            'total_trips' => 1,
            'completed_trips' => 1,
            'safety_score' => 80,
            'status' => 'on_duty',
        ]);

        $this->trip = Trip::create([
            'vehicle_registry_id' => $this->vehicle->id,
            'driver_id' => $driver->id,
            'origin_address' => 'A',
            'destination_address' => 'B',
            'cargo_weight' => 500,
            'estimated_fuel_cost' => 1000,
            'status' => 'completed',
            'completed_at' => now(),
            'actual_distance_km' => 220,
            'revenue_amount' => 5000,
        ]);
    }

    public function test_fuel_log_can_be_created_for_completed_trip(): void
    {
        $response = $this->actingAs($this->admin)->post(route('fuel-log.store'), [
            'vehicle_registry_id' => $this->vehicle->id,
            'trip_id' => $this->trip->id,
            'liters' => 30,
            'cost' => 2700,
            'logged_on' => now()->toDateString(),
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('fuel_logs', [
            'vehicle_registry_id' => $this->vehicle->id,
            'trip_id' => $this->trip->id,
            'liters' => 30.00,
        ]);
    }
}
