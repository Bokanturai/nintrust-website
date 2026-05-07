@extends('layouts.auth')

@section('title', 'Register')
@push('styles')
    <style>
        .form-check {
            display: flex;
        }

        .form-check .form-check-label {
            margin-left: 0.85rem;
        }
    </style>
@endpush

@section('content')
    <div class="content-wrapper d-flex align-items-center auth px-0">
        <div class="row w-100 mx-0">
            <div class="col-lg-5 mx-auto">
                <div class="auth-form-light text-start py-5 px-4 px-sm-5 border rounded shadow-sm bg-white">
                    <div class="brand-logo text-center mb-4">
                        <a href="{{ url('/') }}">
                            <img src="{{ asset('assets/images/' . $settings->logo) }}" alt="logo" style="width: 150px;">
                        </a>
                    </div>
                    <div class="text-center mb-4">
                        <h4 class="fw-bold">New here?</h4>
                        <h6 class="fw-light text-muted">Join us today! It takes only a few steps</h6>
                    </div>

                    @include('common.message')

                    <form method="POST" action="{{ route('auth.register') }}" class="pt-3 needs-validation" novalidate>
                        @csrf

                        <!-- Email -->
                        <div class="form-group mb-3">
                            <label for="email" class="form-label font-weight-bold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-right-0">
                                    <i class="mdi mdi-email-outline"></i>
                                </span>
                                <input type="email"
                                    class="form-control form-control-lg border-left-0 @error('email') is-invalid @enderror"
                                    id="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Referral Code -->
                        <div class="form-group mb-3">
                            <label for="referral_code" class="form-label font-weight-bold">Referral Code (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-right-0">
                                    <i class="mdi mdi-tag-outline"></i>
                                </span>
                                <input type="text"
                                    class="form-control form-control-lg border-left-0 @error('referral_code') is-invalid @enderror"
                                    id="referral_code" name="referral_code" maxlength="6"
                                    placeholder="Referral Code (if any)" value="{{ old('referral_code') }}">
                                @error('referral_code')
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
                                <span class="input-group-text bg-transparent border-left-0" onclick="togglePassword('password', 'regIcon')" style="cursor: pointer;">
                                    <i class="mdi mdi-eye-outline" id="regIcon"></i>
                                </span>
                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="form-group mb-3">
                            <label for="password_confirmation" class="form-label font-weight-bold">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-right-0">
                                    <i class="mdi mdi-lock-outline"></i>
                                </span>
                                <input type="password" class="form-control form-control-lg border-left-0 border-right-0"
                                    id="password_confirmation" name="password_confirmation" placeholder="Confirm Password"
                                    required>
                                <span class="input-group-text bg-transparent border-left-0" onclick="togglePassword('password_confirmation', 'confIcon')" style="cursor: pointer;">
                                    <i class="mdi mdi-eye-outline" id="confIcon"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check d-flex align-items-center">
                                <input type="checkbox" name="terms" id="terms" value="1"
                                    class="form-check-input me-2 @error('terms') is-invalid @enderror" {{ old('terms') ? 'checked' : '' }} style="margin-top: 0;">
                                <label class="form-check-label text-muted mb-0" for="terms">
                                    I agree to all <a href="#" class="text-primary font-weight-bold">Terms & Conditions</a>
                                </label>
                            </div>
                            @error('terms')
                                <div class="text-danger small mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-3 d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg fw-medium auth-form-btn py-3">
                                CREATE ACCOUNT
                            </button>
                        </div>

                        <div class="text-center mt-4 fw-light">
                            Already have an account? <a href="{{ route('auth.login') }}" class="text-primary font-weight-bold">Login</a>
                        </div>
                    </form>
                </div>
                <p class="text-muted mt-4 text-center">Copyright &copy; {{ date('Y') }} {{ $settings->site_name }}. All rights reserved.</p>
            </div>
        </div>
    </div>
@endsection
