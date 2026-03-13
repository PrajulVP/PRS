@extends('layouts.admin')

@section('page-body')
    <style>
        /* Modern Select2 Styling */
        .select2-container--default .select2-selection--single {
            border-radius: 12px !important;
            height: 52px !important;
            border: 1px solid var(--med-border, #dee2e6) !important;
            display: flex !important;
            align-items: center !important;
            padding: 0 16px !important;
            background: var(--med-bg-card, #ffffff) !important;
            transition: all 0.3s ease;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: var(--med-text-main, #333) !important;
            font-weight: 500;
        }

        /* PREMIUM 3D GOLD COIN (Flipkart Style) */
        .coin-wrapper {
            perspective: 1000px;
            padding: 10px;
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
            animation: coinFloat3D 4s ease-in-out infinite, coinShine 3s linear infinite;
            transform-style: preserve-3d;
        }

        .gold-coin-3d::after {
            content: '';
            position: absolute;
            top: 5%;
            left: 5%;
            right: 5%;
            bottom: 5%;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            pointer-events: none;
        }

        .gold-coin-3d i {
            font-size: 2rem;
            color: rgba(0,0,0,0.4); /* Darker dollar symbol for engraving effect */
            z-index: 2;
            animation: iconPulse 2s ease-in-out infinite;
            font-weight: 900;
        }

        /* Removed Shine Effect Overlay - unnecessary white shade removed */

        @keyframes coinFloat3D {
            0%, 100% { transform: translateY(0) rotateY(0deg); }
            50% { transform: translateY(-10px) rotateY(10deg); }
        }
        @keyframes shineSweep {
            0% { left: -100%; }
            20% { left: 100%; }
            100% { left: 100%; }
        }
        @keyframes iconPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.9; }
        }

        /* DataTable Polished Padding & Alignment */
        #points-table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
        }
        #points-table thead th {
            padding: 18px 20px !important;
            border-bottom: 2px solid var(--med-border, #f1f5f9) !important;
            vertical-align: middle !important;
        }
        #points-table tbody td {
            padding: 20px !important;
            vertical-align: middle !important;
            border-bottom: 1px solid var(--med-border, #f1f5f9) !important;
        }
        
        .dt-buttons {
            padding: 15px 20px !important;
            display: flex !important;
            gap: 10px !important;
        }

        /* Cards Theme Support */
        .loyalty-card-stat {
            background: var(--med-bg-card, #ffffff);
            border: 1px solid var(--med-border, #f1f5f9);
            border-radius: 20px !important;
        }
        
        body.dark-only .profile-highlight-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        }

        .heading-theme { color: var(--med-text-main, #1e293b) !important; }
        .sub-heading-theme { color: var(--med-text-muted, #64748b) !important; }
        
        .badge-points {
            background: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--med-primary);
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.8rem;
        }
    </style>

    <div class="container-fluid">
        <div class="page-title">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="fw-bold m-0 heading-theme">Loyalty Dashboard</h3>
                    <p class="text-muted small m-0">Dynamic tracking of retailer reward milestones</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Selection Area -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow-sm border-0" style="border-radius: 20px; background: var(--med-bg-card);">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-lg-5 mb-3 mb-lg-0">
                                <h5 class="fw-bold mb-1 heading-theme">Retailer Selection</h5>
                                <p class="text-muted small mb-0">Search and select a retailer to view their comprehensive points history.</p>
                            </div>
                            <div class="col-lg-7">
                                <select id="retailer_selector" class="form-select select2">
                                    <option value="">-- Choose/Search Retailer --</option>
                                    @foreach($retailers as $r)
                                        <option value="{{ $r->id }}" data-points="{{ number_format($r->dynamic_loyalty_points, 2) }}">
                                            {{ $r->shop_name }} ({{ $r->user->name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail View -->
            <div id="points-container" class="col-12" style="display:none;">
                <div class="row">
                    <!-- Retailer Profile Card -->
                    <div class="col-xl-4 mb-4">
                        <div class="card h-100 shadow-sm profile-highlight-card loyalty-card-stat border-0">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                                    <div class="avatar-md bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 52px; height: 52px; background: rgba(var(--bs-primary-rgb), 0.15) !important;">
                                        <i class="fa fa-shopping-bag text-primary"></i>
                                    </div>
                                    <div class="text-truncate">
                                        <h5 id="display_shop_name" class="fw-bold mb-0 heading-theme text-truncate">...</h5>
                                        <p id="display_owner_name" class="sub-heading-theme mb-0 small">...</p>
                                    </div>
                                </div>
                                
                                <div class="row g-4">
                                    <div class="col-6">
                                        <label class="text-muted small d-block text-uppercase fw-bold mb-1">Phone</label>
                                        <span id="display_phone" class="heading-theme fw-500">...</span>
                                    </div>
                                    <div class="col-6">
                                        <label class="text-muted small d-block text-uppercase fw-bold mb-1">Region</label>
                                        <span id="display_region" class="heading-theme fw-500 text-truncate d-block">...</span>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted text-uppercase fw-bold mb-1 d-block" style="font-size: 0.7rem;">Email</small>
                                        <span id="display_email" class="heading-theme fw-500 text-break">...</span>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted text-uppercase fw-bold mb-1 d-block" style="font-size: 0.7rem;">Retailer Since</small>
                                        <span id="display_joined" class="heading-theme fw-500">...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Points Card -->
                    <div class="col-xl-4 mb-4">
                        <div class="card h-100 shadow-sm loyalty-card-stat border-0 overflow-hidden">
                            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                                <div class="coin-wrapper mb-4">
                                    <div class="gold-coin-3d">
                                        <i class="fa fa-dollar-sign"></i>
                                    </div>
                                </div>
                                <h1 id="display_total_points" class="display-5 fw-800 text-primary mb-1">0.00</h1>
                                <p class="text-muted text-uppercase fw-bold small m-0 letter-spacing-1">Current Loyalty Points</p>
                                <div class="mt-4 px-3 py-2 rounded-pill bg-light w-100 border">
                                    <small class="text-muted">Calculated from Delivered Orders</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Redemption Summary -->
                    <div class="col-xl-4 mb-4">
                        <div class="card h-100 shadow-sm loyalty-card-stat border-0">
                            <div class="card-body p-4 d-flex flex-column">
                                <h6 class="fw-bold heading-theme mb-3">Redemption Summary</h6>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Available for Use</span>
                                        <span class="fw-bold text-success" id="available_points">0.00</span>
                                    </div>
                                    <div class="progress mb-4" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <button class="btn btn-primary w-100 rounded-pill py-2 fw-bold disabled" style="opacity: 0.6">Redeem Rewards (Coming Soon)</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction Logs -->
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-0 overflow-hidden" style="border-radius: 20px; background: var(--med-bg-card);">
                            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center" style="background: var(--med-bg-card) !important;">
                                <h5 class="fw-bold mb-0 heading-theme">Transaction Summary</h5>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-hover align-middle mb-0" id="points-table" style="width: 100%;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="px-4 text-muted small text-uppercase">Date</th>
                                            <th class="text-muted small text-uppercase">Reference</th>
                                            <th class="text-muted small text-uppercase">Details</th>
                                            <th class="text-center text-muted small text-uppercase">Points</th>
                                            <th class="text-center text-muted small text-uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="heading-theme">
                                        <!-- AJAX Loaded -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Empty State -->
            <div id="empty-state" class="col-12 text-center py-5">
                <div class="py-5 bg-white border shadow-sm" style="border-radius: 24px; background: var(--med-bg-card) !important;">
                    <i class="fa fa-search text-muted opacity-25" style="font-size: 6rem;"></i>
                    <h4 class="mt-4 heading-theme fw-bold">Select a Retailer</h4>
                    <p class="text-muted px-4">Search for a retail partner to view their performance analytics.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Confetti -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <script>
        $(document).ready(function () {
            // Select2 custom template
            $('#retailer_selector').select2({
                placeholder: "-- Search Retailer --",
                allowClear: true,
                width: '100%',
                templateResult: formatRetailer,
                templateSelection: formatRetailer
            });

            function formatRetailer(res) {
                if (!res.id) return res.text;
                let pts = $(res.element).data('points');
                return $(`<div class="d-flex justify-content-between align-items-center">
                    <span>${res.text}</span>
                    <span class="badge-points">${pts} PTS</span>
                </div>`);
            }

            // Handle Selection
            $('#retailer_selector').on('change', function () {
                let id = $(this).val();
                if (id) {
                    $('#empty-state').hide();
                    $('#points-container').fadeIn();
                    fetchData(id);
                    
                    // Trigger confetti for premium feel
                    confetti({
                        particleCount: 100,
                        spread: 70,
                        origin: { y: 0.6 },
                        colors: ['#ffd700', '#daa520', '#b8860b']
                    });
                } else {
                    $('#points-container').hide();
                    $('#empty-state').fadeIn();
                }
            });

            function fetchData(retailerId) {
                $.get("{{ route('admin.loyalty-points.summary', ':id') }}".replace(':id', retailerId), function (data) {
                    $('#display_shop_name').text(data.shop_name);
                    $('#display_owner_name').text(data.owner_name);
                    $('#display_phone').text(data.phone);
                    $('#display_email').text(data.email);
                    $('#display_region').text(data.district + ', ' + data.area);
                    $('#display_joined').text(data.joined_date);
                    $('#display_total_points, #available_points').text(parseFloat(data.total_points).toFixed(2));
                });

                if ($.fn.DataTable.isDataTable('#points-table')) {
                    $('#points-table').DataTable().destroy();
                }

                $('#points-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('admin.loyalty-points.index') }}",
                        data: function (d) { d.retailer_id = retailerId; }
                    },
                    dom: "<'row'<'col-12'B>><'row'<'col-12'f>><'row'<'col-12'tr>><'row'<'col-5'i><'col-7'p>>",
                    buttons: [
                        { extend: 'copy', className: 'btn btn-xs btn-outline-secondary' },
                        { extend: 'csv', className: 'btn btn-xs btn-outline-secondary' },
                        { extend: 'excel', className: 'btn btn-xs btn-outline-secondary' }
                    ],
                    columns: [
                        { data: 'updated_at', name: 'updated_at', className: 'px-4' },
                        { data: 'order_code', name: 'order_code', render: d => `<strong class="text-primary">#${d}</strong>` },
                        { data: 'product_summary', name: 'product_summary', orderable: false, className: 'small' },
                        {
                            data: 'loyalty_points_earned',
                            name: 'loyalty_points_earned',
                            className: 'text-center fw-bold',
                            render: data => parseFloat(data).toFixed(2)
                        },
                        {
                            data: 'status',
                            name: 'status',
                            className: 'text-center',
                            render: function (data) {
                                let badgeClass = data.toLowerCase() === 'delivered' ? 'bg-success' : 'bg-info';
                                return `<span class="badge ${badgeClass} text-uppercase" style="font-size: 10px;">${data}</span>`;
                            }
                        }
                    ],
                    order: [[0, 'desc']],
                    pageLength: 10
                });
            }
        });
    </script>
@endpush