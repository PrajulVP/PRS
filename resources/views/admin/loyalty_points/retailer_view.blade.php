@extends('layouts.admin')

@section('page-body')
    <style>
        /* PREMIUM 3D GOLD COIN (Synced with Header big-gold-coin) */
        .coin-wrapper {
            perspective: 1000px;
            padding: 5px;
            display: inline-block;
        }
        
        .big-gold-coin-dashboard {
            width: 70px;
            height: 70px;
            background: radial-gradient(ellipse at center, #ffd700 0%, #daa520 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 
                inset 0 0 0 4px #d4af37,
                0 4px 10px rgba(0,0,0,0.1);
            animation: coin-shine-flip-dashboard 7s infinite ease-in-out;
            position: relative;
            transform-style: preserve-3d;
            flex-shrink: 0;
            margin: 0 auto 20px auto;
        }

        .big-gold-coin-dashboard::before {
            content: '';
            position: absolute;
            inset: 8px;
            border: 2px dashed rgba(184, 134, 11, 0.5);
            border-radius: 50%;
        }

        .coin-inner-dashboard {
            font-size: 35px;
            color: #ffffff;
            text-shadow: 2px 2px 4px rgba(184, 134, 11, 0.8);
            transform: translateZ(1px);
        }

        @keyframes coin-flip-dashboard {
            0%, 90% { transform: rotateY(0deg); }
            100% { transform: rotateY(360deg); }
        }

        @keyframes coin-shine-flip-dashboard {
            0%, 75% { transform: rotateY(0deg); filter: brightness(1) drop-shadow(0 0 3px rgba(184, 134, 11, 0.3)); }
            85% { filter: brightness(1.8) drop-shadow(0 0 15px rgba(212, 175, 55, 0.8)); transform: rotateY(0deg); }
            100% { transform: rotateY(360deg); filter: brightness(1) drop-shadow(0 0 3px rgba(184, 134, 11, 0.3)); }
        }

        /* Entrance Animations */
        .entrance-animate {
            animation: slideUpFade 0.6s ease-out forwards;
            opacity: 0;
        }
        @keyframes slideUpFade {
            from { transform: translateY(25px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }

        /* DataTable Polished Padding & Alignment */
        #retailer-points-table thead th {
            padding: 20px 25px !important;
            background: var(--med-bg-card, #fff);
            font-weight: 700;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--med-border, #f1f5f9) !important;
        }
        #retailer-points-table tbody td {
            padding: 22px 25px !important;
            border-bottom: 1px solid var(--med-border, #f1f5f9) !important;
            vertical-align: middle !important;
        }
        
        .table-controls-row {
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--med-border, #f1f5f9);
            background: rgba(0, 0, 0, 0.01);
        }
        .dt-buttons {
            display: flex !important;
            gap: 8px !important;
            margin-bottom: 0 !important;
        }
        .dt-buttons .btn {
            margin: 0 !important;
            border-radius: 8px !important;
            padding: 5px 12px !important;
        }
        .dataTables_filter {
            margin: 0 !important;
        }
        .dataTables_filter input {
            border-radius: 10px !important;
            padding: 8px 15px !important;
            border: 1px solid var(--med-border, #dee2e6) !important;
            outline: none !important;
            width: 250px !important;
        }
        
        /* Updated Points Card Style */
        .loyalty-widget-card {
            background: linear-gradient(135deg, #00497a 0%, #002b5c 100%) !important;
            border-radius: 20px;
            color: #fff !important;
        }
        .loyalty-widget-card .text-muted {
            color: rgba(255, 255, 255, 0.7) !important;
        }
        .loyalty-widget-card h1 {
            color: #fff !important;
        }

        /* Spacing Fix for DataTable Footer */
        .dataTables_info {
            padding-left: 30px !important;
            padding-bottom: 25px !important;
            padding-top: 25px !important;
            color: var(--med-text-muted, #64748b) !important;
            font-size: 0.85rem !important;
        }
        .dataTables_paginate {
            padding-right: 30px !important;
            padding-bottom: 25px !important;
            padding-top: 25px !important;
        }
        .dataTables_paginate .paginate_button {
            border: none !important;
            border-radius: 8px !important;
            margin: 0 2px !important;
            background: transparent !important;
            padding: 5px 12px !important;
        }
        .dataTables_paginate .paginate_button.current {
            background: var(--med-primary, #00497a) !important;
            color: white !important;
            border: none !important;
        }
        .dataTables_paginate .paginate_button:hover {
            background: rgba(0, 73, 122, 0.1) !important;
            color: var(--med-primary, #00497a) !important;
            border: none !important;
        }
    </style>

    <div class="container-fluid">
        <div class="page-title">
            <div class="row align-items-center">
                <div class="col-6">
                    <h3 class="fw-bold m-0" style="color: var(--med-text-main, #1e293b);">Loyalty Points</h3>
                    <p class="text-muted small m-0">Dynamic tracking of your earned rewards</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Total Points Card (Horizontal & Simple) -->
            <div class="col-xl-4 col-lg-6 mb-4 entrance-animate delay-1">
                <div class="card shadow-sm border-0 loyalty-widget-card overflow-hidden">
                    <div class="card-body d-flex align-items-center py-3 px-4">
                        <div class="coin-wrapper me-4">
                            <div class="big-gold-coin-dashboard" style="margin: 0; width: 60px; height: 60px;">
                                <div class="coin-inner-dashboard" style="font-size: 30px;">
                                    <i class="fa fa-star"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h1 class="fw-800 mb-0 display-6 line-height-1" style="line-height: 1;">{{ number_format($totalPoints, 2) }}</h1>
                            <p class="text-white opacity-75 text-uppercase mb-0 fw-bold small" style="letter-spacing: 0.5px; font-size: 0.75rem;">Loyalty Points</p>
                        </div>
                        <div class="ms-auto">
                            <i class="fa fa-angle-right opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="col-xl-12 entrance-animate delay-2">
                <div class="card shadow-sm border-0" style="border-radius: 20px; overflow: hidden; background: var(--med-bg-card);">
                    <div class="card-header bg-white py-3 px-4 border-bottom" style="background: var(--med-bg-card) !important;">
                        <h5 class="card-title mb-0 fw-bold" style="color: var(--med-text-main);"><i class="fa fa-history me-2"></i>Points Transaction History</h5>
                    </div>
                    <div id="retailer-table-controls" class="table-controls-row">
                        <!-- DT Buttons and Search will be moved here -->
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="retailer-points-table" class="display table table-hover align-middle mb-0" style="width:100%">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-muted small text-uppercase">Finalized Date</th>
                                        <th class="text-muted small text-uppercase">Reference #</th>
                                        <th class="text-muted small text-uppercase">Items Summary</th>
                                        <th class="text-muted small text-uppercase">Loyalty Points</th>
                                        <th class="text-center text-muted small text-uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody style="color: var(--med-text-main);">
                                    @forelse($orders as $order)
                                        <tr>
                                            <td>{{ $order->updated_at->format('d M Y, h:i A') }}</td>
                                            <td><span class="fw-bold text-primary">#{{ $order->order_code }}</span></td>
                                            <td class="small">
                                                @foreach($order->items as $item)
                                                    <div class="mb-1">
                                                        @if($item->product)
                                                            <span class="fw-bold">{{ $item->product->product_name }}</span>
                                                            @if($item->product->generic_name && $item->product->generic_name !== 'N/A')
                                                                <span class="text-muted small">({{ $item->product->generic_name }})</span>
                                                            @endif
                                                        @else
                                                            <span class="fw-bold">{{ $item->missing_product_name ?? 'Unknown Product #' . $item->product_id }}</span>
                                                            @if($item->missing_product_code && $item->missing_product_code !== 'N/A')
                                                                <span class="text-muted small">({{ $item->missing_product_code }})</span>
                                                            @endif
                                                        @endif
                                                        <br>
                                                        <span class="small">{{ $item->quantity }} {{ $item->unit }}</span>
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td class="fw-bold">{{ number_format($order->loyalty_points_earned, 2) }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $order->status === 'delivered' ? 'bg-success' : 'bg-info' }} text-uppercase" style="font-size: 10px;">
                                                    {{ str_replace('_', ' ', $order->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center py-5">No loyalty points recorded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#retailer-points-table').DataTable({
                dom: "Bfrtip", 
                initComplete: function() {
                    let container = $('#retailer-table-controls');
                    $('.dt-buttons').appendTo(container);
                    $('.dataTables_filter').appendTo(container);
                },
                buttons: [
                    { extend: 'copy', className: 'btn btn-xs btn-outline-secondary' },
                    { extend: 'csv', className: 'btn btn-xs btn-outline-secondary' },
                    { extend: 'excel', className: 'btn btn-xs btn-outline-secondary' }
                ],
                order: [[0, 'desc']],
                pageLength: 10,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Filter transactions..."
                }
            });

            // Trigger confetti on page load
            confetti({
                particleCount: 150,
                spread: 70,
                origin: { y: 0.6 },
                colors: ['#ffd700', '#daa520', '#b8860b']
            });
        });
    </script>
@endpush