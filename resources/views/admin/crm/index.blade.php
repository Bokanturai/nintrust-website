@extends('layouts.dashboard')

@section('title', 'Admin - BVN CRM Requests')

@section('content')
<div class="page-title mb-4">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <h3 class="fw-bold text-primary">BVN CRM Management</h3>
            <p class="text-muted small mb-0">Oversee and manage all Central Risk Management requests.</p>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-white"><i class="mdi mdi-format-list-bulleted me-2"></i>All CRM Requests</h5>
                    <button type="button" class="btn btn-light btn-sm fw-bold shadow-sm" id="syncAllPending">
                        <i class="bi bi-arrow-clockwise me-1"></i> Sync All Pending (Page)
                    </button>
                </div>
                <div class="card-body p-4">
                    <!-- Filters -->
                    <form method="GET" class="row g-3 mb-4 bg-light p-3 rounded-3 border">
                        <div class="col-md-5">
                            <input class="form-control border-0 shadow-sm" name="search" type="text" placeholder="Search by Ticket ID, Batch ID, Ref, Name or Email..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select border-0 shadow-sm" name="status">
                                <option value="">All Statuses</option>
                                @foreach(['pending','processing','successful','query','resolved','rejected','remark','failed'] as $status)
                                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100 shadow-sm" type="submit">
                                <i class="bi bi-filter"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('admin.crm.index') }}" class="btn btn-outline-secondary w-100 shadow-sm">Reset</a>
                        </div>
                    </form>

                    @if(session('success'))
                        <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
                    @endif

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="crmTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Reference</th>
                                    <th>Ticket/Batch</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($submissions as $submission)
                                    <tr data-row-id="{{ $submission->id }}">
                                        <td class="small">{{ $submission->created_at->format('d M, Y H:i') }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $submission->user->name ?? 'Unknown' }}</div>
                                            <div class="small text-muted">{{ $submission->user->email ?? '' }}</div>
                                        </td>
                                        <td><code class="text-primary fw-bold">{{ $submission->reference }}</code></td>
                                        <td>
                                            <div><span class="badge bg-info-subtle text-info border border-info-subtle">T: {{ $submission->ticket_id }}</span></div>
                                            <div class="mt-1"><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">B: {{ $submission->batch_id }}</span></div>
                                        </td>
                                        <td class="status-cell">
                                            <span class="badge rounded-pill bg-{{ match($submission->status) {
                                                'resolved', 'successful' => 'success',
                                                'processing'             => 'primary',
                                                'rejected', 'failed'     => 'danger',
                                                'query'                  => 'info',
                                                default                  => 'warning'
                                            } }}">
                                                {{ ucfirst($submission->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                @if(in_array($submission->status, ['pending', 'processing', 'query']))
                                                    <button type="button" 
                                                            class="btn btn-sm btn-info text-white btn-check-status" 
                                                            data-id="{{ $submission->id }}"
                                                            data-url="{{ route('user.crm.check', $submission->id) }}"
                                                            title="Check Status">
                                                        <i class="bi bi-arrow-clockwise"></i> Check
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewModal{{ $submission->id }}">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#updateModal{{ $submission->id }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            </div>

                                            <!-- Update Modal -->
                                            <div class="modal fade" id="updateModal{{ $submission->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content border-0 shadow rounded-4 text-dark">
                                                        <div class="modal-header bg-warning text-dark">
                                                            <h5 class="modal-title fw-bold">Update Request Status</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('admin.crm.update', $submission->id) }}" method="POST" enctype="multipart/form-data">
                                                            @csrf
                                                            <div class="modal-body p-4 text-dark">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Status</label>
                                                                    <select name="status" class="form-select border shadow-sm text-dark" required>
                                                                        @foreach(['pending','processing','successful','query','resolved','rejected','failed'] as $st)
                                                                            <option value="{{ $st }}" {{ $submission->status == $st ? 'selected' : '' }}>
                                                                                {{ ucfirst($st) }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    <small class="text-danger mt-1 d-block">Note: Changing status to 'failed' or 'rejected' will trigger an automatic refund.</small>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Comment / Reason</label>
                                                                    <textarea name="comment" class="form-control border shadow-sm text-dark" rows="4" placeholder="Enter reason or response here...">{{ $submission->comment }}</textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Result File (Optional)</label>
                                                                    <input type="file" name="result_file" class="form-control border shadow-sm text-dark">
                                                                    <small class="text-muted">Allowed: PDF, JPG, PNG, ZIP (Max 5MB)</small>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer border-0">
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-warning fw-bold text-dark">Update Status</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- View Modal -->
                                            <div class="modal fade" id="viewModal{{ $submission->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content border-0 shadow rounded-4">
                                                        <div class="modal-header bg-primary text-white">
                                                            <h5 class="modal-title fw-bold text-white">CRM Request Details</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body p-4 text-dark">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Current Status</label>
                                                                <div class="alert alert-secondary py-2 mb-0">{{ ucfirst($submission->status) }}</div>
                                                            </div>
                                                            <div class="mb-3" style="max-width: 100%;">
                                                                <label class="form-label fw-bold text-dark">Response / Comment</label>
                                                                <div class="bg-light p-3 rounded-3 border text-dark" 
                                                                     style="min-height: 60px; 
                                                                            width: 100%; 
                                                                            word-wrap: break-word; 
                                                                            overflow-wrap: break-word; 
                                                                            word-break: break-word; 
                                                                            white-space: normal;">
                                                                    {{ $submission->comment ?? 'No response message yet.' }}
                                                                </div>
                                                            </div>
                                                            <div class="p-3 bg-light rounded-3 border">
                                                                <div class="small fw-bold text-muted mb-2">Request Info:</div>
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <span>User:</span> <span class="fw-bold">{{ $submission->user->name }}</span>
                                                                </div>
                                                                <div class="d-flex justify-content-between mb-1 text-break" style="overflow-wrap: break-word;">
                                                                    <span>Reference:</span> <span class="fw-bold text-primary">{{ $submission->reference }}</span>
                                                                </div>
                                                                <div class="d-flex justify-content-between mb-1 text-break" style="overflow-wrap: break-word;">
                                                                    <span>Tracking ID:</span> <span>{{ $submission->tracking_id ?? 'N/A' }}</span>
                                                                </div>
                                                                <div class="d-flex justify-content-between text-break" style="overflow-wrap: break-word;">
                                                                    <span>Ticket/Batch:</span> <span>{{ $submission->ticket_id }} / {{ $submission->batch_id }}</span>
                                                                </div>
                                                                @if($submission->file_url)
                                                                    <div class="mt-3">
                                                                        <a href="{{ $submission->file_url }}" target="_blank" class="btn btn-sm btn-primary w-100">
                                                                            <i class="bi bi-download me-1"></i> Download Result File
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No CRM requests found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $submissions->withQueryString()->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Individual Check Status
    $('.btn-check-status').on('click', function() {
        const $btn = $(this);
        const url = $btn.data('url');
        const $row = $btn.closest('tr');
        const $statusCell = $row.find('.status-cell');

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Info', response.message, 'info');
                    $btn.prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i> Check');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Failed to check status. Please try again.', 'error');
                $btn.prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i> Check');
            }
        });
    });

    // Bulk Sync (10 by 10 logic / Sequential)
    $('#syncAllPending').on('click', function() {
        const pendingButtons = $('.btn-check-status:not(:disabled)');
        if (pendingButtons.length === 0) {
            Swal.fire('Info', 'No pending requests found on this page.', 'info');
            return;
        }

        Swal.fire({
            title: 'Syncing Statuses',
            text: `Updating ${pendingButtons.length} requests...`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
                processSequentially(0);
            }
        });

        function processSequentially(index) {
            if (index >= pendingButtons.length) {
                Swal.fire({
                    icon: 'success',
                    title: 'Finished',
                    text: 'All pending requests on this page have been synchronized.',
                    timer: 2000
                }).then(() => location.reload());
                return;
            }

            const $btn = $(pendingButtons[index]);
            const url = $btn.data('url');

            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                success: function() {
                    processSequentially(index + 1);
                },
                error: function() {
                    // Continue even if one fails
                    processSequentially(index + 1);
                }
            });
        }
    });
});
</script>
@endpush
@endsection
