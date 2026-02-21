<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalAnalyticsModuleTest extends TestCase
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

    public function test_analytics_index_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('analytics.index'));

        $response->assertOk();
        $response->assertSee('Operational Analytics');
    }

    public function test_payroll_csv_export_downloads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('analytics.export.payroll.csv', ['month' => now()->format('Y-m')]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
