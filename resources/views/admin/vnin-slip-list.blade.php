@extends('layouts.dashboard')

@section('title', 'VNIN Slip List')

@section('content')
    <div class="row">
        <div class="mb-3 mt-1">
            <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
        </div>
        <div class="col-lg-12 grid-margin d-flex flex-column">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card ">
                        <div class="card-header">
                            <h5 class="card-title">VNIN Slip Requests</h5>
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

                            <div class="col-xl-12 mb-3">
                                <div class="row">
                                    <div class="col-xxl-3 col-lg-3 col-md-3">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div class="flex-fill">
                                                        <p class="text-muted mb-0">Total</p>
                                                        <h4 class="fw-semibold mt-1">{{ $total_request }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-lg-3 col-md-3">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div class="flex-fill">
                                                        <p class="text-muted mb-0 text-warning">Pending</p>
                                                        <h4 class="fw-semibold mt-1 text-warning">
                                                            {{ $pending + $processing }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xxl-3 col-lg-3 col-md-3">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div class="flex-fill">
                                                        <p class="text-muted mb-0 text-success">Resolved</p>
                                                        <h4 class="fw-semibold mt-1 text-success">{{ $resolved }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-lg-3 col-md-3">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div class="flex-fill">
                                                        <p class="text-muted mb-0 text-danger">Rejected</p>
                                                        <h4 class="fw-semibold mt-1 text-danger">{{ $rejected }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form method="GET" action="{{ route('admin.vnin-slip.index') }}"
                                class="row g-2 mb-3 align-items-end">
                                <div class="col-md-3">
                                    <input type="text" name="search" class="form-control"
                                        value="{{ request('search') }}" placeholder="Search Reference, Name, NIN...">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="date_from" class="form-control"
                                        value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="date_to" class="form-control"
                                        value="{{ request('date_to') }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table text-nowrap" style="background:#fafafc !important">
                                    <thead>
                                        <tr>
                                            <th>SN</th>
                                            <th>Date</th>
                                            <th>Reference</th>
                                            <th>NIN</th>
                                            <th>User</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($records as $row)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $row->created_at->format('d/m/Y') }}</td>
                                                <td>{{ strtoupper($row->refno) }}</td>
                                                <td>{{ $row->nin }}</td>
                                                <td>{{ optional($row->user)->name }}</td>
                                                <td>
                                                    @if ($row->status == 'submitted')
                                                        <span class="badge bg-warning">Submitted</span>
                                                    @elseif($row->status == 'processing')
                                                        <span class="badge bg-primary">Processing</span>
                                                    @elseif($row->status == 'successful')
                                                        <span class="badge bg-success">Successful</span>
                                                    @else
                                                        <span class="badge bg-danger">Rejected</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.vnin-slip.view', [$row->id, $request_type]) }}"
                                                        class="btn btn-primary btn-sm">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-center mt-3">
                                {{ $records->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
