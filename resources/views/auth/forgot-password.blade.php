@extends('layouts.auth')
@section('title', 'Forget Password')
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
                        <h4 class="fw-bold">Forget Password</h4>
                        <h6 class="fw-light text-muted">Enter your email to receive a reset link</h6>
                    </div>

                    @include('common.message')

                    <form method="POST" action="{{ route('auth.password.email') }}" class="pt-3">
                        @csrf

                        <div class="form-group mb-4">
                            <label for="email" class="form-label font-weight-bold">E-Mail Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-right-0">
                                    <i class="mdi mdi-email-outline"></i>
                                </span>
                                <input id="email" type="email"
                                    class="form-control form-control-lg border-left-0 @error('email') is-invalid @enderror" 
                                    name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                                    placeholder="your@email.com">

                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg fw-medium auth-form-btn py-3">
                                SEND RESET LINK
                            </button>
                            <a href="{{ route('auth.login') }}" class="btn btn-light btn-lg border mt-2">
                                BACK TO LOGIN
                            </a>
                        </div>
                    </form>
                </div>
                <p class="text-muted mt-4 text-center">Copyright &copy; {{ date('Y') }} {{ $settings->site_name }}. All rights reserved.</p>
            </div>
        </div>
    </div>
@endsection
