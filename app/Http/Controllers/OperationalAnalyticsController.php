<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\FuelLog;
use App\Models\MaintenanceLog;
use App\Models\Permission;
use App\Models\Trip;
use App\Models\VehicleRegistry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OperationalAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $canRead = Permission::checkCRUDPermissionToUser('Operational Analytics', 'read') || Permission::isSuperAdmin();
        abort_unless($canRead, 403);

        [$startDate, $endDate, $month] = $this->resolveMonthRange($request->input('month'));

        $vehicleMetrics = VehicleRegistry::query()
            ->orderBy('name_model')
            ->get()
            ->map(function (VehicleRegistry $vehicle) use ($startDate, $endDate) {
                $fuelCost = (float) FuelLog::query()
                    ->where('vehicle_registry_id', $vehicle->id)
                    ->whereBetween('logged_on', [$startDate->toDateString(), $endDate->toDateString()])
                    ->sum('cost');

                $fuelLiters = (float) FuelLog::query()
                    ->where('vehicle_registry_id', $vehicle->id)
                    ->whereBetween('logged_on', [$startDate->toDateString(), $endDate->toDateString()])
                    ->sum('liters');

                $maintenanceCost = (float) MaintenanceLog::query()
                    ->where('vehicle_registry_id', $vehicle->id)
                    ->whereBetween('service_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->sum('cost');

                $trips = Trip::query()
                    ->where('vehicle_registry_id', $vehicle->id)
                    ->where('status', 'completed')
                    ->whereBetween('completed_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                    ->get(['revenue_amount', 'actual_distance_km']);

                $revenue = (float) $trips->sum('revenue_amount');
                $distanceKm = (float) $trips->sum('actual_distance_km');

                $totalOperationalCost = $fuelCost + $maintenanceCost;
                $fuelEfficiency = $fuelLiters > 0 ? ($distanceKm / $fuelLiters) : 0.0;
                $roiPercent = (float) $vehicle->acquisition_cost > 0
                    ? (($revenue - $totalOperationalCost) / (float) $vehicle->acquisition_cost) * 100
                    : null;

                return [
                    'vehicle_id' => $vehicle->id,
                    'vehicle' => "{$vehicle->name_model} ({$vehicle->license_plate})",
                    'distance_km' => $distanceKm,
                    'fuel_liters' => $fuelLiters,
                    'fuel_efficiency_km_per_l' => $fuelEfficiency,
                    'fuel_cost' => $fuelCost,
                    'maintenance_cost' => $maintenanceCost,
                    'total_operational_cost' => $totalOperationalCost,
                    'revenue' => $revenue,
                    'acquisition_cost' => (float) $vehicle->acquisition_cost,
                    'roi_percent' => $roiPercent,
                ];
            })
            ->values();

        $payrollRows = Driver::query()
            ->orderBy('full_name')
            ->get()
            ->map(function (Driver $driver) use ($startDate, $endDate) {
                $completedTripsInMonth = Trip::query()
                    ->where('driver_id', $driver->id)
                    ->where('status', 'completed')
                    ->whereBetween('completed_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                    ->count();

                return [
                    'driver' => $driver->full_name,
                    'status' => $driver->status,
                    'completed_trips' => $completedTripsInMonth,
                    'safety_score' => (float) $driver->safety_score,
                    'license_expiry_date' => optional($driver->license_expiry_date)->format('Y-m-d') ?? '-',
                    'monthly_salary' => (float) $driver->monthly_salary,
                ];
            })
            ->values();

        $healthRows = VehicleRegistry::query()
            ->orderBy('name_model')
            ->get()
            ->map(function (VehicleRegistry $vehicle) use ($startDate, $endDate) {
                $openServices = MaintenanceLog::query()
                    ->where('vehicle_registry_id', $vehicle->id)
                    ->where('status', 'in_shop')
                    ->count();

                $completedTrips = Trip::query()
                    ->where('vehicle_registry_id', $vehicle->id)
                    ->where('status', 'completed')
                    ->whereBetween('completed_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                    ->count();

                $fuelCost = (float) FuelLog::query()
                    ->where('vehicle_registry_id', $vehicle->id)
                    ->whereBetween('logged_on', [$startDate->toDateString(), $endDate->toDateString()])
                    ->sum('cost');

                $maintenanceCost = (float) MaintenanceLog::query()
                    ->where('vehicle_registry_id', $vehicle->id)
                    ->whereBetween('service_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->sum('cost');

                return [
                    'vehicle' => "{$vehicle->name_model} ({$vehicle->license_plate})",
                    'status' => $vehicle->is_out_of_service ? 'Out of Service' : ($vehicle->is_in_shop ? 'In Shop' : 'Available'),
                    'open_service_logs' => $openServices,
                    'completed_trips' => $completedTrips,
                    'operational_cost' => $fuelCost + $maintenanceCost,
                ];
            })
            ->values();

        $totalFuelCost = (float) FuelLog::query()
            ->whereBetween('logged_on', [$startDate->toDateString(), $endDate->toDateString()])
            ->sum('cost');

        $totalMaintenanceCost = (float) MaintenanceLog::query()
            ->whereBetween('service_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->sum('cost');

        $totalRevenue = (float) Trip::query()
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->sum('revenue_amount');

        $fleetAcquisitionCost = (float) VehicleRegistry::query()->sum('acquisition_cost');
        $fleetRoiPercent = $fleetAcquisitionCost > 0
            ? (($totalRevenue - ($totalFuelCost + $totalMaintenanceCost)) / $fleetAcquisitionCost) * 100
            : null;

        $totalFleetVehicles = (int) VehicleRegistry::query()->count();
        $usedVehicleCount = (int) Trip::query()
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($sq) use ($startDate, $endDate) {
                    $sq->where('status', 'completed')
                        ->whereBetween('completed_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);
                })->orWhere(function ($sq) use ($startDate, $endDate) {
                    $sq->where('status', 'dispatched')
                        ->whereBetween('updated_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);
                });
            })
            ->distinct('vehicle_registry_id')
            ->count('vehicle_registry_id');
        $utilizationRate = $totalFleetVehicles > 0 ? ($usedVehicleCount / $totalFleetVehicles) * 100 : 0.0;

        $topCostliestVehicles = $vehicleMetrics
            ->sortByDesc('total_operational_cost')
            ->take(5)
            ->values()
            ->map(fn (array $row) => [
                'vehicle' => $row['vehicle'],
                'cost' => (float) $row['total_operational_cost'],
            ]);

        $trendLabels = [];
        $fuelEfficiencySeries = [];
        $financialSummaryRows = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $startDate->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $mFuelCost = (float) FuelLog::query()
                ->whereBetween('logged_on', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->sum('cost');

            $mMaintenanceCost = (float) MaintenanceLog::query()
                ->whereBetween('service_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->sum('cost');

            $mRevenue = (float) Trip::query()
                ->where('status', 'completed')
                ->whereBetween('completed_at', [$monthStart->copy()->startOfDay(), $monthEnd->copy()->endOfDay()])
                ->sum('revenue_amount');

            $distanceKm = (float) Trip::query()
                ->where('status', 'completed')
                ->whereBetween('completed_at', [$monthStart->copy()->startOfDay(), $monthEnd->copy()->endOfDay()])
                ->sum('actual_distance_km');

            $fuelLiters = (float) FuelLog::query()
                ->whereBetween('logged_on', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->sum('liters');

            $trendLabels[] = $monthStart->format('M Y');
            $fuelEfficiencySeries[] = $fuelLiters > 0 ? round($distanceKm / $fuelLiters, 2) : 0.0;
            $financialSummaryRows[] = [
                'month' => $monthStart->format('M Y'),
                'revenue' => $mRevenue,
                'fuel_cost' => $mFuelCost,
                'maintenance_cost' => $mMaintenanceCost,
                'net_profit' => $mRevenue - ($mFuelCost + $mMaintenanceCost),
            ];
        }

        return view('analytics.index', [
            'selectedMonth' => $month,
            'vehicleMetrics' => $vehicleMetrics,
            'payrollRows' => $payrollRows,
            'healthRows' => $healthRows,
            'kpiTotalFuelCost' => $totalFuelCost,
            'kpiFleetRoiPercent' => $fleetRoiPercent,
            'kpiUtilizationRate' => $utilizationRate,
            'chartFuelEfficiencyLabels' => $trendLabels,
            'chartFuelEfficiencySeries' => $fuelEfficiencySeries,
            'chartTopCostlyLabels' => $topCostliestVehicles->pluck('vehicle')->all(),
            'chartTopCostlySeries' => $topCostliestVehicles->pluck('cost')->all(),
            'financialSummaryRows' => $financialSummaryRows,
        ]);
    }

    public function exportPayrollCsv(Request $request): StreamedResponse
    {
        [$startDate, $endDate, $month] = $this->resolveMonthRange($request->input('month'));
        $rows = $this->buildPayrollExportRows($startDate, $endDate);

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Driver', 'Status', 'Completed Trips', 'Safety Score', 'License Expiry', 'Monthly Salary']);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, "payroll-report-{$month}.csv", ['Content-Type' => 'text/csv']);
    }

    public function exportHealthCsv(Request $request): StreamedResponse
    {
        [$startDate, $endDate, $month] = $this->resolveMonthRange($request->input('month'));
        $rows = $this->buildHealthExportRows($startDate, $endDate);

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Vehicle', 'Status', 'Open Service Logs', 'Completed Trips', 'Operational Cost']);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, "health-audit-{$month}.csv", ['Content-Type' => 'text/csv']);
    }

    public function exportPayrollPdf(Request $request)
    {
        [$startDate, $endDate, $month] = $this->resolveMonthRange($request->input('month'));
        $rows = $this->buildPayrollExportRows($startDate, $endDate);

        return $this->renderPdfOrHtml(
            'analytics.exports.payroll',
            ['month' => $month, 'rows' => $rows],
            "payroll-report-{$month}.pdf"
        );
    }

    public function exportHealthPdf(Request $request)
    {
        [$startDate, $endDate, $month] = $this->resolveMonthRange($request->input('month'));
        $rows = $this->buildHealthExportRows($startDate, $endDate);

        return $this->renderPdfOrHtml(
            'analytics.exports.health',
            ['month' => $month, 'rows' => $rows],
            "health-audit-{$month}.pdf"
        );
    }

    private function resolveMonthRange(?string $month): array
    {
        $safeMonth = preg_match('/^\d{4}-\d{2}$/', (string) $month) ? $month : now()->format('Y-m');
        $startDate = Carbon::createFromFormat('Y-m-d', $safeMonth.'-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return [$startDate, $endDate, $safeMonth];
    }

    private function buildPayrollExportRows(Carbon $startDate, Carbon $endDate): array
    {
        return Driver::query()
            ->orderBy('full_name')
            ->get()
            ->map(function (Driver $driver) use ($startDate, $endDate) {
                $completedTripsInMonth = Trip::query()
                    ->where('driver_id', $driver->id)
                    ->where('status', 'completed')
                    ->whereBetween('completed_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                    ->count();

                return [
                    $driver->full_name,
                    $driver->status,
                    $completedTripsInMonth,
                    number_format((float) $driver->safety_score, 2),
                    optional($driver->license_expiry_date)->format('Y-m-d') ?? '-',
                    number_format((float) $driver->monthly_salary, 2),
                ];
            })
            ->all();
    }

    private function buildHealthExportRows(Carbon $startDate, Carbon $endDate): array
    {
        return VehicleRegistry::query()
            ->orderBy('name_model')
            ->get()
            ->map(function (VehicleRegistry $vehicle) use ($startDate, $endDate) {
                $openServices = MaintenanceLog::query()
                    ->where('vehicle_registry_id', $vehicle->id)
                    ->where('status', 'in_shop')
                    ->count();

                $completedTrips = Trip::query()
                    ->where('vehicle_registry_id', $vehicle->id)
                    ->where('status', 'completed')
                    ->whereBetween('completed_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                    ->count();

                $fuelCost = (float) FuelLog::query()
                    ->where('vehicle_registry_id', $vehicle->id)
                    ->whereBetween('logged_on', [$startDate->toDateString(), $endDate->toDateString()])
                    ->sum('cost');

                $maintenanceCost = (float) MaintenanceLog::query()
                    ->where('vehicle_registry_id', $vehicle->id)
                    ->whereBetween('service_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->sum('cost');

                return [
                    "{$vehicle->name_model} ({$vehicle->license_plate})",
                    $vehicle->is_out_of_service ? 'Out of Service' : ($vehicle->is_in_shop ? 'In Shop' : 'Available'),
                    $openServices,
                    $completedTrips,
                    number_format($fuelCost + $maintenanceCost, 2),
                ];
            })
            ->all();
    }

    private function renderPdfOrHtml(string $view, array $data, string $filename)
    {
        if (app()->bound('dompdf.wrapper')) {
            $pdf = app('dompdf.wrapper');
            $pdf->loadView($view, $data);

            return $pdf->download($filename);
        }

        return response()->view($view, $data);
    }
}
