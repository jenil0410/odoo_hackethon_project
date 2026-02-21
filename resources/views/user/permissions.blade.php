@extends('layouts.app')
@section('title', 'Update Role')

@section('styles')
    <style>
        @media screen and (max-width: 425px) {
            .table-responsive {
                overflow-x: scroll !important;
            }
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 page-header-title text-white">User ({{ $user->name }}) Permission Update</h5>
                </div>
                <form method="POST" enctype="multipart/form-data" action="{{ route('user.permissions-update', $user->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <input autocomplete="off" type="hidden" name="id" value="{{ $role?->id }}">
                            <input autocomplete="off" type="hidden" name="user_id" value="{{ $user->id }}">

                            <div class="form-floating form-floating-outline mb-4">
                                <input autocomplete="off" type="text" class="form-control" name="name" id="name"
                                    value="{{ $role?->name }}" placeholder="name" required readonly/>
                                <label for="name">Role</label>
                                @error('name')
                                    <small class="red-text ml-10" role="alert">
                                        {{ $message }}
                                    </small>
                                @enderror
                            </div>
                            <div class="input-field col-sm-12">
                                <div class="card">
                                    <h5 class="card-header text-white py-3">Permission Table</h5>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Module Permission</th>
                                                    <th>Create</th>
                                                    <th>Read</th>
                                                    <th>Update</th>
                                                    <th>Delete</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($accessData as $key => $module)
                                                    <input type="hidden" name="all_modules[]" value="{{ $module }}">
                                                    @php
                                                        $rolePerm = $rolePermissionData[$module] ?? null;
                                                        $userPerm = $userPermissionData[$module] ?? null;
                                                        $actions = ['create', 'read', 'update', 'delete'];
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $module }}</td>
                                                        @foreach ($actions as $action)
                                                            <td>
                                                                @php
                                                                    $isRoleBased = $rolePerm && $rolePerm->$action;
                                                                    $isUserSpecific = $userPerm && $userPerm->$action;
                                                                @endphp

                                                                @if ($isRoleBased)
                                                                    {{-- Role-based permission (displayed but not editable) --}}
                                                                    <input type="hidden"
                                                                        name="role_permission[{{ $key }}][{{ $action }}]"
                                                                        value="1" />
                                                                    <label class="form-check" title="Inherited from Role">
                                                                        <input autocomplete="off" class="form-check-input"
                                                                            type="checkbox" checked disabled />
                                                                        <span></span>
                                                                    </label>
                                                                @else
                                                                    {{-- User-specific extra permission (editable) --}}
                                                                    <label class="form-check">
                                                                        <input autocomplete="off" class="form-check-input"
                                                                            type="checkbox"
                                                                            name="permission[{{ $key }}][{{ $action }}]"
                                                                            {{ $isUserSpecific ? 'checked' : '' }} />
                                                                        <span></span>
                                                                    </label>
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end pt-0">
                        <a href="{{ route('user.index') }}"
                            class="btn btn-outline-secondary waves-effect waves-light me-1">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
@endsection


