<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogModuleTest extends TestCase
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

    public function test_activity_log_index_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('activity-log.index'));

        $response->assertOk();
        $response->assertSee('Activity Log');
    }
}

