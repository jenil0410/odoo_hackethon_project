<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $baseQuery = User::query()
                ->where('id', '<>', Auth::id())
                ->select(['id', 'first_name', 'last_name', 'email', 'phone_number', 'gender', 'created_at'])
                ->with(['roles:id,name'])
                ->latest();

            if ($request->filled('role_id_filter')) {
                $baseQuery->whereHas('roles', function ($q) use ($request) {
                    $q->where('id', $request->role_id_filter);
                });
            }

            $search = trim((string) data_get($request->input('search'), 'value', ''));
            $draw = (int) $request->input('draw', 1);
            $start = max(0, (int) $request->input('start', 0));
            $length = max(1, (int) $request->input('length', 10));

            $recordsTotal = (clone $baseQuery)->count();

            if ($search !== '') {
                $baseQuery->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('gender', 'like', "%{$search}%")
                        ->orWhereHas('roles', function ($roleQuery) use ($search) {
                            $roleQuery->where('name', 'like', "%{$search}%");
                        });
                });
            }

            $recordsFiltered = (clone $baseQuery)->count();
            $users = $baseQuery->skip($start)->take($length)->get();
            $readCheck = Permission::checkCRUDPermissionToUser('User', 'read');
            $updateCheck = Permission::checkCRUDPermissionToUser('User', 'update');
            $deleteCheck = Permission::checkCRUDPermissionToUser('User', 'delete');
            $isSuperAdmin = Permission::isSuperAdmin();

            $data = $users->values()->map(function ($row, $idx) use ($start, $readCheck, $updateCheck, $deleteCheck, $isSuperAdmin) {
                    $html = '';

                    if ($updateCheck) {
                        $html .= '<li><a class="dropdown-item" href="'.route('user.edit', $row->id).'">Edit</a></li>';
                        $html .= '<li><a class="dropdown-item" href="'.route('user.permissions', $row->id).'">Permissions</a></li>';
                    }
                    if ($readCheck) {
                        $html .= '<li><a class="dropdown-item" href="'.route('user.show', $row->id).'">View</a></li>';
                    }
                    if ($isSuperAdmin || $deleteCheck) {
                        $html .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="deleteUser(\''.$row->id.'\', \''.e($row->name).'\')">Delete</a></li>';
                    }
                    if (! $isSuperAdmin && ! $updateCheck && ! $readCheck && ! $deleteCheck) {
                        $action = '';
                    } else {
                        $action = '<div class="dropdown"><button type="button" class="btn btn-primary px-1 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">Action</button><div class="dropdown-menu">'.$html.'</div></div>';
                    }

                    return [
                        'id' => $row->id,
                        'action' => $action,
                        'DT_RowIndex' => $start + $idx + 1,
                        'name' => $row->full_name,
                        'role_id' => optional($row->roles->first())->name ?? '-',
                        'phone_number' => $row->phone_number ?? '-',
                        'gender' => $row->gender ? ucfirst($row->gender) : '-',
                    ];
                })->all();

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        }

        $roles = Role::query()
            ->where('name', '!=', 'Master Admin')
            ->orderBy('name')
            ->get();

        return view('user.index', compact('request', 'roles'));
    }

    public function create()
    {
        $roles = Role::query()
            ->where('name', '!=', 'Master Admin')
            ->orderBy('name')
            ->get();

        return view('user.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'role_id' => ['required', Rule::exists('roles', 'id')->where('guard_name', 'web')],
        ]);

        if ($validator->fails()) {
            if ($request->page === 'modal') {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors(),
                ]);
            }

            return Redirect::back()->withErrors($validator)->withInput($params);
        }

        $password = Str::password(12);
        $profilePhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => strtolower($request->email),
            'phone_number' => $request->phone_number,
            'profile_photo' => $profilePhotoPath,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'password' => Hash::make($password),
        ]);

        $role = Role::query()
            ->whereKey($request->role_id)
            ->where('guard_name', 'web')
            ->firstOrFail();

        $user->syncRoles([$role]);

        $mailFailed = false;
        try {
            Mail::raw(
                "Hello {$user->name},\n\n".
                "Your account has been created.\n".
                "Email: {$user->email}\n".
                "Password: {$password}\n\n".
                'Please login and change your password after first sign in.'.
                "\nLogin URL: ".route('login'),
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Your EcoTrace Login Credentials');
                }
            );
        } catch (Throwable $e) {
            $mailFailed = true;
        }

        if ($request->page === 'modal') {
            return response()->json([
                'user' => $user,
                'message' => $mailFailed
                    ? 'User added successfully, but credential email could not be sent.'
                    : 'User added successfully.',
                'status' => 'success',
                'mail_failed' => $mailFailed,
            ]);
        }

        return redirect()->route('user.index')->with([
            'message' => $mailFailed
                ? 'User added successfully, but credential email could not be sent.'
                : 'User added successfully.',
            'status' => 'success',
        ]);
    }

    public function show(string $id)
    {
        $user = User::with('roles')->findOrFail($id);

        return view('user.show', compact('user'));
    }

    public function edit(string $id)
    {
        $user = User::with('roles')->findOrFail($id);
        $roles = Role::query()
            ->where('name', '!=', 'Master Admin')
            ->orderBy('name')
            ->get();

        return view('user.update', compact('user', 'roles'));
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'role_id' => ['required', Rule::exists('roles', 'id')->where('guard_name', 'web')],
        ]);

        if ($validator->fails()) {
            if ($request->page === 'modal') {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors(),
                ]);
            }

            return Redirect::back()->withErrors($validator)->withInput();
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $user->profile_photo = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => strtolower($request->email),
            'phone_number' => $request->phone_number,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'profile_photo' => $user->profile_photo,
        ]);

        $role = Role::query()
            ->whereKey($request->role_id)
            ->where('guard_name', 'web')
            ->firstOrFail();

        $user->syncRoles([$role]);

        if ($request->page === 'modal') {
            return response()->json([
                'user' => $user,
                'message' => 'User updated successfully.',
                'status' => 'success',
            ]);
        }

        return redirect()->route('user.index')->with([
            'message' => 'User updated successfully.',
            'status' => 'success',
        ]);
    }

    public function destroy(string $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully.',
        ]);
    }
}
