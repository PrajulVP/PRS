@extends('layouts.admin')

@section('page-body')
    <style>
        /* PREMIUM 3D GOLD COIN (Flipkart Style) - Smaller Size */
        .coin-wrapper {
            perspective: 1000px;
            padding: 5px;
            display: inline-block;
        }
        
        .gold-coin-3d {
            width: 70px;
            height: 70px;
            background: radial-gradient(circle at 30% 30%, #fff7bc 0%, #ffd700 30%, #daa520 70%, #b8860b 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 
                0 6px 12px rgba(0,0,0,0.15),
                inset 0 -3px 6px rgba(0,0,0,0.3),
                inset 0 3px 6px rgba(255,255,255,0.8);
            border: 3px solid #f9a825;
            animation: coinFloat3D 4s ease-in-out infinite;
            transform-style: preserve-3d;
        }

        .gold-coin-3d::after {
            content: '';
            position: absolute;
            top: 5%;
            left: 5%;
            right: 5%;
            bottom: 5%;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 50%;
            pointer-events: none;
        }

        .gold-coin-3d i {
            font-size: 2rem;
            color: rgba(0,0,0,0.4);
            z-index: 2;
            animation: iconPulse 2s ease-in-out infinite;
            font-weight: 900;
        }

        @keyframes coinFloat3D {
            0%, 100% { transform: translateY(0) rotateY(0deg); }
            50% { transform: translateY(-10px) rotateY(10deg); }
        }
        @keyframes iconPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.9; }
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
        
        .dt-buttons {
            padding: 20px 0 !important;
            display: flex !important;
            gap: 12px !important;
            align-items: center;
        }

        .dataTables_filter {
            padding: 15px 0 !important;
        }
        
        .dataTables_filter input {
            border-radius: 10px !important;
            padding: 8px 15px !important;
            border: 1px solid var(--med-border, #dee2e6) !important;
            outline: none !important;
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
    </style>

    <div class="container-fluid">
        <div class="page-title">
            <div class="row align-items-center">
                <div class="col-6">
                    <h3 class="fw-bold m-0" style="color: var(--med-text-main, #1e293b);">Loyalty Points</h3>
                    <p class="text-muted small m-0">Dynamic tracking of your earned rewards</p>
                </div>
                <div class="col-6 text-end">
                    <div class="p-2 bg-white shadow-sm d-inline-block rounded-circle" style="background: var(--med-bg-card) !important;">
                        <i class="fa fa-coins text-warning fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Total Points Card -->
            <div class="col-xl-3 col-sm-6 mb-4 entrance-animate delay-1">
                <div class="card shadow-sm border-0 h-100 loyalty-widget-card overflow-hidden">
                    <div class="card-body text-center py-4">
                        <div class="coin-wrapper mb-3">
                            <div class="gold-coin-3d">
                                <i class="fa fa-dollar-sign"></i>
                            </div>
                        </div>
                        <h1 class="fw-800 mb-1 display-5">{{ number_format($totalPoints, 2) }}</h1>
                        <p class="text-muted text-uppercase mb-0 fw-bold small" style="letter-spacing: 0.5px;">Current Loyalty Points</p>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="col-xl-12 entrance-animate delay-2">
                <div class="card shadow-sm border-0" style="border-radius: 20px; overflow: hidden; background: var(--med-bg-card);">
                    <div class="card-header bg-white py-3 px-4 border-bottom" style="background: var(--med-bg-card) !important;">
                        <h5 class="card-title mb-0 fw-bold" style="color: var(--med-text-main);"><i class="fa fa-history me-2"></i>Points Transaction History</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="retailer-points-table" class="display table table-hover align-middle mb-0" style="width:100%">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-muted small text-uppercase">Finalized Date</th>
                                        <th class="text-muted small text-uppercase">Reference #</th>
                                        <th class="text-muted small text-uppercase">Items Summary</th>
                                        <th class="text-muted small text-uppercase">Points Earned</th>
                                        <th class="text-center text-muted small text-uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody style="color: var(--med-text-main);">
                                    @forelse($orders as $order)
                                        <tr>
                                            <td>{{ $order->updated_at->format('d M Y, h:i A') }}</td>
                                            <td><span class="fw-bold text-primary">#{{ $order->order_code }}</span></td>
                                            <td class="small">
                                                {!! $order->items->map(function ($item) {
                                                    return ($item->product ? $item->product->product_name : 'Unknown') . ' (' . $item->quantity . ' qty)';
                                                })->implode('<br>') !!}
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
                dom: "<'row'<'col-12'B>><'row'<'col-12'f>><'row'<'col-12'tr>><'row'<'col-5'i><'col-7'p>>",
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