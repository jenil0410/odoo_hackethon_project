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

        .trip-modal .modal-content {
            border-radius: 14px;
            border: 0;
        }
    </style>
@endsection

@section('content')
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
            <div class="table-responsive">
                <table class="table table-striped" id="trip_table">
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
                                            {{ $vehicle->name_model }} ({{ $vehicle->license_plate }}) - Max {{ rtrim(rtrim((string) $vehicle->max_load_capacity, '0'), '.') }} {{ $vehicle->load_unit }} - Status: {{ ucfirst(str_replace('_', ' ', $vehicle->status)) }}
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
                                            {{ $driver->full_name }} ({{ $driver->license_number }}) - Status: {{ ucfirst(str_replace('_', ' ', $driver->status)) }}
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
                ajax: "{{ route('trip.index') }}",
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
                tripStatusModal.hide();
                changeTripStatus(id, status);
            });

            $('#tripStatusModal').on('hidden.bs.modal', function() {
                $('#status_trip_id').val('');
                $('#next_trip_status').empty().append('<option value="">Select status</option>').trigger('change');
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
                    const vehicleStatus = String(row.vehicle.status || '').replace('_', ' ');
                    const vehicleLabel = row.vehicle.name_model + ' (' + row.vehicle.license_plate + ') - Max ' +
                        Number(row.vehicle.max_load_capacity).toFixed(2).replace(/\.00$/, '') + ' ' + row.vehicle.load_unit +
                        ' - Status: ' + (vehicleStatus ? vehicleStatus.charAt(0).toUpperCase() + vehicleStatus.slice(1) : 'Unknown');
                    $('#vehicle_registry_id').append(
                        $('<option>', { value: row.vehicle_registry_id, text: vehicleLabel }).attr('data-max-kg', maxKg)
                    );
                }

                if ($('#driver_id option[value="' + row.driver_id + '"]').length === 0 && row.driver) {
                    const driverStatus = String(row.driver.status || '').replace('_', ' ');
                    const driverLabel = row.driver.full_name + ' (' + row.driver.license_number + ')' +
                        ' - Status: ' + (driverStatus ? driverStatus.charAt(0).toUpperCase() + driverStatus.slice(1) : 'Unknown');
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

        function changeTripStatus(id, status) {
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
                        status: status
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
            const statusOptions = ['draft', 'dispatched', 'completed', 'cancelled'];

            $status.empty().append('<option value="">Select status</option>');
            statusOptions.forEach(function(status) {
                const label = status.charAt(0).toUpperCase() + status.slice(1);
                $status.append('<option value="' + status + '">' + label + '</option>');
            });

            $('#status_trip_id').val(id);
            $status.val(currentStatus).trigger('change');
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
