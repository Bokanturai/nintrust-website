@extends('layouts.dashboard')

@section('title', 'NIN IPE Request')

@section('content')
    <div class="row">
        <div class="mb-3 mt-1">
            <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
        </div>
        <div class="col-lg-12 grid-margin d-flex flex-column">
            <div class="grid-margin stretch-card col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">IPE Request</h4>
                        <p class="card-description">Send your ipe request to get your tracking number</p>
                        <ul class="nav nav-tabs"
                            role="tablist">
                            <li class="nav-item"
                                role="presentation">
                                <a class="nav-link active"
                                   id="new-tab"
                                   data-bs-toggle="tab"
                                   href="#new-1"
                                   role="tab"
                                   aria-controls="new-1"
                                   aria-selected="true">New</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active"
                                 id="new-1"
                                 role="tabpanel"
                                 aria-labelledby="new-tab">

                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show"
                                         role="alert">
                                        {{ session('success') }}
                                    </div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show"
                                         role="alert">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show"
                                         role="alert">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <form name="ipe-form"
                                              id="ipe-form"
                                              method="POST"
                                              action="{{ route('user.ipe-request') }}">
                                            @csrf
                                            <div class="row mb-3">

                                                <div class="col-md-12 mt-2">
                                                    <div class="row">
                                                        <div class="col-md-12 mb-0 mt-2">
                                                            <p class="form-label">Tracking Number</p>
                                                            <input type="text"
                                                                   id="trackingId"
                                                                   name="trackingId"
                                                                   maxlength="15"
                                                                   class="form-control"
                                                                   required />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-2 mt-1">

                                                {{-- <small class="text-danger">Our IPE request process is fully automated. You
                                                    can track the status of your request using the 'Check Status' button
                                                </small><br /> --}}

                                                <p class="fw-bold mt-2"> Service Fee:
                                                    &#x20A6;{{ number_format($ServiceFee->amount, 2) }}</p>

                                            </div>
                                            <button type="submit"
                                                    id="submit"
                                                    name="submit"
                                                    class="btn btn-primary"><i class="las la-share"></i> Submit
                                                Request
                                            </button>
                                        </form>
                                    </div>
                                    <div class="col-md-8">
                                        @if (!$ipes->isEmpty())
                                            @php
                                                $currentPage = $ipes->currentPage();
                                                $perPage = $ipes->perPage();
                                                $serialNumber = ($currentPage - 1) * $perPage + 1;
                                            @endphp
                                            <div class="table-responsive">
                                                <table class="table text-nowrap"
                                                       style="background:#fafafc !important">
                                                    <thead>
                                                        <tr class="table-primary">
                                                            <th width="5%"
                                                                scope="col">S/N</th>
                                                            <th scope="col">Tracking ID</th>
                                                            <th>Status</th>
                                                            <th>Date</th>
                                                            <th scope="col" class="text-center">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php $i = 1; @endphp
                                                        @foreach ($ipes as $data)
                                                            <tr>
                                                                <th scope="row">{{ $serialNumber++ }}</th>
                                                                <td>{{ $data->trackingId }}</td>
                                                                <td>
                                                                    @php
                                                                        $status = strtolower($data->status ?? 'pending');
                                                                    @endphp
                                                                    @if ($status == 'successful' || $status == 'success')
                                                                        <span class="badge bg-success">Successful</span>
                                                                    @elseif ($status == 'failed' || $status == 'rejected')
                                                                        <span class="badge bg-danger">Failed</span>
                                                                    @elseif ($status == 'pending')
                                                                        <span class="badge bg-warning">Pending</span>
                                                                    @else
                                                                        <span class="badge bg-primary">{{ ucfirst($status) }}</span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $data->created_at }}</td>
                                                                
                                                                <td class="text-center">
                                                                    <div class="d-flex gap-2 justify-content-center">
                                                                        <button type="button"
                                                                                class="btn btn-sm btn-icon btn-outline-primary"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#commentModal"
                                                                                data-comment="{{ $data->reply ?? 'No reply yet.' }}">
                                                                            <i class="bi bi-eye-fill"></i>
                                                                        </button>

                                                                        @php
                                                                            $status = strtolower($data->status ?? 'pending');
                                                                        @endphp
                                                                        @if (!in_array($status, ['successful', 'success', 'failed', 'rejected']))
                                                                            <button type="button" 
                                                                                    class="btn btn-sm btn-icon btn-outline-info btn-check-status" 
                                                                                    data-url="{{ route('user.ipeStatus', $data->trackingId) }}"
                                                                                    title="Check Status">
                                                                                <i class="bi bi-arrow-clockwise"></i>
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            @php $i++ @endphp
                                                        @endforeach

                                                    </tbody>
                                                </table>
                                                <!-- Pagination Links -->
                                                <div class="d-flex justify-content-center">
                                                    {{ $ipes->links('vendor.pagination.bootstrap-4') }}
                                                </div>
                                            </div>
                                        @else
                                            <center><img width="65%"
                                                     src="{{ asset('assets/images/no-transaction.gif') }}"
                                                     alt="">
                                            </center>
                                            <p class="fw-semibold fs-15 text-center"> No Request
                                                Available!</p>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Comment Modal --}}
    <div class="modal fade" id="commentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold">IPE Reply Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="fw-bold mb-2">Message/Reply:</p>
                    <div class="bg-light p-3 rounded-3 mb-3 border" id="modal-comment-text" 
                         style="width: 100%; 
                                word-wrap: break-word; 
                                overflow-wrap: break-word; 
                                word-break: break-word; 
                                white-space: normal;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .bi-spin { animation: spin 2s linear infinite; display: inline-block; }
        .btn-icon { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Comment Modal Handling
            const commentModal = document.getElementById('commentModal');
            if (commentModal) {
                commentModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const comment = button.getAttribute('data-comment');
                    document.getElementById('modal-comment-text').innerHTML = comment;
                });
            }

            const form = document.getElementById('ipe-form');
            const submitButton = document.getElementById('submit');

            if (form) {
                form.addEventListener('submit', function() {
                    submitButton.disabled = true;
                    submitButton.innerText = 'Please wait while we process your request...';
                });
            }

            // AJAX Status Check
            document.querySelectorAll('.btn-check-status').forEach(button => {
                button.addEventListener('click', function() {
                    const url = this.getAttribute('data-url');
                    const icon = this.querySelector('i');
                    
                    // Show loading
                    if (icon) icon.classList.add('bi-spin'); 
                    this.disabled = true;

                    fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (icon) icon.classList.remove('bi-spin');
                        this.disabled = false;
                        
                        Swal.fire({
                             icon: data.status === 'success' ? 'success' : (data.status === 'info' ? 'info' : 'error'),
                             title: data.status === 'success' ? 'Status Updated' : (data.status === 'info' ? 'Info' : 'Error'),
                             text: data.message,
                             confirmButtonColor: '#3085d6',
                        }).then(() => {
                            if (data.status === 'success') {
                                location.reload(); // Reload to show updated status in table
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (icon) icon.classList.remove('bi-spin');
                        this.disabled = false;
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An unexpected error occurred.',
                        });
                    });
                });
            });
        });
    </script>
@endpush
