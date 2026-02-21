<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverModuleTest extends TestCase
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

    public function test_driver_index_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('driver.index'));

        $response->assertOk();
    }

    public function test_driver_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('driver.store'), [
            'full_name' => 'Alex Driver',
            'email' => 'alex.driver@example.com',
            'phone_number' => '9999999999',
            'license_number' => 'DL-12345',
            'license_expiry_date' => now()->addYear()->format('Y-m-d'),
            'total_trips' => 100,
            'completed_trips' => 95,
            'safety_score' => 88.5,
            'status' => 'available',
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('drivers', [
            'license_number' => 'DL-12345',
            'full_name' => 'Alex Driver',
            'status' => 'available',
        ]);
    }

    public function test_driver_assignment_is_blocked_when_license_expired(): void
    {
        $driver = Driver::create([
            'full_name' => 'Expired Driver',
            'email' => 'expired.driver@example.com',
            'phone_number' => '9999999988',
            'license_number' => 'DL-EXPIRED',
            'license_expiry_date' => now()->subDay()->format('Y-m-d'),
            'total_trips' => 50,
            'completed_trips' => 40,
            'safety_score' => 70,
            'status' => 'on_duty',
        ]);

        $response = $this->actingAs($this->admin)->get(route('driver.can-assign', $driver->id));

        $response->assertOk();
        $response->assertJson([
            'status' => true,
            'can_assign' => false,
        ]);
    }
}
