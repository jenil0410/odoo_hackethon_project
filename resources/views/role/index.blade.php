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
    </style>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between py-2 module-card-header">
        <h5 class="card-title m-0 me-2 text-secondary">Role</h5>
        <a href="{{ route('role.create') }}" class="btn btn-primary waves-effect waves-light addButton">Add Role</a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped" id="role_table">
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
    $('#role_table').DataTable({
        processing: true,
        serverSide: true,
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
        confirmButtonText: 'Yes, delete it'
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
