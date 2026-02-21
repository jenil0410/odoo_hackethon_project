@extends('layouts.app')
@section('title', 'View Role')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center py-3">
        <h5 class="m-0 text-white">Role Details</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('role.index') }}" class="btn btn-outline-secondary">Back</a>
            <a href="{{ route('role.edit', $role->id) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>

    <div class="card-body">
        <div class="mb-4">
            <label class="text-muted">Name</label>
            <div>{{ $role->name }}</div>
        </div>

        <h6 class="text-primary mb-3">Module Permissions</h6>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Create</th>
                        <th>Read</th>
                        <th>Update</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($accessData as $module)
                    @php $perm = $permissionData[$module] ?? null; @endphp
                    <tr>
                        <td>{{ $module }}</td>
                        <td>{{ !empty($perm) && $perm->create ? 'Yes' : 'No' }}</td>
                        <td>{{ !empty($perm) && $perm->read ? 'Yes' : 'No' }}</td>
                        <td>{{ !empty($perm) && $perm->update ? 'Yes' : 'No' }}</td>
                        <td>{{ !empty($perm) && $perm->delete ? 'Yes' : 'No' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection


