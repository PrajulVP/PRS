@extends('layouts.admin')

@section('page-body')

    <style>
        .page-title {
            padding-top: 0px !important;
        }
    </style>
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>My Loyalty Points</h3>
                </div>
                <div class="col-6 text-end">
                    <i class="fa fa-coins text-warning fa-3x"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Total Points Card -->
            <div class="col-sm-6 col-xl-3 mb-4">
                <div
                    class="card bg-warning text-white widget-visitor-card shadow-sm border-0 overflow-hidden position-relative loyalty-card">
                    <div class="card-body text-center py-2 position-relative z-index-1">
                        <div class="coin-container mb-2">
                            <i class="fa fa-coins text-white fa-3x animate-bounce"></i>
                        </div>
                        <h1 class="display-4 fw-bold mb-0 text-shadow">{{ number_format($retailer->loyalty_points, 2) }}
                        </h1>
                        <h6 class="text-uppercase font-weight-bold mt-2">Total Points Earned</h6>
                    </div>
                    <!-- Decorative huge icon -->
                    <i class="fa fa-coins font-warning"
                        style="font-size: 150px; opacity: 0.15; position: absolute; right: -20px; bottom: -20px; transform: rotate(-15deg);"></i>

                    <!-- Falling Coins Container -->
                    <div id="falling-coins-container"></div>
                </div>
            </div>

            <style>
                .loyalty-card {
                    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%) !important;
                    transition: transform 0.3s;
                }

                .loyalty-card:hover {
                    transform: translateY(-5px);
                }

                .animate-bounce {
                    animation: bounce 2s infinite;
                }

                @keyframes bounce {

                    0%,
                    20%,
                    50%,
                    80%,
                    100% {
                        transform: translateY(0);
                    }

                    40% {
                        transform: translateY(-10px);
                    }

                    60% {
                        transform: translateY(-5px);
                    }
                }

                .text-shadow {
                    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
                }

                /* Falling Coins Animation */
                .falling-coin {
                    position: absolute;
                    top: -50px;
                    width: 20px;
                    height: 20px;
                    background-color: #ffd700;
                    border-radius: 50%;
                    border: 2px solid #fff;
                    box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
                    animation: fall linear infinite;
                    z-index: 0;
                    opacity: 0.7;
                }

                .falling-coin::after {
                    content: '$';
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    font-size: 12px;
                    color: #b8860b;
                    font-weight: bold;
                }

                @keyframes fall {
                    to {
                        transform: translateY(300px) rotate(360deg);
                    }
                }
            </style>

            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    const container = document.getElementById('falling-coins-container');
                    const coinCount = 15; // Number of coins

                    for (let i = 0; i < coinCount; i++) {
                        let coin = document.createElement('div');
                        coin.classList.add('falling-coin');

                        // Randomize position and animation properties
                        coin.style.left = Math.random() * 100 + '%';
                        coin.style.animationDuration = (Math.random() * 3 + 2) + 's'; // 2-5s
                        coin.style.animationDelay = (Math.random() * 5) + 's';

                        // Randomize size slightly
                        let size = Math.random() * 10 + 15; // 15-25px
                        coin.style.width = size + 'px';
                        coin.style.height = size + 'px';

                        container.appendChild(coin);
                    }
                });
            </script>

            <!-- Transaction History -->
            <div class="col-sm-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-2 border-bottom">
                        <h5 class="card-title mb-0"><i class="fa fa-history me-2"></i>Points History</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="retailer-points-table" class="display table table-hover align-middle"
                                style="width:100%">
                                <thead class="table">
                                    <tr>
                                        <th>Date</th>
                                        <th>Order Reference</th>
                                        <th>Product Summary</th>
                                        <th>Points Earned</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $order)
                                                                    <tr>
                                                                        <td>{{ $order->updated_at->format('d M Y, h:i A') }}</td>
                                                                        <td>
                                                                            <span class="fw-bold text-primary">#{{ $order->order_code }}</span>
                                                                        </td>
                                                                        <td>
                                                                            {!! $order->items->map(function ($item) {
                                            return ($item->product ? $item->product->product_name : 'Unknown') . ' (' . $item->quantity . ' qty)';
                                        })->implode('<br>') !!}
                                                                        </td>
                                                                        <td>
                                                                            <span class="badge bg-warning text-dark fs-6">
                                                                                {{ number_format($order->loyalty_points_earned, 2) }}
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            <span class="badge bg-success">
                                                                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                    @empty
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
    <script>
        $(document).ready(function () {
            let retailerName = '{{ $retailer->shop_name }} ({{ $retailer->user->name }})';
            let exportTitle = 'Loyalty Points History - ' + retailerName;

            $('#retailer-points-table').DataTable({
                // Let it look similar to the admin datatables with exports
                dom: "<'row mb-3 d-flex align-items-center'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-4 text-center'B><'col-sm-12 col-md-4'f>>" +
                    "<'row '<'col-sm-12'tr>>" +
                    "<'row mt-3 '<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end align-items-center'p>>",
                buttons: [
                    { extend: 'copy', className: 'btn btn-secondary btn-sm', title: exportTitle },
                    { extend: 'csv', className: 'btn btn-secondary btn-sm', title: exportTitle },
                    { extend: 'excel', className: 'btn btn-secondary btn-sm', title: exportTitle },
                    { extend: 'pdf', className: 'btn btn-secondary btn-sm', title: exportTitle },
                    { extend: 'print', className: 'btn btn-secondary btn-sm', title: exportTitle }
                ],
                order: [[0, 'desc']], // Order by Date descending initially
                pageLength: 10,
                language: {
                    emptyTable: "<i class='fa fa-info-circle me-2'></i> No points earned yet."
                }
            });
        });
    </script>
@endpush