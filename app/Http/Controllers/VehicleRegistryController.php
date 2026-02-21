<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\VehicleRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VehicleRegistryController extends Controller
{
    public function index(Request $request)
    {
        $createCheck = Permission::checkCRUDPermissionToUser('Vehicle Registry', 'create');
        $readCheck = Permission::checkCRUDPermissionToUser('Vehicle Registry', 'read');
        $updateCheck = Permission::checkCRUDPermissionToUser('Vehicle Registry', 'update');
        $deleteCheck = Permission::checkCRUDPermissionToUser('Vehicle Registry', 'delete');
        $isSuperAdmin = Permission::isSuperAdmin();

        if ($request->ajax()) {
            $query = VehicleRegistry::query()->latest();
            $search = trim((string) data_get($request->input('search'), 'value', ''));
            $draw = (int) $request->input('draw', 1);
            $start = max(0, (int) $request->input('start', 0));
            $length = max(1, (int) $request->input('length', 10));

            $recordsTotal = (clone $query)->count();

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name_model', 'like', "%{$search}%")
                        ->orWhere('license_plate', 'like', "%{$search}%")
                        ->orWhere('load_unit', 'like', "%{$search}%")
                        ->orWhere('max_load_capacity', 'like', "%{$search}%")
                        ->orWhere('odometer', 'like', "%{$search}%");
                });
            }

            $recordsFiltered = (clone $query)->count();
            $vehicles = $query->skip($start)->take($length)->get();

            $data = $vehicles->values()->map(function ($row, $idx) use ($start, $readCheck, $updateCheck, $deleteCheck, $isSuperAdmin) {
                $html = '';

                if ($updateCheck || $isSuperAdmin) {
                    $html .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="openVehicleEditModal('.$row->id.')">Edit</a></li>';
                }

                if ($deleteCheck || $isSuperAdmin) {
                    $html .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="deleteVehicle('.$row->id.', \''.e($row->license_plate).'\')">Delete</a></li>';
                }

                if (! $isSuperAdmin && ! $readCheck && ! $updateCheck && ! $deleteCheck) {
                    $action = '';
                } else {
                    $action = '<div class="dropdown"><button type="button" class="btn btn-primary px-1 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">Action</button><div class="dropdown-menu">'.$html.'</div></div>';
                }

                return [
                    'DT_RowIndex' => $start + $idx + 1,
                    'action' => $action,
                    'name_model' => $row->name_model,
                    'license_plate' => $row->license_plate,
                    'max_load_capacity' => rtrim(rtrim((string) $row->max_load_capacity, '0'), '.').' '.$row->load_unit,
                    'odometer' => number_format((float) $row->odometer, 2),
                    'is_out_of_service' => $row->is_out_of_service
                        ? '<span class="badge bg-label-danger">Out of Service</span>'
                        : '<span class="badge bg-label-success">Active</span>',
                ];
            })->all();

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        }

        return view('vehicle_registry.index', compact('createCheck'));
    }

    public function fetch(string $id)
    {
        $vehicle = VehicleRegistry::findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $vehicle,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_model' => ['required', 'string', 'max:255'],
            'license_plate' => ['required', 'string', 'max:50', 'unique:vehicle_registries,license_plate'],
            'max_load_capacity' => ['required', 'numeric', 'min:0'],
            'load_unit' => ['required', Rule::in(['kg', 'tons'])],
            'odometer' => ['required', 'numeric', 'min:0'],
            'is_out_of_service' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        VehicleRegistry::create([
            'name_model' => $request->name_model,
            'license_plate' => strtoupper(trim((string) $request->license_plate)),
            'max_load_capacity' => $request->max_load_capacity,
            'load_unit' => $request->load_unit,
            'odometer' => $request->odometer,
            'is_out_of_service' => (bool) $request->boolean('is_out_of_service'),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Vehicle created successfully.',
        ]);
    }

    public function update(Request $request, string $id)
    {
        $vehicle = VehicleRegistry::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name_model' => ['required', 'string', 'max:255'],
            'license_plate' => ['required', 'string', 'max:50', Rule::unique('vehicle_registries', 'license_plate')->ignore($vehicle->id)],
            'max_load_capacity' => ['required', 'numeric', 'min:0'],
            'load_unit' => ['required', Rule::in(['kg', 'tons'])],
            'odometer' => ['required', 'numeric', 'min:0'],
            'is_out_of_service' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $vehicle->update([
            'name_model' => $request->name_model,
            'license_plate' => strtoupper(trim((string) $request->license_plate)),
            'max_load_capacity' => $request->max_load_capacity,
            'load_unit' => $request->load_unit,
            'odometer' => $request->odometer,
            'is_out_of_service' => (bool) $request->boolean('is_out_of_service'),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Vehicle updated successfully.',
        ]);
    }

    public function destroy(string $id)
    {
        $vehicle = VehicleRegistry::find($id);

        if (! $vehicle) {
            return response()->json([
                'status' => false,
                'message' => 'Vehicle not found.',
            ], 404);
        }

        $vehicle->delete();

        return response()->json([
            'status' => true,
            'message' => 'Vehicle deleted successfully.',
        ]);
    }
}
