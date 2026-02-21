@extends('layouts.app')
@section('title', 'Roles')
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
        <h5 class="card-title m-0 me-2 text-secondary">Role</h5>
        <a href="{{ route('role.create') }}" class="btn btn-primary waves-effect waves-light addButton">Add Role</a>
    </div>

    <div class="card-body">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table table-striped" id="role_table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Action</th>
                        <th>Name</th>
                        <th>Guard</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function () {
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

    $('#role_table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        buttons: moduleTableButtons,
        ajax: "{{ route('role.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'guard_name', name: 'guard_name' }
        ]
    });
});

function deleteRole(id, name) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You want to delete ' + name + '.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (!result.isConfirmed) return;

        $.get("{{ url('role/delete') }}/" + id, function (data) {
            if (data.status) {
                $('#role_table').DataTable().ajax.reload();
                Swal.fire('Deleted', data.message, 'success');
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        });
    });
}
</script>
@endsection

