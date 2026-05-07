@extends('layouts.dashboard')

@section('title', 'IPE Clearance Details')
@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <style>
        .form-check .form-check-input {
            margin-left: 0;
        }
        .form-check-input, .form-check-label {
            cursor: pointer;
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
                            <h5 class="card-title">IPE Clearance Request</h5>
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
                                                        <p><i class="ti ti-user fs-16"></i> &nbsp;<strong>Full Name:</strong> {{ $requests->user->name }}</p>
                                                        <p><i class="ti ti-mail fs-16"></i> &nbsp;<strong>Email:</strong> {{ $requests->user->email }}</p>
                                                        <p><i class="ti ti-phone fs-16"></i> &nbsp;<strong>Phone:</strong> {{ $requests->user->phone_number }}</p>
                                                    </div>
                                                </div>
                                                <!-- Transaction Details -->
                                                <div class="col-md-6 mb-4">
                                                    <div class="p-3 border rounded bg-light">
                                                        <h6 class="text-uppercase text-muted mb-3">Transaction Information</h6>
                                                        <p><strong>Transaction ID:</strong> {{ $requests->transaction->id ?? 'N/A' }}</p>
                                                        <p><strong>Amount:</strong> ₦{{ number_format($requests->transaction->amount ?? 0, 2) }}</p>
                                                        <p><strong>Service Type:</strong> {{ $requests->transaction->service_type ?? 'N/A' }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Request Information -->
                                            <div class="mb-4">
                                                <div class="p-3 border rounded bg-light">
                                                    <h6 class="text-uppercase mb-3">
                                                        <span class="text-muted">Request Information</span> - IPE Clearance
                                                    </h6>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p><strong>Tracking ID:</strong> {{ $requests->trackingId }}</p>
                                                            <p><strong>Date Sent:</strong> {{ \Carbon\Carbon::parse($requests->created_at)->format('d/m/Y h:i A') }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Status:</strong>
                                                                @if ($requests->resp_code == '200')
                                                                    <span class="badge bg-success">Successful</span>
                                                                @elseif($requests->resp_code == '400')
                                                                    <span class="badge bg-danger">Failed</span>
                                                                @elseif($requests->resp_code == '100')
                                                                    <span class="badge bg-warning">Pending</span>
                                                                @elseif($requests->resp_code == '101')
                                                                    <span class="badge bg-primary">Processing</span>
                                                                @else
                                                                    <span class="badge bg-secondary">Unknown</span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <p class="mt-3"><strong>Current Reply:</strong><br>
                                                        {!! $requests->reply !!}</p>
                                                    <hr>
                                                </div>
                                            </div>

                                            <!-- Comment and Action Section -->
                                            <div class="p-3 border rounded bg-light">
                                                <h6 class="text-uppercase text-muted mb-3">Action</h6>
                                                <form action="{{ route('admin.ipe.update-request-status', $requests->id) }}" method="POST" id="statusForm">
                                                    @csrf

                                                    <!-- Status Selection -->
                                                    <div class="mb-3">
                                                        <label for="status" class="form-label"><strong>Select Status</strong></label>
                                                        <select name="status" id="status" class="form-select text-dark" required>
                                                            <option value="" disabled selected>-- Choose Status --</option>
                                                            <option value="200">Resolved</option>
                                                            <option value="101">Processing</option>
                                                            <option value="400">Failed</option>
                                                            <option value="100">Pending</option>
                                                        </select>
                                                    </div>

                                                    <!-- Refund Option -->
                                                    <div class="mb-3 d-none" id="refundOption">
                                                        <label class="form-label"><strong>Refund Options</strong></label>
                                                        <div class="d-flex gap-3">
                                                            @foreach([10, 20, 30, 50, 100] as $perc)
                                                                <div class="form-check">
                                                                    <input type="radio" name="refund_percentage" value="{{$perc}}" id="refund{{$perc}}" class="form-check-input refund-percentage">
                                                                    <label for="refund{{$perc}}" class="form-check-label">{{$perc}}%</label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div class="mt-3">
                                                            <label for="refundAmount" class="form-label"><strong>Refund Amount (₦)</strong></label>
                                                            <input type="text" id="refundAmount" name="refundAmount" class="form-control">
                                                        </div>
                                                    </div>

                                                    <!-- Quill Editor Section -->
                                                    <div class="mb-3">
                                                        <label for="editor" class="form-label"><strong>Comment</strong></label>
                                                        <div id="editor" class="form-control" style="height: 150px;"></div>
                                                        <input type="hidden" name="comment" id="commentInput">
                                                    </div>

                                                    <!-- Submit Button -->
                                                    <button type="submit" class="btn btn-primary w-100">Submit</button>
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
            var quill = new Quill('#editor', {
                theme: 'snow',
                placeholder: 'Enter your comment...',
            });

            const statusSelect = document.getElementById('status');
            const refundOption = document.getElementById('refundOption');
            const refundAmountInput = document.getElementById('refundAmount');
            const refundPercentageRadios = document.querySelectorAll('.refund-percentage');

            // Transaction amount
            const transactionAmount = {{ $requests->transaction->amount ?? 0 }};

            // Show or hide refund option based on status
            statusSelect.addEventListener('change', function() {
                quill.root.innerHTML = '';
                if (this.value === '400') {
                    refundOption.classList.remove('d-none');
                    refundAmountInput.setAttribute('required', 'required');
                } else {
                    refundOption.classList.add('d-none');
                    refundAmountInput.removeAttribute('required');
                    refundAmountInput.value = '';
                    refundPercentageRadios.forEach(radio => (radio.checked = false));

                    if (this.value === '101') {
                         quill.root.innerHTML = "Thank you for reaching out. Your request has been received and is currently being processed. We will notify you promptly upon resolution.";
                    }
                }
            });

            // Calculate refund amount based on selected percentage
            refundPercentageRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    const percentage = parseInt(this.value, 10);
                    const refundAmount = (transactionAmount * percentage) / 100;
                    refundAmountInput.value = `${refundAmount}`;
                });
            });

            // Handle Form Submission
            const form = document.getElementById('statusForm');
            form.addEventListener('submit', function(event) {
                // Get Quill content as HTML
                const commentContent = quill.root.innerHTML;
                // Set it in the hidden input
                document.getElementById('commentInput').value = commentContent;

                // Optionally: Validate the comment is not empty
                if (quill.getText().trim().length === 0) {
                    event.preventDefault();
                    alert('Please add a comment before submitting.');
                }
            });
        });
    </script>
@endpush
