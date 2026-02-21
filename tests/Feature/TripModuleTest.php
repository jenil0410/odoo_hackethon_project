<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Role;
use App\Models\Trip;
use App\Models\User;
use App\Models\VehicleRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private VehicleRegistry $vehicle;
    private Driver $driver;

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
            'name_model' => 'Volvo Truck',
            'license_plate' => 'GJ01AA1111',
            'max_load_capacity' => 2000,
            'load_unit' => 'kg',
            'odometer' => 10000,
            'is_out_of_service' => false,
        ]);

        $this->driver = Driver::create([
            'full_name' => 'John Driver',
            'email' => 'john.driver@example.com',
            'phone_number' => '9999999988',
            'license_number' => 'DRV-1001',
            'license_expiry_date' => now()->addYear()->format('Y-m-d'),
            'total_trips' => 30,
            'completed_trips' => 28,
            'safety_score' => 90,
            'status' => 'on_duty',
        ]);
    }

    public function test_trip_index_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('trip.index'));
        $response->assertOk();
    }

    public function test_trip_can_be_created_as_draft(): void
    {
        $response = $this->actingAs($this->admin)->post(route('trip.store'), [
            'vehicle_registry_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id,
            'origin_address' => 'Ahmedabad',
            'destination_address' => 'Surat',
            'cargo_weight' => 1500,
            'estimated_fuel_cost' => 2500,
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('trips', [
            'vehicle_registry_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id,
            'status' => 'draft',
        ]);
    }

    public function test_trip_creation_fails_when_cargo_exceeds_vehicle_capacity(): void
    {
        $response = $this->actingAs($this->admin)->post(route('trip.store'), [
            'vehicle_registry_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id,
            'origin_address' => 'Ahmedabad',
            'destination_address' => 'Surat',
            'cargo_weight' => 2500,
            'estimated_fuel_cost' => 2500,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', false);
    }

    public function test_trip_status_can_move_from_draft_to_dispatched_to_completed(): void
    {
        $trip = Trip::create([
            'vehicle_registry_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id,
            'origin_address' => 'A',
            'destination_address' => 'B',
            'cargo_weight' => 1000,
            'estimated_fuel_cost' => 500,
            'status' => 'draft',
        ]);

        $dispatched = $this->actingAs($this->admin)->post(route('trip.status', $trip->id), [
            'status' => 'dispatched',
        ]);
        $dispatched->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('trips', ['id' => $trip->id, 'status' => 'dispatched']);

        $completed = $this->actingAs($this->admin)->post(route('trip.status', $trip->id), [
            'status' => 'completed',
            'final_odometer' => 10500,
            'revenue_amount' => 15000,
        ]);
        $completed->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('trips', ['id' => $trip->id, 'status' => 'completed', 'final_odometer' => 10500.00]);
    }
}
