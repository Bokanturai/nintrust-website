@extends('layouts.dashboard')

@section('title', 'NIN Services')

@section('content')
    <div class="row">
        <div class="mb-3 mt-1">
            <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
        </div>
        <div class="col-lg-12 grid-margin d-flex flex-column">
            <div class=" grid-margin stretch-card col-md-12   grid-margin stretch-card ">
                <div class="card">
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
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
                        <h4 class="card-title">NIN Validation</h4>
                        <p class="card-description">Submit NIN Service request with Tracking ID and NIN for
                            assistance with NIN Issues.</p>

                        <div class="row">
                            <div class="col-md-4 mb-3" role="tabpanel" aria-labelledby="new-tab">

                                <center>
                                    <img class="img-fluid" src="{{ asset('assets/images/img/nimc.png') }}" width="30%">
                                </center>
                                <center>
                                    <small class="font-italic text-danger"><i>Please note that this request will be
                                            processed within 2 working days. We appreciate your patience
                                            and
                                            will keep you updated on the status.
                                        </i>
                                    </small>
                                </center>

                                <div class="row text-center">
                                    <div class="col-md-12">
                                        <form id="form" name="nin-request" method="POST"
                                            action="{{ route('user.nin.services.request') }}" enctype="multipart/form-data">
                                            @csrf
                                            <div class="row mb-2">
                                                <div class="row">
                                                    <div class="col-md-12 mt-3 mb-3">
                                                        <select name="service" id="service" class="form-select text-dark"
                                                            required>
                                                            <option value="">-- Service Type --</option>
                                                            @foreach ($services as $service)
                                                                <option value="{{ $service->service_code }}">
                                                                    {{ $service->name }} -
                                                                    &#x20A6;{{ number_format($service->amount, 2) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <p class="mb-2 form-label" id="modify_lbl"></p>
                                                        <div id="input-container"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit" id="nin-request" class="btn btn-primary">
                                                <i class="las la-share"></i> Submit Request
                                            </button>
                                        </form>
                                    </div>

                                </div>

                            </div>
                            <div class="col-md-8" role="tabpanel" aria-labelledby="history-tab">


                                @if (!$ninServices->isEmpty())
                                    @php
                                        $currentPage = $ninServices->currentPage(); // Current page number
                                        $perPage = $ninServices->perPage(); // Number of items per page
                                        $serialNumber = ($currentPage - 1) * $perPage + 1; // Starting serial number for current page
                                    @endphp
                                    <div class="table-responsive">
                                        <table class="table text-nowrap" style="background:#fafafc !important">
                                            <thead>
                                                <tr class="table-primary">
                                                    <th width="5%" scope="col">ID</th>
                                                    <th scope="col">Reference No.</th>
                                                    <th scope="col">Service Type</th>
                                                    <th scope="col" class="text-center">Status
                                                    </th>
                                                    <th scope="col" class="text-center">Response</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $i = 1; @endphp
                                                @foreach ($ninServices as $data)
                                                    <tr>
                                                        <th scope="row">{{ $serialNumber++ }}</th>
                                                        <td>{{ $data->refno }}</td>
                                                        <td>{{ $data->service_type }}</td>
                                                        <td class="text-center">

                                                            @if ($data->status == 'resolved')
                                                                <span class="badge bg-success">Sucessful</span>
                                                            @elseif($data->status == 'rejected')
                                                                <span
                                                                    class="badge bg-danger">{{ Str::upper($data->status) }}</span>
                                                            @elseif($data->status == 'processing')
                                                                <span
                                                                    class="badge bg-primary">{{ Str::upper($data->status) }}</span>
                                                            @else
                                                                <span
                                                                    class="badge bg-warning">{{ Str::upper($data->status) }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <a type="button" data-bs-toggle="modal" data-id="2"
                                                                data-reason="{{ $data->reason }}" data-bs-target="#reason">

                                                                <i class="ti-info-alt" style="font-size:24px"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @php $i++ @endphp
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <!-- Pagination Links -->
                                        <div class="d-flex justify-content-center">
                                            {{ $ninServices->links('vendor.pagination.bootstrap-4') }}
                                        </div>
                                    </div>
                                @else
                                    <center><img width="65%" src="{{ asset('assets/images/no-transaction.gif') }}"
                                            alt=""></center>
                                    <p class="text-center fw-semibold  fs-15"> No Request
                                        Available!</p>
                                @endif

                            </div>
                        </div>


                    </div>
                </div>
            </div>
            <div class="modal fade" id="reason" tabindex="-1" aria-labelledby="reason" data-bs-keyboard="true"
                aria-hidden="true">
                <!-- Scrollable modal -->
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title" id="staticBackdropLabel2">Support
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p id="message">No Message Yet.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $("#reason").on("shown.bs.modal", function(event) {
            var button = $(event.relatedTarget);

            var reason = button.data("reason");
            if (reason != "") $("#message").html(reason);
            else $("#message").html("No Message Yet.");
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form');
            const submitButton = document.getElementById('nin-request');

            form.addEventListener('submit', function() {
                submitButton.disabled = true;
                submitButton.innerText = 'Please wait while we process your request...';
            });
        });

        $(document).ready(function() {
            hide();

            $("#service").change(function() {
                const selectedItem = this.value;

                // Clear dynamic content area
                $("#input-container").empty();
                $("#modify_lbl").text("").hide();

                let labelText = "";
                let inputs = '';

                switch (selectedItem) {
                    case '000':
                        // Requirements: NIN, Email
                        labelText = "Enter NIN and Email Address";
                        inputs += createInput('nin', 'NIN Number', 11, 'text', '^\\d{11}$',
                            'NIN must be 11 digits');
                        inputs += createInput('email', 'Email Address', 100, 'email');
                        break;

                    case '113':
                    case '114':
                    case '115':
                    case '116':
                    case '117':
                    case '118':
                    case '119':
                    case '120':
                    case '121':
                        // Requirement: Only NIN
                        labelText = "Enter NIN Number";
                        inputs += createInput('nin', 'NIN Number', 11, 'text', '^\\d{11}$',
                            'NIN must be 11 digits');
                        break;

                    case '001':
                        // Requirements: NIN, Tracking ID, Surname, First Name, Middle Name, DOB
                        labelText = "Full Identity Details";
                        inputs += createInput('nin', 'NIN Number', 11, 'text', '^\\d{11}$',
                            'NIN must be 11 digits');
                        inputs += createInput('tracking_id', 'Tracking ID', '15', 'text',
                            '^(?=.*[a-zA-Z])(?=.*\\d)[a-zA-Z0-9]{15}$',
                            'Tracking ID must be 15 characters, containing letters and numbers');
                        inputs += createInput('surname', 'Surname');
                        inputs += createInput('firstname', 'First Name');
                        inputs += createInput('middlename', 'Middle Name', '', 'text', '',
                            '', '');
                        inputs += createInput('dob', 'Date of Birth', '', 'date', '',
                            'Date of Birth is required!');
                        break;

                    default:
                        break;
                }

                $("#modify_lbl").text(labelText).show();
                $("#input-container").append(inputs);

                if (selectedItem === '115') {
                    const dobInput = document.getElementById('dob');

                    if (dobInput) {
                        dobInput.addEventListener('invalid', function() {
                            if (dobInput.validity.valueMissing) {
                                dobInput.setCustomValidity('Date of Birth is required!');
                            } else {
                                dobInput.setCustomValidity('');
                            }
                        });

                        dobInput.addEventListener('input', function() {
                            dobInput.setCustomValidity('');
                        });

                        // Optional: Prevent future dates
                        dobInput.max = new Date().toISOString().split("T")[0];
                    }
                }
            });
        });

        function hide() {
            $("#modify_lbl").hide();
        }

        function createInput(id, placeholder, maxlength = '', type = 'text', pattern = '', title = '', required =
            'required') {
            const max = maxlength ? `maxlength="${maxlength}"` : '';
            const pat = pattern ? `pattern="${pattern}"` : '';
            const tip = title ? `title="${title}"` : '';


            return `<input type="${type}" name="${id}" id="${id}" ${max} ${pat} ${tip} class="form-control mb-2" placeholder="${placeholder}" ${required} />`;
        }
    </script>
@endpush
