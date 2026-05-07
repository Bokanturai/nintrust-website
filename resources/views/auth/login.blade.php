@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="content-wrapper d-flex align-items-center auth px-0">
        <div class="row w-100 mx-0">
            <div class="col-lg-4 mx-auto">
                <div class="auth-form-light text-start py-5 px-4 px-sm-5 border rounded shadow-sm bg-white">
                    <div class="brand-logo text-center mb-4">
                        <a href="{{ url('/') }}">
                            <img src="{{ asset('assets/images/' . $settings->logo) }}" alt="logo" style="width: 150px;">
                        </a>
                    </div>
                    <div class="text-center mb-4">
                        <h4 class="fw-bold">Welcome back!</h4>
                        <h6 class="fw-light text-muted">Happy to see you again!</h6>
                    </div>

                    @include('common.message')

                    <form method="POST" action="{{ route('auth.login') }}" class="pt-3 needs-validation" novalidate>
                        @csrf

                        <!-- Username -->
                        <div class="form-group mb-3">
                            <label for="email" class="form-label font-weight-bold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-right-0">
                                    <i class="mdi mdi-account-outline"></i>
                                </span>
                                <input type="text"
                                    class="form-control form-control-lg border-left-0 @error('email') is-invalid @enderror"
                                    id="email" name="email" placeholder="Username" value="{{ old('email') }}"
                                    required autofocus>
                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="form-group mb-3">
                            <label for="password" class="form-label font-weight-bold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-right-0">
                                    <i class="mdi mdi-lock-outline"></i>
                                </span>
                                <input type="password"
                                    class="form-control form-control-lg border-left-0 border-right-0 @error('password') is-invalid @enderror"
                                    id="password" name="password" placeholder="Password" required>
                                <span class="input-group-text bg-transparent border-left-0" onclick="togglePassword('password', 'toggleIcon')" style="cursor: pointer;">
                                    <i class="mdi mdi-eye-outline" id="toggleIcon"></i>
                                </span>
                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="my-2 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <label class="form-check-label text-muted">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember"
                                        {{ old('remember') ? 'checked' : '' }}>
                                    Keep me signed in
                                </label>
                            </div>
                            <a href="{{ route('auth.password.request') }}" class="auth-link text-black">Forgot password?</a>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="my-3 d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg fw-medium auth-form-btn py-3">
                                SIGN IN
                            </button>
                        </div>

                        <div class="text-center mt-4 fw-light">
                            Don't have an account? <a href="{{ route('auth.register') }}" class="text-primary font-weight-bold">Create</a>
                        </div>
                    </form>
                </div>
                <p class="text-muted mt-4 text-center">Copyright &copy; {{ date('Y') }} {{ $settings->site_name }}. All rights reserved.</p>
            </div>
        </div>
    </div>

@endsection
