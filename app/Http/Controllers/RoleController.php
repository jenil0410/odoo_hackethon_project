<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Role::query()->latest();

            if ($request->filled('guard_name_filter')) {
                $query->where('guard_name', $request->guard_name_filter);
            }

            $search = trim((string) data_get($request->input('search'), 'value', ''));
            $draw = (int) $request->input('draw', 1);
            $start = max(0, (int) $request->input('start', 0));
            $length = max(1, (int) $request->input('length', 10));

            $recordsTotal = (clone $query)->count();

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('guard_name', 'like', "%{$search}%");
                });
            }

            $recordsFiltered = (clone $query)->count();
            $roles = $query->skip($start)->take($length)->get();

            $data = $roles->values()->map(function ($row, $idx) use ($start) {
                $action = '<div class="dropdown"><button type="button" class="btn btn-primary px-1 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">Action</button><div class="dropdown-menu"><a class="dropdown-item" href="'.route('role.edit', $row->id).'">Edit</a><a class="dropdown-item" href="'.route('role.show', $row->id).'">View</a><a class="dropdown-item" href="javascript:void(0)" onclick="deleteRole('.$row->id.', \''.e($row->name).'\')">Delete</a></div></div>';

                return [
                    'DT_RowIndex' => $start + $idx + 1,
                    'action' => $action,
                    'name' => $row->name,
                    'guard_name' => $row->guard_name,
                ];
            })->all();

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        }

        $guards = Role::query()
            ->select('guard_name')
            ->distinct()
            ->orderBy('guard_name')
            ->pluck('guard_name');

        return view('role.index', compact('guards'));
    }

    public function create()
    {
        return view('role.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:50', 'unique:roles,name'],
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        return redirect()->route('role.edit', $role->id)->with('success', 'Role created successfully.');
    }

    public function show(string $id)
    {
        $role = Role::findOrFail($id);
        Permission::ensureRolePermissionRows($role->id);
        $permissionData = Permission::where('role_id', $role->id)->get()->keyBy('module');

        return view('role.show', [
            'role' => $role,
            'accessData' => Permission::moduleList(),
            'permissionData' => $permissionData,
        ]);
    }

    public function edit(string $id)
    {
        $role = Role::findOrFail($id);
        Permission::ensureRolePermissionRows($role->id);
        $permissionData = Permission::where('role_id', $role->id)->get()->keyBy('module');

        return view('role.update', [
            'role' => $role,
            'accessData' => Permission::moduleList(),
            'permissionData' => $permissionData,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:50', 'unique:roles,name,'.$role->id],
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $role->update([
            'name' => $request->name,
        ]);

        $now = now();
        $upserts = [];
        foreach (Permission::moduleList() as $module) {
            $actions = $request->input("permission.{$module}", []);
            $upserts[] = [
                'role_id' => $role->id,
                'module' => $module,
                'create' => !empty($actions['create']),
                'read' => !empty($actions['read']),
                'update' => !empty($actions['update']),
                'delete' => !empty($actions['delete']),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        Permission::upsert(
            $upserts,
            ['role_id', 'module'],
            ['create', 'read', 'update', 'delete', 'updated_at']
        );

        return redirect()->route('role.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(string $id)
    {
        $role = Role::find($id);

        if (! $role) {
            return response()->json([
                'status' => false,
                'message' => 'Role not found.',
            ], 404);
        }

        if ($role->users()->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'This role is already assigned to users.',
            ]);
        }

        Permission::where('role_id', $role->id)->delete();
        $role->delete();

        return response()->json([
            'status' => true,
            'message' => 'Role deleted successfully.',
        ]);
    }
}
