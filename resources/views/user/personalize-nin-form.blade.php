@extends('layouts.dashboard')

@section('title', 'Personalize NIN Request')

@section('content')
    <div class="row">

        <div class="col-12 mb-3 mt-1">
            <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center my-3">
                        <h4 class="fw-bold mb-0">Personalize NIN Request</h4>
                        <img src="{{ asset('assets/images/img/nimc.png') }}"
                             alt="NIMC Logo"
                             height="60">
                    </div>

                    <div class="alert alert-info">
                        <h5 class="mb-1">
                            Processing Fee:
                            <span>₦{{ number_format($fee, 2) }}</span>
                        </h5>
                        <small>This covers the submission and processing of Personalize NIN requests.</small>
                    </div>

                    <ul class="nav nav-tabs mb-3"
                        id="ninTabs"
                        role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active"
                                    id="form-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#formTab"
                                    type="button"
                                    role="tab">Submissions</button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link"
                                    id="history-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#historyTab"
                                    type="button"
                                    role="tab">Submission History</button>
                        </li>
                    </ul>

                    <div class="tab-content"
                         id="ninTabsContent">

                        <div class="tab-pane fade show active"
                             id="formTab"
                             role="tabpanel">

                            <form method="POST"
                                  action="{{ route('user.personalize-nin.store') }}"
                                  enctype="multipart/form-data">
                                @csrf

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Enter your Tracking ID <span
                                                  class="text-danger">*</span></label>
                                        <input type="text"
                                               name="tracking_id"
                                               required
                                               class="form-control"
                                               value="{{ old('tracking_id') }}"
                                               placeholder="Enter your tracking ID">
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit"
                                            class="btn btn-primary px-5">Submit Request</button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade"
                             id="historyTab"
                             role="tabpanel">
                            <h5 class="mb-3 mt-3">Submission History</h5>

                            @if ($records->count())
                                <div class="table-responsive">
                                    <table class="table-bordered table-hover table">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Reference</th>
                                                <th>Tracking ID</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($records as $rec)
                                                <tr>
                                                    <td>{{ $rec->created_at->format('d M, Y') }}</td>
                                                    <td>{{ strtoupper($rec->refno) }}</td>
                                                    <td>{{ $rec->tracking_id }}</td>
                                                    <td>
                                                        @if ($rec->status == 'submitted')
                                                            <span class="badge bg-warning">Submitted</span>
                                                        @elseif($rec->status == 'processing')
                                                            <span class="badge bg-primary">Processing</span>
                                                        @elseif($rec->status == 'successful')
                                                            <span class="badge bg-success">Successful</span>
                                                        @else
                                                            <span class="badge bg-danger">Rejected</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-info text-white"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewModal{{ $rec->id }}">
                                                            View
                                                        </button>
                                                    </td>
                                                </tr>

                                                {{-- Modal --}}
                                                <div class="modal fade"
                                                     id="viewModal{{ $rec->id }}"
                                                     tabindex="-1"
                                                     aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-scrollable modal-md">
                                                        <div class="modal-content shadow-lg">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title">
                                                                    <i class="bi bi-file-text-fill me-2"></i> Request
                                                                    Details
                                                                </h5>
                                                                <button type="button"
                                                                        class="btn-close btn-close-white"
                                                                        data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="bg-light mb-3 rounded border p-3">
                                                                    <h6 class="fw-bold text-dark">
                                                                        <i class="bi bi-person-fill me-1"></i> Submitted Information
                                                                    </h6>
                                                                    <div class="row">
                                                                        <div class="col-md-12"><strong>Tracking ID:</strong>
                                                                            {{ $rec->tracking_id }}</div>
                                                                    </div>
                                                                </div>

                                                                <div class="bg-light rounded border p-3">
                                                                    <h6 class="fw-bold text-dark">
                                                                        <i class="bi bi-info-circle-fill me-1"></i> Status
                                                                        & Admin Comment
                                                                    </h6>
                                                                    <p class="mb-2">
                                                                        <strong>Status:</strong>
                                                                        @if ($rec->status == 'submitted')
                                                                            <span
                                                                                  class="badge bg-warning text-dark">Submitted</span>
                                                                        @elseif($rec->status == 'processing')
                                                                            <span
                                                                                  class="badge bg-primary">Processing</span>
                                                                        @elseif($rec->status == 'successful')
                                                                            <span
                                                                                  class="badge bg-success">Successful</span>
                                                                        @else
                                                                            <span class="badge bg-danger">Rejected</span>
                                                                        @endif
                                                                    </p>
                                                                    <p class="mb-0"><strong>Comment:</strong></p>
                                                                    <div class="text-muted small mt-1">
                                                                        {!! $rec->reason ?? 'No comment yet.' !!}
                                                                    </div>
                                                                </div>
                                                                @if ($rec->result_file)
                                                                    <div
                                                                         class="border-success mb-1 mt-2 rounded border bg-white p-3">

                                                                        <a href="{{ asset($rec->result_file) }}"
                                                                           target="_blank"
                                                                           class="btn btn-sm btn-success w-100 fw-bold">DOWNLOAD
                                                                            RESULT</a>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button"
                                                                        class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">
                                                                    <i class="bi bi-x-circle me-1"></i> Close
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">{{ $records->links() }}</div>
                            @else
                                <p class="text-muted">No submissions found.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
