<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class UserPermissionController extends Controller
{
    public function edit(string $id)
    {
        $user = User::with('roles')->find($id);

        if (! $user) {
            return Redirect::back()->with('error', 'User not found.');
        }

        $role = $user->roles->first();
        $rolePermissionData = $role
            ? Permission::where('role_id', $role->id)->get()->keyBy('module')
            : collect();

        $userPermissionData = UserPermission::where('user_id', $user->id)->get()->keyBy('module');

        return view('user.permissions', [
            'user' => $user,
            'role' => $role,
            'accessData' => Permission::moduleList(),
            'rolePermissionData' => $rolePermissionData,
            'userPermissionData' => $userPermissionData,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'all_modules' => 'required|array',
        ]);

        $user = User::findOrFail($id);

        $permissions = $request->input('permission', []);
        $allModules = $request->input('all_modules', []);
        $now = now();
        $upserts = [];

        foreach ($allModules as $module) {
            $value = $permissions[$module] ?? [];
            $upserts[] = [
                'user_id' => $user->id,
                'module' => $module,
                'create' => ! empty($value['create']),
                'read' => ! empty($value['read']),
                'update' => ! empty($value['update']),
                'delete' => ! empty($value['delete']),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        UserPermission::upsert(
            $upserts,
            ['user_id', 'module'],
            ['create', 'read', 'update', 'delete', 'updated_at']
        );

        return redirect()->route('user.index')->with('success', 'User permissions updated successfully.');
    }
}
