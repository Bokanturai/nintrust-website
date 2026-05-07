@extends('layouts.dashboard')

@section('title', 'Suspended NIN Request View')
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
        <div class="col-lg-12 grid-margin d-flex flex-column">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card ">
                        <div class="card-header">
                            <h5 class="card-title">Suspended NIN Request</h5>
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

                            <!-- Page Header -->
                            <div class="d-flex align-items-center justify-content-between my-3">
                                <h4 class="mb-0">Request Details </h4>
                                <small class="pull-right fw-bold"> (Last Modified - {{ $requests->updated_at }})</small>
                            </div>

                            <!-- Request Details Card -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0">Details</h5>
                                        </div>
                                        <div class="card-body">
                                            <!-- Grid Layout for User and Transaction Details -->
                                            <div class="row">
                                                <!-- User Details -->
                                                <div class="col-md-6 mb-4">
                                                    <div class="p-3 border rounded bg-light">
                                                        <h6 class="text-uppercase text-muted mb-3">Customer Information</h6>
                                                        <p> &nbsp;<strong>Full Name:</strong>
                                                            {{ strtoupper($requests->user->name) }}
                                                        </p>
                                                        <p> &nbsp;<strong>Email:</strong>
                                                            {{ strtoupper($requests->user->email) }}</p>
                                                        <p>&nbsp;<strong>Phone:</strong> {{ $requests->user->phone_number }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <!-- Transaction Details -->
                                                <div class="col-md-6 mb-4">
                                                    <div class="p-3 border rounded bg-light">
                                                        <h6 class="text-uppercase text-muted mb-3">Transaction Information
                                                        </h6>
                                                        <p><strong>Transaction ID:</strong>
                                                            {{ optional($requests->transactions)->id ?? 'N/A' }}</p>
                                                        <p><strong>Amount:</strong>
                                                            ₦{{ number_format(optional($requests->transactions)->amount ?? 0, 2) }}
                                                        </p>
                                                        <p><strong>Service Type:</strong> Suspended NIN</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Form Details Section -->
                                            <div class="row p-3 border rounded bg-light mb-4">
                                                <div class="col-md-4 text-center">
                                                    <h6 class="text-uppercase text-muted mb-3">Passport Photo</h6>
                                                    @if ($requests->photo)
                                                        <img src="{{ asset('storage/' . $requests->photo) }}" alt="Photo"
                                                            class="img-thumbnail mb-2"
                                                            style="width: 180px; height: 180px; object-fit: cover;">
                                                        <br>
                                                        <a href="{{ asset('storage/' . $requests->photo) }}"
                                                            target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                                            View Full Photo
                                                        </a>
                                                    @else
                                                        <p class="text-muted">No photo uploaded</p>
                                                    @endif
                                                </div>
                                                <div class="col-md-4">
                                                    <h6 class="text-uppercase text-muted mb-3">Personal Details</h6>
                                                    <p><strong>Reference No.:</strong>
                                                        {{ strtoupper($requests->refno ?? 'N/A') }}</p>
                                                    <p><strong>Title:</strong> {{ strtoupper($requests->title ?? 'N/A') }}
                                                    </p>
                                                    <p><strong>NIN:</strong> {{ $requests->nin ?? 'N/A' }}</p>
                                                    <p><strong>Surname:</strong> {{ strtoupper($requests->surname) }}</p>
                                                    <p><strong>First Name:</strong> {{ strtoupper($requests->first_name) }}
                                                    </p>
                                                    <p><strong>Middle Name:</strong>
                                                        {{ strtoupper($requests->middle_name ?? '-') }}
                                                    </p>
                                                    <p><strong>Gender:</strong>
                                                        {{ strtoupper($requests->gender ?? 'N/A') }}</p>
                                                    <p><strong>Date of Birth:</strong>
                                                        {{ $requests->dob ? \Carbon\Carbon::parse($requests->dob)->format('d/m/Y') : 'N/A' }}
                                                    </p>
                                                </div>
                                                <div class="col-md-4">
                                                    <h6 class="text-uppercase text-muted mb-3">Contact & Residence</h6>
                                                    <p><strong>Phone:</strong> {{ $requests->phone ?? 'N/A' }}</p>
                                                    <p><strong>Email:</strong> {{ strtoupper($requests->email ?? 'N/A') }}
                                                    </p>
                                                    <p><strong>State of Origin:</strong>
                                                        {{ strtoupper($requests->state_origin ?? 'N/A') }}</p>
                                                    <p><strong>LGA of Origin:</strong>
                                                        {{ strtoupper($requests->lga_origin ?? 'N/A') }}
                                                    </p>
                                                    <p><strong>Residence State:</strong>
                                                        {{ strtoupper($requests->state_residence ?? 'N/A') }}</p>
                                                    <p><strong>Residence LGA:</strong>
                                                        {{ strtoupper($requests->lga_residence ?? 'N/A') }}</p>
                                                    <p><strong>Residence City:</strong>
                                                        {{ strtoupper($requests->town_city ?? 'N/A') }}
                                                    </p>
                                                    <p><strong>Address:</strong>
                                                        {{ strtoupper($requests->address_residence ?? 'N/A') }}</p>

                                                    <p class="border-top pt-2 mt-2"><strong>Status:</strong>
                                                        @php
                                                            $status = $requests->status ?? 'submitted';
                                                            $badgeClass =
                                                                [
                                                                    'submitted' => 'bg-warning',
                                                                    'processing' => 'bg-primary',
                                                                    'successful' => 'bg-success',
                                                                    'rejected' => 'bg-danger',
                                                                ][$status] ?? 'bg-secondary';
                                                            $statusLabel =
                                                                [
                                                                    'submitted' => 'Submitted',
                                                                    'processing' => 'In-Progress',
                                                                    'successful' => 'Successful',
                                                                    'rejected' => 'Rejected',
                                                                ][$status] ?? ucfirst($status);
                                                        @endphp
                                                        <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                                    </p>
                                                </div>
                                            </div>

                                            @if ($requests->reason)
                                                <div class="p-3 border rounded bg-light mb-4">
                                                    <h6 class="text-uppercase text-muted mb-3">Previous Admin Comments</h6>
                                                    <div>{!! $requests->reason !!}</div>
                                                </div>
                                            @endif

                                            @if ($requests->result_file)
                                                <div
                                                    class="alert alert-success py-2 d-flex justify-content-between align-items-center">
                                                    <span><strong>Current Result:</strong> File Uploaded</span>
                                                    <a href="{{ asset($requests->result_file) }}" target="_blank"
                                                        class="btn btn-sm btn-light py-0">VIEW FILE</a>
                                                </div>
                                            @endif

                                            @if ($requests->refunded_at)
                                                <div class="alert alert-info py-2">
                                                    <strong>Refunded On:</strong>
                                                    {{ \Carbon\Carbon::parse($requests->refunded_at)->format('d/m/Y H:i') }}
                                                </div>
                                            @endif

                                            <!-- Comment and Action Section -->
                                            <div class="p-3 border rounded bg-light mt-5">
                                                <h6 class="text-uppercase text-muted mb-3">Action</h6>
                                                <form
                                                    action="{{ route('admin.update-suspended-nin-status', [$requests->id, $request_type]) }}"
                                                    method="POST" id="statusForm" enctype="multipart/form-data">
                                                    @csrf

                                                    <div class="mb-3">
                                                        <label for="status" class="form-label"><strong>Select
                                                                Status</strong></label>
                                                        <select name="status" id="status" class="form-select text-dark"
                                                            required>
                                                            <option value="" disabled selected>-- Choose Status --
                                                            </option>
                                                            <option value="successful">Successful / Resolved</option>
                                                            <option value="processing">Processing</option>
                                                            <option value="rejected">Rejected</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="result_file" class="form-label"><strong>Upload Result
                                                                (Optional)</strong></label>
                                                        <input type="file" name="result_file" id="result_file"
                                                            class="form-control">
                                                        <small class="text-muted">You can upload a document (PDF, Image)
                                                            for the user.</small>
                                                    </div>

                                                    <!-- Refund Option -->
                                                    <div class="mb-3 d-none" id="refundOption">
                                                        <label class="form-label"><strong>Refund Options</strong></label>
                                                        <div class="d-flex gap-3 mb-2">
                                                            <div class="form-check">
                                                                <input type="radio" name="refund_percentage"
                                                                    value="10" id="refund10"
                                                                    class="form-check-input refund-percentage">
                                                                <label for="refund10" class="form-check-label">10%</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="radio" name="refund_percentage"
                                                                    value="20" id="refund20"
                                                                    class="form-check-input refund-percentage">
                                                                <label for="refund20" class="form-check-label">20%</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="radio" name="refund_percentage"
                                                                    value="30" id="refund30"
                                                                    class="form-check-input refund-percentage">
                                                                <label for="refund30" class="form-check-label">30%</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="radio" name="refund_percentage"
                                                                    value="50" id="refund50"
                                                                    class="form-check-input refund-percentage">
                                                                <label for="refund50" class="form-check-label">50%</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="radio" name="refund_percentage"
                                                                    value="100" id="refund100"
                                                                    class="form-check-input refund-percentage">
                                                                <label for="refund100"
                                                                    class="form-check-label">100%</label>
                                                            </div>
                                                        </div>
                                                        <div class="mt-3">
                                                            <label for="refundAmount" class="form-label"><strong>Refund
                                                                    Amount (₦)</strong></label>
                                                            <input type="text" id="refundAmount" name="refundAmount"
                                                                class="form-control">
                                                        </div>
                                                    </div>

                                                    <!-- Quill Editor Section -->
                                                    <div class="mb-3">
                                                        <label for="editor"
                                                            class="form-label"><strong>Comment</strong></label>
                                                        <div id="editor" class="form-control" style="height: 150px;">
                                                        </div>
                                                        <input type="hidden" name="comment" id="commentInput">
                                                    </div>

                                                    <button type="submit" class="btn btn-primary w-100">Submit
                                                        Update</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const quill = new Quill('#editor', {
                theme: 'snow',
                placeholder: 'Enter your comment...',
            });

            const statusSelect = document.getElementById('status');
            const refundOption = document.getElementById('refundOption');
            const refundAmountInput = document.getElementById('refundAmount');
            const refundPercentageRadios = document.querySelectorAll('.refund-percentage');
            const transactionAmount = {{ optional($requests->transactions)->amount ?? 0 }};

            statusSelect.addEventListener('change', function() {
                quill.root.innerHTML = "";
                if (this.value === 'rejected') {
                    refundOption.classList.remove('d-none');
                    refundAmountInput.setAttribute('required', 'required');
                } else {
                    refundOption.classList.add('d-none');
                    refundAmountInput.removeAttribute('required');
                    refundAmountInput.value = '';
                    refundPercentageRadios.forEach(radio => (radio.checked = false));
                }

                if (this.value === 'processing') {
                    quill.root.innerHTML =
                        "Your request is currently being processed. We will update you shortly.";
                } else if (this.value === 'successful') {
                    quill.root.innerHTML = "Your Suspended NIN request has been successfully resolved.";
                }
            });

            refundPercentageRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    const percentage = parseInt(this.value, 10);
                    const refundAmount = (transactionAmount * percentage) / 100;
                    refundAmountInput.value = refundAmount;
                });
            });

            const form = document.getElementById('statusForm');
            form.addEventListener('submit', function(event) {
                const commentContent = quill.root.innerHTML;
                document.getElementById('commentInput').value = commentContent;

                if (quill.getText().trim().length === 0) {
                    event.preventDefault();
                    alert('Please add a comment before submitting.');
                }
            });
        });
    </script>
@endpush
