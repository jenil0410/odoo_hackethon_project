<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\VehicleRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleRegistryModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

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
    }

    public function test_vehicle_registry_index_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('vehicle-registry.index'));

        $response->assertOk();
    }

    public function test_vehicle_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('vehicle-registry.store'), [
            'name_model' => 'Volvo FH16',
            'license_plate' => 'GJ01AB1234',
            'max_load_capacity' => 18000,
            'load_unit' => 'kg',
            'odometer' => 125000.5,
            'is_out_of_service' => 0,
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('vehicle_registries', [
            'license_plate' => 'GJ01AB1234',
            'name_model' => 'Volvo FH16',
        ]);
    }

    public function test_vehicle_registry_index_ajax_returns_datatable_payload(): void
    {
        VehicleRegistry::create([
            'name_model' => 'Tata 407',
            'license_plate' => 'GJ05CD7788',
            'max_load_capacity' => 2.5,
            'load_unit' => 'tons',
            'odometer' => 15000,
            'is_out_of_service' => false,
        ]);

        $response = $this->actingAs($this->admin)->get(route('vehicle-registry.index', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
        ]), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
        ]);
    }
}
