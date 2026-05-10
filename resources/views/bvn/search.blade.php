@extends('layouts.dashboard')

@section('title', 'BVN Search API')

@section('content')
<div class="page-title mb-4">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <h3 class="fw-bold text-primary">BVN Search Request</h3>
            <p class="text-muted small mb-0">Submit a phone number to search for associated BVN details.</p>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row g-4">
        <!-- Submission Form -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold text-white"><i class="bi bi-search me-2"></i>New Search Request</h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info border-0 shadow-sm small mb-4">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        A service fee of <strong>₦{{ number_format($bvnService->amount ?? 500, 2) }}</strong> will be deducted for each submission.
                        <hr class="my-2 opacity-25">
                        <span class="fw-bold"><i class="bi bi-clock-history me-1"></i> Processing Time:</span> This request can take up to 28h to 48h working days.
                    </div>

                    <form action="{{ route('user.bvn-search.store') }}" method="POST" id="bvnSearchForm">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase tracking-wider">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="phone_number" class="form-control border-start-0 ps-0 @error('phone_number') is-invalid @enderror" 
                                       placeholder="e.g. 08012345678" value="{{ old('phone_number') }}" required maxlength="11">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm transition-all hover-lift">
                            <i class="bi bi-send-fill me-2"></i> Submit Request
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Submission History -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Requests</h5>
                    <form action="{{ route('user.verify-bvn2') }}" method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Search phone..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0 small text-uppercase fw-bold text-muted">Date</th>
                                    <th class="border-0 small text-uppercase fw-bold text-muted">Phone Number</th>
                                    <th class="border-0 small text-uppercase fw-bold text-muted">Reference</th>
                                    <th class="border-0 small text-uppercase fw-bold text-muted text-center">Status</th>
                                    <th class="pe-4 border-0 small text-uppercase fw-bold text-muted text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($submissions as $submission)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="fw-bold text-dark">{{ $submission->created_at->format('d M, Y') }}</div>
                                            <div class="text-muted small">{{ $submission->created_at->format('h:i A') }}</div>
                                        </td>
                                        <td><span class="fw-bold">{{ $submission->phone_number }}</span></td>
                                        <td><code class="text-primary">{{ $submission->reference }}</code></td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill bg-{{ match($submission->status) {
                                                'successful', 'resolved' => 'success',
                                                'processing', 'pending'    => 'primary',
                                                'failed', 'rejected'       => 'danger',
                                                'query'                    => 'info',
                                                default                    => 'warning'
                                            } }} px-3">
                                                {{ ucfirst($submission->status) }}
                                            </span>
                                        </td>
                                        <td class="pe-4 text-end">
                                            @php
                                                $fileUrl = null;
                                                if ($submission->file_url) {
                                                    if (filter_var($submission->file_url, FILTER_VALIDATE_URL)) {
                                                        $fileUrl = $submission->file_url;
                                                    } else {
                                                        $fileUrl = \Illuminate\Support\Facades\Storage::url($submission->file_url);
                                                    }
                                                }
                                            @endphp
                                            <div class="d-flex gap-2 justify-content-end">
                                                <button type="button"
                                                        class="btn btn-sm btn-icon btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#commentModal"
                                                        data-comment="{{ $submission->comment ?? 'No response message yet.' }}"
                                                        data-file-url="{{ $fileUrl }}">
                                                    <i class="bi bi-eye-fill"></i>
                                                </button>

                                                @if(in_array($submission->status, ['pending', 'processing', 'query']))
                                                    <button type="button" 
                                                            class="btn btn-sm btn-icon btn-outline-info btn-check-status" 
                                                            data-id="{{ $submission->id }}"
                                                            data-url="{{ route('user.bvn-search.check', $submission->id) }}"
                                                            title="Check Status">
                                                        <i class="bi bi-arrow-clockwise"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                            No search requests found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($submissions->hasPages())
                    <div class="card-footer bg-white py-3">
                        {{ $submissions->links('vendor.pagination.custom') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Comment Modal -->
<div class="modal fade" id="commentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold text-white">Request Response</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4" style="max-width: 100%;">
                    <label class="form-label fw-bold text-muted small text-uppercase">API Comment / Response</label>
                    <div id="modalComment" class="bg-light p-3 rounded-3 border text-dark" 
                         style="min-height: 100px; 
                                width: 100%; 
                                word-wrap: break-word; 
                                overflow-wrap: break-word; 
                                word-break: break-word; 
                                white-space: normal;"></div>
                </div>
                <div id="modalFileContainer" class="d-none">
                    <label class="form-label fw-bold text-muted small text-uppercase">Result File</label>
                    <a id="modalFileUrl" href="#" target="_blank" class="btn btn-outline-primary w-100 py-3 fw-bold">
                        <i class="bi bi-download me-2"></i> Download Result File
                    </a>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Comment Modal logic
    $('#commentModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var comment = button.data('comment');
        var fileUrl = button.data('file-url');
        
        var modal = $(this);
        modal.find('#modalComment').text(comment);
        
        if(fileUrl) {
            modal.find('#modalFileUrl').attr('href', fileUrl);
            modal.find('#modalFileContainer').removeClass('d-none');
        } else {
            modal.find('#modalFileContainer').addClass('d-none');
        }
    });

    // Check Status AJAX
    $('.btn-check-status').on('click', function() {
        const btn = $(this);
        const url = btn.data('url');
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        
        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                console.log('Status Check Response:', response);
                if(response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else if(response.status === 'info') {
                    Swal.fire('Info', response.message, 'info');
                    btn.prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i>');
                } else {
                    Swal.fire('Error', response.message, 'error');
                    btn.prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i>');
                }
            },
            error: function(xhr) {
                console.error('Status Check Error:', xhr);
                let msg = 'Failed to check status. Please try again.';
                if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                Swal.fire('Error', msg, 'error');
                btn.prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i>');
            }
        });
    });

    // Handle form submission
    $('#bvnSearchForm').on('submit', function() {
        Swal.fire({
            title: 'Submitting...',
            text: 'Please wait while we process your search request.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
    });
});
</script>
@endpush
@endsection
