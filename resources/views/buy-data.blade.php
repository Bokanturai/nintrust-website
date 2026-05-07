@extends('layouts.dashboard')

@section('title', 'Buy Data')
@push('styles')
    <style>
        .form-select-lg,
        .form-control-lg {
            padding: 0.75rem 1rem;
            font-size: 1.1rem;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .card-title {
            font-weight: 600;
        }
    </style>
@endpush
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
            </div>

            <div class="card">
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

                    @if ($errors->any()))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="text-center mb-4">
                        <h4 class="card-title">Buy Data</h4>
                        <p class="card-description text-muted">
                            To purchase data, select your mobile network, enter your mobile number, and choose the amount to
                            proceed with the transaction.
                        </p>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="text-center mb-4">
                                <img class="img-fluid" src="{{ asset('assets/images/network_providers.png') }}"
                                    style="max-width: 200px;">
                            </div>
                            <form id="buyDataForm" name="buy-data" method="POST" action="{{ route('user.buydata') }}">
                                @csrf
                                <div class="mb-3 row">
                                    <div class="col-md-12">
                                        <select name="network" id="service_id" class="form-select text-center text-dark"
                                            aria-label="Default select example ">
                                            <option value="">Choose Network</option>
                                            @foreach ($servicename as $service)
                                                <option value="{{ $service->service_id }}">
                                                    {{ $service->service_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-12 mt-3">
                                        <select name="bundle" id="bundle" class="form-select text-center text-dark"
                                            aria-label="Default select example">
                                            <option value="">Choose Bundle</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <p class="mb-2 text-muted">Amount To Pay</p>
                                        <input type="text" id="amountToPay" readonly value=""
                                            class="form-control text-center" />
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <p class="mb-0 text-muted">Phone Number</p>
                                        <input type="text" id="mobileno" name="mobileno" oninput="validateNumber()"
                                            value="" class="form-control phone text-center" maxlength="11" required />
                                        <p id="networkResult"></p>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" id="buy-data" class="btn btn-primary btn-lg"><i
                                                class="las la-shopping-cart me-2"></i> Buy
                                            Data</button>
                                    </div>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/data.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('buyDataForm');
            const submitButton = document.getElementById('buy-data');

            if (form && submitButton) {
                form.addEventListener('submit', function() {
                    submitButton.disabled = true;
                    submitButton.innerText = 'Processing your request, please wait...';
                });
            }
        });

        function identifyNetwork(prefix) {
            var mtnPrefixes = [
                "0803",
                "0806",
                "0703",
                "0706",
                "0813",
                "0814",
                "0816",
                "0903",
                "0810",
                "0906",
                "0913",
                "0916",
                "0702",
                "0704",
            ];
            var airtelPrefixes = [
                "0802",
                "0902",
                "0701",
                "0808",
                "0708",
                "0812",
                "0901",
                "0904",
                "0907",
                "0912",
                "0911",
            ];
            var gloPrefixes = ["0805", "0807", "0815", "0811", "0705", "0905", "0915"];
            var etisalatPrefixes = ["0809", "0817", "0818", "0909", "0908"];

            if (mtnPrefixes.includes(prefix)) {
                return "MTN";
            } else if (airtelPrefixes.includes(prefix)) {
                return "Airtel";
            } else if (gloPrefixes.includes(prefix)) {
                return "GLO";
            } else if (etisalatPrefixes.includes(prefix)) {
                return "9mobile";
            } else {
                return "Unknown Network";
            }
        }

        function validateNumber() {
            var phoneNumberInput = document.querySelector(".phone");
            //var bypassCheckbox = document.getElementById("bypassValidation");
            var networkResult = document.getElementById("networkResult");
            var phoneNumber = phoneNumberInput.value.replace(/[^0-9]/g, "");
            // && !bypassCheckbox.checked
            if (phoneNumber.length >= 4) {
                var prefix = phoneNumber.substring(0, 4);
                var network = identifyNetwork(prefix);
                networkResult.textContent = "Network Identified: " + network;
                networkResult.classList.add(
                    "p-3",
                    "bg-info",
                    "bg-opacity-10",
                    "border",
                    "border-info",
                    "rounded",
                    "rounded-top-0",
                    "border-top-0",
                    "mt-0"
                );
            } else {
                networkResult.textContent = "";
                networkResult.classList.remove(
                    "p-3",
                    "bg-info",
                    "bg-opacity-10",
                    "border",
                    "border-info",
                    "rounded",
                    "rounded-top-0",
                    "border-top-0",
                    "mt-0"
                );
            }
        }
    </script>
@endpush
