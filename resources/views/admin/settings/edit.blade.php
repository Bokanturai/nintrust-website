@extends('layouts.dashboard')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4">Site Settings</h1>

        {{-- Success message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Error messages --}}
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>There were some problems with your input:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('admin.site-settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Enable Home Page --}}
            <div class="form-check form-switch mb-3">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="home_enabled"
                    id="home_enabled"
                    value="1"
                    {{ old('home_enabled', $settings->home_enabled ?? false) ? 'checked' : '' }}
                >
                <label class="form-check-label" for="home_enabled">
                    Enable Home Page
                </label>
            </div>

            {{-- Enable Login Page --}}
           {{-- Enable Login Page --}}
<div class="form-check form-switch mb-3">
    <input
        class="form-check-input"
        type="checkbox"
        name="login_enabled"
        id="login_enabled"
        value="1"
        {{ old('login_enabled', $settings->login_enabled ?? false) ? 'checked' : '' }}
    >
    <label class="form-check-label" for="login_enabled">
        Enable Login Page
    </label>

    {{-- Description: Admin access even if login is disabled --}}
    <small class="text-muted d-block mt-1">
        If disabled, normal users cannot access the login page. Admins can still log in via:
        <code>{{ url('auth/login?admin=1') }}</code>
    </small>
</div>



            <div class="form-check form-switch mb-4">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="register_enabled"
                    id="register_enabled"
                    value="1"
                    {{ old('register_enabled', $settings->register_enabled ?? false) ? 'checked' : '' }}
                >
                <label class="form-check-label" for="register_enabled">
                    Enable Register Page
                </label>
            </div>

            <button type="submit" class="btn btn-primary w-100">Save Settings</button>
        </form>
    </div>
</div>
@endsection
