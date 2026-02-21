@extends('layouts.guest')

@section('content')
<div class="card p-4 text-center">
    <h2>🎉 Thank You!</h2>
    <p>{{ $message ?? 'Your password reset is successful.' }}</p>
    {{-- <a href="{{ route('login') }}" class="btn btn-primary mt-3">Go to Login</a> --}}
</div>
@endsection
