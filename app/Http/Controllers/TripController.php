<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Permission;
use App\Models\Trip;
use App\Models\VehicleRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $createCheck = Permission::checkCRUDPermissionToUser('Trip', 'create');
        $readCheck = Permission::checkCRUDPermissionToUser('Trip', 'read');
        $updateCheck = Permission::checkCRUDPermissionToUser('Trip', 'update');
        $deleteCheck = Permission::checkCRUDPermissionToUser('Trip', 'delete');
        $isSuperAdmin = Permission::isSuperAdmin();

        if ($request->ajax()) {
            $query = Trip::query()
                ->with(['vehicle:id,name_model,license_plate', 'driver:id,full_name,license_number'])
                ->latest();

            if ($request->filled('status_filter')) {
                $query->where('status', $request->status_filter);
            }

            if ($request->filled('vehicle_filter')) {
                $query->where('vehicle_registry_id', $request->vehicle_filter);
            }

            if ($request->filled('driver_filter')) {
                $query->where('driver_id', $request->driver_filter);
            }

            $search = trim((string) data_get($request->input('search'), 'value', ''));
            $draw = (int) $request->input('draw', 1);
            $start = max(0, (int) $request->input('start', 0));
            $length = max(1, (int) $request->input('length', 10));

            $recordsTotal = (clone $query)->count();

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('origin_address', 'like', "%{$search}%")
                        ->orWhere('destination_address', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('vehicle', function ($vehicleQ) use ($search) {
                            $vehicleQ->where('name_model', 'like', "%{$search}%")
                                ->orWhere('license_plate', 'like', "%{$search}%");
                        })
                        ->orWhereHas('driver', function ($driverQ) use ($search) {
                            $driverQ->where('full_name', 'like', "%{$search}%")
                                ->orWhere('license_number', 'like', "%{$search}%");
                        });
                });
            }

            $recordsFiltered = (clone $query)->count();
            $trips = $query->skip($start)->take($length)->get();

            $data = $trips->values()->map(function ($row, $idx) use ($start, $readCheck, $updateCheck, $deleteCheck, $isSuperAdmin) {
                $html = '';
                if ($updateCheck || $isSuperAdmin) {
                    $html .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="openTripEditModal('.$row->id.')">Edit</a></li>';
                    if (in_array((string) $row->status, ['completed', 'cancelled'], true)) {
                        $html .= '<li><a class="dropdown-item disabled" href="javascript:void(0)">Change Status</a></li>';
                    } else {
                        $html .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="openTripStatusModal('.$row->id.', \''.$row->status.'\')">Change Status</a></li>';
                    }
                }

                if ($deleteCheck || $isSuperAdmin) {
                    $html .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="deleteTrip('.$row->id.')">Delete</a></li>';
                }

                if (! $isSuperAdmin && ! $readCheck && ! $updateCheck && ! $deleteCheck) {
                    $action = '';
                } else {
                    $action = '<div class="dropdown"><button type="button" class="btn btn-primary px-1 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">Action</button><div class="dropdown-menu">'.$html.'</div></div>';
                }

                $statusBadge = match ($row->status) {
                    'draft' => '<span class="badge bg-label-secondary">Draft</span>',
                    'dispatched' => '<span class="badge bg-label-primary">Dispatched</span>',
                    'completed' => '<span class="badge bg-label-success">Completed</span>',
                    'cancelled' => '<span class="badge bg-label-danger">Cancelled</span>',
                    default => '<span class="badge bg-label-dark">'.e($row->status).'</span>',
                };

                return [
                    'DT_RowIndex' => $start + $idx + 1,
                    'action' => $action,
                    'vehicle' => optional($row->vehicle)->name_model.' ('.optional($row->vehicle)->license_plate.')',
                    'driver' => optional($row->driver)->full_name,
                    'origin_address' => $row->origin_address,
                    'destination_address' => $row->destination_address,
                    'cargo_weight' => number_format((float) $row->cargo_weight, 2).' kg',
                    'estimated_fuel_cost' => number_format((float) $row->estimated_fuel_cost, 2),
                    'status' => $statusBadge,
                ];
            })->all();

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        }

        return view('trip.index', [
            'createCheck' => $createCheck,
            'vehicles' => $this->availableVehicles(),
            'drivers' => $this->availableDrivers(),
            'filterVehicles' => VehicleRegistry::query()->orderBy('name_model')->get(['id', 'name_model', 'license_plate']),
            'filterDrivers' => Driver::query()->orderBy('full_name')->get(['id', 'full_name', 'license_number']),
        ]);
    }

    public function fetch(string $id)
    {
        $trip = Trip::with([
            'vehicle:id,name_model,license_plate,max_load_capacity,load_unit,is_out_of_service,is_in_shop,odometer',
            'driver:id,full_name,license_number,status,license_expiry_date',
        ])
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $trip,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_registry_id' => ['required', 'integer', 'exists:vehicle_registries,id'],
            'driver_id' => ['required', 'integer', 'exists:drivers,id'],
            'origin_address' => ['required', 'string', 'max:255'],
            'destination_address' => ['required', 'string', 'max:255'],
            'cargo_weight' => ['required', 'numeric', 'min:0.01'],
            'estimated_fuel_cost' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $vehicle = VehicleRegistry::findOrFail($request->vehicle_registry_id);
        $driver = Driver::findOrFail($request->driver_id);

        $businessErrors = $this->businessValidationErrors(
            $vehicle,
            $driver,
            (float) $request->cargo_weight
        );

        if ($businessErrors !== []) {
            return response()->json(['status' => false, 'errors' => $businessErrors], 422);
        }

        Trip::create([
            'vehicle_registry_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'origin_address' => $request->origin_address,
            'destination_address' => $request->destination_address,
            'cargo_weight' => $request->cargo_weight,
            'estimated_fuel_cost' => $request->estimated_fuel_cost,
            'status' => 'draft',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Trip created successfully.',
        ]);
    }

    public function update(Request $request, string $id)
    {
        $trip = Trip::findOrFail($id);

        if ($trip->status !== 'draft') {
            return response()->json([
                'status' => false,
                'errors' => ['status' => ['Only draft trips can be edited.']],
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'vehicle_registry_id' => ['required', 'integer', 'exists:vehicle_registries,id'],
            'driver_id' => ['required', 'integer', 'exists:drivers,id'],
            'origin_address' => ['required', 'string', 'max:255'],
            'destination_address' => ['required', 'string', 'max:255'],
            'cargo_weight' => ['required', 'numeric', 'min:0.01'],
            'estimated_fuel_cost' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $vehicle = VehicleRegistry::findOrFail($request->vehicle_registry_id);
        $driver = Driver::findOrFail($request->driver_id);

        $businessErrors = $this->businessValidationErrors(
            $vehicle,
            $driver,
            (float) $request->cargo_weight,
            $trip->id
        );

        if ($businessErrors !== []) {
            return response()->json(['status' => false, 'errors' => $businessErrors], 422);
        }

        $trip->update([
            'vehicle_registry_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'origin_address' => $request->origin_address,
            'destination_address' => $request->destination_address,
            'cargo_weight' => $request->cargo_weight,
            'estimated_fuel_cost' => $request->estimated_fuel_cost,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Trip updated successfully.',
        ]);
    }

    public function changeStatus(Request $request, string $id)
    {
        $trip = Trip::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(['draft', 'dispatched', 'completed', 'cancelled'])],
            'final_odometer' => ['nullable', 'numeric', 'min:0'],
            'revenue_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $nextStatus = (string) $request->status;
        $currentStatus = (string) $trip->status;

        if ($nextStatus === (string) $trip->status) {
            return response()->json([
                'status' => true,
                'message' => 'Trip status is already '.ucfirst($nextStatus).'.',
            ]);
        }

        $allowedTransitions = [
            'draft' => ['dispatched', 'cancelled'],
            'dispatched' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => [],
        ];

        if (! in_array($nextStatus, $allowedTransitions[$currentStatus] ?? [], true)) {
            return response()->json([
                'status' => false,
                'errors' => ['status' => ['Invalid status transition.']],
            ], 422);
        }

        if ($nextStatus === 'dispatched') {
            $vehicle = VehicleRegistry::findOrFail($trip->vehicle_registry_id);
            $driver = Driver::findOrFail($trip->driver_id);
            $businessErrors = $this->businessValidationErrors($vehicle, $driver, (float) $trip->cargo_weight, $trip->id);
            if ($businessErrors !== []) {
                return response()->json(['status' => false, 'errors' => $businessErrors], 422);
            }

            $trip->update(['status' => $nextStatus]);
            $driver->increment('total_trips');

            return response()->json([
                'status' => true,
                'message' => 'Trip status updated to '.ucfirst($nextStatus).'.',
            ]);
        }

        if ($nextStatus === 'completed') {
            $vehicle = VehicleRegistry::findOrFail($trip->vehicle_registry_id);
            $driver = Driver::findOrFail($trip->driver_id);

            $finalOdometer = $request->input('final_odometer');
            if ($finalOdometer === null || $finalOdometer === '') {
                return response()->json([
                    'status' => false,
                    'errors' => ['final_odometer' => ['Final odometer is required to complete a trip.']],
                ], 422);
            }

            $finalOdometerValue = (float) $finalOdometer;
            $currentVehicleOdometer = (float) $vehicle->odometer;
            if ($finalOdometerValue < $currentVehicleOdometer) {
                return response()->json([
                    'status' => false,
                    'errors' => ['final_odometer' => ['Final odometer cannot be less than current vehicle odometer.']],
                ], 422);
            }

            $distance = $finalOdometerValue - $currentVehicleOdometer;
            $trip->update([
                'status' => 'completed',
                'completed_at' => now(),
                'final_odometer' => $finalOdometerValue,
                'actual_distance_km' => $distance,
                'revenue_amount' => (float) $request->input('revenue_amount', 0),
            ]);
            $vehicle->update(['odometer' => $finalOdometerValue]);
            $driver->increment('completed_trips');

            return response()->json([
                'status' => true,
                'message' => 'Trip status updated to Completed.',
            ]);
        }

        $trip->update(['status' => $nextStatus]);

        return response()->json([
            'status' => true,
            'message' => 'Trip status updated to '.ucfirst($nextStatus).'.',
        ]);
    }

    public function destroy(string $id)
    {
        $trip = Trip::find($id);
        if (! $trip) {
            return response()->json(['status' => false, 'message' => 'Trip not found.'], 404);
        }

        $trip->delete();

        return response()->json(['status' => true, 'message' => 'Trip deleted successfully.']);
    }

    private function businessValidationErrors(VehicleRegistry $vehicle, Driver $driver, float $cargoWeightKg, ?int $ignoreTripId = null): array
    {
        $errors = [];

        $maxCapacityKg = (float) $vehicle->max_load_capacity;
        if ($vehicle->load_unit === 'tons') {
            $maxCapacityKg *= 1000;
        }

        if ($cargoWeightKg > $maxCapacityKg) {
            $errors['cargo_weight'] = ['Cargo weight exceeds selected vehicle max capacity.'];
        }

        if ($vehicle->is_out_of_service) {
            $errors['vehicle_registry_id'] = ['Selected vehicle is out of service.'];
        }

        if ($vehicle->is_in_shop) {
            $errors['vehicle_registry_id'] = ['Selected vehicle is currently in shop.'];
        }

        $dispatchedVehicleTrip = Trip::query()
            ->where('status', 'dispatched')
            ->where('vehicle_registry_id', $vehicle->id)
            ->when($ignoreTripId, fn ($q) => $q->where('id', '!=', $ignoreTripId))
            ->exists();

        if ($dispatchedVehicleTrip) {
            $errors['vehicle_registry_id'] = ['Selected vehicle is currently on a dispatched trip.'];
        }

        if ($driver->status !== 'on_duty') {
            $errors['driver_id'] = ['Selected driver is not on duty.'];
        }

        if (! $driver->canBeAssigned()) {
            $errors['driver_id'] = ['Selected driver has an expired license.'];
        }

        $dispatchedDriverTrip = Trip::query()
            ->where('status', 'dispatched')
            ->where('driver_id', $driver->id)
            ->when($ignoreTripId, fn ($q) => $q->where('id', '!=', $ignoreTripId))
            ->exists();

        if ($dispatchedDriverTrip) {
            $errors['driver_id'] = ['Selected driver is currently on a dispatched trip.'];
        }

        return $errors;
    }

    private function availableVehicles()
    {
        $busyVehicleIds = Trip::query()->where('status', 'dispatched')->pluck('vehicle_registry_id');

        return VehicleRegistry::query()
            ->where('is_out_of_service', false)
            ->where('is_in_shop', false)
            ->whereNotIn('id', $busyVehicleIds)
            ->orderBy('name_model')
            ->get(['id', 'name_model', 'license_plate', 'max_load_capacity', 'load_unit']);
    }

    private function availableDrivers()
    {
        $busyDriverIds = Trip::query()->where('status', 'dispatched')->pluck('driver_id');

        return Driver::query()
            ->where('status', 'on_duty')
            ->whereDate('license_expiry_date', '>=', now()->toDateString())
            ->whereNotIn('id', $busyDriverIds)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'license_number']);
    }
}
