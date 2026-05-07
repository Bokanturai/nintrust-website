@extends('layouts.dashboard')

@section('title', 'Buy Airtime')
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
                        <h4 class="card-title">Buy Airtime</h4>
                        <p class="card-description text-muted">
                            Select your mobile network, enter your phone number, and choose the amount to proceed
                        </p>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="text-center mb-4">
                                <img class="img-fluid" src="{{ asset('assets/images/network_providers.png') }}"
                                    style="max-width: 200px;">
                            </div>

                            <form id="buyAirtimeForm" method="POST" action="{{ route('user.buyairtime') }}">
                                @csrf
                                <div class="mb-3">
                                    <select name="network" class="form-select form-select-lg text-center text-dark"
                                        required>
                                        <option value="">Choose Network</option>
                                        <option value="mtn">MTN</option>
                                        <option value="airtel">AIRTEL</option>
                                        <option value="glo">GLO</option>
                                        <option value="etisalat">9Mobile</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" id="mobileno" oninput="validateNumber()" name="mobileno"
                                        class="form-control phone form-control-lg text-center" maxlength="11" required>
                                    <p id="networkResult" class="small mt-1"></p>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Amount (₦)</label>
                                    <input type="number" id="amount" name="amount"
                                        class="form-control form-control-lg text-center" required>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" id="buy-airtime" class="btn btn-primary btn-lg">
                                        <i class="las la-shopping-cart me-2"></i>
                                        Buy Airtime
                                    </button>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('buyAirtimeForm');
            const submitButton = document.getElementById('buy-airtime');

            if (form && submitButton) {
                form.addEventListener('submit', function() {
                    submitButton.disabled = true;
                    submitButton.innerText = 'Please wait while we process your request...';
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
