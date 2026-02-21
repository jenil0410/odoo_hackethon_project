<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModuleTest extends TestCase
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

    public function test_user_create_page_does_not_show_master_admin_role_in_dropdown(): void
    {
        Role::create([
            'name' => 'Organization Admin',
            'guard_name' => 'web',
        ]);

        $response = $this->actingAs($this->admin)->get(route('user.create'));

        $response->assertOk();
        $roles = $response->viewData('roles');
        $this->assertNotNull($roles);
        $this->assertFalse($roles->contains(fn ($role) => $role->name === 'Master Admin'));
        $this->assertTrue($roles->contains(fn ($role) => $role->name === 'Organization Admin'));
    }

    public function test_user_can_be_created_and_assigned_to_a_role(): void
    {
        $role = Role::create([
            'name' => 'Organization Admin',
            'guard_name' => 'web',
        ]);

        $email = fake()->unique()->safeEmail();
        $contact = fake()->numerify('##########');

        $response = $this->actingAs($this->admin)->post(route('user.store'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => $email,
            'phone_number' => $contact,
            'date_of_birth' => fake()->date(),
            'gender' => 'male',
            'role_id' => $role->id,
        ]);

        $response->assertRedirect(route('user.index'));
        $this->assertDatabaseHas('users', ['email' => strtolower($email)]);

        $user = User::where('email', strtolower($email))->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('Organization Admin'));
    }

    public function test_user_index_ajax_returns_datatable_payload(): void
    {
        $response = $this->actingAs($this->admin)->get(route('user.index', [
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
