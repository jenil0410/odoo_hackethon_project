<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Permission;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $createCheck = Permission::checkCRUDPermissionToUser('Driver', 'create');
        $readCheck = Permission::checkCRUDPermissionToUser('Driver', 'read');
        $updateCheck = Permission::checkCRUDPermissionToUser('Driver', 'update');
        $deleteCheck = Permission::checkCRUDPermissionToUser('Driver', 'delete');
        $isSuperAdmin = Permission::isSuperAdmin();

        if ($request->ajax()) {
            $query = Driver::query()->latest();
            $dispatchedDriverIds = Trip::query()
                ->where('status', 'dispatched')
                ->distinct()
                ->pluck('driver_id')
                ->filter()
                ->values();

            if ($request->filled('status_filter')) {
                $statusFilter = (string) $request->status_filter;
                if ($statusFilter === 'on_trip') {
                    $query->whereIn('id', $dispatchedDriverIds->all());
                } else {
                    $query->where('status', $statusFilter);
                    if ($statusFilter === 'on_duty') {
                        $query->whereNotIn('id', $dispatchedDriverIds->all());
                    }
                }
            }

            if ($request->filled('compliance_filter')) {
                if ($request->compliance_filter === 'assignable') {
                    $query->whereDate('license_expiry_date', '>=', now()->toDateString());
                }
                if ($request->compliance_filter === 'blocked') {
                    $query->whereDate('license_expiry_date', '<', now()->toDateString());
                }
            }

            $search = trim((string) data_get($request->input('search'), 'value', ''));
            $draw = (int) $request->input('draw', 1);
            $start = max(0, (int) $request->input('start', 0));
            $length = max(1, (int) $request->input('length', 10));

            $recordsTotal = (clone $query)->count();

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('license_number', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                });
            }

            $recordsFiltered = (clone $query)->count();
            $drivers = $query->skip($start)->take($length)->get();

            $data = $drivers->values()->map(function ($row, $idx) use ($start, $readCheck, $updateCheck, $deleteCheck, $isSuperAdmin, $dispatchedDriverIds) {
                $html = '';

                if ($updateCheck || $isSuperAdmin) {
                    $html .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="openDriverEditModal('.$row->id.')">Edit</a></li>';
                }

                if ($deleteCheck || $isSuperAdmin) {
                    $html .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="deleteDriver('.$row->id.', \''.e($row->full_name).'\')">Delete</a></li>';
                }

                if (! $isSuperAdmin && ! $readCheck && ! $updateCheck && ! $deleteCheck) {
                    $action = '';
                } else {
                    $action = '<div class="dropdown"><button type="button" class="btn btn-primary px-1 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">Action</button><div class="dropdown-menu">'.$html.'</div></div>';
                }

                $isOnTrip = $dispatchedDriverIds->contains($row->id);

                $statusText = match ($row->status) {
                    'on_duty' => 'On Duty',
                    'off_duty' => 'Off Duty',
                    'suspended' => 'Suspended',
                    default => ucfirst(str_replace('_', ' ', $row->status)),
                };

                $statusBadge = $isOnTrip
                    ? '<span class="badge bg-label-primary">On Trip</span>'
                    : match ($row->status) {
                        'on_duty' => '<span class="badge bg-label-success">On Duty</span>',
                        'off_duty' => '<span class="badge bg-label-secondary">Off Duty</span>',
                        'suspended' => '<span class="badge bg-label-danger">Suspended</span>',
                        default => '<span class="badge bg-label-primary">'.e($statusText).'</span>',
                    };

                $complianceBadge = $row->canBeAssigned()
                    ? '<span class="badge bg-label-success">Assignable</span>'
                    : '<span class="badge bg-label-danger">Blocked (License Expired)</span>';

                return [
                    'DT_RowIndex' => $start + $idx + 1,
                    'action' => $action,
                    'full_name' => $row->full_name,
                    'license_number' => $row->license_number,
                    'license_expiry_date' => optional($row->license_expiry_date)->format('Y-m-d') ?? '-',
                    'status' => $statusBadge,
                    'compliance' => $complianceBadge,
                    'trip_completion_rate' => number_format($row->trip_completion_rate, 2).'%',
                    'safety_score' => number_format((float) $row->safety_score, 2),
                    'monthly_salary' => number_format((float) $row->monthly_salary, 2),
                ];
            })->all();

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        }

        $statuses = ['on_trip', 'on_duty', 'off_duty', 'suspended'];

        return view('driver.index', compact('createCheck', 'statuses'));
    }

    public function fetch(string $id)
    {
        $driver = Driver::findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $driver,
            'can_assign' => $driver->canBeAssigned(),
            'assignment_reason' => $driver->canBeAssigned() ? 'Assignable' : 'Blocked due to expired license',
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:drivers,email'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'license_number' => ['required', 'string', 'max:100', 'unique:drivers,license_number'],
            'license_expiry_date' => ['required', 'date'],
            'total_trips' => ['required', 'integer', 'min:0'],
            'completed_trips' => ['required', 'integer', 'min:0', 'lte:total_trips'],
            'safety_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'monthly_salary' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['on_duty', 'off_duty', 'suspended'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        Driver::create($request->only([
            'full_name',
            'email',
            'phone_number',
            'license_number',
            'license_expiry_date',
            'total_trips',
            'completed_trips',
            'safety_score',
            'monthly_salary',
            'status',
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Driver created successfully.',
        ]);
    }

    public function update(Request $request, string $id)
    {
        $driver = Driver::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('drivers', 'email')->ignore($driver->id)],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'license_number' => ['required', 'string', 'max:100', Rule::unique('drivers', 'license_number')->ignore($driver->id)],
            'license_expiry_date' => ['required', 'date'],
            'total_trips' => ['required', 'integer', 'min:0'],
            'completed_trips' => ['required', 'integer', 'min:0', 'lte:total_trips'],
            'safety_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'monthly_salary' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['on_duty', 'off_duty', 'suspended'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $driver->update($request->only([
            'full_name',
            'email',
            'phone_number',
            'license_number',
            'license_expiry_date',
            'total_trips',
            'completed_trips',
            'safety_score',
            'monthly_salary',
            'status',
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Driver updated successfully.',
        ]);
    }

    public function destroy(string $id)
    {
        $driver = Driver::find($id);

        if (! $driver) {
            return response()->json([
                'status' => false,
                'message' => 'Driver not found.',
            ], 404);
        }

        $driver->delete();

        return response()->json([
            'status' => true,
            'message' => 'Driver deleted successfully.',
        ]);
    }

    public function canAssign(string $id)
    {
        $driver = Driver::findOrFail($id);
        $canAssign = $driver->canBeAssigned();

        return response()->json([
            'status' => true,
            'can_assign' => $canAssign,
            'reason' => $canAssign ? 'Driver can be assigned.' : 'Driver license is expired. Assignment blocked.',
        ]);
    }
}
