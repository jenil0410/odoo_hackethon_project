<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementModuleTest extends TestCase
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

    public function test_role_creation_creates_permission_rows_for_all_modules(): void
    {
        $response = $this->actingAs($this->admin)->post(route('role.store'), [
            'name' => 'Organization Admin',
        ]);

        $role = Role::where('name', 'Organization Admin')->first();

        $response->assertRedirect(route('role.edit', $role?->id));
        $this->assertNotNull($role);

        foreach (Permission::moduleList() as $module) {
            $this->assertDatabaseHas('module_permission', [
                'role_id' => $role->id,
                'module' => $module,
            ]);
        }
    }

    public function test_role_index_ajax_returns_datatable_payload(): void
    {
        Role::create([
            'name' => 'Organization Admin',
            'guard_name' => 'web',
        ]);

        $response = $this->actingAs($this->admin)->get(route('role.index', [
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

