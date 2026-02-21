<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DefaultRolesSeeder extends Seeder
{
    public function run(): void
    {
        $rolePermissions = [
            'Fleet Managers' => [
                'Dashboard' => ['read' => true],
                'Vehicle Registry' => ['read' => true, 'create' => true, 'update' => true],
                'Driver' => ['read' => true],
                'Trip' => ['read' => true, 'create' => true, 'update' => true],
                'Maintenance Log' => ['read' => true, 'create' => true, 'update' => true],
                'Fuel Log' => ['read' => true],
                'Operational Analytics' => ['read' => true],
            ],
            'Dispatchers' => [
                'Dashboard' => ['read' => true],
                'Vehicle Registry' => ['read' => true],
                'Driver' => ['read' => true],
                'Trip' => ['read' => true, 'create' => true, 'update' => true],
            ],
            'Safety Officers' => [
                'Dashboard' => ['read' => true],
                'Driver' => ['read' => true, 'update' => true],
                'Trip' => ['read' => true],
                'Maintenance Log' => ['read' => true],
            ],
            'Financial Analysts' => [
                'Dashboard' => ['read' => true],
                'Trip' => ['read' => true],
                'Maintenance Log' => ['read' => true],
                'Fuel Log' => ['read' => true],
                'Vehicle Registry' => ['read' => true],
                'Operational Analytics' => ['read' => true],
            ],
        ];

        $modules = Permission::moduleList();
        $now = now();

        foreach ($rolePermissions as $roleName => $grants) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            Permission::ensureRolePermissionRows($role->id);

            $rows = [];
            foreach ($modules as $module) {
                $moduleGrant = $grants[$module] ?? [];
                $rows[] = [
                    'role_id' => $role->id,
                    'module' => $module,
                    'create' => (bool) ($moduleGrant['create'] ?? false),
                    'read' => (bool) ($moduleGrant['read'] ?? false),
                    'update' => (bool) ($moduleGrant['update'] ?? false),
                    'delete' => (bool) ($moduleGrant['delete'] ?? false),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            Permission::upsert(
                $rows,
                ['role_id', 'module'],
                ['create', 'read', 'update', 'delete', 'updated_at']
            );
        }
    }
}
