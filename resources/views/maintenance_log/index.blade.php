@extends('layouts.app')
@section('title', 'Maintenance Logs')
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
            <h5 class="card-title m-0 me-2">Maintenance & Service Logs</h5>
            @if ($createCheck)
                <button type="button" class="btn btn-primary waves-effect waves-light addButton" id="openCreateMaintenanceModal">Add Service Log</button>
            @endif
        </div>
        <div class="card-body">
            <div class="card-datatable table-responsive pt-0">
                <table class="datatables-basic table table-striped" id="maintenance_log_table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Action</th>
                            <th>Vehicle</th>
                            <th>Title</th>
                            <th>Service Date</th>
                            <th>Cost</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="maintenanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-2">
                    <h5 class="modal-title" id="maintenanceModalTitle">Add Service Log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="maintenanceForm" data-parsley-validate>
                    @csrf
                    <input type="hidden" id="maintenance_id">
                    <div class="modal-body pt-2">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="vehicle_registry_id">Vehicle</label>
                                <select class="form-select select2" id="vehicle_registry_id" name="vehicle_registry_id" required>
                                    <option value="">Select Vehicle</option>
                                    @foreach ($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}">{{ $vehicle->name_model }} ({{ $vehicle->license_plate }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="title">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="service_date">Service Date</label>
                                <input type="text" class="form-control flatpickr-date" id="service_date" name="service_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="cost">Cost</label>
                                <input type="number" min="0" step="0.01" class="form-control" id="cost" name="cost" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label" for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="maintenance_status">Status</label>
                                <select class="form-select select2" id="maintenance_status" name="status">
                                    <option value="in_shop">In Shop</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveMaintenanceBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/parsley.min.js') }}"></script>
    <script>
        let maintenanceTable;
        let maintenanceModal;
        let maintenanceForm;
        let isMaintenanceEditMode = false;

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

            maintenanceModal = new bootstrap.Modal(document.getElementById('maintenanceModal'));
            maintenanceForm = $('#maintenanceForm').parsley();

            $('.flatpickr-date').flatpickr({ dateFormat: 'Y-m-d', allowInput: true });

            maintenanceTable = $('#maintenance_log_table').DataTable({
                processing: true,
                serverSide: true,
                dom: '<"flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                buttons: moduleTableButtons,
                ajax: "{{ route('maintenance-log.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'vehicle', name: 'vehicle', searchable: false },
                    { data: 'title', name: 'title' },
                    { data: 'service_date', name: 'service_date' },
                    { data: 'cost', name: 'cost', searchable: false },
                    { data: 'status', name: 'status', searchable: false }
                ]
            });

            $('#openCreateMaintenanceModal').on('click', openMaintenanceCreateModal);

            $('#maintenanceForm').on('submit', function(e) {
                e.preventDefault();
                if (!maintenanceForm.isValid()) return;

                const id = $('#maintenance_id').val();
                const url = isMaintenanceEditMode ? "{{ url('maintenance-log') }}/" + id : "{{ route('maintenance-log.store') }}";
                const payload = {
                    _token: "{{ csrf_token() }}",
                    vehicle_registry_id: $('#vehicle_registry_id').val(),
                    title: $('#title').val(),
                    description: $('#description').val(),
                    service_date: $('#service_date').val(),
                    cost: $('#cost').val(),
                    status: $('#maintenance_status').val()
                };
                if (isMaintenanceEditMode) payload._method = 'PUT';

                $('#saveMaintenanceBtn').prop('disabled', true);
                $.post(url, payload).done(function(response) {
                    $('#saveMaintenanceBtn').prop('disabled', false);
                    maintenanceModal.hide();
                    maintenanceTable.ajax.reload(null, false);
                    Swal.fire('Success', response.message, 'success');
                }).fail(function(xhr) {
                    $('#saveMaintenanceBtn').prop('disabled', false);
                    Swal.fire('Error', xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors)[0][0] : 'Unable to save maintenance log.', 'error');
                });
            });
        });

        function resetMaintenanceForm() {
            $('#maintenanceForm')[0].reset();
            $('#maintenance_id').val('');
            $('#vehicle_registry_id').val('').trigger('change');
            $('#maintenance_status').val('in_shop').trigger('change');
            $('#maintenanceModalTitle').text('Add Service Log');
            $('#saveMaintenanceBtn').text('Save');
            isMaintenanceEditMode = false;
            maintenanceForm.reset();
        }

        function openMaintenanceCreateModal() {
            resetMaintenanceForm();
            maintenanceModal.show();
        }

        function openMaintenanceEditModal(id) {
            resetMaintenanceForm();
            isMaintenanceEditMode = true;
            $('#maintenanceModalTitle').text('Update Service Log');
            $('#saveMaintenanceBtn').text('Update');

            $.get("{{ url('maintenance-log/fetch') }}/" + id, function(response) {
                const row = response.data;
                $('#maintenance_id').val(row.id);
                $('#vehicle_registry_id').val(row.vehicle_registry_id).trigger('change');
                $('#title').val(row.title);
                $('#description').val(row.description);
                $('#service_date').val(row.service_date);
                $('#cost').val(row.cost);
                $('#maintenance_status').val(row.status).trigger('change');
                maintenanceModal.show();
            }).fail(function() {
                Swal.fire('Error', 'Unable to fetch maintenance details.', 'error');
            });
        }

        function markMaintenanceCompleted(id) {
            Swal.fire({
                title: 'Mark completed?',
                text: 'Vehicle will be moved out of shop when no open service logs remain.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (!result.isConfirmed) return;
                $.post("{{ url('maintenance-log') }}/" + id + "/complete", {
                    _token: "{{ csrf_token() }}"
                }).done(function(response) {
                    maintenanceTable.ajax.reload(null, false);
                    Swal.fire('Success', response.message, 'success');
                }).fail(function() {
                    Swal.fire('Error', 'Unable to mark maintenance completed.', 'error');
                });
            });
        }

        function deleteMaintenanceLog(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to delete this maintenance log.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (!result.isConfirmed) return;
                $.get("{{ url('maintenance-log/delete') }}/" + id, function(response) {
                    maintenanceTable.ajax.reload(null, false);
                    Swal.fire('Success', response.message, 'success');
                }).fail(function() {
                    Swal.fire('Error', 'Unable to delete maintenance log.', 'error');
                });
            });
        }
    </script>
@endsection

