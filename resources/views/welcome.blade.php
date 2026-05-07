<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NIN TRUST - Premium Identity & Digital Services Platform</title>
    <meta name="description"
        content="NIN TRUST provides professional NIN verification, BVN services, utility bill payments, and educational pins. Fast, secure, and reliable digital agency solutions.">
    <meta name="keywords"
        content="NIN verification, BVN search, utility bills, JAMB pins, CAC registration, Nigeria digital services">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ config('app.url') }}">
    <meta property="og:title" content="NIN TRUST - Secure Identity & Digital Services">
    <meta property="og:description"
        content="Professional verification and agency services. Instant NIN, BVN, and Utility payments.">
    <meta property="og:image" content="{{ asset('assets/images/img/logo.png') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ config('app.url') }}">
    <meta property="twitter:title" content="NIN TRUST - Secure Identity & Digital Services">
    <meta property="twitter:description"
        content="Professional verification and agency services. Instant NIN, BVN, and Utility payments.">
    <meta property="twitter:image" content="{{ asset('assets/images/img/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('assets/images/img/logo.png') }}" type="image">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            scroll-behavior: smooth;
        }

        .reveal {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease-out;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .stat-icon {
            transition: transform 0.3s ease;
        }

        #loader-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.95);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out;
        }

        #loader-wrapper.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .loader {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #082851;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .brand-color {
            color: #082851;
        }

        .brand-bg {
            background-color: #082851;
        }

        .brand-border {
            border-bottom: 2px solid #082851;
        }

        .brand-gradient {
            background: linear-gradient(135deg, #08285133 0%, #ffffff 50%, #08285133 100%);
        }

        a[href^="mailto:"]:hover {
            color: #082851;
        }

        .hover\:brand-bg:hover {
            background-color: #082851;
        }
    </style>
</head>

<body class="brand-gradient text-gray-800 antialiased">

    <!-- Loader -->
    <div id="loader-wrapper">
        <div class="loader"></div>
    </div>

    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex-shrink-0 flex items-center">
                    <img src="{{ asset('assets/images/img/logo.png') }}" alt="NIN TRUST Logo" class="h-8 w-auto">
                    <span class="ml-2 text-xl font-semibold text-gray-700">NIN TRUST</span>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    <a href="#" class="text-gray-900 brand-border px-1 pt-1 text-sm font-medium">Home</a>
                    <a href="#services"
                        class="text-gray-600 hover:text-gray-900 px-1 pt-1 text-sm font-medium">Services</a>
                    <a href="#contact"
                        class="text-gray-600 hover:text-gray-900 px-1 pt-1 text-sm font-medium">Contact</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="overflow-hidden">
        <!-- Hero Section -->
        <section class="relative pt-24 pb-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-12 lg:gap-16 items-center">
                    <div class="lg:col-span-6 text-center lg:text-left">
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-gray-900">
                            Secure Verification Services by <span class="brand-color">NIN TRUST</span>
                        </h1>
                        <p class="mt-4 text-lg sm:text-xl text-gray-600">
                            Professional Verification and agency services. Fast, reliable, and secure.
                        </p>
                        <div class="mt-8 flex gap-4 justify-center lg:justify-start">
                            <a href="{{ route('auth.login') }}"
                                class="brand-bg text-white px-6 py-3 rounded-lg font-medium shadow-lg hover:opacity-90 transition-all">
                                Login Portal
                            </a>
                            <a href="{{ route('auth.register') }}"
                                class="border-2 border-[#082851] px-6 py-3 rounded-lg font-medium hover:bg-[#08285111] hover:text-white transition-all">
                                Register Now
                            </a>
                        </div>
                    </div>
                    <div class="mt-6 lg:mt-0 lg:col-span-6 flex justify-center">
                        <img src="{{ asset('assets/images/img/verification-hero.png') }}" alt="Verification Services"
                            class="rounded-xl shadow-2xl w-full max-w-2xl">
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-20 bg-white reveal border-y border-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div class="text-center stat-card">
                        <div class="brand-color text-4xl font-bold mb-2">99.9%</div>
                        <p class="text-gray-500 font-medium">Uptime Guarantee</p>
                    </div>
                    <div class="text-center stat-card">
                        <div class="brand-color text-4xl font-bold mb-2">15k+</div>
                        <p class="text-gray-500 font-medium">Daily Verifications</p>
                    </div>
                    <div class="text-center stat-card">
                        <div class="brand-color text-4xl font-bold mb-2">24/7</div>
                        <p class="text-gray-500 font-medium">Support Access</p>
                    </div>
                    <div class="text-center stat-card">
                        <div class="brand-color text-4xl font-bold mb-2">Inst.</div>
                        <p class="text-gray-500 font-medium">Fast Processing</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" class="py-16 sm:py-24 bg-white/50 backdrop-blur-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-sm brand-color font-semibold uppercase tracking-wide">Professional Solutions</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                        A Digital Ecosystem You Can Trust
                    </p>
                    <p class="mt-4 text-gray-600 max-w-2xl mx-auto">
                        We leverage advanced API integrations to provide you with direct access to essential identity
                        and financial data services.
                    </p>
                </div>

                <div class="mt-12 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- NIN Service -->
                    <div
                        class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:brand-bg hover:text-white group border border-gray-100">
                        <div
                            class="brand-bg w-fit p-3 rounded-lg group-hover:bg-white group-hover:text-[#082851] transition-colors">
                            <svg class="w-8 h-8 text-white group-hover:text-[#082851]" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-xl font-semibold group-hover:text-white">NIN Verification</h3>
                        <p class="mt-2 text-gray-600 group-hover:text-gray-200">Instant National Identity Number
                            verification with multiple lookup options and printable slips.</p>
                    </div>

                    <!-- BVN Service -->
                    <div
                        class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:brand-bg hover:text-white group border border-gray-100">
                        <div
                            class="brand-bg w-fit p-3 rounded-lg group-hover:bg-white group-hover:text-[#082851] transition-colors">
                            <svg class="w-8 h-8 text-white group-hover:text-[#082851]" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-xl font-semibold group-hover:text-white">BVN Services</h3>
                        <p class="mt-2 text-gray-600 group-hover:text-gray-200">Bank Verification Number validation and
                            official document generation for seamless banking.</p>
                    </div>

                    <!-- Utility Bills -->
                    <div
                        class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:brand-bg hover:text-white group border border-gray-100">
                        <div
                            class="brand-bg w-fit p-3 rounded-lg group-hover:bg-white group-hover:text-[#082851] transition-colors">
                            <svg class="w-8 h-8 text-white group-hover:text-[#082851]" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-xl font-semibold group-hover:text-white">Utility Payments</h3>
                        <p class="mt-2 text-gray-600 group-hover:text-gray-200">Fast and secure payment for electricity,
                            water, and other essential utility bills nationwide.</p>
                    </div>

                    <!-- Airtime & Data -->
                    <div
                        class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:brand-bg hover:text-white group border border-gray-100">
                        <div
                            class="brand-bg w-fit p-3 rounded-lg group-hover:bg-white group-hover:text-[#082851] transition-colors">
                            <svg class="w-8 h-8 text-white group-hover:text-[#082851]" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-xl font-semibold group-hover:text-white">Airtime & Data</h3>
                        <p class="mt-2 text-gray-600 group-hover:text-gray-200">Instant top-up for all major networks.
                            Competitive rates and lightning-fast delivery.</p>
                    </div>

                    <!-- Education Pins -->
                    <div
                        class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:brand-bg hover:text-white group border border-gray-100">
                        <div
                            class="brand-bg w-fit p-3 rounded-lg group-hover:bg-white group-hover:text-[#082851] transition-colors">
                            <svg class="w-8 h-8 text-white group-hover:text-[#082851]" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-xl font-semibold group-hover:text-white">Education Pins</h3>
                        <p class="mt-2 text-gray-600 group-hover:text-gray-200">Get your JAMB, WAEC, and NECO pins
                            instantly. Hassle-free educational services.</p>
                    </div>

                    <!-- Document Service -->
                    <div
                        class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:brand-bg hover:text-white group border border-gray-100">
                        <div
                            class="brand-bg w-fit p-3 rounded-lg group-hover:bg-white group-hover:text-[#082851] transition-colors">
                            <svg class="w-8 h-8 text-white group-hover:text-[#082851]" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-xl font-semibold group-hover:text-white">Agency Banking</h3>
                        <p class="mt-2 text-gray-600 group-hover:text-gray-200">Become a BVN enrolment agent. Enroll
                            customers, earn commissions, and grow your business.</p>
                    </div>

                    <!-- CAC Registration -->
                    <div
                        class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:brand-bg hover:text-white group border border-gray-100">
                        <div
                            class="brand-bg w-fit p-3 rounded-lg group-hover:bg-white group-hover:text-[#082851] transition-colors">
                            <svg class="w-8 h-8 text-white group-hover:text-[#082851]" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-xl font-semibold group-hover:text-white">Business Registration</h3>
                        <p class="mt-2 text-gray-600 group-hover:text-gray-200">Streamlined CAC registration services
                            for small and large businesses. Professional support.</p>
                    </div>

                    <!-- More Services -->
                    <div
                        class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:brand-bg hover:text-white group border border-gray-100">
                        <div
                            class="brand-bg w-fit p-3 rounded-lg group-hover:bg-white group-hover:text-[#082851] transition-colors">
                            <svg class="w-8 h-8 text-white group-hover:text-[#082851]" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-xl font-semibold group-hover:text-white">And Much More</h3>
                        <p class="mt-2 text-gray-600 group-hover:text-gray-200">From insurance to custom agency
                            solutions, we provide a wide range of essential digital services.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Ecosystem & Security -->
        <section class="py-20 bg-gray-50 reveal">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:flex lg:items-center lg:gap-16">
                    <div class="lg:w-1/2">
                        <h2 class="text-3xl font-bold text-gray-900 mb-6">Security & Reliability First</h2>
                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div class="bg-green-100 p-2 rounded-full mt-1">
                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="font-bold text-gray-900">End-to-End Encryption</h4>
                                    <p class="text-gray-600">All data transmissions are secured using bank-grade TLS 1.3
                                        encryption protocols.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="bg-green-100 p-2 rounded-full mt-1">
                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="font-bold text-gray-900">Privacy Compliant</h4>
                                    <p class="text-gray-600">We respect data privacy laws and ensure all verifications
                                        are conducted under strict regulatory guidelines.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="bg-green-100 p-2 rounded-full mt-1">
                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="font-bold text-gray-900">Redundant Infrastructure</h4>
                                    <p class="text-gray-600">Our systems are built on high-availability cloud servers to
                                        ensure zero downtime for your business.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="lg:w-1/2 mt-12 lg:mt-0">
                        <div class="grid grid-cols-2 gap-4">
                            <div
                                class="glass-card p-8 rounded-2xl text-center border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                                <img src="{{ asset('assets/images/img/nimc.png') }}" alt="NIMC Integrated"
                                    class="h-12 mx-auto grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition-all duration-300">
                                <p class="mt-4 text-xs font-semibold text-gray-400 uppercase tracking-widest">NIN
                                    Integrated</p>
                            </div>
                            <div
                                class="glass-card p-8 rounded-2xl text-center border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                                <div
                                    class="h-12 flex items-center justify-center font-black text-2xl text-gray-300 italic">
                                    BVN</div>
                                <p class="mt-4 text-xs font-semibold text-gray-400 uppercase tracking-widest">Verified
                                    API</p>
                            </div>
                            <div
                                class="glass-card p-8 rounded-2xl text-center border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                                <div
                                    class="h-12 flex items-center justify-center font-black text-2xl text-gray-300 uppercase">
                                    Utility</div>
                                <p class="mt-4 text-xs font-semibold text-gray-400 uppercase tracking-widest">Global
                                    Payouts</p>
                            </div>
                            <div
                                class="glass-card p-8 rounded-2xl text-center border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                                <div class="h-12 flex items-center justify-center font-black text-2xl text-gray-300">CAC
                                </div>
                                <p class="mt-4 text-xs font-semibold text-gray-400 uppercase tracking-widest">Reg.
                                    Systems</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="py-16 sm:py-24 brand-gradient overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-sm brand-color font-semibold uppercase tracking-wide">Testimonials</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        Trusted by Thousands of Users
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Testimonial 1 -->
                    <div class="bg-white p-8 rounded-2xl shadow-xl border border-gray-100 relative">
                        <div class="absolute -top-4 left-8 brand-bg text-white p-2 rounded-lg">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M14.017 21L14.017 18C14.017 16.8954 14.9124 16 16.017 16H19.017C19.5693 16 20.017 15.5523 20.017 15V9C20.017 8.44772 19.5693 8 19.017 8H16.017C14.9124 8 14.017 7.10457 14.017 6V5C14.017 3.89543 14.9124 3 16.017 3H19.017C21.2261 3 23.017 4.79086 23.017 7V15C23.017 18.3137 20.3307 21 17.017 21H14.017ZM1.017 21L1.017 18C1.017 16.8954 1.91243 16 3.017 16H6.017C6.56928 16 7.017 15.5523 7.017 15V9C7.017 8.44772 6.56928 8 6.017 8H3.017C1.91243 8 1.017 7.10457 1.017 6V5C1.017 3.89543 1.91243 3 3.017 3H6.017C8.22614 3 10.017 4.79086 10.017 7V15C10.017 18.3137 7.33066 21 4.017 21H1.017Z" />
                            </svg>
                        </div>
                        <p class="text-gray-600 italic mb-6">"NIN TRUST has revolutionized how I handle identity
                            verifications for my agency. The speed and accuracy are unmatched in the industry."</p>
                        <div class="flex items-center">
                            <div
                                class="h-12 w-12 rounded-full brand-bg flex items-center justify-center text-white font-bold text-lg">
                                AS</div>
                            <div class="ml-4">
                                <h4 class="font-bold text-gray-900">Ahmed Saliu</h4>
                                <p class="text-sm text-gray-500">Agency Owner, Lagos</p>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 2 -->
                    <div class="bg-white p-8 rounded-2xl shadow-xl border border-gray-100 relative">
                        <div class="absolute -top-4 left-8 brand-bg text-white p-2 rounded-lg">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M14.017 21L14.017 18C14.017 16.8954 14.9124 16 16.017 16H19.017C19.5693 16 20.017 15.5523 20.017 15V9C20.017 8.44772 19.5693 8 19.017 8H16.017C14.9124 8 14.017 7.10457 14.017 6V5C14.017 3.89543 14.9124 3 16.017 3H19.017C21.2261 3 23.017 4.79086 23.017 7V15C23.017 18.3137 20.3307 21 17.017 21H14.017ZM1.017 21L1.017 18C1.017 16.8954 1.91243 16 3.017 16H6.017C6.56928 16 7.017 15.5523 7.017 15V9C7.017 8.44772 6.56928 8 6.017 8H3.017C1.91243 8 1.017 7.10457 1.017 6V5C1.017 3.89543 1.91243 3 3.017 3H6.017C8.22614 3 10.017 4.79086 10.017 7V15C10.017 18.3137 7.33066 21 4.017 21H1.017Z" />
                            </svg>
                        </div>
                        <p class="text-gray-600 italic mb-6">"The utility bill payment feature is so convenient. I no
                            longer have to visit physical offices for electricity tokens or data top-ups."</p>
                        <div class="flex items-center">
                            <div
                                class="h-12 w-12 rounded-full brand-bg flex items-center justify-center text-white font-bold text-lg">
                                CO</div>
                            <div class="ml-4">
                                <h4 class="font-bold text-gray-900">Chioma Okeke</h4>
                                <p class="text-sm text-gray-500">Business Consultant</p>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 3 -->
                    <div class="bg-white p-8 rounded-2xl shadow-xl border border-gray-100 relative">
                        <div class="absolute -top-4 left-8 brand-bg text-white p-2 rounded-lg">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M14.017 21L14.017 18C14.017 16.8954 14.9124 16 16.017 16H19.017C19.5693 16 20.017 15.5523 20.017 15V9C20.017 8.44772 19.5693 8 19.017 8H16.017C14.9124 8 14.017 7.10457 14.017 6V5C14.017 3.89543 14.9124 3 16.017 3H19.017C21.2261 3 23.017 4.79086 23.017 7V15C23.017 18.3137 20.3307 21 17.017 21H14.017ZM1.017 21L1.017 18C1.017 16.8954 1.91243 16 3.017 16H6.017C6.56928 16 7.017 15.5523 7.017 15V9C7.017 8.44772 6.56928 8 6.017 8H3.017C1.91243 8 1.017 7.10457 1.017 6V5C1.017 3.89543 1.91243 3 3.017 3H6.017C8.22614 3 10.017 4.79086 10.017 7V15C10.017 18.3137 7.33066 21 4.017 21H1.017Z" />
                            </svg>
                        </div>
                        <p class="text-gray-600 italic mb-6">"Excellent customer support! Whenever I have a question
                            about educational pins or BVN services, they respond instantly."</p>
                        <div class="flex items-center">
                            <div
                                class="h-12 w-12 rounded-full brand-bg flex items-center justify-center text-white font-bold text-lg">
                                MI</div>
                            <div class="ml-4">
                                <h4 class="font-bold text-gray-900">Musa Ibrahim</h4>
                                <p class="text-sm text-gray-500">Cyber Cafe Manager, Kano</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-16 sm:py-5 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-sm brand-color font-semibold uppercase tracking-wide">Get Support</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        Contact Our Team
                    </p>
                </div>

                <div class="mt-12 grid lg:grid-cols-2 gap-12">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Support Information</h3>
                            <div class="mt-4 space-y-2 text-gray-600">
                                <p>Email: <a href="mailto:support@nintrust.gov.ng"
                                        class="brand-color hover:underline">nintrust001@gmail.com</a></p>
                                <p>Phone: <a href="tel:+234700NINTRUST"
                                        class="brand-color hover:underline">XX-XXX-XXXX</a></p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div class="bg-gray-50 p-8 rounded-xl">
                        <form class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Full Name</label>
                                <input type="text" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-3">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email Address</label>
                                <input type="email" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-3">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Message</label>
                                <textarea rows="4" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-3"></textarea>
                            </div>
                            <button type="submit"
                                class="w-full brand-bg text-white py-3 rounded-md font-medium hover:opacity-90 transition-all">
                                Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-[#082851] text-white pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 pb-16 border-b border-white/10">
                <!-- Column 1: About -->
                <div>
                    <div class="flex items-center">
                        <img src="{{ asset('assets/images/img/logo.png') }}" alt="NIN TRUST Logo"
                            class="h-10 w-auto brightness-200">
                        <span class="ml-2 text-2xl font-bold tracking-tight">NIN TRUST</span>
                    </div>
                    <p class="mt-6 text-gray-300 leading-relaxed">
                        Nigeria's leading platform for secure identity verification, agency banking, and essential
                        digital services. Empowering businesses and individuals with trust and reliability.
                    </p>
                    <div class="mt-8 flex space-x-4">
                        <a href="#" class="bg-white/10 p-2 rounded-full hover:bg-white/20 transition-colors">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
                            </svg>
                        </a>
                        <a href="#" class="bg-white/10 p-2 rounded-full hover:bg-white/20 transition-colors">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4s1.791-4 4-4 4 1.791 4 4-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                            </svg>
                        </a>
                        <a href="#" class="bg-white/10 p-2 rounded-full hover:bg-white/20 transition-colors">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Column 2: Quick Links -->
                <div>
                    <h4
                        class="text-lg font-bold mb-6 relative pb-2 after:content-[''] after:absolute after:bottom-0 after:left-0 after:w-10 after:h-1 after:brand-bg">
                        Quick Links</h4>
                    <ul class="space-y-4 text-gray-300">
                        <li><a href="#" class="hover:text-white transition-colors flex items-center"><span
                                    class="mr-2">›</span> Home</a></li>
                        <li><a href="#services" class="hover:text-white transition-colors flex items-center"><span
                                    class="mr-2">›</span> Our Services</a></li>
                        <li><a href="#contact" class="hover:text-white transition-colors flex items-center"><span
                                    class="mr-2">›</span> Contact Support</a></li>
                        <li><a href="{{ route('auth.login') }}"
                                class="hover:text-white transition-colors flex items-center"><span class="mr-2">›</span>
                                User Login</a></li>
                        <li><a href="{{ route('auth.register') }}"
                                class="hover:text-white transition-colors flex items-center"><span class="mr-2">›</span>
                                Create Account</a></li>
                    </ul>
                </div>

                <!-- Column 3: Services -->
                <div>
                    <h4
                        class="text-lg font-bold mb-6 relative pb-2 after:content-[''] after:absolute after:bottom-0 after:left-0 after:w-10 after:h-1 after:brand-bg">
                        Our Services</h4>
                    <ul class="space-y-4 text-gray-300">
                        <li><a href="#" class="hover:text-white transition-colors">NIN Verification</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">BVN Services</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Utility Bill Payments</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Airtime & Data Topup</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Education Pins (JAMB/WAEC)</a></li>
                    </ul>
                </div>

                <!-- Column 4: Newsletter -->
                <div>
                    <h4
                        class="text-lg font-bold mb-6 relative pb-2 after:content-[''] after:absolute after:bottom-0 after:left-0 after:w-10 after:h-1 after:brand-bg">
                        Newsletter</h4>
                    <p class="text-gray-300 mb-6">Stay updated with our latest features and agency opportunities.</p>
                    <form class="flex">
                        <input type="email" placeholder="Email Address"
                            class="bg-white/10 border-none rounded-l-lg p-3 w-full focus:ring-1 focus:ring-white/30 text-white placeholder-gray-500">
                        <button type="submit" class="brand-bg px-4 rounded-r-lg hover:opacity-90 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Bottom Footer -->
            <div class="mt-10 flex flex-col md:flex-row justify-between items-center text-gray-400 text-sm">
                <p>&copy; {{ date('Y') }} NIN TRUST. All rights reserved. | Built for Trust.</p>
                <div class="mt-4 md:mt-0 flex space-x-6">
                    <a href="#" class="hover:text-white">Privacy Policy</a>
                    <a href="#" class="hover:text-white">Terms of Service</a>
                    <a href="#" class="hover:text-white">Refund Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        window.onload = function () {
            document.getElementById('loader-wrapper').classList.add('hidden');
            reveal();
        };

        function reveal() {
            var reveals = document.querySelectorAll(".reveal");
            for (var i = 0; i < reveals.length; i++) {
                var windowHeight = window.innerHeight;
                var elementTop = reveals[i].getBoundingClientRect().top;
                var elementVisible = 150;
                if (elementTop < windowHeight - elementVisible) {
                    reveals[i].classList.add("active");
                }
            }
        }

        window.addEventListener("scroll", reveal);
    </script>

</body>

</html>