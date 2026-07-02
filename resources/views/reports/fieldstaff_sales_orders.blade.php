<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Field Staff Sales Orders Report</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 1cm 1.2cm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1e293b;
            font-size: 9pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* ── Header ──────────────────────────────────────────── */
        .header {
            width: 100%;
            border-bottom: 3px solid #0c4a6e;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header-table { width: 100%; border: none; }
        .header-table td { border: none; vertical-align: middle; padding: 0; }
        .logo-text {
            font-size: 20pt;
            font-weight: bold;
            color: #0c4a6e;
            letter-spacing: -0.5px;
        }
        .logo-sub {
            font-size: 8pt;
            color: #64748b;
            display: block;
            margin-top: 2px;
        }
        .report-title {
            font-size: 14pt;
            font-weight: bold;
            color: #0c4a6e;
            text-align: right;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-sub {
            font-size: 8pt;
            color: #64748b;
            text-align: right;
            margin-top: 2px;
        }

        /* ── Meta Bar ─────────────────────────────────────────── */
        .meta-bar {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 12px;
            width: 100%;
        }
        .meta-bar table { width: 100%; border: none; }
        .meta-bar td { border: none; padding: 2px 0; font-size: 8pt; color: #334155; vertical-align: top; }
        .meta-label { color: #64748b; text-transform: uppercase; font-size: 7pt; font-weight: bold; }

        /* ── KPI Summary Boxes ────────────────────────────────── */
        .kpi-row { width: 100%; margin-bottom: 12px; }
        .kpi-row table { width: 100%; border: none; border-spacing: 6px; }
        .kpi-row td { border: none; padding: 0; width: 25%; }
        .kpi-box {
            background: linear-gradient(135deg, #0c4a6e 0%, #075985 100%);
            color: #fff;
            border-radius: 6px;
            padding: 8px 10px;
            text-align: center;
        }
        .kpi-box.green  { background: linear-gradient(135deg, #065f46 0%, #047857 100%); }
        .kpi-box.purple { background: linear-gradient(135deg, #4c1d95 0%, #6d28d9 100%); }
        .kpi-box.orange { background: linear-gradient(135deg, #7c2d12 0%, #b45309 100%); }
        .kpi-value { font-size: 14pt; font-weight: bold; margin: 0; }
        .kpi-label { font-size: 7pt; opacity: 0.82; margin-top: 2px; text-transform: uppercase; letter-spacing: 0.3px; }

        /* ── Filters applied ─────────────────────────────────── */
        .filters-row {
            background: #fefce8;
            border: 1px solid #fde68a;
            border-radius: 4px;
            padding: 5px 10px;
            margin-bottom: 10px;
            font-size: 7.5pt;
            color: #78350f;
        }

        /* ── Data Table ───────────────────────────────────────── */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 7.5pt;
        }
        table.data-table thead tr {
            background: #0c4a6e;
            color: #fff;
        }
        table.data-table th {
            padding: 6px 5px;
            border: 1px solid #0c4a6e;
            text-align: left;
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: bold;
        }
        table.data-table td {
            padding: 5px 5px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
            font-size: 7.5pt;
        }
        table.data-table tbody tr {
            page-break-inside: avoid !important;
        }
        table.data-table tbody tr:nth-child(even) { background: #f8fafc; }
        table.data-table tbody tr:hover { background: #eff6ff; }

        .order-code { font-weight: bold; color: #0c4a6e; }
        .product-name { font-weight: 600; color: #1e293b; }
        .product-brand { font-size: 6.5pt; color: #64748b; margin-top: 1px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .muted { color: #64748b; font-size: 7pt; }

        /* Status badges */
        .badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }
        .badge-delivered  { background: #dcfce7; color: #15803d; }
        .badge-processing { background: #dbeafe; color: #1d4ed8; }
        .badge-pending    { background: #fef3c7; color: #b45309; }
        .badge-cancelled  { background: #fee2e2; color: #b91c1c; }
        .badge-rejected   { background: #f3e8ff; color: #7e22ce; }

        /* ── Footer ───────────────────────────────────────────── */
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
            font-size: 7pt;
            color: #94a3b8;
            background: white;
        }
        .footer table { width: 100%; border: none; }
        .footer td { border: none; padding: 0; }
    </style>
</head>
<body>

    {{-- ── Header ─────────────────────────────────────────────── --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 55%;">
                    <span class="logo-text">Atomed Wellness</span>
                    <span class="logo-sub">Distribution & Field Operations</span>
                </td>
                <td style="width: 45%;">
                    <div class="report-title">Sales Orders Report</div>
                    <div class="report-sub">Field Staff &mdash; {{ $staffName }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── Meta Bar ─────────────────────────────────────────────── --}}
    <div class="meta-bar">
        <table>
            <tr>
                <td>
                    <span class="meta-label">Staff Name</span><br>
                    <strong>{{ $staffName }}</strong>
                </td>
                <td>
                    <span class="meta-label">Date Range</span><br>
                    <strong>{{ $dateRange }}</strong>
                </td>
                <td>
                    <span class="meta-label">Generated At</span><br>
                    <strong>{{ $generatedAt }}</strong>
                </td>
                @if(!empty($filters))
                <td>
                    <span class="meta-label">Filters Applied</span><br>
                    @foreach($filters as $key => $val)
                        <strong>{{ $key }}:</strong> {{ $val }}@if(!$loop->last) &nbsp;&bull;&nbsp; @endif
                    @endforeach
                </td>
                @endif
            </tr>
        </table>
    </div>

    {{-- ── KPI Boxes ────────────────────────────────────────────── --}}
    <div class="kpi-row">
        <table>
            <tr>
                <td>
                    <div class="kpi-box">
                        <div class="kpi-value">{{ $totalOrderCount }}</div>
                        <div class="kpi-label">Total Orders</div>
                    </div>
                </td>
                <td>
                    <div class="kpi-box green">
                        <div class="kpi-value">&#8377;{{ number_format($totalRevenue, 2) }}</div>
                        <div class="kpi-label">Total Revenue</div>
                    </div>
                </td>
                <td>
                    <div class="kpi-box purple">
                        <div class="kpi-value">{{ number_format($totalQty) }}</div>
                        <div class="kpi-label">Total Units Sold</div>
                    </div>
                </td>
                <td>
                    <div class="kpi-box orange">
                        <div class="kpi-value">{{ $uniqueRetailers }}</div>
                        <div class="kpi-label">Retailers Served</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── Orders Table ─────────────────────────────────────────── --}}
    <table class="data-table">
        <colgroup>
            <col style="width: 10%;">
            <col style="width: 15%;">
            <col style="width: 11%;">
            <col style="width: 16%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
        </colgroup>
        <thead>
            <tr>
                <th>Order Code / Date</th>
                <th>Retailer / Location</th>
                <th>Distributor</th>
                <th>Product / Brand</th>
                <th class="text-center">Qty (Free)</th>
                <th>Unit / Variant</th>
                <th class="text-right">Price / MRP</th>
                <th class="text-right">Line Total</th>
                <th class="text-right">Order Total</th>
                <th class="text-center">Status / Payment</th>
            </tr>
        </thead>
        <tbody>
            @php $rowNum = 1; @endphp
            @forelse($orders as $order)
                @php
                    $retailerName = $order->retailer->user->name ?? 'N/A';
                    $shopName     = $order->retailer->shop_name ?? 'N/A';
                    $area         = $order->retailer->area->name ?? 'N/A';
                    $district     = $order->retailer->district->name ?? 'N/A';
                    $distributor  = $order->distributor->user->name ?? 'N/A';
                    $statusMap    = [
                        'delivered'  => 'badge-delivered',
                        'processing' => 'badge-processing',
                        'pending'    => 'badge-pending',
                        'cancelled'  => 'badge-cancelled',
                        'rejected'   => 'badge-rejected',
                    ];
                    $badgeClass   = $statusMap[$order->status] ?? 'badge-pending';
                @endphp
                @if($order->items->isEmpty())
                    <tr>
                        <td>
                            {{ $rowNum++ }}<br>
                            <span class="order-code">{{ $order->order_code }}</span><br>
                            <span class="muted">{{ $order->placed_at ? $order->placed_at->format('d M Y') : 'N/A' }}</span>
                        </td>
                        <td>
                            <strong>{{ $retailerName }}</strong><br>
                            <span class="muted">{{ $shopName }}</span><br>
                            <span class="muted">{{ $area }} / {{ $district }}</span>
                        </td>
                        <td>{{ $distributor }}</td>
                        <td colspan="5" class="muted text-center" style="vertical-align: middle;">No Items</td>
                        <td class="text-right" style="vertical-align: middle;">
                            <strong>&#8377;{{ number_format($order->total_amount, 2) }}</strong>
                        </td>
                        <td class="text-center" style="vertical-align: middle;">
                            <span class="badge {{ $badgeClass }}">{{ strtoupper($order->status) }}</span><br>
                            <span class="muted">{{ ucfirst($order->payment_status ?? 'N/A') }}</span>
                        </td>
                    </tr>
                @else
                    @foreach($order->items as $idx => $item)
                        @php $product = $item->product; @endphp
                        <tr>
                            @if($idx === 0)
                                <td>
                                    {{ $rowNum++ }}<br>
                                    <span class="order-code">{{ $order->order_code }}</span><br>
                                    <span class="muted">{{ $order->placed_at ? $order->placed_at->format('d M Y') : 'N/A' }}</span>
                                </td>
                                <td>
                                    <strong>{{ $retailerName }}</strong><br>
                                    <span class="muted">{{ $shopName }}</span><br>
                                    <span class="muted">{{ $area }} / {{ $district }}</span>
                                </td>
                                <td>{{ $distributor }}</td>
                            @else
                                <td></td>
                                <td></td>
                                <td></td>
                            @endif
                            <td>
                                <div class="product-name">{{ $product->product_name ?? 'Unknown' }}</div>
                                <div class="muted">{{ $product->product_code ?? '' }}</div>
                                <div class="muted">{{ $product->brand ?? 'N/A' }}</div>
                            </td>
                            <td class="text-center">
                                <strong>{{ $item->quantity }}</strong><br>
                                <span class="muted">({{ $item->free_quantity ?? 0 }} Free)</span>
                            </td>
                            <td>
                                {{ $item->unit ?? 'Nos' }}
                                @if($item->side || $item->size)
                                    <br><span class="muted">{{ implode('/', array_filter([$item->side, $item->size])) }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                &#8377;{{ number_format($item->unit_price, 2) }}<br>
                                <span class="muted">MRP: &#8377;{{ number_format($product->mrp ?? 0, 2) }}</span>
                            </td>
                            <td class="text-right">
                                <strong>&#8377;{{ number_format($item->total_amount, 2) }}</strong>
                            </td>
                            @if($idx === 0)
                                <td class="text-right">
                                    <strong>&#8377;{{ number_format($order->total_amount, 2) }}</strong><br>
                                    <span class="muted">{{ $order->loyalty_points_earned ?? 0 }} Pts</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $badgeClass }}">{{ strtoupper($order->status) }}</span><br>
                                    <span class="muted">{{ ucfirst($order->payment_status ?? 'N/A') }}</span>
                                    @if($order->delivered_at)
                                        <br><span class="muted">Del: {{ $order->delivered_at->format('d M') }}</span>
                                    @endif
                                </td>
                            @else
                                <td></td>
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                @endif
            @empty
                <tr>
                    <td colspan="10" class="text-center muted" style="padding: 20px;">No orders found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Footer ───────────────────────────────────────────────── --}}
    <div class="footer">
        <table>
            <tr>
                <td>Confidential &mdash; Field Staff Sales Report &mdash; {{ $staffName }}</td>
                <td class="text-right">Generated: {{ $generatedAt }}</td>
            </tr>
        </table>
    </div>

</body>
</html>
