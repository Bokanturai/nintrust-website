@extends('layouts.dashboard')

@section('title', 'Purchase Scratch Cards')

@push('styles')
    <style>
        .card-type-logos {
            display: flex;
            gap: 30px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .card-type-logos img {
            height: 80px;
            width: auto;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .card-type-logos img:hover {
            transform: scale(1.05);
        }

        .subtotal,
        #grand-total {
            font-weight: 600;
        }

        .table thead th {
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            table {
                font-size: 14px;
            }

            .quantity-input {
                max-width: 70px;
            }

            #purchase-btn {
                width: 100%;
                margin-top: 10px;
            }

            .card-type-logos img {
                height: 50px;
            }
        }

        .download-btn {
            background: #03214d;
            color: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
        }

        .download-btn:hover {
            background: #0d3064;
            color: #fff;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="mb-3 mt-1">
            <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
            <p class="mb-0">Easily buy and manage your exam scratch cards below.</p>
        </div>

        <div class="card custom-card shadow-sm">
            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="las la-check-circle me-1"></i> {{ session('success') }}
                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->has('quantities'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="las la-exclamation-circle me-1"></i> {{ $errors->first('quantities') }}
                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card-type-logos">
                    <img src="{{ asset('assets/images/pin.png') }}"
                         alt="WAEC/NECO/JAMB">
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-semibold mb-0">Available Scratch Cards</h4>
                    <small class="text-muted text-danger">Select quantities and click “Purchase Selected”</small>
                </div>

                <form action="{{ route('user.scratch_cards.purchase') }}"
                      method="POST">
                    @csrf

                    <div class="table-responsive mb-4">
                        <table class="table-striped table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Fee (₦)</th>
                                    <th>Available</th>
                                    <th>Quantity</th>
                                    <th>Subtotal (₦)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($availableCards as $card)
                                    @php $key = "{$card->type}_{$card->fee}"; @endphp
                                    <tr>
                                        <td>{{ strtoupper($card->type) }}</td>
                                        <td>₦{{ number_format($card->fee, 2) }}</td>
                                        <td>{{ $card->available_count }}</td>
                                        <td style="max-width:120px;">
                                            <input type="number"
                                                   name="quantities[{{ $key }}]"
                                                   min="0"
                                                   max="{{ $card->available_count }}"
                                                   class="form-control quantity-input"
                                                   data-fee="{{ $card->fee }}"
                                                   value="0"
                                                   {{ $card->available_count == 0 ? 'disabled' : '' }}>
                                        </td>
                                        <td class="subtotal text-muted">₦0.00</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5"
                                            class="text-muted py-4 text-center">
                                            <i class="las la-info-circle"></i> No cards available for purchase.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="4"
                                        class="text-end">Grand Total:</td>
                                    <td id="grand-total">₦0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="text-end">
                        <button type="submit"
                                class="btn btn-primary px-4"
                                id="purchase-btn"
                                disabled>
                            <i class="las la-shopping-cart me-1"></i> Purchase Selected
                        </button>
                    </div>
                </form>

                {{-- ✅ Purchased Cards --}}
                <hr class="my-5">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-semibold mb-0">My Purchased Cards</h5>
                    <small class="text-muted text-danger">Download and view your purchased cards below</small>
                </div>

                <div class="table-responsive">
                    <table class="table-bordered table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>RefNo.</th>
                                <th>Type</th>
                                <th>Fee</th>
                                <th>Purchased At</th>
                                <th>Download</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchasedCards as $card)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ strtoupper($card->refno ?? 'NA') }}</td>
                                    <td>{{ strtoupper($card->type) }}</td>
                                    <td>₦{{ number_format($card->fee, 2) }}</td>
                                    <td>{{ $card->purchased_at?->format('d M Y h:i A') }}</td>
                                    <td>
                                        <a href="{{ route('user.scratch_cards.download', $card->id) }}"
                                           class="download-btn">
                                            <i class="las la-download"></i> Download
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7"
                                        class="text-muted py-4 text-center">
                                        <i class="las la-info-circle"></i> You haven’t purchased any cards yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $purchasedCards->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInputs = document.querySelectorAll('.quantity-input');
            const grandTotalEl = document.getElementById('grand-total');
            const purchaseBtn = document.getElementById('purchase-btn');

            function formatCurrency(num) {
                return '₦' + num.toLocaleString('en-NG', {
                    minimumFractionDigits: 2
                });
            }

            function updateTotals() {
                let total = 0;
                let hasQty = false;

                quantityInputs.forEach(input => {
                    const fee = parseFloat(input.dataset.fee);
                    const qty = parseInt(input.value) || 0;
                    const subtotalCell = input.closest('tr').querySelector('.subtotal');
                    const subtotal = qty * fee;
                    subtotalCell.textContent = formatCurrency(subtotal);
                    total += subtotal;
                    if (qty > 0) hasQty = true;
                });

                grandTotalEl.textContent = formatCurrency(total);
                purchaseBtn.disabled = !hasQty;
            }

            quantityInputs.forEach(input => {
                input.addEventListener('input', updateTotals);
            });

            document.querySelector('form').addEventListener('submit', function(e) {
                const total = grandTotalEl.textContent;
                if (!confirm(`Confirm purchase of cards totaling ${total}?`)) {
                    e.preventDefault();
                }
            });

            updateTotals();
        });
    </script>
@endpush
