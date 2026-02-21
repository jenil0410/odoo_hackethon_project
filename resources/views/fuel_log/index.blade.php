@extends('layouts.app')
@section('title', 'Fuel Logs')
@section('styles')
    <style>
        .addButton {
            color: #fff !important;
            background-color: transparent !important;
            border: 1px solid #fff !important;
        }

        .addButton:hover {
            color: #1F7A4C !important;
            background-color: #fff !important;
        }

        .module-card-header {
            background-color: #1F7A4C !important;
            border-radius: 10px;
        }

        .module-card-header .card-title {
            color: #fff !important;
            background: transparent !important;
            border: 0 !important;
            border-radius: 0 !important;
            padding: 0 !important;
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
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between py-2 module-card-header">
            <h5 class="card-title m-0 me-2">Completed Trip, Expense & Fuel Logging</h5>
            @if ($createCheck)
                <button type="button" class="btn btn-primary waves-effect waves-light addButton" id="openCreateFuelModal">Add Fuel Log</button>
            @endif
        </div>
        <div class="card-body">
            <div class="card-datatable table-responsive pt-0">
                <table class="datatables-basic table table-striped" id="fuel_log_table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Action</th>
                            <th>Vehicle</th>
                            <th>Trip</th>
                            <th>Date</th>
                            <th>Liters</th>
                            <th>Cost</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="fuelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-2">
                    <h5 class="modal-title" id="fuelModalTitle">Add Fuel Log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="fuelForm" data-parsley-validate>
                    @csrf
                    <input type="hidden" id="fuel_log_id">
                    <div class="modal-body pt-2">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="fuel_vehicle_registry_id">Vehicle</label>
                                <select class="form-select select2" id="fuel_vehicle_registry_id" name="vehicle_registry_id" required>
                                    <option value="">Select Vehicle</option>
                                    @foreach ($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}">{{ $vehicle->name_model }} ({{ $vehicle->license_plate }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="fuel_trip_id">Completed Trip (Optional)</label>
                                <select class="form-select select2" id="fuel_trip_id" name="trip_id">
                                    <option value="">No Trip Link</option>
                                    @foreach ($completedTrips as $trip)
                                        <option value="{{ $trip->id }}" data-vehicle-id="{{ $trip->vehicle_registry_id }}">Trip #{{ $trip->id }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="liters">Liters</label>
                                <input type="number" min="0.01" step="0.01" class="form-control" id="liters" name="liters" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="fuel_cost">Cost</label>
                                <input type="number" min="0" step="0.01" class="form-control" id="fuel_cost" name="cost" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="logged_on">Date</label>
                                <input type="text" class="form-control flatpickr-date" id="logged_on" name="logged_on" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveFuelBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/parsley.min.js') }}"></script>
    <script>
        let fuelTable;
        let fuelModal;
        let fuelForm;
        let isFuelEditMode = false;

        $(function() {
            const moduleTableButtons = [{
                extend: 'colvis',
                collectionLayout: 'fixed one-column',
                columns: function(idx, data, node) {
                    const headerText = String($(node).text() || '').trim().toLowerCase();
                    return headerText !== '' && headerText !== '#' && headerText !== 'action';
                },
                text: '<i class="mdi mdi-eye me-1"></i> Select Columns',
                className: 'btn btn-label-secondary'
            }, {
                extend: 'collection',
                className: 'btn btn-label-primary dropdown-toggle me-2',
                text: '<i class="mdi mdi-export-variant me-sm-1"></i> <span class="d-none d-sm-inline-block">Export</span>',
                buttons: ['print', 'csv', 'excel', 'pdf', 'copy'].map(function(type) {
                    return {
                        extend: type,
                        className: 'dropdown-item',
                        exportOptions: {
                            columns: function(idx, data, node) {
                                return $(node).is(':visible');
                            }
                        }
                    };
                })
            }];

            fuelModal = new bootstrap.Modal(document.getElementById('fuelModal'));
            fuelForm = $('#fuelForm').parsley();

            $('.flatpickr-date').flatpickr({ dateFormat: 'Y-m-d', allowInput: true });

            fuelTable = $('#fuel_log_table').DataTable({
                processing: true,
                serverSide: true,
                dom: '<"flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                buttons: moduleTableButtons,
                ajax: "{{ route('fuel-log.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'vehicle', name: 'vehicle', searchable: false },
                    { data: 'trip', name: 'trip', searchable: false },
                    { data: 'logged_on', name: 'logged_on' },
                    { data: 'liters', name: 'liters', searchable: false },
                    { data: 'cost', name: 'cost', searchable: false }
                ]
            });

            $('#openCreateFuelModal').on('click', openFuelCreateModal);

            $('#fuelForm').on('submit', function(e) {
                e.preventDefault();
                if (!fuelForm.isValid()) return;

                const id = $('#fuel_log_id').val();
                const url = isFuelEditMode ? "{{ url('fuel-log') }}/" + id : "{{ route('fuel-log.store') }}";
                const payload = {
                    _token: "{{ csrf_token() }}",
                    vehicle_registry_id: $('#fuel_vehicle_registry_id').val(),
                    trip_id: $('#fuel_trip_id').val(),
                    liters: $('#liters').val(),
                    cost: $('#fuel_cost').val(),
                    logged_on: $('#logged_on').val()
                };
                if (isFuelEditMode) payload._method = 'PUT';

                $('#saveFuelBtn').prop('disabled', true);
                $.post(url, payload).done(function(response) {
                    $('#saveFuelBtn').prop('disabled', false);
                    fuelModal.hide();
                    fuelTable.ajax.reload(null, false);
                    Swal.fire('Success', response.message, 'success');
                }).fail(function(xhr) {
                    $('#saveFuelBtn').prop('disabled', false);
                    Swal.fire('Error', xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors)[0][0] : 'Unable to save fuel log.', 'error');
                });
            });
        });

        function resetFuelForm() {
            $('#fuelForm')[0].reset();
            $('#fuel_log_id').val('');
            $('#fuel_vehicle_registry_id').val('').trigger('change');
            $('#fuel_trip_id').val('').trigger('change');
            $('#fuelModalTitle').text('Add Fuel Log');
            $('#saveFuelBtn').text('Save');
            isFuelEditMode = false;
            fuelForm.reset();
        }

        function openFuelCreateModal() {
            resetFuelForm();
            fuelModal.show();
        }

        function openFuelEditModal(id) {
            resetFuelForm();
            isFuelEditMode = true;
            $('#fuelModalTitle').text('Update Fuel Log');
            $('#saveFuelBtn').text('Update');

            $.get("{{ url('fuel-log/fetch') }}/" + id, function(response) {
                const row = response.data;
                $('#fuel_log_id').val(row.id);
                $('#fuel_vehicle_registry_id').val(row.vehicle_registry_id).trigger('change');
                $('#fuel_trip_id').val(row.trip_id).trigger('change');
                $('#liters').val(row.liters);
                $('#fuel_cost').val(row.cost);
                $('#logged_on').val(row.logged_on);
                fuelModal.show();
            }).fail(function() {
                Swal.fire('Error', 'Unable to fetch fuel log details.', 'error');
            });
        }

        function deleteFuelLog(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to delete this fuel log.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (!result.isConfirmed) return;
                $.get("{{ url('fuel-log/delete') }}/" + id, function(response) {
                    fuelTable.ajax.reload(null, false);
                    Swal.fire('Success', response.message, 'success');
                }).fail(function() {
                    Swal.fire('Error', 'Unable to delete fuel log.', 'error');
                });
            });
        }
    </script>
@endsection

