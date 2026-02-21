@extends('layouts.guest')

@section('styles')
@endsection

@section('content')
    <!-- Forgot Password -->
    <div class="card p-2 mt-4">
        <!-- Logo -->
        <div class="app-brand justify-content-center mt-2">
        </div>

        <p class="text-center pt-4 m-0">
            Forgot your password? No problem. Just provide your email address and we will email you a password reset link.
        </p>

        <!-- Session Status -->
        @if (session('status'))
            <div class="alert alert-success text-center mt-3">
                {{ session('status') }}
            </div>
        @endif

        <div class="card-body mt-2">
            <form id="formForgotPassword" class="mb-3" method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email Address -->
                <div class="form-floating form-floating-outline mb-3 text-start">
                    <input autocomplete="off" type="email" class="form-control" id="email" name="email" placeholder="Enter your email"
                        autofocus value="{{ old('email') }}" />
                    <label for="email">Email</label>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" style="color: red;" />
                </div>

                <div class="mb-3">
                    <button class="btn btn-primary d-grid w-100" type="submit">Email Password Reset Link</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
