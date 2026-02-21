@extends('layouts.app')
@section('title', 'Operational Analytics')
@section('styles')
    <style>
        .analytics-shell {
            background: #fff;
            border: 1px solid var(--brand-border);
            border-radius: 16px;
            padding: 16px;
            color: var(--brand-text);
        }

        .analytics-header {
            background-color: var(--brand-primary);
            border-radius: 10px;
            padding: 10px 14px;
        }

        .analytics-header h5 {
            color: #fff;
        }

        .analytics-kpi {
            border: 1px solid var(--brand-border);
            border-radius: 18px;
            padding: 14px 16px;
            background: var(--brand-bg);
            min-height: 115px;
        }

        .analytics-kpi .label {
            color: var(--brand-primary);
            font-weight: 600;
            font-size: 18px;
        }

        .analytics-kpi .value {
            color: var(--brand-text);
            font-size: 32px;
            font-weight: 700;
            line-height: 1.1;
        }

        .analytics-chart-card {
            background: #fff;
            border: 1px solid var(--brand-border);
            border-radius: 10px;
            padding: 10px;
            color: var(--brand-text);
        }

        .financial-table-wrap {
            border: 1px solid var(--brand-border);
            border-radius: 10px;
            padding: 8px;
            background: #fff;
        }

        .financial-title {
            color: var(--brand-primary);
        }

        .financial-table-wrap table thead th {
            color: #fff !important;
            background: var(--brand-primary) !important;
            border-color: var(--brand-primary) !important;
        }

        .financial-table-wrap table tbody td {
            color: var(--brand-text) !important;
            border-color: var(--brand-border) !important;
            background: #fff !important;
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

        .analytics-tabs .nav-link {
            color: var(--brand-text);
            font-weight: 600;
            border-radius: 8px;
            border: 1px solid transparent;
            padding: 10px 14px;
        }

        .analytics-tabs .nav-link.active {
            background-color: var(--brand-primary);
            color: #fff;
            border-color: var(--brand-primary);
            box-shadow: 0 2px 8px rgba(31, 122, 76, 0.2);
        }

        .analytics-tab-pane {
            border: 1px solid var(--brand-border);
            border-radius: 12px;
            background: #fff;
            padding: 12px;
        }

        .dt-button-collection {
            min-width: 223px !important;
            background-color: #fff !important;
            border: 1px solid #ddd !important;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2) !important;
            padding: 10px !important;
        }

        .dt-button-collection .dt-button.buttons-columnVisibility {
            display: block !important;
            width: 100% !important;
            text-align: left !important;
            padding: 6px 10px !important;
            margin: 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            background: #fff !important;
            color: #1F2933 !important;
        }

        .dt-button-collection .dt-button.buttons-columnVisibility:hover,
        .dt-button-collection .dt-button.buttons-columnVisibility.active {
            background-color: rgba(47, 174, 122, 0.12) !important;
            color: #1F2933 !important;
        }

        [type="search"]::-webkit-search-cancel-button {
            -webkit-appearance: none;
            appearance: none;
            height: 10px;
            width: 10px;
            background-image: url('{{ asset('assets/img/branding/search-close.png') }}');
            background-size: 10px 10px;
        }

        div.dataTables_wrapper div.dataTables_length select {
            width: 80px;
        }

        div.dataTables_wrapper div.col-sm-12 {
            padding: 0 !important;
        }

        .dt-overlay {
            height: 100%;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 99999;
            background-color: #000;
            filter: alpha(opacity=75);
            -moz-opacity: 0.75;
            opacity: 0.3;
            border-radius: 10px;
            display: none;
        }
    
        /* Datatable dropdown positioning per reference */
        .dt-button-collection {
            position: absolute !important;
            top: auto !important;
            left: auto !important;
            right: 163px !important;
            z-index: 1050 !important;
        }

        .dt-button-collection.dtb-b2 {
            position: absolute !important;
            top: auto !important;
            left: auto !important;
            right: 6px !important;
            z-index: 1050 !important;
            min-width: 150px !important;
        }
    </style>
@endsection

@section('content')
    <div class="analytics-shell mb-3">
        <div class="analytics-header d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Fleet Flow - Operational Analytics</h5>
            <form method="GET" action="{{ route('analytics.index') }}" class="d-flex gap-2 align-items-center">
                <input type="hidden" name="month" id="analytics_month_value" value="{{ $selectedMonth }}">
                <input type="text" id="analytics_month_picker" class="form-control bg-white">
                <button class="btn btn-primary waves-effect waves-light addButton" type="submit">APPLY</button>
            </form>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="analytics-kpi">
                    <div class="label">Total Fuel Cost</div>
                    <div class="value">Rs. {{ number_format($kpiTotalFuelCost, 2) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="analytics-kpi">
                    <div class="label">Fleet ROI</div>
                    <div class="value">{{ $kpiFleetRoiPercent === null ? '-' : number_format($kpiFleetRoiPercent, 2).'%' }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="analytics-kpi">
                    <div class="label">Utilization Rate</div>
                    <div class="value">{{ number_format($kpiUtilizationRate, 2) }}%</div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="analytics-chart-card">
                    <h5 class="mb-2">Fuel Efficiency Trend (km/L)</h5>
                    <div id="fuelEfficiencyChart"></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="analytics-chart-card">
                    <h5 class="mb-2">Top 5 Costliest Vehicles</h5>
                    <div id="topCostlyVehiclesChart"></div>
                </div>
            </div>
        </div>

        <div class="financial-table-wrap mb-3">
            <h5 class="text-center financial-title mb-2">Financial Summary of Month</h5>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Revenue</th>
                            <th>Fuel Cost</th>
                            <th>Maintenance</th>
                            <th>Net Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($financialSummaryRows as $row)
                            <tr>
                                <td>{{ $row['month'] }}</td>
                                <td>Rs. {{ number_format($row['revenue'], 2) }}</td>
                                <td>Rs. {{ number_format($row['fuel_cost'], 2) }}</td>
                                <td>Rs. {{ number_format($row['maintenance_cost'], 2) }}</td>
                                <td>Rs. {{ number_format($row['net_profit'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <ul class="nav nav-pills analytics-tabs mb-3" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-vehicle-metrics" type="button" role="tab">Vehicle Metrics</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-payroll" type="button" role="tab">Monthly Payroll Report</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-health-audit" type="button" role="tab">Monthly Health Audit</button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active analytics-tab-pane" id="tab-vehicle-metrics" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between py-3">
                            <h5 class="card-title m-0 me-2 text-secondary">Vehicle Metrics</h5>
                        </div>
                        <div class="card-body">
                            <div class="card-datatable table-responsive pt-0">
                                <table class="datatables-basic table table-striped" id="vehicle_metrics_table">
                                    <div id="vehicle_metrics_overlay" class="dt-overlay"></div>
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Sr. No.</th>
                                            <th>Vehicle</th>
                                            <th>Distance (km)</th>
                                            <th>Fuel (L)</th>
                                            <th>Fuel Efficiency (km/L)</th>
                                            <th>Fuel Cost</th>
                                            <th>Maintenance Cost</th>
                                            <th>Total Operational Cost</th>
                                            <th>Revenue</th>
                                            <th>ROI (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($vehicleMetrics as $row)
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td>{{ $row['vehicle'] }}</td>
                                                <td>{{ number_format($row['distance_km'], 2) }}</td>
                                                <td>{{ number_format($row['fuel_liters'], 2) }}</td>
                                                <td>{{ number_format($row['fuel_efficiency_km_per_l'], 2) }}</td>
                                                <td>{{ number_format($row['fuel_cost'], 2) }}</td>
                                                <td>{{ number_format($row['maintenance_cost'], 2) }}</td>
                                                <td>{{ number_format($row['total_operational_cost'], 2) }}</td>
                                                <td>{{ number_format($row['revenue'], 2) }}</td>
                                                <td>{{ $row['roi_percent'] === null ? '-' : number_format($row['roi_percent'], 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="11" class="text-center">No data available.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade analytics-tab-pane" id="tab-payroll" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between py-3">
                            <h5 class="card-title m-0 me-2 text-secondary">Monthly Payroll Report</h5>
                        </div>
                        <div class="card-body">
                            <div class="card-datatable table-responsive pt-0">
                                <table class="datatables-basic table table-striped" id="payroll_table">
                                    <div id="payroll_overlay" class="dt-overlay"></div>
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Sr. No.</th>
                                            <th>Driver</th>
                                            <th>Status</th>
                                            <th>Completed Trips</th>
                                            <th>Safety Score</th>
                                            <th>License Expiry</th>
                                            <th>Monthly Salary</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($payrollRows as $row)
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td>{{ $row['driver'] }}</td>
                                                <td>{{ ucfirst(str_replace('_', ' ', $row['status'])) }}</td>
                                                <td>{{ $row['completed_trips'] }}</td>
                                                <td>{{ number_format($row['safety_score'], 2) }}</td>
                                                <td>{{ $row['license_expiry_date'] }}</td>
                                                <td>{{ number_format($row['monthly_salary'], 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="8" class="text-center">No data available.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade analytics-tab-pane" id="tab-health-audit" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between py-3">
                            <h5 class="card-title m-0 me-2 text-secondary">Monthly Health Audit</h5>
                        </div>
                        <div class="card-body">
                            <div class="card-datatable table-responsive pt-0">
                                <table class="datatables-basic table table-striped" id="health_audit_table">
                                    <div id="health_audit_overlay" class="dt-overlay"></div>
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Sr. No.</th>
                                            <th>Vehicle</th>
                                            <th>Status</th>
                                            <th>Open Service Logs</th>
                                            <th>Completed Trips</th>
                                            <th>Operational Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($healthRows as $row)
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td>{{ $row['vehicle'] }}</td>
                                                <td>{{ $row['status'] }}</td>
                                                <td>{{ $row['open_service_logs'] }}</td>
                                                <td>{{ $row['completed_trips'] }}</td>
                                                <td>{{ number_format($row['operational_cost'], 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7" class="text-center">No data available.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const monthPicker = flatpickr('#analytics_month_picker', {
                defaultDate: "{{ $selectedMonth }}-01",
                dateFormat: 'Y-m',
                altInput: true,
                altFormat: 'F, Y',
                allowInput: false,
                clickOpens: true,
                onChange: function(selectedDates, dateStr) {
                    document.getElementById('analytics_month_value').value = dateStr;
                }
            });

            if (monthPicker.selectedDates.length) {
                const selected = monthPicker.formatDate(monthPicker.selectedDates[0], 'Y-m');
                document.getElementById('analytics_month_value').value = selected;
            }

            const fuelEfficiencyChart = new ApexCharts(document.querySelector('#fuelEfficiencyChart'), {
                chart: {
                    type: 'line',
                    height: 280,
                    toolbar: { show: false },
                    foreColor: '#1F2933'
                },
                series: [{
                    name: 'Fuel Efficiency',
                    data: @json($chartFuelEfficiencySeries)
                }],
                stroke: {
                    width: 3,
                    curve: 'smooth'
                },
                markers: {
                    size: 4
                },
                colors: ['#1F7A4C'],
                grid: {
                    borderColor: '#E0E6E4'
                },
                xaxis: {
                    categories: @json($chartFuelEfficiencyLabels),
                    labels: {
                        style: { colors: '#1F2933' }
                    }
                },
                yaxis: {
                    labels: {
                        style: { colors: '#1F2933' }
                    }
                }
            });
            fuelEfficiencyChart.render();

            const topCostlyVehiclesChart = new ApexCharts(document.querySelector('#topCostlyVehiclesChart'), {
                chart: {
                    type: 'bar',
                    height: 280,
                    toolbar: { show: false },
                    foreColor: '#1F2933'
                },
                series: [{
                    name: 'Operational Cost',
                    data: @json($chartTopCostlySeries)
                }],
                legend: {
                    show: false
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return Number(val).toLocaleString(undefined, {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 2
                        });
                    }
                },
                xaxis: {
                    categories: @json($chartTopCostlyLabels),
                    labels: {
                        rotate: -25,
                        style: { colors: '#1F2933' },
                        formatter: function(value) {
                            const text = String(value || '');
                            return text.length > 18 ? text.slice(0, 18) + '...' : text;
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: { colors: '#1F2933' },
                        formatter: function(val) {
                            return Number(val).toLocaleString(undefined, {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            });
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return 'Rs. ' + Number(val).toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: '50%',
                        distributed: true
                    }
                },
                colors: ['#1F7A4C', '#2FAE7A', '#4CC2B8', '#6BCB9B', '#88D8B0'],
                grid: {
                    borderColor: '#E0E6E4'
                }
            });
            topCostlyVehiclesChart.render();

            function initAnalyticsDataTable(selector, title, options = {}) {
                const buttonsConfig = [];

                if ($.fn.dataTable.ext.buttons.colvis) {
                buttonsConfig.push({
                    extend: 'colvis',
                    collectionLayout: 'fixed one-column',
                    columns: function(idx) {
                        return idx !== 0;
                    },
                    text: '<i class="mdi mdi-eye me-1"></i> Select Columns',
                    className: 'btn btn-label-secondary'
                });
                }

                buttonsConfig.push({
                    extend: 'collection',
                    className: 'btn btn-label-primary dropdown-toggle me-2',
                    text: '<i class="mdi mdi-export-variant me-sm-1"></i> <span class="d-none d-sm-inline-block">Export</span>',
                    buttons: [
                        {
                            extend: 'print',
                            text: '<i class="mdi mdi-printer-outline me-1"></i>Print',
                            title: title + ' - ' + "{{ $selectedMonth }}",
                            className: 'dropdown-item',
                            exportOptions: {
                                columns: function(idx, data, node) {
                                    return $(node).is(':visible');
                                }
                            }
                        },
                        {
                            extend: 'csv',
                            text: '<i class="mdi mdi-file-delimited-outline me-1"></i>Csv',
                            title: title + ' - ' + "{{ $selectedMonth }}",
                            className: 'dropdown-item',
                            bom: true,
                            exportOptions: {
                                columns: function(idx, data, node) {
                                    return $(node).is(':visible');
                                }
                            }
                        },
                        {
                            extend: 'excel',
                            text: '<i class="mdi mdi-file-excel-outline me-1"></i>Excel',
                            title: title + ' - ' + "{{ $selectedMonth }}",
                            className: 'dropdown-item',
                            bom: true,
                            exportOptions: {
                                columns: function(idx, data, node) {
                                    return $(node).is(':visible');
                                }
                            }
                        },
                        {
                            extend: 'pdf',
                            text: '<i class="mdi mdi-file-pdf-box me-1"></i>Pdf',
                            title: title + ' - ' + "{{ $selectedMonth }}",
                            className: 'dropdown-item',
                            exportOptions: {
                                columns: function(idx, data, node) {
                                    return $(node).is(':visible');
                                }
                            },
                            orientation: 'landscape',
                            pageSize: 'A4'
                        },
                        {
                            extend: 'copy',
                            text: '<i class="mdi mdi-content-copy me-1"></i>Copy',
                            title: title + ' - ' + "{{ $selectedMonth }}",
                            className: 'dropdown-item',
                            exportOptions: {
                                columns: function(idx, data, node) {
                                    return $(node).is(':visible');
                                }
                            }
                        }
                    ]
                });

                return $(selector).DataTable({
                    paging: true,
                    searching: true,
                    ordering: true,
                    info: true,
                    responsive: true,
                    pageLength: 10,
                    order: [[2, 'asc']],
                    dom: '<"flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    columnDefs: [
                        {
                            className: 'control',
                            orderable: false,
                            searchable: false,
                            responsivePriority: 1,
                            targets: 0,
                            render: function() {
                                return '';
                            }
                        },
                        {
                            targets: 1,
                            orderable: false,
                            searchable: false,
                            responsivePriority: 2,
                            render: function(data, type, row, meta) {
                                return meta.row + 1;
                            }
                        }
                    ],
                    buttons: buttonsConfig
                });
            }

            const vehicleMetricsTable = initAnalyticsDataTable('#vehicle_metrics_table', 'Vehicle Metrics');
            const payrollTable = initAnalyticsDataTable('#payroll_table', 'Monthly Payroll Report');
            const healthAuditTable = initAnalyticsDataTable('#health_audit_table', 'Monthly Health Audit');

            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
                vehicleMetricsTable.columns.adjust();
                payrollTable.columns.adjust();
                healthAuditTable.columns.adjust();
            });
        });
    </script>
@endsection
