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
        $role = Role::firstOrCreate(
            ['name' => 'Master Admin', 'guard_name' => 'web']
        );

        $now = now();
        $rows = [];
        foreach (Permission::moduleList() as $module) {
            $rows[] = [
                'role_id' => $role->id,
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

        $user = User::updateOrCreate(
            ['email' => 'masteradmin@example.com'],
            [
                'first_name' => 'Master',
                'last_name' => 'Admin',
                'password' => Hash::make('MasterAdmin@123'),
                'phone_number' => '9999999999',
            ]
        );

        $user->syncRoles([$role->id]);
    }
}
