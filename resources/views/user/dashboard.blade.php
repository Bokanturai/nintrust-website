@extends('layouts.dashboard')

@section('title', 'Dashboard')
@push('styles')
    <style>
        .service-card {
            border-radius: 16px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .service-icon img {
            border-radius: 8px;
        }

        .service-card {
            border-radius: 20px;
            /* Almost circular */
        }

        .service-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .service-icon img {
            max-width: 40px;
            max-height: 40px;
            object-fit: contain;
            transition: transform 0.3s ease;
            filter: grayscale(20%);
        }

        .service-card:hover .service-icon img {
            transform: scale(1.1);
            filter: none;
        }

        h6 {
            font-size: 0.95rem;
        }

        @media (max-width: 576px) {
            .service-icon img {
                max-width: 34px;
                max-height: 34px;
            }

            h6 {
                font-size: 0.85rem;
            }
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #28a745, #218838);
        }

        .bg-gradient-info {
            background: linear-gradient(135deg, #17a2b8, #138496);
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
        }

        .bg-gradient-dark {
            background: linear-gradient(135deg, #343a40, #23272b);
        }

        .bg-gradient-warning {
            background: linear-gradient(135deg, #ffc107, #e0a800);
        }

        .bg-gradient-secondary {
            background: linear-gradient(135deg, #6c757d, #545b62);
        }

        /* Default style (for larger screens) */
        .price {
            font-size: 2rem;
            /* Default font size for larger screens */
            white-space: normal;
            /* Allow wrapping on larger screens */
            overflow: visible;
            /* Allow content to overflow if necessary */
            text-overflow: unset;
            /* Reset ellipsis */
            line-height: 1.2;
            /* Standard line height */
        }

        /* Style for smaller screens (e.g., mobile or tablet) */
        @media (max-width: 767px) {
            .price {
                font-size: 1.2rem;
                /* Adjust font size for smaller screens */
                white-space: nowrap;
                /* Prevent text from wrapping */
                overflow: hidden;
                /* Hide overflow */
                text-overflow: ellipsis;
                /* Show ellipsis if text overflows */
            }
        }

        /* General Styles for Service Cards */
        .service-card-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .icon-box {
            margin-bottom: 1.5rem;
        }

        .icon-box-media {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #5e2572;
            border-radius: 50%;
            width: 70px;
            height: 70px;
        }

        .icon-box-title {
            font-weight: bolder;
            font-size: 1rem;
            color: #333;
        }

        /* Responsive Layout */
        @media (max-width: 768px) {
            .icon-box-media {
                width: 60px;
                height: 60px;
            }

            .icon-box-title {
                font-size: 1rem;
            }
        }

        /* Ensures 2 items per row on mobile (smaller than 576px) */
        @media (max-width: 576px) {
            .col-6 {
                flex: 0 0 50%;
                max-width: 50%;
            }

            .icon-box-media {
                width: 50px;
                height: 50px;
            }

            .icon-box-title {
                font-size: 0.9rem;
            }
        }

        /* Custom CSS for icon box */
        .icon-box-media {
            transition: transform 0.3s ease;
        }

        .icon-box-media:hover {
            transform: scale(1.1);
        }

        /* Custom CSS for cards */
        .card {
            transition: box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .copy-btn-wrap .btn {
            padding: 4px 12px;
            font-size: 14px;
            font-weight: 500;
            color: #fff;
            background-color: #007bff;
            /* Bootstrap primary blue */
            border: none;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .copy-btn-wrap .btn:hover {
            background-color: #0056b3;
            /* Darker blue on hover */
        }
    </style>
    <style>
        .service-card {
            transition: all 0.3s ease;
            border-radius: 10px;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .bg-purple {
            background-color: #d1c2ee;
        }

        .text-purple {
            color: #080708;
        }

        .bg-orange {
            background-color: #e4c5ab;
        }

        .text-orange {
            color: #181716;
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="mb-3 mt-1">
            <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
            <p class="mb-0">Here’s a quick look at your dashboard.</p>
        </div>
        {{-- @if ($status == 'Pending')
            <div class="alert alert-danger alert-dismissible fade show"
                 role="alert">
                We're excited to have you on board! However, we need to verify your identity before activating your
                account. Simply click the link below to complete the verification process<br>
            </div>
        @endif --}}
        @include('common.message')
        <div class="col-lg-12 grid-margin d-flex flex-column">
            <div class="row">
                <div class="col-md-6 col-6 grid-margin stretch-card">
                    <div class="card hover-shadow">
                        <div class="card-body text-center">
                            <div class="text-primary mb-2">
                                <i class="mdi mdi-wallet-outline mdi-36px"></i>
                                <p class="fw-medium mt-3">Main Wallet</p>
                            </div>
                            <h1 class="fw-light price">
                                ₦{{ auth()->user()->wallet ? number_format(auth()->user()->wallet->balance, 2) : '0.00' }}
                            </h1>

                            <a href="#"
                               data-bs-toggle="modal"
                               data-bs-target="#walletModal"
                               class="btn btn-sm btn-outline-primary mt-3">
                                Add Fund
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-6 grid-margin stretch-card">
                    <div class="card hover-shadow">
                        <div class="card-body text-center">
                            <div class="text-danger mb-2">
                                <i class="mdi mdi-gift-outline mdi-36px"></i>
                                <p class="fw-medium mt-3">Bonus Wallet</p>
                            </div>
                            <h1 class="fw-light price">
                                ₦{{ auth()->user()->wallet ? number_format(auth()->user()->wallet->bonus, 2) : '0.00' }}
                            </h1>

                            <a href="{{ route('user.wallet') }}"
                               class="btn btn-sm btn-outline-danger mt-3">
                                Claim Bonus
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @if (auth()->user()->role == 'admin')
                <div class="row g-3 g-sm-4 mb-4">
                    @foreach ($metrics as $metric)
                        <div class="col-6 col-sm-5 col-md-4">
                            <x-dashboard.metric :title="$metric['title']"
                                                :value="$metric['value']"
                                                :icon="$metric['icon']"
                                                :bg="$metric['bg']"
                                                :href="$metric['href']" />
                        </div>
                    @endforeach
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card mb-2">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Daily Charts</h5>
                            </div>
                            <div class="card-body">
                                <div style="max-height: 300px;">
                                    <canvas id="depositBreakdownChart"
                                            style="height: 100%; max-height: 300px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Top Funding</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="fundingChart"
                                        width="600"
                                        height="400"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="row">
                <div class="col-12">
                    <div class="container py-4">
                        <!-- Section Header -->
                        <div class="mb-5 text-center">
                            <h2 class="fw-semibold mb-3">Our Services</h2>
                            <p class="text-muted mx-auto"
                               style="max-width: 600px;">
                                Comprehensive identity verification and utility services
                            </p>
                        </div>

                        <!-- Services Grid -->
                        {{-- <div class="row g-4">

                            <!-- NBAIS -->
                            <div class="col-6 col-md-4 col-lg-3">
                                <div
                                     class="card service-card bg-primary position-relative h-20 overflow-hidden border-0 shadow-sm">
                                    <img src="{{ asset('assets/images/services/nibais.png') }}"
                                         class="img-fluid w-100" />
                                    <a href="{{ route('user.scratch_cards.index') }}"
                                       class="stretched-link"></a>
                                </div>
                            </div>

                            <!-- JAMB CBT Software -->
                            <div class="col-6 col-md-4 col-lg-3">
                                <div
                                     class="card service-card bg-primary position-relative h-20 overflow-hidden border-0 shadow-sm">
                                    <img src="{{ asset('assets/images/services/jamb.png') }}"
                                         class="img-fluid w-100" />
                                    <a href="{{ route('user.scratch_cards.index') }}"
                                       class="stretched-link"></a>
                                </div>
                            </div>

                            <!-- NABTEB A Level -->
                            <div class="col-6 col-md-4 col-lg-3">
                                <div
                                     class="card service-card bg-primary position-relative h-20 overflow-hidden border-0 shadow-sm">
                                    <img src="{{ asset('assets/images/services/nabteb_alevel.png') }}"
                                         class="img-fluid w-100" />
                                    <a href="{{ route('user.scratch_cards.index') }}"
                                       class="stretched-link"></a>
                                </div>
                            </div>

                            <!-- NABTEB O Level -->
                            <div class="col-6 col-md-4 col-lg-3">
                                <div
                                     class="card service-card h-100 bg-primary position-relative overflow-hidden border-0 shadow-sm">
                                    <img src="{{ asset('assets/images/services/nabteb_olevel.png') }}"
                                         class="img-fluid w-100" />
                                    <a href="{{ route('user.scratch_cards.index') }}"
                                       class="stretched-link"></a>
                                </div>
                            </div>

                            <!-- NABTEB Result Checker -->
                            <div class="col-6 col-md-4 col-lg-3">
                                <div
                                     class="card service-card bg-primary position-relative h-20 overflow-hidden border-0 shadow-sm">
                                    <img src="{{ asset('assets/images/services/nabteb_result.png') }}"
                                         class="img-fluid w-100" />
                                    <a href="{{ route('user.scratch_cards.index') }}"
                                       class="stretched-link"></a>
                                </div>
                            </div>

                            <!-- NECO Token -->
                            <div class="col-6 col-md-4 col-lg-3">
                                <div
                                     class="card service-card bg-primary position-relative h-20 overflow-hidden border-0 shadow-sm">
                                    <img src="{{ asset('assets/images/services/neco_token.png') }}"
                                         class="img-fluid w-100" />
                                    <a href="{{ route('user.scratch_cards.index') }}"
                                       class="stretched-link"></a>
                                </div>
                            </div>

                            <!-- WAEC Verification -->
                            <div class="col-6 col-md-4 col-lg-3">
                                <div
                                     class="card service-card bg-primary position-relative h-20 overflow-hidden border-0 shadow-sm">
                                    <img src="{{ asset('assets/images/services/waec_verification.png') }}"
                                         class="img-fluid w-100" />
                                    <a href="{{ route('user.scratch_cards.index') }}"
                                       class="stretched-link"></a>
                                </div>
                            </div>

                            <!-- WAEC GCE -->
                            <div class="col-6 col-md-4 col-lg-3">
                                <div
                                     class="card service-card bg-primary position-relative h-20 overflow-hidden border-0 shadow-sm">
                                    <img src="{{ asset('assets/images/services/waec_gce.png') }}"
                                         class="img-fluid w-100" />
                                    <a href="{{ route('user.scratch_cards.index') }}"
                                       class="stretched-link"></a>
                                </div>
                            </div>

                            <!-- WAEC Result Checker -->
                            <div class="col-6 col-md-4 col-lg-3">
                                <div
                                     class="card service-card h-100 bg-primary position-relative overflow-hidden border-0 shadow-sm">
                                    <img src="{{ asset('assets/images/services/waec_result_checker.png') }}"
                                         class="img-fluid w-100" />
                                    <a href="{{ route('user.scratch_cards.index') }}"
                                       class="stretched-link"></a>
                                </div>
                            </div>

                            <!-- Verification Services -->
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card service-card h-100 bg-primary border-0 shadow-sm">
                                    <div class="card-body p-3 text-center">
                                        <div class="icon-box-media bg-light text-primary rounded-circle mx-auto mb-3"
                                             style="width: 60px; height: 60px;">
                                            <i class="bi bi-fingerprint fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold text-light mb-2">Verify NIN</h6>
                                        <a href="{{ route('user.verify-nin') }}"
                                           class="stretched-link"></a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card service-card h-100 bg-primary border-0 shadow-sm">
                                    <div class="card-body p-3 text-center">
                                        <div class="icon-box-media bg-light text-primary rounded-circle mx-auto mb-3"
                                             style="width: 60px; height: 60px;">
                                            <i class="bi bi-fingerprint fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold text-light mb-2">Verify NIN V2 </h6>
                                        <a href="{{ route('user.verify-nin2') }}"
                                           class="stretched-link"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card service-card h-100 bg-primary border-0 shadow-sm">
                                    <div class="card-body p-3 text-center">
                                        <div class="icon-box-media bg-light text-primary rounded-circle mx-auto mb-3"
                                             style="width: 60px; height: 60px;">
                                            <i class="bi bi-fingerprint fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold text-light mb-2">Verify NIN Demographic</h6>
                                        <a href="{{ route('user.verify-demo') }}"
                                           class="stretched-link"></a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card service-card h-100 bg-primary border-0 shadow-sm">
                                    <div class="card-body p-3 text-center">
                                        <div class="icon-box-media bg-light text-primary rounded-circle mx-auto mb-3"
                                             style="width: 60px; height: 60px;">
                                            <i class="bi bi-phone fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold text-light mb-2">Verify NIN Phone</h6>
                                        <a href="{{ route('user.verify-nin-phone') }}"
                                           class="stretched-link"></a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card service-card h-100 bg-primary border-0 shadow-sm">
                                    <div class="card-body p-3 text-center">
                                        <div class="icon-box-media bg-light text-primary rounded-circle mx-auto mb-3"
                                             style="width: 60px; height: 60px;">
                                            <i class="bi bi-search fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold text-light mb-2">IPE</h6>
                                        <a href="{{ route('user.ipe') }}"
                                           class="stretched-link"></a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card service-card h-100 bg-primary border-0 shadow-sm">
                                    <div class="card-body p-3 text-center">
                                        <div class="icon-box-media bg-light text-primary rounded-circle mx-auto mb-3"
                                             style="width: 60px; height: 60px;">
                                            <i class="bi bi-fingerprint fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold text-light mb-2">Verify BVN</h6>
                                        <a href="{{ route('user.verify-bvn') }}"
                                           class="stretched-link"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card service-card h-100 bg-primary border-0 shadow-sm">
                                    <div class="card-body p-3 text-center">
                                        <div class="icon-box-media bg-light text-primary rounded-circle mx-auto mb-3"
                                             style="width: 60px; height: 60px;">
                                            <i class="bi bi-search fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold text-light mb-2">Instant BVN Search</h6>
                                        <a href="{{ route('user.verify-bvn2') }}"
                                           class="stretched-link"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card service-card h-100 bg-primary border-0 shadow-sm">
                                    <div class="card-body p-3 text-center">
                                        <div class="icon-box-media bg-light text-primary rounded-circle mx-auto mb-3"
                                             style="width: 60px; height: 60px;">
                                            <i class="bi bi-search fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold text-light mb-2">BVN Phone Search</h6>
                                        <a href="{{ route('user.bvn-phone-search') }}"
                                           class="stretched-link"></a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card service-card h-100 bg-primary border-0 shadow-sm">
                                    <div class="card-body p-3 text-center">
                                        <div class="icon-box-media bg-light text-primary rounded-circle mx-auto mb-3"
                                             style="width: 60px; height: 60px;">
                                            <i class="bi bi-telephone fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold text-light mb-2">Buy Airtime</h6>
                                        <a href="{{ route('user.airtime') }}"
                                           class="stretched-link"></a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card service-card h-100 bg-primary border-0 shadow-sm">
                                    <div class="card-body p-3 text-center">
                                        <div class="icon-box-media bg-light text-primary rounded-circle mx-auto mb-3"
                                             style="width: 60px; height: 60px;">
                                            <i class="bi bi-wifi fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold text-light mb-2">Buy Data</h6>
                                        <a href="{{ route('user.data') }}"
                                           class="stretched-link"></a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card service-card h-100 bg-primary border-0 shadow-sm">
                                    <div class="card-body p-3 text-center">
                                        <div class="icon-box-media bg-light text-primary rounded-circle mx-auto mb-3"
                                             style="width: 60px; height: 60px;">
                                            <i class="bi bi-search fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold text-light mb-2">Personalize</h6>
                                        <a href="{{ route('user.personalize-nin') }}"
                                           class="stretched-link"></a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card service-card h-100 bg-primary border-0 shadow-sm">
                                    <div class="card-body p-3 text-center">
                                        <div class="icon-box-media bg-light text-primary rounded-circle mx-auto mb-3"
                                             style="width: 60px; height: 60px;">
                                            <i class="bi bi-person-plus fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold text-light mb-2">BVN Enrollment</h6>
                                        <a href="{{ route('user.bvn-enrollment') }}"
                                           class="stretched-link"></a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card service-card h-100 bg-primary border-0 shadow-sm">
                                    <div class="card-body p-3 text-center">
                                        <div class="icon-box-media bg-light text-primary rounded-circle mx-auto mb-3"
                                             style="width: 60px; height: 60px;">
                                            <i class="bi bi-tools fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold text-light mb-2">NIN Validation</h6>
                                        <a href="{{ route('user.nin.services') }}"
                                           class="stretched-link"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card service-card h-100 bg-primary border-0 shadow-sm">
                                    <div class="card-body p-3 text-center">
                                        <div class="icon-box-media bg-light text-primary rounded-circle mx-auto mb-3"
                                             style="width: 60px; height: 60px;">
                                            <i class="bi bi-pencil fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold text-light mb-2">NIN Modification</h6>
                                        <a href="{{ route('user.nin.mod') }}"
                                           class="stretched-link"></a>
                                    </div>
                                </div>
                            </div>

                        </div> --}}
                        <div class="row g-4">

                            <div class="col-12 mt-4">
                                <h5 class="category-title">NIN Verification Services</h5>
                            </div>

                            @php
                                $ninServices = [
                                    ['icon' => 'bi-fingerprint', 'label' => 'Verify NIN', 'route' => 'user.verify-nin'],
                                    [
                                        'icon' => 'bi-fingerprint',
                                        'label' => 'Verify NIN V2',
                                        'route' => 'user.verify-nin2',
                                    ],
                                    [
                                        'icon' => 'bi-fingerprint',
                                        'label' => 'Verify Demographic',
                                        'route' => 'user.verify-demo',
                                    ],
                                    [
                                        'icon' => 'bi-phone',
                                        'label' => 'Verify NIN Phone',
                                        'route' => 'user.verify-nin-phone',
                                    ],
                                ];
                            @endphp

                            @foreach ($ninServices as $item)
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="card service-card h-100 bg-primary border-0 shadow-sm">
                                        <div class="card-body p-3 text-center">
                                            <div class="icon-box-media bg-light text-primary rounded-circle mx-auto mb-3"
                                                 style="width: 60px; height: 60px;">
                                                <i class="bi {{ $item['icon'] }} fs-4"></i>
                                            </div>
                                            <h6 class="fw-bold text-light mb-2">{{ $item['label'] }}</h6>
                                            <a href="{{ route($item['route']) }}"
                                               class="stretched-link"></a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <!-- ============================
                                                            <!-- ============================
                                             CATEGORY 1: EXAMINATION PINS
                                        ============================== -->
                            <div class="col-12">
                                <h5 class="category-title">Examination PIN Services</h5>
                            </div>

                            @php
                                $examPins = [
                                    ['img' => 'nibais.png', 'route' => 'user.scratch_cards.index'],
                                    ['img' => 'jamb.png', 'route' => 'user.scratch_cards.index'],
                                    // ['img' => 'nabteb_alevel.png', 'route' => 'user.scratch_cards.index'],
                                    // ['img' => 'nabteb_olevel.png', 'route' => 'user.scratch_cards.index'],

                                    ['img' => 'nabteb_result.png', 'route' => 'user.scratch_cards.index'],
                                    ['img' => 'neco_token.png', 'route' => 'user.scratch_cards.index'],
                                    ['img' => 'waec_verification.png', 'route' => 'user.scratch_cards.index'],
                                    ['img' => 'waec_gce.png', 'route' => 'user.scratch_cards.index'],
                                    ['img' => 'waec_result_checker.png', 'route' => 'user.scratch_cards.index'],
                                ];
                            @endphp

                            @foreach ($examPins as $item)
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div
                                         class="card service-card bg-primary position-relative overflow-hidden border-0 shadow-sm">
                                        <img src="{{ asset('assets/images/services/' . $item['img']) }}"
                                             class="img-fluid w-100" />
                                        <a href="{{ route($item['route']) }}"
                                           class="stretched-link"></a>
                                    </div>
                                </div>
                            @endforeach

                            <!-- ================================

                                             CATEGORY 3: BVN SERVICES
                                        ============================ -->
                            <div class="col-12 mt-4">
                                <h5 class="category-title">BVN Services</h5>
                            </div>

                            @php
                                $bvnServices = [
                                    ['icon' => 'bi-fingerprint', 'label' => 'Verify BVN', 'route' => 'user.verify-bvn'],
                                    [
                                        'icon' => 'bi-search',
                                        'label' => 'Instant BVN Search',
                                        'route' => 'user.verify-bvn2',
                                    ],
                                    [
                                        'icon' => 'bi-search',
                                        'label' => 'BVN Phone Search',
                                        'route' => 'user.bvn-phone-search',
                                    ],
                                    [
                                        'icon' => 'bi-person-plus',
                                        'label' => 'BVN Enrollment',
                                        'route' => 'user.bvn-enrollment',
                                    ],
                                ];
                            @endphp

                            @foreach ($bvnServices as $item)
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="card service-card h-100 bg-primary border-0 shadow-sm">
                                        <div class="card-body p-3 text-center">
                                            <div class="icon-box-media bg-light text-primary rounded-circle mx-auto mb-3"
                                                 style="width: 60px; height: 60px;">
                                                <i class="bi {{ $item['icon'] }} fs-4"></i>
                                            </div>
                                            <h6 class="fw-bold text-light mb-2">{{ $item['label'] }}</h6>
                                            <a href="{{ route($item['route']) }}"
                                               class="stretched-link"></a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <!-- ============================
                                             CATEGORY 4: OTHER SERVICES
                                        ============================ -->
                            <div class="col-12 mt-4">
                                <h5 class="category-title">Other Services</h5>
                            </div>

                            @php
                                $otherServices = [
                                    ['icon' => 'bi-search', 'label' => 'IPE', 'route' => 'user.ipe'],
                                    // ['icon' => 'bi-telephone', 'label' => 'Buy Airtime', 'route' => 'user.airtime'],
                                    // ['icon' => 'bi-wifi', 'label' => 'Buy Data', 'route' => 'user.data'],
                                    [
                                        'icon' => 'bi-search',
                                        'label' => 'Personalize NIN',
                                        'route' => 'user.personalize-nin',
                                    ],
                                    ['icon' => 'bi-tools', 'label' => 'NIN Validation', 'route' => 'user.nin.services'],
                                    ['icon' => 'bi-pencil', 'label' => 'NIN Modification', 'route' => 'user.nin.mod'],
                                    [
                                        'icon' => 'bi-x-circle-fill',
                                        'label' => 'Suspended NIN',
                                        'route' => 'user.suspended-nin.form',
                                    ],
                                ];
                            @endphp

                            @foreach ($otherServices as $item)
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="card service-card h-100 bg-primary border-0 shadow-sm">
                                        <div class="card-body p-3 text-center">
                                            <div class="icon-box-media bg-light text-primary rounded-circle mx-auto mb-3"
                                                 style="width: 60px; height: 60px;">
                                                <i class="bi {{ $item['icon'] }} fs-4"></i>
                                            </div>
                                            <h6 class="fw-bold text-light mb-2">{{ $item['label'] }}</h6>
                                            <a href="{{ route($item['route']) }}"
                                               class="stretched-link"></a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        </div>

                    </div>
                </div>

                <!-- Right side column for transaction table -->
                <div class="col-lg-12 stretch-card mt-">
                    <div class="container py-3"
                         style="max-width: 100%">
                        <h4 class="fw-light mb-4 text-center">Recent Transactions</h4>
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="table-responsive">
                                    @php
                                        $transactions = auth()->user()->transactions()->latest()->paginate(10);
                                        $serialNumber =
                                            ($transactions->currentPage() - 1) * $transactions->perPage() + 1;
                                    @endphp

                                    @forelse ($transactions as $data)
                                        @if ($loop->first)
                                            <table class="table text-nowrap"
                                                   style="background: #fafafc !important;">
                                                <thead>
                                                    <tr class="table-primary">
                                                        <th width="5%">ID</th>
                                                        <th>Reference No.</th>
                                                        <th>Service Type</th>
                                                        <th>Description</th>
                                                        <th>Amount</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Receipt</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                        @endif

                                        <tr>
                                            <td>{{ $serialNumber++ }}</td>
                                            <td>
                                                <a target="_blank"
                                                   href="{{ route('user.reciept', $data->referenceId) }}">
                                                    {{ strtoupper($data->referenceId) }}
                                                </a>
                                            </td>
                                            <td>{{ $data->service_type }}</td>
                                            <td>{{ $data->service_description }}</td>
                                            <td>&#8358;{{ number_format($data->amount, 2) }}</td>
                                            <td class="text-center">
                                                <span
                                                      class="badge {{ $data->status == 'Approved' ? 'bg-success' : ($data->status == 'Rejected' ? 'bg-danger' : 'bg-warning') }}">
                                                    {{ strtoupper($data->status) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a target="_blank"
                                                   href="{{ route('user.reciept', $data->referenceId) }}"
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-download"></i> Download
                                                </a>
                                            </td>
                                        </tr>

                                        @if ($loop->last)
                                            </tbody>
                                            </table>

                                            <div class="d-flex justify-content-center mt-3">
                                                {{ $transactions->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
                                            </div>
                                        @endif
                                    @empty
                                        <div class="text-center">
                                            <p class="fw-semibold fs-15 mt-2">No Transaction Available!</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div>

            <div class="modal fade"
                 id="walletModal"
                 tabindex="-1"
                 aria-labelledby="walletModalModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"
                                id="walletModalLabel">Fund Wallet</h5>
                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <small class="fw-semibold">Fund your wallet instantly by depositing
                                into the virtual account number</small>
                            <ul class="list-unstyled virtual-account-list mb-0 mt-3">
                                @if (auth()->user()->virtualAccount != null && count(auth()->user()->virtualAccount) > 0)
                                    @foreach (auth()->user()->virtualAccount as $data)
                                        <li class="account-item mb-3 p-2">
                                            <div class="d-flex align-items-start">
                                                <div class="bank-logo me-3">
                                                    <img src="{{ asset('assets/images/' . strtolower(str_replace(' ', '', $data->bankName)) . '.png') }}"
                                                         alt="{{ $data->bankName }} logo">
                                                </div>
                                                <div class="flex-fill">
                                                    <p class="account-name mb-1">{{ $data->accountName }}</p>
                                                    <span class="account-number d-block">{{ $data->accountNo }}</span>
                                                    <small class="bank-name text-muted">{{ $data->bankName }}</small>
                                                </div>
                                                <div class="copy-btn-wrap ms-auto">
                                                    <button class="btn btn-outline-secondary btn-sm copy-account-number"
                                                            data-account="{{ $data->accountNo }}">
                                                        Copy
                                                    </button>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                @else
                                    <div class="text-center p-4 border-0 rounded-4 bg-light shadow-sm">
                                        <div class="mb-3">
                                            <i class="mdi mdi-bank-plus text-primary mdi-48px"></i>
                                        </div>
                                        <h6 class="fw-bold mb-2">Setup Instant Funding</h6>
                                        <p class="text-muted small mb-4">
                                            To enable instant wallet funding, we need to generate a personal virtual account for you. 
                                            Please provide your 11-digit BVN below to get started.
                                        </p>
                                        
                                        <form id="verify" method="POST" action="{{ route('user.verify-user') }}">
                                            @csrf
                                            <div class="mb-3">
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white border-end-0">
                                                        <i class="mdi mdi-numeric"></i>
                                                    </span>
                                                    <input type="text" id="bvn" name="bvn" 
                                                        class="form-control form-control-lg border-start-0 text-center fw-bold" 
                                                        placeholder="Enter 11-digit BVN" 
                                                        maxlength="11" 
                                                        pattern="\d{11}"
                                                        required>
                                                </div>
                                            </div>
                                            <button type="submit" id="submit" class="btn btn-primary btn-lg w-100 rounded-pill shadow-sm">
                                                <i class="mdi mdi-plus-circle-outline me-1"></i> Generate Virtual Account
                                            </button>
                                        </form>

                                        @if($errors->any() || session('error'))
                                            <div class="alert alert-danger mt-3 small border-0 shadow-sm text-start">
                                                <i class="mdi mdi-alert-circle-outline me-1"></i> 
                                                {{ session('error') ?: 'Please ensure you entered a valid 11-digit BVN.' }}
                                            </div>
                                        @endif
                                        
                                        <div class="mt-4 pt-2 border-top">
                                            <p class="text-muted xx-small mb-0">
                                                <i class="mdi mdi-shield-check-outline me-1"></i> 
                                                Your BVN is used strictly for identity verification as required by CBN.
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </ul>

                            <hr>
                            <center>
                                <a style="text-decoration:none"
                                   class="mb-2"
                                   href="{{ route('user.support') }}">
                                    <small class="fw-semibol text-danger">If your funds is not
                                        received within 30mins.
                                        Please Contact Support
                                        <i class="mdi mdi-headphones mdi-12px"
                                           style="font-size:24px"></i>
                                    </small> </a>

                                <a style="text-decoration:none"
                                   href="{{ route('user.wallet') }}">
                                    <h4 class="fw-semibol text-danger">Go to wallet
                                        <i class="mdi mdi-wallet-outline mdi-36px"
                                           style="font-size:24px"></i>
                                    </h4>
                                </a>
                            </center>

                        </div>
                    </div>
                </div>
            </div>
        @endsection
        @push('scripts')
            <script>
                @if (session('error') || $errors->any())
                    const walletModal = new bootstrap.Modal(document.getElementById('walletModal'));
                    walletModal.show();
                @endif

                document.addEventListener('DOMContentLoaded', function() {
                    const form = document.getElementById('verify');
                    const submitButton = document.getElementById('submit');

                    if (form && submitButton) {
                        form.addEventListener('submit', function() {
                            submitButton.disabled = true;
                            submitButton.innerText = 'Verifying ...';
                        });
                    }
                });


                document.querySelectorAll('.copy-account-number').forEach(button => {
                    button.addEventListener('click', function() {
                        const acctNo = this.getAttribute('data-account');
                        navigator.clipboard.writeText(acctNo);
                        this.innerText = 'Copied!';
                        setTimeout(() => {
                            this.innerText = 'Copy';
                        }, 2000);
                    });
                });
            </script>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const data = @json($depositChartData);
                    const labels = Object.keys(data);
                    const values = Object.values(data);

                    const ctx = document.getElementById('depositBreakdownChart');
                    if (ctx) {
                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Deposits Breakdown',
                                    data: values,
                                    backgroundColor: [
                                        'rgba(25, 135, 84, 0.7)',
                                        'rgba(255, 193, 7, 0.7)',
                                        'rgba(220, 53, 69, 0.7)'
                                    ],
                                    borderColor: [
                                        'rgba(25, 135, 84, 1)',
                                        'rgba(255, 193, 7, 1)',
                                        'rgba(220, 53, 69, 1)'
                                    ],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: context =>
                                                `${context.label}: ₦${context.parsed.toLocaleString()}`
                                        }
                                    }
                                }
                            }
                        });
                    }
                });
            </script>
            <script>
                const ctx = document.getElementById('fundingChart').getContext('2d');

                const data = {
                    labels: @json($topFunders->pluck('name')), // user names
                    datasets: [{
                        label: 'Top 5 Funders Today',
                        data: @json($topFunders->pluck('total_funding')), // funding amounts
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 206, 86, 0.6)',
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(153, 102, 255, 0.6)'
                        ],
                        borderColor: '#fff',
                        borderWidth: 1,
                    }]
                };

                new Chart(ctx, {
                    type: 'bar',
                    data: data,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let index = context.dataIndex;
                                        let email = @json($topFunders->pluck('email'))[index];
                                        let amount = context.formattedValue;
                                        return email + ': ₦' + amount;
                                    }
                                }
                            },
                            title: {
                                display: true,
                                text: 'Top 5 Funders for Today'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₦' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }

                });
            </script>
        @endpush
