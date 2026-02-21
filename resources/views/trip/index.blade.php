@extends('layouts.app')
@section('title', 'Trip Management')

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

        .filter-card {
            background-color: #F4F7F6 !important;
            border: 1px solid #E0E6E4;
        }

        .filter-card .form-control,
        .filter-card .form-select,
        .filter-card .select2-selection {
            border-color: #E0E6E4 !important;
            color: #1F2933 !important;
            background-color: #fff !important;
        }

        .trip-modal .modal-content {
            border-radius: 14px;
            border: 0;
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
    <div class="card mb-3 filter-card">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label" for="status_filter">Status</label>
                    <select class="form-select select2" id="status_filter">
                        <option value="">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="dispatched">Dispatched</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="vehicle_filter">Vehicle</label>
                    <select class="form-select select2" id="vehicle_filter">
                        <option value="">All Vehicles</option>
                        @foreach ($filterVehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->name_model }} ({{ $vehicle->license_plate }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="driver_filter">Driver</label>
                    <select class="form-select select2" id="driver_filter">
                        <option value="">All Drivers</option>
                        @foreach ($filterDrivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->full_name }} ({{ $driver->license_number }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary" type="button" id="applyTripFilters">Filter</button>
                    <button class="btn btn-outline-secondary" type="button" id="resetTripFilters">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between py-2 module-card-header">
            <h5 class="card-title m-0 me-2">Trip Dispatcher & Management</h5>
            @if ($createCheck)
                <button type="button" class="btn btn-primary waves-effect waves-light addButton" id="openCreateTripModal">
                    Create Trip
                </button>
            @endif
        </div>

        <div class="card-body">
            <div class="card-datatable table-responsive pt-0">
                <table class="datatables-basic table table-striped" id="trip_table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Action</th>
                            <th>Vehicle</th>
                            <th>Driver</th>
                            <th>Origin</th>
                            <th>Destination</th>
                            <th>Cargo Weight</th>
                            <th>Est. Fuel Cost</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade trip-modal" id="tripModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-2">
                    <h5 class="modal-title" id="tripModalTitle">Create Trip</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="tripForm" data-parsley-validate>
                    @csrf
                    <input type="hidden" id="trip_id" name="trip_id">
                    <div class="modal-body pt-2">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="vehicle_registry_id" class="form-label">Available Vehicle</label>
                                <select class="form-select select2" id="vehicle_registry_id" name="vehicle_registry_id" required
                                    data-parsley-required-message="Vehicle is required."
                                    data-parsley-errors-container="#vehicle_err">
                                    <option value="">Select Vehicle</option>
                                    @foreach ($vehicles as $vehicle)
                                        @php
                                            $maxKg = (float) $vehicle->max_load_capacity * ($vehicle->load_unit === 'tons' ? 1000 : 1);
                                        @endphp
                                        <option value="{{ $vehicle->id }}" data-max-kg="{{ $maxKg }}">
                                            {{ $vehicle->name_model }} ({{ $vehicle->license_plate }}) - Max {{ rtrim(rtrim((string) $vehicle->max_load_capacity, '0'), '.') }} {{ $vehicle->load_unit }}
                                        </option>
                                    @endforeach
                                </select>
                                <small id="vehicle_err" class="red-text"></small>
                            </div>
                            <div class="col-md-6">
                                <label for="driver_id" class="form-label">Available Driver</label>
                                <select class="form-select select2" id="driver_id" name="driver_id" required
                                    data-parsley-required-message="Driver is required."
                                    data-parsley-errors-container="#driver_err">
                                    <option value="">Select Driver</option>
                                    @foreach ($drivers as $driver)
                                        <option value="{{ $driver->id }}">
                                            {{ $driver->full_name }} ({{ $driver->license_number }})
                                        </option>
                                    @endforeach
                                </select>
                                <small id="driver_err" class="red-text"></small>
                            </div>
                            <div class="col-md-6">
                                <label for="origin_address" class="form-label">Origin Address</label>
                                <input type="text" class="form-control" id="origin_address" name="origin_address" required
                                    data-parsley-required-message="Origin address is required.">
                            </div>
                            <div class="col-md-6">
                                <label for="destination_address" class="form-label">Destination Address</label>
                                <input type="text" class="form-control" id="destination_address" name="destination_address" required
                                    data-parsley-required-message="Destination address is required.">
                            </div>
                            <div class="col-md-6">
                                <label for="cargo_weight" class="form-label">Cargo Weight (kg)</label>
                                <input type="number" step="0.01" min="0.01" class="form-control" id="cargo_weight" name="cargo_weight" required
                                    data-parsley-required-message="Cargo weight is required."
                                    data-parsley-maxcargoforvehicle="#vehicle_registry_id"
                                    data-parsley-maxcargoforvehicle-message="Cargo weight exceeds selected vehicle max capacity.">
                            </div>
                            <div class="col-md-6">
                                <label for="estimated_fuel_cost" class="form-label">Estimated Fuel Cost</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="estimated_fuel_cost" name="estimated_fuel_cost" required
                                    data-parsley-required-message="Estimated fuel cost is required.">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveTripBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade trip-modal" id="tripStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-2">
                    <h5 class="modal-title">Change Trip Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="tripStatusForm" data-parsley-validate>
                    @csrf
                    <input type="hidden" id="status_trip_id">
                    <div class="modal-body pt-2">
                        <label for="next_trip_status" class="form-label">Status</label>
                        <select class="form-select select2" id="next_trip_status" required
                            data-parsley-required-message="Please select status."
                            data-parsley-errors-container="#trip_status_err">
                            <option value="">Select status</option>
                        </select>
                        <small id="trip_status_err" class="red-text"></small>
                        <div class="row g-3 mt-1 d-none" id="trip_completion_fields">
                            <div class="col-md-6">
                                <label for="final_odometer" class="form-label">Final Odometer</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="final_odometer">
                            </div>
                            <div class="col-md-6">
                                <label for="revenue_amount" class="form-label">Revenue Amount</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="revenue_amount">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">OK</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/parsley.min.js') }}"></script>
    <script>
        window.Parsley.addValidator('maxcargoforvehicle', {
            requirementType: 'string',
            validateString: function(value, requirement) {
                const selected = $(requirement + ' option:selected');
                const maxKg = Number(selected.data('max-kg'));
                const cargo = Number(value);

                if (!selected.length || !Number.isFinite(cargo) || !Number.isFinite(maxKg) || maxKg <= 0) {
                    return true;
                }

                return cargo <= maxKg;
            }
        });

        let tripTable;
        let tripModal;
        let tripStatusModal;
        let tripForm;
        let tripStatusForm;
        let isEditMode = false;

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

            tripModal = new bootstrap.Modal(document.getElementById('tripModal'));
            tripStatusModal = new bootstrap.Modal(document.getElementById('tripStatusModal'));
            tripForm = $('#tripForm').parsley({
                excluded: 'input[type=button], input[type=submit], input[type=reset], [disabled]'
            });
            tripStatusForm = $('#tripStatusForm').parsley({
                excluded: 'input[type=button], input[type=submit], input[type=reset], [disabled]'
            });

            tripTable = $('#trip_table').DataTable({
                processing: true,
                serverSide: true,
                dom: '<"flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                buttons: moduleTableButtons,
                ajax: {
                    url: "{{ route('trip.index') }}",
                    data: function(d) {
                        d.status_filter = $('#status_filter').val();
                        d.vehicle_filter = $('#vehicle_filter').val();
                        d.driver_filter = $('#driver_filter').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'vehicle', name: 'vehicle', searchable: false },
                    { data: 'driver', name: 'driver', searchable: false },
                    { data: 'origin_address', name: 'origin_address' },
                    { data: 'destination_address', name: 'destination_address' },
                    { data: 'cargo_weight', name: 'cargo_weight', searchable: false },
                    { data: 'estimated_fuel_cost', name: 'estimated_fuel_cost', searchable: false },
                    { data: 'status', name: 'status', searchable: false }
                ]
            });

            $('#applyTripFilters').on('click', function() {
                tripTable.ajax.reload();
            });

            $('#resetTripFilters').on('click', function() {
                $('#status_filter').val('').trigger('change');
                $('#vehicle_filter').val('').trigger('change');
                $('#driver_filter').val('').trigger('change');
                tripTable.ajax.reload();
            });

            $('#openCreateTripModal').on('click', function() {
                openTripCreateModal();
            });

            $('#vehicle_registry_id, #driver_id').on('change', function() {
                tripForm.validate();
            });
            $('#vehicle_registry_id').on('change', function() {
                $('#cargo_weight').parsley().validate();
            });
            $('#next_trip_status').on('change', function() {
                const isCompleted = $(this).val() === 'completed';
                $('#trip_completion_fields').toggleClass('d-none', !isCompleted);
                tripStatusForm.validate();
            });

            $('#tripForm').on('submit', function(e) {
                e.preventDefault();
                clearServerErrors();

                if (!tripForm.isValid()) {
                    return;
                }

                const tripId = $('#trip_id').val();
                const url = isEditMode ? "{{ url('trip') }}/" + tripId : "{{ route('trip.store') }}";
                const payload = {
                    _token: "{{ csrf_token() }}",
                    vehicle_registry_id: $('#vehicle_registry_id').val(),
                    driver_id: $('#driver_id').val(),
                    origin_address: $('#origin_address').val(),
                    destination_address: $('#destination_address').val(),
                    cargo_weight: $('#cargo_weight').val(),
                    estimated_fuel_cost: $('#estimated_fuel_cost').val()
                };

                if (isEditMode) {
                    payload._method = 'PUT';
                }

                $('#saveTripBtn').prop('disabled', true);
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: payload,
                    success: function(response) {
                        $('#saveTripBtn').prop('disabled', false);
                        tripModal.hide();
                        tripTable.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            confirmButtonText: 'OK',
                            showCancelButton: false
                        });
                    },
                    error: function(xhr) {
                        $('#saveTripBtn').prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            Object.keys(errors).forEach(function(field) {
                                const input = $('#' + field);
                                if (input.length) {
                                    input.parsley().addError('server', {
                                        message: errors[field][0],
                                        updateClass: true
                                    });
                                }
                            });
                        } else {
                            Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                        }
                    }
                });
            });

            $('#tripModal').on('hidden.bs.modal', function() {
                resetTripForm();
            });

            $('#tripStatusForm').on('submit', function(e) {
                e.preventDefault();
                if (!tripStatusForm.isValid()) {
                    return;
                }

                const id = $('#status_trip_id').val();
                const status = $('#next_trip_status').val();
                const completionPayload = {
                    final_odometer: $('#final_odometer').val(),
                    revenue_amount: $('#revenue_amount').val()
                };
                tripStatusModal.hide();
                changeTripStatus(id, status, completionPayload);
            });

            $('#tripStatusModal').on('hidden.bs.modal', function() {
                $('#status_trip_id').val('');
                $('#next_trip_status').empty().append('<option value="">Select status</option>').trigger('change');
                $('#final_odometer').val('');
                $('#revenue_amount').val('');
                $('#trip_completion_fields').addClass('d-none');
                tripStatusForm.reset();
            });
        });

        function resetTripForm() {
            $('#tripForm')[0].reset();
            $('#trip_id').val('');
            $('#vehicle_registry_id').val('').trigger('change');
            $('#driver_id').val('').trigger('change');
            isEditMode = false;
            $('#tripModalTitle').text('Create Trip');
            $('#saveTripBtn').text('Save');
            tripForm.reset();
        }

        function clearServerErrors() {
            ['vehicle_registry_id', 'driver_id', 'origin_address', 'destination_address', 'cargo_weight', 'estimated_fuel_cost', 'status']
                .forEach(function(field) {
                    const input = $('#' + field);
                    if (input.length && input.parsley()) {
                        input.parsley().removeError('server', { updateClass: true });
                    }
                });
        }

        function openTripCreateModal() {
            resetTripForm();
            clearServerErrors();
            tripModal.show();
        }

        function openTripEditModal(id) {
            resetTripForm();
            clearServerErrors();
            isEditMode = true;
            $('#tripModalTitle').text('Update Trip');
            $('#saveTripBtn').text('Update');

            $.get("{{ url('trip/fetch') }}/" + id, function(response) {
                const row = response.data;
                $('#trip_id').val(row.id);

                if ($('#vehicle_registry_id option[value="' + row.vehicle_registry_id + '"]').length === 0 && row.vehicle) {
                    const maxKg = Number(row.vehicle.max_load_capacity) * (row.vehicle.load_unit === 'tons' ? 1000 : 1);
                    const vehicleLabel = row.vehicle.name_model + ' (' + row.vehicle.license_plate + ') - Max ' +
                        Number(row.vehicle.max_load_capacity).toFixed(2).replace(/\.00$/, '') + ' ' + row.vehicle.load_unit;
                    $('#vehicle_registry_id').append(
                        $('<option>', { value: row.vehicle_registry_id, text: vehicleLabel }).attr('data-max-kg', maxKg)
                    );
                }

                if ($('#driver_id option[value="' + row.driver_id + '"]').length === 0 && row.driver) {
                    const driverLabel = row.driver.full_name + ' (' + row.driver.license_number + ')';
                    $('#driver_id').append(
                        $('<option>', { value: row.driver_id, text: driverLabel })
                    );
                }

                $('#vehicle_registry_id').val(row.vehicle_registry_id).trigger('change');
                $('#driver_id').val(row.driver_id).trigger('change');
                $('#origin_address').val(row.origin_address);
                $('#destination_address').val(row.destination_address);
                $('#cargo_weight').val(row.cargo_weight);
                $('#estimated_fuel_cost').val(row.estimated_fuel_cost);
                tripForm.reset();
                tripModal.show();
            }).fail(function() {
                Swal.fire('Error', 'Unable to fetch trip details.', 'error');
            });
        }

        function changeTripStatus(id, status, completionPayload) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Change status to ' + status + '?',
                icon: 'warning',
                showCancelButton: true,
                showDenyButton: false,
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: "{{ url('trip') }}/" + id + "/status",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        status: status,
                        final_odometer: completionPayload?.final_odometer ?? '',
                        revenue_amount: completionPayload?.revenue_amount ?? ''
                    },
                    success: function(response) {
                        tripTable.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            confirmButtonText: 'OK',
                            showCancelButton: false
                        });
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors)[0][0] : 'Unable to update status.';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });
        }

        function openTripStatusModal(id, currentStatus) {
            const $status = $('#next_trip_status');
            const transitions = {
                draft: ['dispatched', 'cancelled'],
                dispatched: ['completed', 'cancelled'],
                completed: [],
                cancelled: []
            };
            const statusOptions = transitions[currentStatus] || [];

            $status.empty().append('<option value="">Select status</option>');
            statusOptions.forEach(function(status) {
                const label = status.charAt(0).toUpperCase() + status.slice(1);
                $status.append('<option value="' + status + '">' + label + '</option>');
            });

            $('#status_trip_id').val(id);
            $('#final_odometer').val('');
            $('#revenue_amount').val('');
            $('#trip_completion_fields').addClass('d-none');
            $status.val('').trigger('change');
            tripStatusForm.reset();
            tripStatusModal.show();
        }

        function deleteTrip(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to delete this trip.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                $.get("{{ url('trip/delete') }}/" + id, function(response) {
                    if (response.status) {
                        tripTable.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted',
                            text: response.message,
                            confirmButtonText: 'OK',
                            showCancelButton: false
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Unable to delete trip.', 'error');
                });
            });
        }
    </script>
@endsection
