@extends('layouts.app')
@section('title', 'Update Role')

@section('content')
<div class="card mb-4">
    <div class="card-header py-3">
        <h5 class="m-0 text-white">Update Role</h5>
    </div>

    <form action="{{ route('role.update', $role->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-floating form-floating-outline mb-4">
                <input type="text" class="form-control" name="name" value="{{ old('name', $role->name) }}" required>
                <label>Role Name</label>
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
                            <td><input type="checkbox" name="permission[{{ $module }}][create]" {{ !empty($perm) && $perm->create ? 'checked' : '' }}></td>
                            <td><input type="checkbox" name="permission[{{ $module }}][read]" {{ !empty($perm) && $perm->read ? 'checked' : '' }}></td>
                            <td><input type="checkbox" name="permission[{{ $module }}][update]" {{ !empty($perm) && $perm->update ? 'checked' : '' }}></td>
                            <td><input type="checkbox" name="permission[{{ $module }}][delete]" {{ !empty($perm) && $perm->delete ? 'checked' : '' }}></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer text-end">
            <a href="{{ route('role.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update</button>
        </div>
    </form>
</div>
@endsection


