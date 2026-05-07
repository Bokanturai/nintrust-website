@extends('layouts.dashboard')

@section('title', 'Add New Scratch Card')
@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <style>
        .form-check .form-check-input {
            margin-left: 0;
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="mb-3 mt-1">
            <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
        </div>
         <div class="col-md-12 text-md-end text-end mt-2 mb-2 mt-md-0">
    </div>

        <div class="col-lg-12 grid-margin d-flex flex-column">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card ">
                        <div class="card-header">
                            <h5 class="card-title">Add New Scratch Card</h5>
                        </div>
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {!! session('success') !!}
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="col-xl-12 mb-3">
                                <div class="row">
<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.scratch_cards.store') }}" method="POST">
        @csrf

        {{-- Type --}}
        <div class="mb-3">
            <label for="type" class="form-label">Card Type</label>
            <input type="text" name="type" id="type" class="form-control" value="{{ old('type') }}" required>
        </div>

        {{-- Fee --}}
        <div class="mb-3">
            <label for="fee" class="form-label">Fee / Value</label>
            <input type="number" name="fee" id="fee" step="0.01" class="form-control" value="{{ old('fee') }}" required>
        </div>

        {{-- Serial Number --}}
        <div class="mb-3">
            <label for="serial_number" class="form-label">Serial Number</label>
            <input type="text" name="serial_number" id="serial_number" class="form-control" value="{{ old('serial_number') }}" required>
        </div>

        {{-- PIN --}}
        <div class="mb-3">
            <label for="pin" class="form-label">PIN</label>
            <input type="text" name="pin" id="pin" class="form-control" value="{{ old('pin') }}" required>
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn btn-primary">Add Scratch Card</button>
        <a href="{{ route('admin.scratch_cards.index') }}" class="btn btn-secondary">Back to List</a>
    </form>
</div>


                            </div>

                        </div>
                    </div>
                </div>



            </div>
        </div>
    </div>
@endsection


@push('scripts')


@endpush
