@extends('layouts.app')
@section('title', 'Driver Management')

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

        .driver-modal .modal-content {
            border-radius: 14px;
            border: 0;
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between py-2 module-card-header">
            <h5 class="card-title m-0 me-2">Driver Management</h5>
            @if ($createCheck)
                <button type="button" class="btn btn-primary waves-effect waves-light addButton" id="openCreateDriverModal">
                    Add Driver
                </button>
            @endif
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="driver_table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Action</th>
                            <th>Name</th>
                            <th>License No.</th>
                            <th>License Expiry</th>
                            <th>Status</th>
                            <th>Compliance</th>
                            <th>Trip Completion</th>
                            <th>Safety Score</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade driver-modal" id="driverModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-2">
                    <h5 class="modal-title" id="driverModalTitle">Add Driver</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="driverForm" data-parsley-validate>
                    @csrf
                    <input type="hidden" id="driver_id" name="driver_id">
                    <div class="modal-body pt-2">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required
                                    data-parsley-required-message="Full name is required.">
                            </div>
                            <div class="col-md-6">
                                <label for="license_number" class="form-label">License Number</label>
                                <input type="text" class="form-control" id="license_number" name="license_number" required
                                    data-parsley-required-message="License number is required.">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    data-parsley-type-message="Please enter a valid email address.">
                            </div>
                            <div class="col-md-6">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number"
                                    data-parsley-pattern="^[0-9+\-\s()]{7,20}$"
                                    data-parsley-pattern-message="Phone number must be 7 to 20 valid characters.">
                            </div>
                            <div class="col-md-4">
                                <label for="license_expiry_date" class="form-label">License Expiry Date</label>
                                <input type="text" class="form-control flatpickr-date" id="license_expiry_date" name="license_expiry_date" required
                                    placeholder="YYYY-MM-DD" data-parsley-required-message="License expiry date is required."
                                    data-parsley-pattern="^(|\d{4}-\d{2}-\d{2})$"
                                    data-parsley-pattern-message="Use date format YYYY-MM-DD.">
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select select2" id="status" name="status" required
                                    data-parsley-required-message="Status is required."
                                    data-parsley-errors-container="#status_err">
                                    <option value="">Select Status</option>
                                    <option value="on_duty">On Duty</option>
                                    <option value="off_duty">Off Duty</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                                <small class="red-text ml-10" id="status_err" role="alert"></small>
                            </div>
                            <div class="col-md-4">
                                <label for="safety_score" class="form-label">Safety Score (0-100)</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-control" id="safety_score"
                                    name="safety_score" required data-parsley-required-message="Safety score is required.">
                            </div>
                            <div class="col-md-6">
                                <label for="total_trips" class="form-label">Total Trips</label>
                                <input type="number" min="0" class="form-control" id="total_trips"
                                    name="total_trips" required data-parsley-required-message="Total trips is required.">
                            </div>
                            <div class="col-md-6">
                                <label for="completed_trips" class="form-label">Completed Trips</label>
                                <input type="number" min="0" class="form-control" id="completed_trips"
                                    name="completed_trips" required data-parsley-required-message="Completed trips is required."
                                    data-parsley-lte="#total_trips">
                            </div>
                            <div class="col-12">
                                <div class="alert alert-warning mb-0 py-2">
                                    Compliance rule: assignments are blocked when the license is expired.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveDriverBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/parsley.min.js') }}"></script>
    <script>
        window.Parsley.addValidator('lte', {
            requirementType: 'string',
            validateString: function(value, requirement) {
                const target = $(requirement).val();
                if (value === '' || target === '') {
                    return true;
                }
                return Number(value) <= Number(target);
            },
            messages: {
                en: 'Completed trips cannot be greater than total trips.'
            }
        });

        let driverTable;
        let driverModal;
        let driverForm;
        let isEditMode = false;

        $(function() {
            driverModal = new bootstrap.Modal(document.getElementById('driverModal'));
            driverForm = $('#driverForm').parsley({
                excluded: 'input[type=button], input[type=submit], input[type=reset], [disabled]'
            });

            $('.flatpickr-date').flatpickr({
                dateFormat: 'Y-m-d',
                allowInput: true
            });

            driverTable = $('#driver_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('driver.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'full_name', name: 'full_name' },
                    { data: 'license_number', name: 'license_number' },
                    { data: 'license_expiry_date', name: 'license_expiry_date' },
                    { data: 'status', name: 'status', searchable: false },
                    { data: 'compliance', name: 'compliance', searchable: false },
                    { data: 'trip_completion_rate', name: 'trip_completion_rate', searchable: false },
                    { data: 'safety_score', name: 'safety_score', searchable: false }
                ]
            });

            $('#openCreateDriverModal').on('click', function() {
                openDriverCreateModal();
            });

            $('#status').on('change', function() {
                driverForm.validate();
            });

            $('#full_name, #license_number, #email, #phone_number, #license_expiry_date, #total_trips, #completed_trips, #safety_score')
                .on('input change blur', function() {
                    const field = $(this).parsley();
                    field.removeError('server', { updateClass: true });
                    field.validate();
                });

            $('#driverForm').on('submit', function(e) {
                e.preventDefault();
                clearServerErrors();

                if (!driverForm.isValid()) {
                    return;
                }

                const driverId = $('#driver_id').val();
                const url = isEditMode
                    ? "{{ url('driver') }}/" + driverId
                    : "{{ route('driver.store') }}";

                const payload = {
                    _token: "{{ csrf_token() }}",
                    full_name: $('#full_name').val(),
                    email: $('#email').val(),
                    phone_number: $('#phone_number').val(),
                    license_number: $('#license_number').val(),
                    license_expiry_date: $('#license_expiry_date').val(),
                    total_trips: $('#total_trips').val(),
                    completed_trips: $('#completed_trips').val(),
                    safety_score: $('#safety_score').val(),
                    status: $('#status').val()
                };

                if (isEditMode) {
                    payload._method = 'PUT';
                }

                $('#saveDriverBtn').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: payload,
                    success: function(response) {
                        $('#saveDriverBtn').prop('disabled', false);
                        driverModal.hide();
                        driverTable.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            confirmButtonText: 'OK',
                            showCancelButton: false
                        });
                    },
                    error: function(xhr) {
                        $('#saveDriverBtn').prop('disabled', false);
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

            $('#driverModal').on('hidden.bs.modal', function() {
                resetDriverForm();
            });
        });

        function resetDriverForm() {
            $('#driverForm')[0].reset();
            $('#driver_id').val('');
            $('#status').val('').trigger('change');
            isEditMode = false;
            $('#driverModalTitle').text('Add Driver');
            $('#saveDriverBtn').text('Save');
            driverForm.reset();
        }

        function clearServerErrors() {
            ['full_name', 'email', 'phone_number', 'license_number', 'license_expiry_date', 'total_trips', 'completed_trips', 'safety_score', 'status']
                .forEach(function(field) {
                    const input = $('#' + field);
                    if (input.length && input.parsley()) {
                        input.parsley().removeError('server', { updateClass: true });
                    }
                });
        }

        function openDriverCreateModal() {
            resetDriverForm();
            clearServerErrors();
            driverModal.show();
        }

        function openDriverEditModal(id) {
            resetDriverForm();
            clearServerErrors();
            isEditMode = true;
            $('#driverModalTitle').text('Update Driver');
            $('#saveDriverBtn').text('Update');

            $.get("{{ url('driver/fetch') }}/" + id, function(response) {
                const row = response.data;
                $('#driver_id').val(row.id);
                $('#full_name').val(row.full_name);
                $('#email').val(row.email);
                $('#phone_number').val(row.phone_number);
                $('#license_number').val(row.license_number);
                $('#license_expiry_date').val(row.license_expiry_date);
                $('#total_trips').val(row.total_trips);
                $('#completed_trips').val(row.completed_trips);
                $('#safety_score').val(row.safety_score);
                $('#status').val(row.status).trigger('change');
                driverForm.reset();
                driverModal.show();
            }).fail(function() {
                Swal.fire('Error', 'Unable to fetch driver details.', 'error');
            });
        }

        function deleteDriver(id, name) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to delete driver ' + name + '.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                $.get("{{ url('driver/delete') }}/" + id, function(response) {
                    if (response.status) {
                        driverTable.ajax.reload(null, false);
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
                    Swal.fire('Error', 'Unable to delete driver.', 'error');
                });
            });
        }
    </script>
@endsection
