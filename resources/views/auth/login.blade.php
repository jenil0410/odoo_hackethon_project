@extends('layouts.guest')
@section('styles')
@endsection
@section('content')
    <!-- Login -->
    <div class="card p-2 mt-4">
        <!-- Logo -->
        <div class="app-brand justify-content-center mt-2">
            {{-- <a href="/" class="app-brand-link gap-2">
                <span class="app-brand-text demo text-heading fw-bold">SBGVSSPPY</span>
            </a> --}}
            {{-- <h3> <b>SBGVSSPPY</b> </h3> --}}
            {{-- <a href="/" class="d-flex justify-content-center" style="display: flex; justify-content: center;">
                <img src="{{ asset('images/header-logo.svg') }}" class="main-logo" alt="">
            </a> --}}
        </div>
        {{-- <p class="text-center pt-4 m-0">
            Enter your email address and password to access admin panel.
        </p> --}}
        <!-- /Logo -->

        <div class="card-body">
            {{--  <h4 class="mb-2">Welcome to Materialize! 👋</h4>
            <p class="mb-4">Please sign-in to your account and start the adventure</p> --}}

            <form id="formAuthentication" class="mb-3" method="POST" action="{{ route('login') }}">
                @csrf
                @error('email')
                    <p class="red-text ml-10 d-block text-start" role="alert">
                        {{ $message }}
                    </p>
                @enderror
                <div class="form-floating form-floating-outline mb-3 text-start">
                    <input autocomplete="off" type="text" class="form-control" id="email" name="email"
                        placeholder="Enter your email" autofocus value="{{ old('email', request()->cookie('email')) }}" />
                    <label for="email">Email</label>
                    {{-- <x-input-error :messages="$errors->get('email')" class="mt-2" style="color: red;" /> --}}
                </div>
                <div class="mb-3 text-start ">
                    <div class="form-password-toggle">
                        <div class="input-group input-group-merge">
                            <div class="form-floating form-floating-outline">
                                <input autocomplete="off" type="password" id="password" class="form-control"
                                    name="password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="password" value="{{ request()->cookie('password') }}" />
                                <label for="password">Password</label>
                            </div>
                            <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
                        </div>
                    </div>
                </div>
                <div class="mb-3 d-flex justify-content-between">
                    <!-- <div class="form-check">
                        {{-- <input autocomplete="off" class="form-check-input" type="checkbox" id="remember-me" name="remember"
                            {{ old('remember', request()->cookie('remember')) ? 'checked' : '' }} /> --}}
                        <input class="form-check-input" type="checkbox" id="remember-me" name="remember"
                            {{ old('remember') ? 'checked' : '' }} />

                        <label class="form-check-label" for="remember-me"> Remember Me </label>
                    </div> -->
                    <a href="{{ route('password.request') }}" class="float-end mb-1">
                        <span>Forgot Password?</span>
                    </a>
                </div>
                <div class="mb-3">
                    <button class="btn btn-primary d-grid w-100 addButton" type="submit">Sign in</button>
                </div>
            </form>

            {{-- <p class="text-center">
                <span>New on our platform?</span>
                <a href="auth-register-basic.html">
                    <span>Create an account</span>
                </a>
            </p>

            <div class="divider my-4">
                <div class="divider-text">or</div>
            </div>

            <div class="d-flex justify-content-center gap-2">
                <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-facebook">
                    <i class="tf-icons mdi mdi-24px mdi-facebook"></i>
                </a>

                <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-twitter">
                    <i class="tf-icons mdi mdi-24px mdi-twitter"></i>
                </a>

                <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-github">
                    <i class="tf-icons mdi mdi-24px mdi-github"></i>
                </a>

                <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-google-plus">
                    <i class="tf-icons mdi mdi-24px mdi-google"></i>
                </a>
            </div> --}}
        </div>
    </div>
    <!-- /Login -->
    {{-- <img alt="mask" src="{{ asset('images/bg.png') }}"
        class="authentication-image d-none d-lg-block" --}} {{-- data-app-light-img="{{ asset('assets/img/illustrations/auth-basic-login-mask-light.png') }}"
        data-app-dark-img="{{ asset('assets/img/illustrations/auth-basic-login-mask-dark.png') }}" --}} {{-- /> --}}
@endsection
@section('scripts')
@endsection
