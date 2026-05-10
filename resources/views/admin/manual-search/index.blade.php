@extends('layouts.dashboard')

@section('title', 'Admin - Manual BVN Search')

@section('content')
<div class="page-title mb-4">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <h3 class="fw-bold text-primary">Manual BVN Search Management</h3>
            <p class="text-muted small mb-0">Review and process manual BVN search requests.</p>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold text-white"><i class="bi bi-list-check me-2"></i>All Manual Requests</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>User</th>
                                    <th>ID Submitted</th>
                                    <th>Reference</th>
                                    <th class="text-center">Status</th>
                                    <th class="pe-4 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($submissions as $submission)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold">{{ $submission->created_at->format('d M, Y') }}</div>
                                            <div class="small text-muted">{{ $submission->created_at->format('h:i A') }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $submission->user->name }}</div>
                                            <div class="small text-muted">{{ $submission->user->email }}</div>
                                        </td>
                                        <td><span class="fw-bold text-dark">{{ $submission->phone_number }}</span></td>
                                        <td><code class="text-primary">{{ $submission->reference }}</code></td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill bg-{{ match($submission->status) {
                                                'successful', 'resolved' => 'success',
                                                'processing', 'pending'    => 'primary',
                                                'failed', 'rejected'       => 'danger',
                                                'query'                    => 'info',
                                                default                    => 'warning'
                                            } }}">
                                                {{ ucfirst($submission->status) }}
                                            </span>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#updateModal{{ $submission->id }}">
                                                <i class="bi bi-pencil"></i> Update
                                            </button>
                                            
                                            <!-- Update Modal -->
                                            <div class="modal fade" id="updateModal{{ $submission->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content border-0 shadow rounded-4 text-start">
                                                        <div class="modal-header bg-warning text-dark">
                                                            <h5 class="modal-title fw-bold">Update Manual Request</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('admin.manual-bvn-search.update', $submission->id) }}" method="POST" enctype="multipart/form-data">
                                                            @csrf
                                                            <div class="modal-body p-4 text-dark">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Status</label>
                                                                    <select name="status" class="form-select border shadow-sm text-dark" required>
                                                                        @foreach(['pending','processing','successful','resolved','rejected','failed','query'] as $st)
                                                                            <option value="{{ $st }}" {{ $submission->status == $st ? 'selected' : '' }}>
                                                                                {{ ucfirst($st) }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Comment / Result</label>
                                                                    <textarea name="comment" class="form-control border shadow-sm text-dark" rows="4" placeholder="Enter result or reason here...">{{ $submission->comment }}</textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Result File (Optional)</label>
                                                                    <input type="file" name="result_file" class="form-control border shadow-sm text-dark">
                                                                    <small class="text-muted">Allowed: PDF, JPG, PNG, ZIP (Max 5MB)</small>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer border-0">
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-warning fw-bold text-dark">Update Request</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">No manual requests found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($submissions->hasPages())
                    <div class="card-footer bg-white">
                        {{ $submissions->links('vendor.pagination.bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
