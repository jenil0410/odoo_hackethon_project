@extends('layouts.app')
@section('title', 'View User')

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between py-3">
        <h5 class="card-title m-0 text-white">User Details</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('user.index') }}" class="btn btn-outline-secondary">Back</a>
            <a href="{{ route('user.edit', $user->id) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>

    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <label class="text-muted">First Name</label>
                <div>{{ $user->first_name }}</div>
            </div>
            <div class="col-md-6">
                <label class="text-muted">Last Name</label>
                <div>{{ $user->last_name }}</div>
            </div>
            <div class="col-md-6">
                <label class="text-muted">Email</label>
                <div>{{ $user->email }}</div>
            </div>
            <div class="col-md-6">
                <label class="text-muted">Phone Number</label>
                <div>{{ $user->phone_number ?? '-' }}</div>
            </div>
            <div class="col-md-6">
                <label class="text-muted">Date of Birth</label>
                <div>{{ $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '-' }}</div>
            </div>
            <div class="col-md-6">
                <label class="text-muted">Gender</label>
                <div>{{ $user->gender ? ucfirst($user->gender) : '-' }}</div>
            </div>
            <div class="col-md-6">
                <label class="text-muted">Role</label>
                <div>{{ optional($user->role)->name ?? '-' }}</div>
            </div>
            <div class="col-md-6">
                <label class="text-muted">Profile Photo</label>
                <div>{{ $user->profile_photo ?? '-' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
