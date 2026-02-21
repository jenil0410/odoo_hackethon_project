@extends('layouts.guest')
@section('styles')
@endsection
@section('content')
    <!-- Login -->
    <div class="card p-2 mt-4">
        <!-- Logo -->
        <div class="app-brand justify-content-center mt-2">
        </div>

        <p class="text-center pt-4 m-0">
            Reset your password. Please provide your email and set a new password to regain access to your account.
        </p>
        <div class="card-body mt-2">

            <form id="formAuthentication" class="mb-3" method="POST" action="{{ route('password.store') }}">
                @csrf
                <!-- Password Reset Token -->
                 <input autocomplete="off" type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div class="form-floating form-floating-outline mb-3 text-start">
                     <input autocomplete="off" type="text" class="form-control" id="email" name="email" placeholder="Enter your email"
                        autofocus value="{{ old('email', $request->email) }}" />
                    <label for="email">Email</label>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" style="color: red;" />
                </div>

                <!-- Password -->
                <div class="mb-3 text-start ">
                    <div class="form-password-toggle">
                        <div class="input-group input-group-merge">
                            <div class="form-floating form-floating-outline">
                                 <input autocomplete="off" type="password" id="password" class="form-control" name="password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="password" />
                                <label for="password">Password</label>
                            </div>
                            <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" style="color: red;" />
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="mb-3 text-start ">
                    <div class="form-password-toggle">
                        <div class="input-group input-group-merge">
                            <div class="form-floating form-floating-outline">
                                 <input autocomplete="off" type="password" id="password_confirmation" class="form-control"
                                    name="password_confirmation"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="password" />
                                <label for="password_confirmation">Confirm Password</label>
                            </div>
                            <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" style="color: red;" />
                    </div>
                </div>

                <div class="mb-3">
                    <button class="btn btn-primary d-grid w-100" type="submit">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
@endsection
