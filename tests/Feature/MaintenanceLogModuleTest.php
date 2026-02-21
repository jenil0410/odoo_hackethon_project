<?php

namespace Tests\Feature;

use App\Models\MaintenanceLog;
use App\Models\Role;
use App\Models\User;
use App\Models\VehicleRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceLogModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private VehicleRegistry $vehicle;

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
            'name_model' => 'Volvo FH',
            'license_plate' => 'GJ00MH1001',
            'max_load_capacity' => 1000,
            'load_unit' => 'kg',
            'odometer' => 5000,
            'is_out_of_service' => false,
        ]);
    }

    public function test_maintenance_log_can_be_created_and_sets_vehicle_in_shop(): void
    {
        $response = $this->actingAs($this->admin)->post(route('maintenance-log.store'), [
            'vehicle_registry_id' => $this->vehicle->id,
            'title' => 'Oil Change',
            'description' => 'Quarterly maintenance',
            'service_date' => now()->toDateString(),
            'cost' => 1200,
            'status' => 'in_shop',
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('maintenance_logs', ['title' => 'Oil Change', 'status' => 'in_shop']);
        $this->assertDatabaseHas('vehicle_registries', ['id' => $this->vehicle->id, 'is_in_shop' => true]);
    }

    public function test_marking_maintenance_complete_releases_vehicle_from_shop_when_no_open_logs(): void
    {
        $log = MaintenanceLog::create([
            'vehicle_registry_id' => $this->vehicle->id,
            'title' => 'Brake Service',
            'service_date' => now()->toDateString(),
            'cost' => 900,
            'status' => 'in_shop',
        ]);
        $this->vehicle->update(['is_in_shop' => true]);

        $response = $this->actingAs($this->admin)->post(route('maintenance-log.complete', $log->id));

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('maintenance_logs', ['id' => $log->id, 'status' => 'completed']);
        $this->assertDatabaseHas('vehicle_registries', ['id' => $this->vehicle->id, 'is_in_shop' => false]);
    }
}
