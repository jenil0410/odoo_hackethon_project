@extends('layouts.app')
@section('title', 'Create Role')

@section('content')
<div class="card">
    <div class="card-header py-3">
        <h5 class="card-title m-0 text-white">Create Role</h5>
    </div>
    <form action="{{ route('role.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-floating form-floating-outline mb-4">
                <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                <label>Role Name</label>
                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('role.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
@endsection


