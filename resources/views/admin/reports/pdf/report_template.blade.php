<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.4;
            font-size: 10pt;
        }
        .header {
            border-bottom: 2px solid #00497a;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
        }
        th, td {
            padding: 10px;
            border: 1px solid #000;
            text-align: left;
            font-size: 10pt;
        }
        th {
            background-color: #eee;
            font-weight: bold;
            text-transform: uppercase;
        }
        .logo-text {
            font-size: 24pt;
            font-weight: bold;
            color: #00497a;
        }
        .report-title {
            text-align: right;
            font-size: 16pt;
            color: #00497a;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .report-subtitle {
            text-align: right;
            font-size: 9pt;
            color: #64748b;
            font-style: italic;
        }
        .meta-info {
            margin-bottom: 20px;
            font-size: 9pt;
            color: #555;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th {
            background-color: #f8fafc;
            color: #00497a;
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            text-transform: uppercase;
            font-size: 8pt;
        }
        table.data-table tr:nth-child(even) {
            background-color: #fafbfc;
        }
        .footer {
            position: fixed;
            bottom: 0px;
            width: 100%;
            font-size: 8pt;
            color: #999;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 8px;
            background: white;
        }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .text-primary { color: #00497a; }
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7pt;
            text-transform: uppercase;
        }
        .status-delivered { background-color: #dcfce7; color: #15803d; }
        .status-pending { background-color: #fef3c7; color: #b45309; }
        .status-cancelled { background-color: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td><span class="logo-text">Atomed Wellness</span></td>
                <td>
                    <div class="report-title">{{ $title }}</div>
                    @if(isset($subtitle))
                        <div class="report-subtitle">{{ $subtitle }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="meta-info" style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 40%; border: none; vertical-align: top;">
                    <span style="text-transform: uppercase; font-size: 7pt; color: #718096; font-weight: bold;">Generation Details</span><br>
                    Date: <strong>{{ $date }}</strong><br>
                    Scope: <strong>{{ ucfirst($type) }} Analysis</strong>
                </td>
                <td style="width: 60%; border: none; text-align: right; vertical-align: top;">
                    @if(!empty($filters))
                        <span style="text-transform: uppercase; font-size: 7pt; color: #718096; font-weight: bold;">Applied Filters</span><br>
                        <div style="font-size: 9pt;">
                            @foreach($filters as $key => $val)
                                <strong>{{ $key }}:</strong> {{ $val }}@if(!$loop->last) <span style="color: #cbd5e1; margin: 0 5px;">|</span> @endif
                            @endforeach
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            @if($type === 'orders')
                <tr>
                    <th>Ref Code</th>
                    <th>Order Date</th>
                    <th>Retailer / Distributor</th>
                    <th class="text-right">Volume (Qty/SKU)</th>
                    <th class="text-right">Sales Value (₹)</th>
                    @if($isManagement)
                    <th class="text-right">Tax (Est.)</th>
                    @endif
                    <th class="text-right">Status / Fulfillment</th>
                </tr>
            @elseif($type === 'distributors')
                <tr>
                    <th>No.</th>
                    <th>Distributor Name</th>
                    <th>Contact/User</th>
                    <th class="text-right">Network Reach</th>
                    <th class="text-right">Total Orders</th>
                    <th class="text-right">Revenue (₹)</th>
                </tr>
            @elseif($type === 'retailers')
                <tr>
                    <th>No.</th>
                    <th>Shop & Location</th>
                    @if($isManagement)
                    <th>Regulatory Profile</th>
                    @endif
                    <th>Personnel</th>
                    <th class="text-right">Orders</th>
                    <th class="text-right">Revenue (₹)</th>
                </tr>
            @elseif($type === 'products')
                <tr>
                    <th>No.</th>
                    <th>Medication Details</th>
                    @if($isManagement)
                    <th class="text-right">PTR / MRP</th>
                    @endif
                    <th class="text-right">Units Solid</th>
                    <th class="text-right">Intensity</th>
                    <th class="text-right">Orders</th>
                    <th class="text-right">Revenue (₹)</th>
                </tr>
            @elseif($type === 'fieldstaffs')
                <tr>
                    <th>Rank</th>
                    <th>Staff Personnel</th>
                    <th>Manager</th>
                    <th class="text-right">Outlets / Engagement</th>
                    <th class="text-right">AOV (₹)</th>
                    <th class="text-right">Orders</th>
                    <th class="text-right">Revenue (₹)</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @foreach($data as $index => $row)
                <tr>
                    @if($type === 'orders')
                        <td class="fw-bold">{{ $row->order_code }}</td>
                        <td>{{ $row->placed_at->format('M d, Y') }}</td>
                        <td>
                            <div class="fw-bold">{{ $row->retailer->user->name ?? 'N/A' }}</div>
                            <div style="font-size: 8pt; color: #666;">via {{ $row->distributor->user->name ?? 'N/A' }}</div>
                        </td>
                        <td class="text-right">
                            <span class="fw-bold">{{ $row->total_quantity }} Units</span><br>
                            <span style="font-size: 8pt; color: #666;">{{ $row->total_items }} SKUs</span>
                        </td>
                        <td class="text-right fw-bold">{{ number_format($row->total_amount, 2) }}</td>
                        @if($isManagement)
                        <td class="text-right" style="font-size: 8pt; color: #666;">
                            @php 
                                $tax = $row->items->sum(fn($i) => ($i->product->gst / 100) * ($i->product->taxable_value * $i->quantity));
                            @endphp
                            ₹{{ number_format($tax, 2) }}
                        </td>
                        @endif
                        <td class="text-right">
                             <span class="badge status-{{ $row->status }}">{{ strtoupper($row->status) }}</span><br>
                             <small style="color: #666;">{{ $row->delivered_at ? ($row->placed_at->diffInDays($row->delivered_at) >= 1 ? ($d = $row->placed_at->diffInDays($row->delivered_at)) . ' ' . ($d == 1 ? 'day' : 'days') : $row->placed_at->diffForHumans($row->delivered_at, true)) : 'Pending' }}</small>
                        </td>
                    @elseif($type === 'distributors')
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $row->user->name ?? $row->name }}</td>
                        <td>{{ $row->user->email ?? 'N/A' }}</td>
                        <td class="text-right">
                            @php 
                                $orderType = request('order_type', 'retailer');
                                $rel = $orderType === 'distributor' ? 'distributorOrders' : 'retailerOrders';
                                $reach = $row->$rel()->distinct('retailer_id')->count();
                            @endphp
                            <span class="fw-bold">{{ $reach }}</span> <span style="font-size: 8pt;">Retailers</span>
                        </td>
                        <td class="text-right">{{ $row->total_orders ?? 0 }}</td>
                        <td class="text-right fw-bold">{{ number_format($row->total_sales ?? 0, 2) }}</td>
                    @elseif($type === 'retailers')
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-bold">{{ $row->shop_name }}</div>
                            <div style="font-size: 8pt; color: #666;">{{ $row->area->name ?? 'N/A' }}</div>
                        </td>
                        @if($isManagement)
                        <td style="font-size: 8pt; line-height: 1.2;">
                            GST: {{ $row->gst ?: 'N/A' }}<br>
                            DL: {{ $row->drug_license_no ?: 'N/A' }}
                        </td>
                        @endif
                        <td>{{ $row->fieldStaff->user->name ?? 'N/A' }}</td>
                        <td class="text-right">{{ $row->total_orders ?? 0 }}</td>
                        <td class="text-right fw-bold">{{ number_format($row->total_sales ?? 0, 2) }}</td>
                    @elseif($type === 'products')
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-bold">{{ $row->product_name }}</div>
                            <div style="font-size: 8pt; color: #666;">{{ $row->product_code }}</div>
                        </td>
                        @if($isManagement)
                        <td class="text-right" style="font-size: 8pt;">
                            PTR: ₹{{ number_format($row->ptr, 2) }}<br>
                            MRP: ₹{{ number_format($row->mrp, 2) }}
                        </td>
                        @endif
                        <td class="text-right">
                            <span class="fw-bold">{{ $row->total_sold ?? 0 }}</span><br>
                            <span style="font-size: 8pt; color: #666;">{{ $row->total_free ?? 0 }} Free</span>
                        </td>
                        <td class="text-right fw-bold text-success">{{ $row->order_count ? number_format($row->total_sold / $row->order_count, 1) : 0 }} / ord</td>
                        <td class="text-right">{{ $row->order_count ?? 0 }}</td>
                        <td class="text-right fw-bold">{{ number_format($row->total_revenue ?? 0, 2) }}</td>
                    @elseif($type === 'fieldstaffs')
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $row->user->name ?? $row->name }}</td>
                        <td>{{ $row->salesManager->user->name ?? 'N/A' }}</td>
                        <td class="text-right">
                            <span class="fw-bold">{{ $row->total_retailers ?? 0 }} Outlets</span><br>
                            <span style="font-size: 8pt; color: #666;">{{ $row->total_orders ? number_format($row->total_orders / max($row->total_retailers, 1), 1) : 0 }} ord/shop</span>
                        </td>
                        <td class="text-right fw-bold text-primary">₹{{ $row->total_orders ? number_format($row->total_revenue / $row->total_orders, 2) : '0.00' }}</td>
                        <td class="text-right">{{ $row->total_orders ?? 0 }}</td>
                        <td class="text-right fw-bold">{{ number_format($row->total_revenue ?? 0, 2) }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Confidentially generated for Atomed Wellness Admin | Page 1 of 1
    </div>
</body>
</html>
