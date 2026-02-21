@extends('layouts.app')
@section('title', 'Vehicle Registry')

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

        .vehicle-modal .modal-content {
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
                <div class="col-md-4">
                    <label class="form-label" for="status_filter">Status</label>
                    <select class="form-select select2" id="status_filter">
                        <option value="">All Statuses</option>
                        <option value="available">Available</option>
                        <option value="in_shop">In Shop</option>
                        <option value="out_of_service">Out of Service</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="load_unit_filter">Load Unit</label>
                    <select class="form-select select2" id="load_unit_filter">
                        <option value="">All Units</option>
                        @foreach ($loadUnits as $unit)
                            <option value="{{ $unit }}">{{ strtoupper($unit) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-primary" type="button" id="applyVehicleFilters">Filter</button>
                    <button class="btn btn-outline-secondary" type="button" id="resetVehicleFilters">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between py-2 module-card-header">
            <h5 class="card-title m-0 me-2">Vehicle Registry</h5>
            @if ($createCheck)
                <button type="button" class="btn btn-primary waves-effect waves-light addButton" id="openCreateVehicleModal">
                    Add Vehicle
                </button>
            @endif
        </div>

        <div class="card-body">
            <div class="card-datatable table-responsive pt-0">
                <table class="datatables-basic table table-striped" id="vehicle_registry_table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Action</th>
                            <th>Name / Model</th>
                            <th>License Plate</th>
                            <th>Max Load Capacity</th>
                            <th>Odometer</th>
                            <th>Acquisition Cost</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade vehicle-modal" id="vehicleRegistryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-2">
                    <h5 class="modal-title" id="vehicleModalTitle">Add Vehicle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="vehicleRegistryForm" data-parsley-validate>
                    @csrf
                    <input type="hidden" id="vehicle_id" name="vehicle_id">
                    <div class="modal-body pt-2">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name_model" class="form-label">Name / Model</label>
                                <input type="text" class="form-control" id="name_model" name="name_model" required
                                    data-parsley-required-message="Name/Model is required.">
                            </div>
                            <div class="col-md-6">
                                <label for="license_plate" class="form-label">License Plate (Unique)</label>
                                <input type="text" class="form-control" id="license_plate" name="license_plate" required
                                    data-parsley-required-message="License plate is required.">
                            </div>
                            <div class="col-md-4">
                                <label for="max_load_capacity" class="form-label">Max Load Capacity</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="max_load_capacity"
                                    name="max_load_capacity" required data-parsley-required-message="Max load capacity is required.">
                            </div>
                            <div class="col-md-4">
                                <label for="load_unit" class="form-label">Unit</label>
                                <select class="form-select select2" id="load_unit" name="load_unit" required
                                    data-parsley-required-message="Unit is required."
                                    data-parsley-errors-container="#load_unit_err">
                                    <option value="">Select Unit</option>
                                    <option value="kg">kg</option>
                                    <option value="tons">tons</option>
                                </select>
                                <small class="red-text ml-10" id="load_unit_err" role="alert"></small>
                            </div>
                            <div class="col-md-4">
                                <label for="odometer" class="form-label">Odometer</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="odometer"
                                    name="odometer" required data-parsley-required-message="Odometer is required.">
                            </div>
                            <div class="col-md-4">
                                <label for="acquisition_cost" class="form-label">Acquisition Cost</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="acquisition_cost"
                                    name="acquisition_cost">
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_out_of_service" name="is_out_of_service" value="1">
                                    <label class="form-check-label" for="is_out_of_service">Out of Service (Retired)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveVehicleBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/parsley.min.js') }}"></script>
    <script>
        let vehicleTable;
        let vehicleModal;
        let vehicleForm;
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

            vehicleModal = new bootstrap.Modal(document.getElementById('vehicleRegistryModal'));
            vehicleForm = $('#vehicleRegistryForm').parsley({
                excluded: 'input[type=button], input[type=submit], input[type=reset], [disabled]'
            });

            vehicleTable = $('#vehicle_registry_table').DataTable({
                processing: true,
                serverSide: true,
                dom: '<"flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                buttons: moduleTableButtons,
                ajax: {
                    url: "{{ route('vehicle-registry.index') }}",
                    data: function(d) {
                        d.status_filter = $('#status_filter').val();
                        d.load_unit_filter = $('#load_unit_filter').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'name_model', name: 'name_model' },
                    { data: 'license_plate', name: 'license_plate' },
                    { data: 'max_load_capacity', name: 'max_load_capacity', searchable: false },
                    { data: 'odometer', name: 'odometer', searchable: false },
                    { data: 'acquisition_cost', name: 'acquisition_cost', searchable: false },
                    { data: 'is_out_of_service', name: 'is_out_of_service', orderable: false, searchable: false }
                ]
            });

            $('#applyVehicleFilters').on('click', function() {
                vehicleTable.ajax.reload();
            });

            $('#resetVehicleFilters').on('click', function() {
                $('#status_filter').val('').trigger('change');
                $('#load_unit_filter').val('').trigger('change');
                vehicleTable.ajax.reload();
            });

            $('#load_unit').on('change', function() {
                vehicleForm.validate();
            });

            $('#name_model, #license_plate, #max_load_capacity, #odometer').on('input change blur', function() {
                const field = $(this).parsley();
                field.removeError('server', { updateClass: true });
                field.validate();
            });

            $('#openCreateVehicleModal').on('click', function() {
                openVehicleCreateModal();
            });

            $('#vehicleRegistryForm').on('submit', function(e) {
                e.preventDefault();
                clearServerErrors();

                if (!vehicleForm.isValid()) {
                    return;
                }

                const vehicleId = $('#vehicle_id').val();
                const url = isEditMode
                    ? "{{ url('vehicle-registry') }}/" + vehicleId
                    : "{{ route('vehicle-registry.store') }}";

                const payload = {
                    _token: "{{ csrf_token() }}",
                    name_model: $('#name_model').val(),
                    license_plate: $('#license_plate').val(),
                    max_load_capacity: $('#max_load_capacity').val(),
                    load_unit: $('#load_unit').val(),
                    odometer: $('#odometer').val(),
                    acquisition_cost: $('#acquisition_cost').val(),
                    is_out_of_service: $('#is_out_of_service').is(':checked') ? 1 : 0
                };

                if (isEditMode) {
                    payload._method = 'PUT';
                }

                $('#saveVehicleBtn').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: payload,
                    success: function(response) {
                        $('#saveVehicleBtn').prop('disabled', false);
                        vehicleModal.hide();
                        vehicleTable.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            confirmButtonText: 'OK',
                            showCancelButton: false
                        });
                    },
                    error: function(xhr) {
                        $('#saveVehicleBtn').prop('disabled', false);
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

            $('#vehicleRegistryModal').on('hidden.bs.modal', function() {
                resetVehicleForm();
            });
        });

        function resetVehicleForm() {
            $('#vehicleRegistryForm')[0].reset();
            $('#vehicle_id').val('');
            $('#load_unit').val('').trigger('change');
            $('#is_out_of_service').prop('checked', false);
            $('#acquisition_cost').val('');
            isEditMode = false;
            $('#vehicleModalTitle').text('Add Vehicle');
            $('#saveVehicleBtn').text('Save');
            vehicleForm.reset();
        }

        function clearServerErrors() {
            ['name_model', 'license_plate', 'max_load_capacity', 'load_unit', 'odometer', 'acquisition_cost'].forEach(function(field) {
                const input = $('#' + field);
                if (input.length && input.parsley()) {
                    input.parsley().removeError('server', {
                        updateClass: true
                    });
                }
            });
        }

        function openVehicleCreateModal() {
            resetVehicleForm();
            clearServerErrors();
            vehicleModal.show();
        }

        function openVehicleEditModal(id) {
            resetVehicleForm();
            clearServerErrors();
            isEditMode = true;
            $('#vehicleModalTitle').text('Update Vehicle');
            $('#saveVehicleBtn').text('Update');

            $.get("{{ url('vehicle-registry/fetch') }}/" + id, function(response) {
                const row = response.data;
                $('#vehicle_id').val(row.id);
                $('#name_model').val(row.name_model);
                $('#license_plate').val(row.license_plate);
                $('#max_load_capacity').val(row.max_load_capacity);
                $('#load_unit').val(row.load_unit).trigger('change');
                $('#odometer').val(row.odometer);
                $('#acquisition_cost').val(row.acquisition_cost);
                $('#is_out_of_service').prop('checked', !!row.is_out_of_service);
                vehicleForm.reset();
                vehicleModal.show();
            }).fail(function() {
                Swal.fire('Error', 'Unable to fetch vehicle details.', 'error');
            });
        }

        function deleteVehicle(id, licensePlate) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to delete vehicle ' + licensePlate + '.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                $.get("{{ url('vehicle-registry/delete') }}/" + id, function(response) {
                    if (response.status) {
                        vehicleTable.ajax.reload(null, false);
                        Swal.fire('Deleted', response.message, 'success');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Unable to delete vehicle.', 'error');
                });
            });
        }
    </script>
@endsection
