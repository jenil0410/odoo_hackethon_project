<?php

namespace App\Http\Controllers;

use App\Models\FuelLog;
use App\Models\Permission;
use App\Models\Trip;
use App\Models\VehicleRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FuelLogController extends Controller
{
    public function index(Request $request)
    {
        $createCheck = Permission::checkCRUDPermissionToUser('Fuel Log', 'create');
        $readCheck = Permission::checkCRUDPermissionToUser('Fuel Log', 'read');
        $updateCheck = Permission::checkCRUDPermissionToUser('Fuel Log', 'update');
        $deleteCheck = Permission::checkCRUDPermissionToUser('Fuel Log', 'delete');
        $isSuperAdmin = Permission::isSuperAdmin();

        if ($request->ajax()) {
            $query = FuelLog::query()
                ->with(['vehicle:id,name_model,license_plate', 'trip:id,status'])
                ->latest();

            $search = trim((string) data_get($request->input('search'), 'value', ''));
            $draw = (int) $request->input('draw', 1);
            $start = max(0, (int) $request->input('start', 0));
            $length = max(1, (int) $request->input('length', 10));

            $recordsTotal = (clone $query)->count();

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('liters', 'like', "%{$search}%")
                        ->orWhere('cost', 'like', "%{$search}%")
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
                    $html .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="openFuelEditModal('.$row->id.')">Edit</a></li>';
                }
                if ($deleteCheck || $isSuperAdmin) {
                    $html .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="deleteFuelLog('.$row->id.')">Delete</a></li>';
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
                    'trip' => $row->trip_id ? 'Trip #'.$row->trip_id : '-',
                    'logged_on' => optional($row->logged_on)->format('Y-m-d') ?? '-',
                    'liters' => number_format((float) $row->liters, 2),
                    'cost' => number_format((float) $row->cost, 2),
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

        $completedTrips = Trip::query()
            ->where('status', 'completed')
            ->orderByDesc('id')
            ->get(['id', 'vehicle_registry_id']);

        return view('fuel_log.index', compact('createCheck', 'vehicles', 'completedTrips'));
    }

    public function fetch(string $id)
    {
        $log = FuelLog::findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $log,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_registry_id' => ['required', 'integer', 'exists:vehicle_registries,id'],
            'trip_id' => ['nullable', 'integer', 'exists:trips,id'],
            'liters' => ['required', 'numeric', 'min:0.01'],
            'cost' => ['required', 'numeric', 'min:0'],
            'logged_on' => ['required', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $businessErrors = $this->businessValidationErrors((int) $request->vehicle_registry_id, $request->trip_id);
        if ($businessErrors !== []) {
            return response()->json(['status' => false, 'errors' => $businessErrors], 422);
        }

        FuelLog::create([
            'vehicle_registry_id' => $request->vehicle_registry_id,
            'trip_id' => $request->trip_id,
            'liters' => $request->liters,
            'cost' => $request->cost,
            'logged_on' => $request->logged_on,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Fuel log created successfully.',
        ]);
    }

    public function update(Request $request, string $id)
    {
        $log = FuelLog::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'vehicle_registry_id' => ['required', 'integer', 'exists:vehicle_registries,id'],
            'trip_id' => ['nullable', 'integer', 'exists:trips,id'],
            'liters' => ['required', 'numeric', 'min:0.01'],
            'cost' => ['required', 'numeric', 'min:0'],
            'logged_on' => ['required', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $businessErrors = $this->businessValidationErrors((int) $request->vehicle_registry_id, $request->trip_id);
        if ($businessErrors !== []) {
            return response()->json(['status' => false, 'errors' => $businessErrors], 422);
        }

        $log->update([
            'vehicle_registry_id' => $request->vehicle_registry_id,
            'trip_id' => $request->trip_id,
            'liters' => $request->liters,
            'cost' => $request->cost,
            'logged_on' => $request->logged_on,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Fuel log updated successfully.',
        ]);
    }

    public function destroy(string $id)
    {
        $log = FuelLog::find($id);
        if (! $log) {
            return response()->json(['status' => false, 'message' => 'Fuel log not found.'], 404);
        }

        $log->delete();

        return response()->json([
            'status' => true,
            'message' => 'Fuel log deleted successfully.',
        ]);
    }

    private function businessValidationErrors(int $vehicleId, mixed $tripId): array
    {
        $errors = [];
        if (! $tripId) {
            return $errors;
        }

        $trip = Trip::query()->find($tripId);
        if (! $trip) {
            $errors['trip_id'] = ['Selected trip was not found.'];

            return $errors;
        }

        if ($trip->status !== 'completed') {
            $errors['trip_id'] = ['Fuel logs can only be attached to completed trips.'];
        }

        if ((int) $trip->vehicle_registry_id !== $vehicleId) {
            $errors['trip_id'] = ['Selected trip does not belong to the selected vehicle.'];
        }

        return $errors;
    }
}
