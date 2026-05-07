@extends('layouts.dashboard')

@section('title', 'Scratch Cards')
@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css"
          rel="stylesheet" />
    <style>
        .form-check .form-check-input {
            margin-left: 0;
        }

        @media (min-width: 992px) {
            form.row.gy-2.gx-2.align-items-end.mb-3 .form-label {
                margin-bottom: 0.25rem;
            }
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="mb-3 mt-1">
            <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
        </div>
        <div class="col-md-12 text-md-end mt-md-0 mb-2 mt-2 text-end">
            <a href="{{ route('admin.scratch_cards.create') }}"
               class="btn btn-primary btn-sm">
                <i class="las la-plus-circle me-1"></i> Create New
            </a>
        </div>

        <div class="col-lg-12 grid-margin d-flex flex-column">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="card-title">Scratch Cards</h5>
                        </div>
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show"
                                     role="alert">
                                    {!! session('success') !!}
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

                            <div class="col-xl-12 mb-3">
                                <div class="row">
                                    <div class="col-xxl-4 col-lg-4 col-md-4">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div>
                                                        <span
                                                              class="avatar avatar-md avatar-rounded bg-primary-transparent">
                                                            <i class="las la-tasks"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-fill ms-3">
                                                        <div
                                                             class="d-flex align-items-center justify-content-between flex-wrap">

                                                            <div>
                                                                <p class="text-muted mb-0">All </p>
                                                                <h4 class="fw-semibold mt-1">{{ $total }}</h4>
                                                            </div>
                                                            <div class="text-end">
                                                                <h6 class="fw-semibold text-dark mt-1">
                                                                    ₦ {{ $feeTotal }}
                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xxl-4 col-lg-4 col-md-4">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div>
                                                        <span
                                                              class="avatar avatar-md avatar-rounded bg-success-transparent">
                                                            <i class="las la-check-double"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-fill ms-3">
                                                        <div
                                                             class="d-flex align-items-center justify-content-between flex-wrap">
                                                            <div>
                                                                <p class="text-muted mb-0">Available</p>
                                                                <h4 class="fw-semibold mt-1">{{ $available }}</h4>
                                                            </div>
                                                            <div class="text-end">

                                                                <h6 class="fw-semibold text-success mt-1">₦
                                                                    {{ $feeAvailable }}</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xxl-4 col-lg-4 col-md-4">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div>
                                                        <span
                                                              class="avatar avatar-md avatar-rounded bg-warning-transparent">
                                                            <i class="las la-list-alt"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-fill ms-3">
                                                        <div
                                                             class="d-flex align-items-center justify-content-between flex-wrap">
                                                            <div>
                                                                <p class="text-muted mb-0">Purchased</p>
                                                                <h4 class="fw-semibold mt-1">{{ $purchased }}</h4>
                                                            </div>
                                                            <div class="text-end">
                                                                <h6 class="fw-semibold text-danger mt-1">
                                                                    ₦ {{ $feePurchased }}
                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <form method="GET"
                                  action="{{ route('admin.scratch_cards.index') }}"
                                  class="row gy-2 gx-2 align-items-end mb-3">

                                {{-- 🔍 Search --}}
                                <div class="col-12 col-md-4 col-lg-4">
                                    <label for="search"
                                           class="form-label fw-semibold small text-muted mb-0">Search</label>
                                    <input type="text"
                                           id="search"
                                           name="search"
                                           class="form-control"
                                           value="{{ request('search') }}"
                                           placeholder="Serial, Pin,Refno, Type...">
                                </div>

                                {{-- 📅 Date From --}}
                                <div class="col-6 col-md-2 col-lg-2">
                                    <label for="date_from"
                                           class="form-label fw-semibold small text-muted mb-0">From</label>
                                    <input type="date"
                                           id="date_from"
                                           name="date_from"
                                           class="form-control"
                                           value="{{ request('date_from') }}">
                                </div>

                                {{-- 📅 Date To --}}
                                <div class="col-6 col-md-2 col-lg-2">
                                    <label for="date_to"
                                           class="form-label fw-semibold small text-muted mb-0">To</label>
                                    <input type="date"
                                           id="date_to"
                                           name="date_to"
                                           class="form-control"
                                           value="{{ request('date_to') }}">
                                </div>

                                {{-- 🏷️ Type Filter --}}
                                <div class="col-12 col-md-4 col-lg-4">
                                    <label for="type"
                                           class="form-label fw-semibold small text-muted mb-0">Type</label>
                                    <select id="type"
                                            name="type"
                                            class="form-select text-dark">
                                        <option value="">All Types</option>
                                        @foreach ($types as $type)
                                            <option value="{{ $type }}"
                                                    {{ request('type') == $type ? 'selected' : '' }}>
                                                {{ ucfirst($type) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- 🔘 Buttons --}}
                                <div class="col-12 col-md-6 col-lg-6 d-flex gap-2">
                                    <button type="submit"
                                            class="btn btn-primary w-50 w-md-auto flex-fill">
                                        <i class="las la-filter me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.scratch_cards.index') }}"
                                       class="btn btn-secondary w-50 w-md-auto flex-fill">
                                        <i class="las la-sync me-1"></i> Reset
                                    </a>
                                </div>

                            </form>

                            <div class="table-responsive">
                                <table class="table text-nowrap"
                                       style="background:#fafafc !important">
                                    <thead>
                                        <tr>
                                            <th>SN</th>
                                            <th>Type</th>
                                            <th>Fee</th>
                                            <th>Serial No</th>
                                            <th>Pin</th>
                                            <th>status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cards as $card)
                                            <tr>
                                                <td> {{ $loop->iteration }}</td>
                                                <td>{{ $card->type }}</td>
                                                <td>₦ {{ $card->fee }}</td>
                                                <td>{{ $card->serial_number }}</td>
                                                <td>{{ $card->pin }}</td>
                                                <td>
                                                    @if ($card->status == 'available')
                                                        <span class="badge bg-success">Available</span>
                                                    @else
                                                        <div class="d-flex flex-column gap-1">
                                                            <span class="badge bg-danger">Purchased</span>

                                                            <span class="badge bg-primary">
                                                                <i class="bi bi-person-fill"></i>
                                                                {{ $card->user->name ?? 'N/A' }}
                                                            </span>

                                                            <span class="badge bg-dark">
                                                                <i class="bi bi-hash"></i>
                                                                {{ $card->refno ?? 'N/A' }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </td>

                                                <td>

                                                    <form method="POST"
                                                          action="{{ route('admin.scratch_cards.activate', $card) }}"
                                                          class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                                class="btn btn-sm {{ $card->active ? 'btn-danger' : 'btn-success' }}">
                                                            <i
                                                               class="bx {{ $card->active ? 'bx-user-x' : 'bx-user-check' }}"></i>
                                                            {{ $card->active ? 'Deactivate' : 'Activate' }}
                                                        </button>
                                                    </form>

                                                    <a href="{{ route('admin.scratch_cards.edit', $card) }}"
                                                       class="btn btn-warning btn-sm">
                                                        <i class="bx bx-edit"></i> Edit
                                                    </a>

                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center mt-3">
                                {{ $cards->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
@endpush
