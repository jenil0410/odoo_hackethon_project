<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterAdminSeeder extends Seeder
{
    public function run(): void
    {
        $masterRole = Role::firstOrCreate(
            ['name' => 'Master Admin', 'guard_name' => 'web']
        );

        $now = now();
        $rows = [];
        foreach (Permission::moduleList() as $module) {
            $rows[] = [
                'role_id' => $masterRole->id,
                'module' => $module,
                'create' => true,
                'read' => true,
                'update' => true,
                'delete' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        Permission::upsert(
            $rows,
            ['role_id', 'module'],
            ['create', 'read', 'update', 'delete', 'updated_at']
        );

        $masterUser = User::updateOrCreate(
            ['email' => 'masteradmin@example.com'],
            [
                'first_name' => 'Master',
                'last_name' => 'Admin',
                'password' => Hash::make('123456789'),
                'phone_number' => '9999999999',
            ]
        );

        $masterUser->syncRoles([$masterRole->id]);

        $roleUserMap = [
            'Fleet Managers' => [
                'email' => 'fleetmanager@example.com',
                'first_name' => 'Fleet',
                'last_name' => 'Manager',
                'phone_number' => '9999999991',
            ],
            'Dispatchers' => [
                'email' => 'dispatcher@example.com',
                'first_name' => 'Dispatcher',
                'last_name' => 'User',
                'phone_number' => '9999999992',
            ],
            'Safety Officers' => [
                'email' => 'safetyofficer@example.com',
                'first_name' => 'Safety',
                'last_name' => 'Officer',
                'phone_number' => '9999999993',
            ],
            'Financial Analysts' => [
                'email' => 'financialanalyst@example.com',
                'first_name' => 'Financial',
                'last_name' => 'Analyst',
                'phone_number' => '9999999994',
            ],
        ];

        foreach ($roleUserMap as $roleName => $userData) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'password' => Hash::make('123456789'),
                    'phone_number' => $userData['phone_number'],
                ]
            );

            $user->syncRoles([$role->id]);
        }
    }
}
