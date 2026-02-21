@extends('layouts.app')
@section('title', 'Dashboard')

@section('styles')
    <style>
        .command-board {
            background: #fff;
            border: 1px solid var(--brand-border);
            border-radius: 16px;
            padding: 14px;
        }

        .command-title {
            color: #fff;
            font-weight: 700;
            letter-spacing: 0.4px;
        }

        .dashboard-header {
            background-color: var(--brand-primary);
            border-radius: 10px;
            padding: 10px 14px;
        }

        .addButton {
            color: #fff !important;
            background-color: transparent !important;
            border: 1px solid #fff !important;
        }

        .addButton:hover {
            color: #1F7A4C !important;
            background-color: #fff !important;
        }

        .command-filter-wrap {
            border: 1px solid var(--brand-border);
            border-radius: 12px;
            padding: 10px;
            margin-top: 8px;
            background: var(--brand-bg);
        }

        .command-filter-label {
            color: var(--brand-primary);
            font-size: 12px;
            margin-bottom: 4px;
            display: block;
        }

        .command-filter-control {
            min-width: 160px;
            background: #fff !important;
            color: var(--brand-text) !important;
            border: 1px solid var(--brand-border) !important;
        }

        .command-filter-control::placeholder {
            color: #8a97a4;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid var(--brand-border) !important;
            border-radius: 8px !important;
            background: #fff !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            color: var(--brand-text) !important;
            padding-left: 12px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
            right: 8px;
        }

        .command-cta {
            border: 1px solid var(--brand-primary);
            color: #fff;
            background: var(--brand-primary);
            border-radius: 10px;
            font-weight: 600;
        }

        .command-cta-outline {
            border: 2px solid var(--brand-primary);
            color: var(--brand-primary);
            background: #fff;
            border-radius: 10px;
            font-weight: 600;
            box-shadow: 0 0 0 1px rgba(31, 122, 76, 0.08);
        }

        .command-cta:hover,
        .command-cta-outline:hover {
            color: #fff;
            background: var(--brand-secondary);
            border-color: var(--brand-secondary);
        }

        .command-kpi {
            border: 1px solid var(--brand-border);
            border-radius: 18px;
            background: var(--brand-bg);
            padding: 18px 16px;
            text-align: center;
            min-height: 130px;
        }

        .command-kpi .label {
            color: var(--brand-primary);
            font-size: 20px;
            font-weight: 600;
            font-family: "Inter", sans-serif;
        }

        .command-kpi .value {
            color: var(--brand-text);
            font-size: 32px;
            margin-top: 8px;
            font-weight: 700;
        }

        .command-kpi .subtext {
            color: #5b6975;
            margin-top: 4px;
            font-size: 13px;
        }

        .command-table-wrap {
            margin-top: 14px;
            border: 1px solid var(--brand-border);
            border-radius: 12px;
            overflow: hidden;
        }

        .command-table-wrap table {
            margin-bottom: 0;
        }

        .command-table-wrap thead th {
            color: #fff !important;
            background: var(--brand-primary) !important;
            border-color: var(--brand-primary) !important;
            font-size: 15px;
            font-family: "Inter", sans-serif;
        }

        .command-table-wrap tbody td {
            color: var(--brand-text) !important;
            background: #fff !important;
            border-color: var(--brand-border) !important;
            font-size: 14px;
        }

        .status-chip {
            font-size: 12px;
            border-radius: 12px;
            padding: 3px 8px;
            display: inline-block;
            font-family: "Inter", sans-serif;
            border: 1px solid transparent;
        }

        .status-chip.on-trip { color: #0c6f43; border-color: #0c6f43; background: rgba(31, 122, 76, 0.1); }
        .status-chip.draft { color: #6b7280; border-color: #6b7280; background: rgba(107, 114, 128, 0.1); }
        .status-chip.completed { color: #0c6f43; border-color: #0c6f43; background: rgba(31, 122, 76, 0.1); }
        .status-chip.cancelled { color: #b42318; border-color: #b42318; background: rgba(180, 35, 24, 0.08); }
        .status-chip.in-shop { color: #b54708; border-color: #b54708; background: rgba(181, 71, 8, 0.1); }
        .status-chip.out-of-service { color: #b42318; border-color: #b42318; background: rgba(180, 35, 24, 0.08); }
        .status-chip.idle { color: #1f4d77; border-color: #1f4d77; background: rgba(31, 77, 119, 0.1); }

        @media (max-width: 768px) {
            .command-kpi .label { font-size: 22px; }
            .command-kpi .value { font-size: 28px; }
            .command-table-wrap thead th,
            .command-table-wrap tbody td { font-size: 16px; }
        }
    </style>
@endsection

@section('content')
    <div class="command-board mt-3 mb-3">
        <div class="d-flex justify-content-between align-items-center dashboard-header">
            <h5 class="mb-0 command-title">Fleet Flow Dashboard</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('trip.index') }}" class="btn btn-primary waves-effect waves-light addButton">New Trip</a>
                <a href="{{ route('vehicle-registry.index') }}" class="btn btn-primary waves-effect waves-light addButton">New Vehicle</a>
            </div>
        </div>

        <form method="GET" action="{{ route('dashboard') }}" class="command-filter-wrap row g-2 align-items-end">
            <div class="col-xl-3 col-lg-4 col-md-6">
                <label class="command-filter-label">Search Fleet</label>
                <input type="text" class="form-control command-filter-control" id="command_search" placeholder="Search trip, vehicle, driver...">
            </div>
            <div class="col-xl-2 col-lg-3 col-md-6">
                <label class="command-filter-label">Month</label>
                <input type="hidden" name="month" id="dashboard_month_value" value="{{ $selectedMonth }}">
                <input type="text" id="dashboard_month_picker" class="form-control command-filter-control">
            </div>
            <div class="col-xl-2 col-lg-3 col-md-6">
                <label class="command-filter-label">Vehicle Type</label>
                <select name="vehicle_type" class="form-select command-filter-control select2 command-select2">
                    <option value="all" {{ $selectedVehicleType === 'all' ? 'selected' : '' }}>Truck/Van/Bike</option>
                    <option value="truck" {{ $selectedVehicleType === 'truck' ? 'selected' : '' }}>Truck</option>
                    <option value="van" {{ $selectedVehicleType === 'van' ? 'selected' : '' }}>Van</option>
                    <option value="bike" {{ $selectedVehicleType === 'bike' ? 'selected' : '' }}>Bike</option>
                </select>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-6">
                <label class="command-filter-label">Status</label>
                <select name="status" class="form-select command-filter-control select2 command-select2">
                    <option value="all" {{ $selectedStatus === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="on_trip" {{ $selectedStatus === 'on_trip' ? 'selected' : '' }}>On Trip</option>
                    <option value="in_shop" {{ $selectedStatus === 'in_shop' ? 'selected' : '' }}>In Shop</option>
                    <option value="idle" {{ $selectedStatus === 'idle' ? 'selected' : '' }}>Idle</option>
                    <option value="out_of_service" {{ $selectedStatus === 'out_of_service' ? 'selected' : '' }}>Out of Service</option>
                </select>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-6">
                <label class="command-filter-label">Region</label>
                <select name="region" class="form-select command-filter-control select2 command-select2">
                    <option value="all" {{ $selectedRegion === 'all' ? 'selected' : '' }}>All Regions</option>
                    @foreach ($commandCenterRegions as $region)
                        <option value="{{ $region }}" {{ $selectedRegion === $region ? 'selected' : '' }}>{{ $region }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-1 col-lg-12 col-md-6">
                <button class="btn command-cta w-100" type="submit">Apply</button>
            </div>
        </form>

        <div class="row g-3 mt-1">
            <div class="col-md-3">
                <div class="command-kpi">
                    <div class="label">Active Fleet</div>
                    <div class="value">{{ number_format($kpiActiveFleet) }}</div>
                    <div class="subtext">Vehicles currently On Trip</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="command-kpi">
                    <div class="label">Maintenance Alert</div>
                    <div class="value">{{ number_format($kpiMaintenanceAlerts) }}</div>
                    <div class="subtext">Vehicles marked In Shop</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="command-kpi">
                    <div class="label">Utilization Rate</div>
                    <div class="value">{{ number_format($kpiUtilizationRate, 2) }}%</div>
                    <div class="subtext">Assigned fleet vs idle</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="command-kpi">
                    <div class="label">Pending Cargo</div>
                    <div class="value">{{ number_format($kpiPendingCargo) }}</div>
                    <div class="subtext">Shipments pending assignment</div>
                </div>
            </div>
        </div>

        <div class="command-table-wrap">
            <table class="table" id="dashboard_command_table">
                <thead>
                    <tr>
                        <th>Trip</th>
                        <th>Vehicle</th>
                        <th>Driver</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tripRows as $row)
                        @php
                            $normalizedStatus = str_replace('_', '-', strtolower($row['status']));
                            $statusLabel = ucfirst(str_replace('_', ' ', $row['status']));
                        @endphp
                        <tr>
                            <td>{{ $row['trip'] }}</td>
                            <td>{{ $row['vehicle'] }}</td>
                            <td>{{ $row['driver'] }}</td>
                            <td>
                                <span class="status-chip {{ $normalizedStatus === 'dispatched' ? 'on-trip' : $normalizedStatus }}">
                                    {{ $normalizedStatus === 'dispatched' ? 'On Trip' : $statusLabel }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No command center trips found for current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('.command-select2').select2({
                width: '100%',
                minimumResultsForSearch: -1
            });

            const monthPicker = flatpickr('#dashboard_month_picker', {
                defaultDate: "{{ $selectedMonth }}-01",
                dateFormat: 'Y-m',
                altInput: true,
                altFormat: 'F, Y',
                allowInput: false,
                clickOpens: true,
                onChange: function(selectedDates, dateStr) {
                    document.getElementById('dashboard_month_value').value = dateStr;
                }
            });

            if (monthPicker.selectedDates.length) {
                document.getElementById('dashboard_month_value').value =
                    monthPicker.formatDate(monthPicker.selectedDates[0], 'Y-m');
            }

            const commandSearch = document.getElementById('command_search');
            const commandRows = Array.from(document.querySelectorAll('#dashboard_command_table tbody tr'));
            if (commandSearch) {
                commandSearch.addEventListener('input', function() {
                    const query = this.value.trim().toLowerCase();
                    commandRows.forEach(function(row) {
                        row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
                    });
                });
            }
        });
    </script>
@endsection
