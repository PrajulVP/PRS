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
            table-layout: fixed;
            word-wrap: break-word;
        }
        table.data-table th, table.data-table td {
            padding: 4px;
            border: 1px solid #e2e8f0;
            font-size: 7.5pt;
            vertical-align: top;
            word-break: break-word;
            overflow-wrap: break-word;
        }
        table.data-table th {
            background-color: #f8fafc;
            color: #00497a;
            text-align: left;
            border-bottom: 2px solid #cbd5e1;
            text-transform: uppercase;
            font-size: 7pt;
            font-weight: bold;
        }
        table.data-table tbody tr {
            page-break-inside: avoid !important;
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
        <table style="border: none;">
            <tr style="border: none;">
                <td style="border: none; width: 60%;">
                    <div style="display: flex; align-items: center;">
                        <img src="{{ public_path('assets/images/logo/logo.png') }}" style="height: 50px; margin-right: 15px;">
                        <span class="logo-text" style="vertical-align: middle;">Atomed Wellness</span>
                    </div>
                </td>
                <td style="border: none; width: 40%;">
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
        @if($type === 'orders')
        <colgroup>
            <col style="width: 3%;">
            <col style="width: 12%;">
            <col style="width: 17%;">
            <col style="width: 12%;">
            <col style="width: 10%;">
            <col style="width: 25%;">
            <col style="width: 7%;">
            <col style="width: 8%;">
            <col style="width: 6%;">
        </colgroup>
        @endif
        <thead>
            @if($type === 'orders')
                <tr>
                    <th>#</th>
                    <th>Order Details</th>
                    <th>Retailer / Shop Details</th>
                    <th>Staff Assigned</th>
                    <th>Distributor</th>
                    <th>Products / Brands</th>
                    <th class="text-right">Units / SKUs</th>
                    <th class="text-right">Amount (₹)</th>
                    <th>Status / Payment</th>
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
                    <th>Retailer Name / Shop</th>
                    <th>Area / District</th>
                    @if($isManagement)
                    <th>Regulatory Profile</th>
                    @endif
                    <th>Field Staff</th>
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
                    <th class="text-right">Revenue (₹)</th>
                </tr>
            @elseif($type === 'fieldstaffs')
                <tr>
                    <th>Rank</th>
                    <th>Staff Member</th>
                    <th>Manager</th>
                    <th class="text-right">Outlets / Visits</th>
                    <th class="text-right">Distance (KM)</th>
                    <th class="text-right">AOV (₹)</th>
                    <th class="text-right">Orders</th>
                    <th class="text-right">Revenue (₹)</th>
                </tr>
            @elseif($type === 'areas')
                <tr>
                    <th>No.</th>
                    <th>Area Name</th>
                    <th>District</th>
                    <th class="text-right">Retailer Base</th>
                    <th class="text-right">Aggregate Revenue (₹)</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @foreach($data as $index => $row)
                <tr>
                    @if($type === 'orders')
                        @php
                            $isDistributorOrder = $row instanceof \App\Models\DistributorOrder;

                            if ($isDistributorOrder) {
                                $retailerName   = 'N/A';
                                $shopName       = 'N/A';
                                $area           = $row->distributor->area->name ?? 'N/A';
                                $district       = $row->distributor->district->name ?? 'N/A';
                                $salesManager   = $row->salesManager->user->name ?? 'N/A';
                                $fieldStaffName = 'N/A';
                                $distributor    = $row->distributor->user->name ?? 'N/A';
                            } else {
                                $retailerName   = $row->retailer->user->name ?? 'N/A';
                                $shopName       = $row->retailer->shop_name ?? 'N/A';
                                $area           = $row->retailer->area->name ?? 'N/A';
                                $district       = $row->retailer->district->name ?? 'N/A';
                                $salesManager   = $row->fieldStaff->salesManager->user->name
                                    ?? $row->retailer->fieldStaff->salesManager->user->name
                                    ?? 'N/A';
                                $fieldStaffName = $row->fieldStaff->user->name
                                    ?? $row->retailer->fieldStaff->user->name
                                    ?? 'N/A';
                                $distributor    = $row->distributor->user->name ?? 'N/A';
                            }

                            $productsSummary = $row->items->map(function($item) {
                                $name = $item->product->product_name ?? 'Unknown';
                                $variant = array_filter([$item->side ?? null, $item->size ?? null]);
                                $vTxt = !empty($variant) ? ' ['.implode('/', $variant).']' : '';
                                return $name . $vTxt . ' x' . $item->quantity;
                            })->implode(', ');
                            $brands = $row->items->map(fn($i) => $i->product->brand ?? null)->unique()->filter()->implode(', ');
                            $totalUnits = $row->items->sum('quantity');
                            $totalSku   = $row->items->count();
                            $tax = $row->items->sum(fn($i) => (($i->product->gst ?? 0) / 100) * (($i->product->taxable_value ?? 0) * $i->quantity));
                        @endphp
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-bold">{{ $row->order_code }}</div>
                            <div style="font-size: 7pt; color: #00497a;">Inv: {{ $row->invoice_no ?? 'N/A' }}</div>
                            <div style="font-size: 7pt; color: #666;">{{ $row->placed_at ? $row->placed_at->format('M d, Y') : 'N/A' }}</div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $retailerName }}</div>
                            <div style="font-size: 7pt; color: #444;">{{ $shopName }}</div>
                            <div style="font-size: 7pt; color: #666;">{{ $area }} / {{ $district }}</div>
                        </td>
                        <td>
                            <div>Mgr: {{ $salesManager }}</div>
                            <div style="font-size: 7pt; color: #666;">FS: {{ $fieldStaffName }}</div>
                        </td>
                        <td>{{ $distributor }}</td>
                        <td style="font-size: 7.5pt;">
                            <div>{{ $productsSummary ?: 'No Items' }}</div>
                            @if($brands)
                                <div style="font-size: 7pt; color: #00497a; margin-top: 2px;">Brands: {{ $brands }}</div>
                            @endif
                        </td>
                        <td class="text-right">
                            <span class="fw-bold">{{ $totalUnits }} Units</span><br>
                            <span style="font-size: 7pt; color: #666;">{{ $totalSku }} SKUs</span>
                        </td>
                        <td class="text-right">
                            <div class="fw-bold">&#8377;{{ number_format($row->total_amount, 2) }}</div>
                            @if($isManagement)
                                <div style="font-size: 7pt; color: #666;">Tax: &#8377;{{ number_format($tax, 2) }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge status-{{ $row->status }}">{{ strtoupper($row->status) }}</span><br>
                            <span style="font-size: 7pt; color: #666; margin-top: 2px; display: inline-block;">{{ ucfirst($row->payment_status ?? 'N/A') }}</span>
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
                            <div class="fw-bold">{{ $row->user->name ?? 'N/A' }}</div>
                            <div style="font-size: 8pt; color: #666;">{{ $row->shop_name }}</div>
                        </td>
                        <td>
                            <div>{{ $row->area->name ?? 'N/A' }}</div>
                            <div style="font-size: 8pt; color: #666;">{{ $row->district->name ?? 'N/A' }}</div>
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
                            <span style="font-size: 8pt; color: #666;">{{ $row->total_visits ?? 0 }} Visits Logged</span>
                        </td>
                        <td class="text-right">
                            @php
                                [$f, $t] = (new \App\Http\Controllers\ReportController)->getFilterDates(request());
                                $dist = 0;
                                if ($f && $t) {
                                    $curr = $f->copy();
                                    while ($curr <= $t) {
                                        $dist += \App\Models\LocationLog::calculateDailyDistance($row->user_id, $curr->toDateString());
                                        $curr->addDay();
                                    }
                                } else {
                                    $dist += \App\Models\LocationLog::calculateDailyDistance($row->user_id, now()->toDateString());
                                }
                            @endphp
                            <span class="fw-bold">{{ number_format($dist, 2) }} KM</span>
                        </td>
                        <td class="text-right fw-bold text-primary">₹{{ $row->total_orders ? number_format($row->total_revenue / $row->total_orders, 2) : '0.00' }}</td>
                        <td class="text-right">{{ $row->total_orders ?? 0 }}</td>
                        <td class="text-right fw-bold">{{ number_format($row->total_revenue ?? 0, 2) }}</td>
                    @elseif($type === 'areas')
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $row->name }}</td>
                        <td>{{ $row->district_name ?? 'N/A' }}</td>
                        <td class="text-right">
                            <span class="fw-bold">{{ $row->retailers_count ?? 0 }} Outlets</span>
                        </td>
                        <td class="text-right fw-bold">
                            @php
                                [$fromDate, $toDate] = (new \App\Http\Controllers\ReportController)->getFilterDates(request());
                                if (request('brand')) {
                                    $total = \App\Models\RetailerOrderItem::whereHas('retailerOrder', function($q) use ($row, $fromDate, $toDate) {
                                        $q->where('status', \App\Models\RetailerOrder::STATUS_DELIVERED);
                                        if ($fromDate && $toDate) {
                                            $q->whereBetween('placed_at', [$fromDate, $toDate]);
                                        }
                                        $q->whereHas('retailer', function($retQ) use ($row) {
                                            $retQ->where('area_id', $row->id);
                                            if (request('sales_manager_id')) {
                                                $retQ->whereHas('fieldStaff', function($fsQ) {
                                                    $fsQ->where('sales_manager_id', request('sales_manager_id'));
                                                });
                                            }
                                            if (request('fieldstaff_id')) {
                                                $retQ->where('field_staff_id', request('fieldstaff_id'));
                                            }
                                            if (request('distributor_id')) {
                                                $retQ->where('distributor_id', request('distributor_id'));
                                            }
                                            if (request('retailer_id')) {
                                                $retQ->where('id', request('retailer_id'));
                                            }
                                        });
                                    })
                                    ->whereHas('product', function($prodQ) {
                                        $prodQ->where('brand', request('brand'));
                                    })
                                    ->sum('total_amount');
                                } else {
                                    $orderQuery = \App\Models\RetailerOrder::whereHas('retailer', function($q) use ($row) {
                                        $q->where('area_id', $row->id);
                                        if (request('sales_manager_id')) {
                                            $q->whereHas('fieldStaff', function($fsQ) {
                                                $fsQ->where('sales_manager_id', request('sales_manager_id'));
                                            });
                                        }
                                        if (request('fieldstaff_id')) {
                                            $q->where('field_staff_id', request('fieldstaff_id'));
                                        }
                                        if (request('distributor_id')) {
                                            $q->where('distributor_id', request('distributor_id'));
                                        }
                                        if (request('retailer_id')) {
                                            $q->where('id', request('retailer_id'));
                                        }
                                    })->where('status', \App\Models\RetailerOrder::STATUS_DELIVERED);
                                    
                                    if ($fromDate && $toDate) {
                                        $orderQuery->whereBetween('placed_at', [$fromDate, $toDate]);
                                    }
                                    
                                    $total = $orderQuery->sum('total_amount');
                                }
                            @endphp
                            ₹{{ number_format($total, 2) }}
                        </td>
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
