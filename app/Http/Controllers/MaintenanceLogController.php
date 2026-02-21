<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceLog;
use App\Models\Permission;
use App\Models\VehicleRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MaintenanceLogController extends Controller
{
    public function index(Request $request)
    {
        $createCheck = Permission::checkCRUDPermissionToUser('Maintenance Log', 'create');
        $readCheck = Permission::checkCRUDPermissionToUser('Maintenance Log', 'read');
        $updateCheck = Permission::checkCRUDPermissionToUser('Maintenance Log', 'update');
        $deleteCheck = Permission::checkCRUDPermissionToUser('Maintenance Log', 'delete');
        $isSuperAdmin = Permission::isSuperAdmin();

        if ($request->ajax()) {
            $query = MaintenanceLog::query()
                ->with(['vehicle:id,name_model,license_plate'])
                ->latest();

            if ($request->filled('status_filter')) {
                $query->where('status', $request->status_filter);
            }

            if ($request->filled('vehicle_filter')) {
                $query->where('vehicle_registry_id', $request->vehicle_filter);
            }

            $search = trim((string) data_get($request->input('search'), 'value', ''));
            $draw = (int) $request->input('draw', 1);
            $start = max(0, (int) $request->input('start', 0));
            $length = max(1, (int) $request->input('length', 10));

            $recordsTotal = (clone $query)->count();

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('vehicle', function ($vehicleQ) use ($search) {
                            $vehicleQ->where('name_model', 'like', "%{$search}%")
                                ->orWhere('license_plate', 'like', "%{$search}%");
                        });
                });
            }

            $recordsFiltered = (clone $query)->count();
            $logs = $query->skip($start)->take($length)->get();

            $data = $logs->values()->map(function ($row, $idx) use ($start, $readCheck, $updateCheck, $deleteCheck, $isSuperAdmin) {
                $html = '';

                if ($updateCheck || $isSuperAdmin) {
                    $html .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="openMaintenanceEditModal('.$row->id.')">Edit</a></li>';
                    if ($row->status === 'in_shop') {
                        $html .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="markMaintenanceCompleted('.$row->id.')">Mark Completed</a></li>';
                    }
                }

                if ($deleteCheck || $isSuperAdmin) {
                    $html .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="deleteMaintenanceLog('.$row->id.')">Delete</a></li>';
                }

                if (! $isSuperAdmin && ! $readCheck && ! $updateCheck && ! $deleteCheck) {
                    $action = '';
                } else {
                    $action = '<div class="dropdown"><button type="button" class="btn btn-primary px-1 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">Action</button><div class="dropdown-menu">'.$html.'</div></div>';
                }

                return [
                    'DT_RowIndex' => $start + $idx + 1,
                    'action' => $action,
                    'vehicle' => optional($row->vehicle)->name_model.' ('.optional($row->vehicle)->license_plate.')',
                    'title' => e($row->title),
                    'service_date' => optional($row->service_date)->format('Y-m-d') ?? '-',
                    'cost' => number_format((float) $row->cost, 2),
                    'status' => $row->status === 'in_shop'
                        ? '<span class="badge bg-label-warning">In Shop</span>'
                        : '<span class="badge bg-label-success">Completed</span>',
                ];
            })->all();

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        }

        $vehicles = VehicleRegistry::query()
            ->where('is_out_of_service', false)
            ->orderBy('name_model')
            ->get(['id', 'name_model', 'license_plate']);

        return view('maintenance_log.index', compact('createCheck', 'vehicles'));
    }

    public function fetch(string $id)
    {
        $log = MaintenanceLog::findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $log,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_registry_id' => ['required', 'integer', 'exists:vehicle_registries,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'service_date' => ['required', 'date'],
            'cost' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::in(['in_shop', 'completed'])],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $log = MaintenanceLog::create([
            'vehicle_registry_id' => $request->vehicle_registry_id,
            'title' => $request->title,
            'description' => $request->description,
            'service_date' => $request->service_date,
            'cost' => $request->cost,
            'status' => $request->input('status', 'in_shop'),
        ]);

        $this->refreshVehicleShopStatus((int) $log->vehicle_registry_id);

        return response()->json([
            'status' => true,
            'message' => 'Maintenance log created successfully.',
        ]);
    }

    public function update(Request $request, string $id)
    {
        $log = MaintenanceLog::findOrFail($id);
        $previousVehicleId = (int) $log->vehicle_registry_id;

        $validator = Validator::make($request->all(), [
            'vehicle_registry_id' => ['required', 'integer', 'exists:vehicle_registries,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'service_date' => ['required', 'date'],
            'cost' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['in_shop', 'completed'])],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $log->update([
            'vehicle_registry_id' => $request->vehicle_registry_id,
            'title' => $request->title,
            'description' => $request->description,
            'service_date' => $request->service_date,
            'cost' => $request->cost,
            'status' => $request->status,
        ]);

        $this->refreshVehicleShopStatus($previousVehicleId);
        $this->refreshVehicleShopStatus((int) $log->vehicle_registry_id);

        return response()->json([
            'status' => true,
            'message' => 'Maintenance log updated successfully.',
        ]);
    }

    public function markCompleted(string $id)
    {
        $log = MaintenanceLog::findOrFail($id);
        $log->update(['status' => 'completed']);
        $this->refreshVehicleShopStatus((int) $log->vehicle_registry_id);

        return response()->json([
            'status' => true,
            'message' => 'Maintenance marked as completed.',
        ]);
    }

    public function destroy(string $id)
    {
        $log = MaintenanceLog::find($id);
        if (! $log) {
            return response()->json(['status' => false, 'message' => 'Maintenance log not found.'], 404);
        }

        $vehicleId = (int) $log->vehicle_registry_id;
        $log->delete();
        $this->refreshVehicleShopStatus($vehicleId);

        return response()->json([
            'status' => true,
            'message' => 'Maintenance log deleted successfully.',
        ]);
    }

    private function refreshVehicleShopStatus(int $vehicleId): void
    {
        $hasInShopLog = MaintenanceLog::query()
            ->where('vehicle_registry_id', $vehicleId)
            ->where('status', 'in_shop')
            ->exists();

        VehicleRegistry::query()->whereKey($vehicleId)->update(['is_in_shop' => $hasInShopLog]);
    }
}
