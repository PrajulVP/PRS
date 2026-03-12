@extends('layouts.admin')

@section('page-body')

    <style>
        .page-title {
            padding-top: 0px !important;
        }

        @keyframes pageEnter {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-animate-in {
            animation: pageEnter 0.45s ease both;
        }
    </style>
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Loyalty Points</h3>
                </div>
                <div class="col-6 text-end">
                    <i class="fa fa-coins text-warning fa-3x"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid page-animate-in">
        <div class="row">
            <!-- Total Points Card -->
            <div class="col-sm-6 col-xl-3 mb-4 entrance-delay-1">
                <div
                    class="card bg-warning text-white widget-visitor-card shadow-sm border-0 overflow-hidden position-relative loyalty-card">
                    <div class="card-body text-center py-2 position-relative z-index-1">
                        <div class="coin-container mb-2">
                            <i class="fa fa-coins text-white fa-3x animate-bounce"></i>
                        </div>
                        <h1 class="fw-bold mb-0 mt-0 text-shadow text-nowrap" style="font-size: 3rem;">
                            {{ number_format($totalPoints, 2) }}
                        </h1>
                        <h6 class="text-uppercase font-weight-bold m-2">Total Points Earned</h6>
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
                    background: linear-gradient(135deg, rgb(131, 204, 97) 0%, #35c26b 100%) !important;
                    transition: transform 0.3s;
                    animation: cardGlow 2.5s ease-in-out infinite;
                }

                @keyframes cardGlow {

                    0%,
                    100% {
                        box-shadow: 0 0 12px 2px rgba(56, 239, 125, 0.4);
                    }

                    50% {
                        box-shadow: 0 0 28px 8px rgba(56, 239, 125, 0.75);
                    }
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

                /* Falling Coins Animation - Slower & More Graceful */
                .falling-coin {
                    position: absolute;
                    top: -50px;
                    width: 20px;
                    height: 20px;
                    background-color: #ffd700;
                    border-radius: 50%;
                    border: 2px solid #fff;
                    box-shadow: 0 0 8px rgba(255, 255, 255, 0.4);
                    animation: fall linear infinite;
                    z-index: 0;
                    opacity: 0.6;
                }

                .falling-coin::after {
                    content: '₹';
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    font-size: 10px;
                    color: #b8860b;
                    font-weight: bold;
                }

                @keyframes fall {
                    0% {
                        transform: translateY(0) rotate(0deg);
                        opacity: 0;
                    }

                    10% {
                        opacity: 0.7;
                    }

                    90% {
                        opacity: 0.7;
                    }

                    100% {
                        transform: translateY(400px) rotate(720deg);
                        opacity: 0;
                    }
                }

                /* Page Entry Animations */
                .entrance-delay-1 {
                    animation: entranceSlideUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
                    opacity: 0;
                }

                .entrance-delay-2 {
                    animation: entranceSlideUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) 0.2s forwards;
                    opacity: 0;
                }

                @keyframes entranceSlideUp {
                    from {
                        transform: translateY(30px);
                        opacity: 0;
                    }

                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }
            </style>

            {{-- Confetti Canvas Overlay --}}
            <canvas id="confetti-canvas"
                style="position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:9999;"></canvas>

            <script>
                (function () {
                    // --- Falling coins inside card ---
                    document.addEventListener("DOMContentLoaded", function () {
                        const container = document.getElementById('falling-coins-container');
                        const coinCount = 12;
                        for (let i = 0; i < coinCount; i++) {
                            let coin = document.createElement('div');
                            coin.classList.add('falling-coin');
                            coin.style.left = Math.random() * 100 + '%';
                            coin.style.animationDuration = (Math.random() * 7 + 7) + 's';
                            coin.style.animationDelay = (Math.random() * 8) + 's';
                            let size = Math.random() * 8 + 12;
                            coin.style.width = size + 'px';
                            coin.style.height = size + 'px';
                            container.appendChild(coin);
                        }
                    });

                    // --- Confetti burst on page load ---
                    const canvas = document.getElementById('confetti-canvas');
                    const ctx = canvas.getContext('2d');
                    canvas.width = window.innerWidth;
                    canvas.height = window.innerHeight;

                    const colors = ['#11998e', '#38ef7d', '#ffd700', '#ff6b6b', '#a18cd1', '#fbc2eb', '#fff', '#43e97b'];
                    const shapes = ['circle', 'rect', 'triangle'];
                    let pieces = [];
                    let running = true;

                    for (let i = 0; i < 180; i++) {
                        pieces.push({
                            x: Math.random() * canvas.width,
                            y: -20 - Math.random() * 300,
                            r: Math.random() * 7 + 4,
                            d: Math.random() * 3 + 1.5,
                            color: colors[Math.floor(Math.random() * colors.length)],
                            shape: shapes[Math.floor(Math.random() * shapes.length)],
                            tilt: Math.random() * 10 - 5,
                            tiltAngle: 0,
                            tiltSpeed: Math.random() * 0.07 + 0.03,
                            angle: Math.random() * Math.PI * 2,
                            spin: (Math.random() - 0.5) * 0.15,
                            opacity: 1,
                        });
                    }

                    let startTime = null;
                    const duration = 4000;

                    function draw(ts) {
                        if (!startTime) startTime = ts;
                        const elapsed = ts - startTime;
                        const fade = Math.max(0, 1 - (elapsed - 2500) / 1500);

                        ctx.clearRect(0, 0, canvas.width, canvas.height);

                        pieces.forEach(p => {
                            ctx.save();
                            ctx.globalAlpha = p.opacity * fade;
                            ctx.fillStyle = p.color;
                            ctx.translate(p.x, p.y);
                            ctx.rotate(p.angle);
                            if (p.shape === 'circle') {
                                ctx.beginPath();
                                ctx.arc(0, 0, p.r, 0, Math.PI * 2);
                                ctx.fill();
                            } else if (p.shape === 'rect') {
                                ctx.fillRect(-p.r, -p.r / 2, p.r * 2, p.r);
                            } else {
                                ctx.beginPath();
                                ctx.moveTo(0, -p.r);
                                ctx.lineTo(p.r, p.r);
                                ctx.lineTo(-p.r, p.r);
                                ctx.closePath();
                                ctx.fill();
                            }
                            ctx.restore();

                            p.y += p.d;
                            p.x += Math.sin(p.angle) * 1.2;
                            p.angle += p.spin;
                            p.tiltAngle += p.tiltSpeed;
                            p.tilt = Math.sin(p.tiltAngle) * 12;
                        });

                        if (elapsed < duration && fade > 0) {
                            requestAnimationFrame(draw);
                        } else {
                            ctx.clearRect(0, 0, canvas.width, canvas.height);
                            canvas.style.display = 'none';
                        }
                    }

                    requestAnimationFrame(draw);
                })();
            </script>

            <!-- Transaction History -->
            <div class="col-sm-12 entrance-delay-2">
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
                buttons: {
                    dom: {
                        button: {
                            className: 'btn btn-sm btn-icon'
                        }
                    },
                    buttons: [{
                        extend: 'copy',
                        className: 'btn btn-secondary btn-sm',
                        text: '<i class="fa fa-copy"></i> Copy'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-info btn-sm text-white',
                        text: '<i class="fa fa-file-csv"></i> CSV'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-success btn-sm',
                        text: '<i class="fa fa-file-excel"></i> Excel'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-danger btn-sm',
                        text: '<i class="fa fa-file-pdf"></i> PDF'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-dark btn-sm',
                        text: '<i class="fa fa-print"></i> Print'
                    }
                    ]
                },
                order: [[0, 'desc']], // Order by Date descending initially
                pageLength: 10,
                language: {
                    emptyTable: "<i class='fa fa-info-circle me-2'></i> No points earned yet."
                }
            });
        });
    </script>
@endpush