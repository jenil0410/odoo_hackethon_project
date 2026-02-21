<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceLog;
use App\Models\Permission;
use App\Models\Trip;
use App\Models\VehicleRegistry;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $canRead = Permission::checkCRUDPermissionToUser('Dashboard', 'read') || Permission::isSuperAdmin();
        abort_unless($canRead, 403);

        $selectedVehicleType = (string) $request->input('vehicle_type', 'all');
        $selectedStatus = (string) $request->input('status', 'all');
        $selectedRegion = (string) $request->input('region', 'all');
        $selectedMonth = preg_match('/^\d{4}-\d{2}$/', (string) $request->input('month'))
            ? (string) $request->input('month')
            : now()->format('Y-m');

        $dispatchedVehicleIds = Trip::query()
            ->where('status', 'dispatched')
            ->distinct()
            ->pluck('vehicle_registry_id')
            ->filter()
            ->values();

        $maintenanceInShopVehicleIds = MaintenanceLog::query()
            ->where('status', 'in_shop')
            ->distinct()
            ->pluck('vehicle_registry_id')
            ->filter()
            ->values();

        $vehicleRegionMap = Trip::query()
            ->select(['vehicle_registry_id', 'origin_address', 'destination_address', 'updated_at'])
            ->whereNotNull('vehicle_registry_id')
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy('vehicle_registry_id')
            ->map(function ($rows) {
                foreach ($rows as $row) {
                    $region = $this->extractRegionFromAddress((string) ($row->origin_address ?: $row->destination_address));
                    if ($region !== null) {
                        return $region;
                    }
                }

                return 'Unassigned';
            });

        $allVehicleProfiles = VehicleRegistry::query()
            ->orderBy('name_model')
            ->get(['id', 'name_model', 'is_out_of_service', 'is_in_shop'])
            ->map(function (VehicleRegistry $vehicle) use ($dispatchedVehicleIds, $maintenanceInShopVehicleIds, $vehicleRegionMap) {
                return [
                    'vehicle_id' => $vehicle->id,
                    'vehicle_type' => $this->inferVehicleType($vehicle->name_model),
                    'status' => $this->resolveVehicleStatus(
                        $vehicle,
                        $dispatchedVehicleIds->contains($vehicle->id),
                        $maintenanceInShopVehicleIds->contains($vehicle->id)
                    ),
                    'region' => (string) ($vehicleRegionMap->get($vehicle->id) ?? 'Unassigned'),
                ];
            })
            ->values();

        $regions = $allVehicleProfiles
            ->pluck('region')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        $filteredVehicleProfiles = $allVehicleProfiles
            ->filter(function (array $profile) use ($selectedVehicleType, $selectedStatus, $selectedRegion) {
                $typeMatches = $selectedVehicleType === 'all' || $profile['vehicle_type'] === $selectedVehicleType;
                $statusMatches = $selectedStatus === 'all' || $profile['status'] === $selectedStatus;
                $regionMatches = $selectedRegion === 'all' || $profile['region'] === $selectedRegion;

                return $typeMatches && $statusMatches && $regionMatches;
            })
            ->values();

        $filteredVehicleIds = $filteredVehicleProfiles->pluck('vehicle_id');

        $activeFleetCount = $filteredVehicleProfiles->where('status', 'on_trip')->count();
        $maintenanceAlertsCount = $filteredVehicleProfiles->where('status', 'in_shop')->count();
        $idleFleetCount = $filteredVehicleProfiles->where('status', 'idle')->count();
        $utilizationDenominator = $activeFleetCount + $idleFleetCount;
        $utilizationRate = $utilizationDenominator > 0 ? ($activeFleetCount / $utilizationDenominator) * 100 : 0.0;

        $pendingCargoCount = (int) Trip::query()
            ->where('status', 'draft')
            ->when(
                $filteredVehicleIds->isNotEmpty(),
                fn ($q) => $q->whereIn('vehicle_registry_id', $filteredVehicleIds->all()),
                fn ($q) => $q->whereRaw('1 = 0')
            )
            ->count();

        $tripRows = Trip::query()
            ->with([
                'vehicle:id,name_model,license_plate',
                'driver:id,full_name',
            ])
            ->when(
                $filteredVehicleIds->isNotEmpty(),
                fn ($q) => $q->whereIn('vehicle_registry_id', $filteredVehicleIds->all()),
                fn ($q) => $q->whereRaw('1 = 0')
            )
            ->whereIn('status', ['draft', 'dispatched', 'completed', 'cancelled'])
            ->latest('id')
            ->limit(12)
            ->get()
            ->map(fn (Trip $trip) => [
                'trip' => '#'.$trip->id,
                'vehicle' => optional($trip->vehicle)->name_model
                    ? optional($trip->vehicle)->name_model.' ('.optional($trip->vehicle)->license_plate.')'
                    : '-',
                'driver' => optional($trip->driver)->full_name ?: '-',
                'status' => (string) $trip->status,
            ])
            ->values();

        return view('dashboard', [
            'selectedMonth' => $selectedMonth,
            'selectedVehicleType' => $selectedVehicleType,
            'selectedStatus' => $selectedStatus,
            'selectedRegion' => $selectedRegion,
            'commandCenterRegions' => $regions,
            'kpiActiveFleet' => $activeFleetCount,
            'kpiMaintenanceAlerts' => $maintenanceAlertsCount,
            'kpiUtilizationRate' => $utilizationRate,
            'kpiPendingCargo' => $pendingCargoCount,
            'tripRows' => $tripRows,
        ]);
    }

    private function inferVehicleType(string $nameModel): string
    {
        $value = strtolower($nameModel);
        if (str_contains($value, 'truck')) {
            return 'truck';
        }
        if (str_contains($value, 'van')) {
            return 'van';
        }
        if (str_contains($value, 'bike') || str_contains($value, 'motorcycle') || str_contains($value, 'scooter')) {
            return 'bike';
        }

        return 'other';
    }

    private function extractRegionFromAddress(string $address): ?string
    {
        $address = trim($address);
        if ($address === '') {
            return null;
        }

        $segments = array_values(array_filter(array_map('trim', explode(',', $address))));
        if ($segments === []) {
            return null;
        }

        return $segments[0];
    }

    private function resolveVehicleStatus(VehicleRegistry $vehicle, bool $hasDispatchedTrip, bool $hasOpenMaintenance): string
    {
        if ($vehicle->is_out_of_service) {
            return 'out_of_service';
        }

        if ($vehicle->is_in_shop || $hasOpenMaintenance) {
            return 'in_shop';
        }

        if ($hasDispatchedTrip) {
            return 'on_trip';
        }

        return 'idle';
    }
}
