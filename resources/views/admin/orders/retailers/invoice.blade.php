<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $retailerOrder->order_code }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --accent: #0ea5e9;
            --success: #10b981;
            --warning: #f59e0b;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-950: #020617;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--slate-700);
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: var(--slate-50);
            -webkit-font-smoothing: antialiased;
        }

        .invoice-container {
            max-width: 850px;
            margin: 40px auto;
            position: relative;
        }

        .action-bar {
            text-align: center;
            margin-bottom: 25px;
        }

        @media print {
            .invoice-container {
                margin: 0;
                max-width: 100%;
            }

            .action-bar {
                display: none !important;
            }

            body {
                background: white;
            }

            .invoice-card {
                box-shadow: none !important;
                border: 1px solid var(--slate-200) !important;
            }
        }

        .invoice-card {
            background-color: #fff;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--slate-100);
        }

        .invoice-header {
            background-color: #0c1427;
            color: #fff;
            padding: 25px 40px;
        }

        .invoice-header table {
            width: 100%;
        }

        .invoice-header td {
            vertical-align: middle;
        }

        .logo-box img {
            max-width: 160px;
            height: auto;
        }

        .invoice-title-wrapper {
            text-align: right;
        }

        .invoice-title {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 5px 0;
            color: #fff;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .invoice-meta {
            font-size: 13px;
            color: var(--slate-400);
            line-height: 1.4;
        }

        .invoice-meta span {
            color: var(--slate-100);
            font-weight: 500;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-paid {
            background: #065f46;
            color: #34d399;
        }

        .status-pending {
            background: #92400e;
            color: #fbbf24;
        }

        .invoice-content {
            padding: 40px;
        }

        .address-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 50px;
        }

        .address-card h6 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--slate-400);
            margin: 0 0 15px 0;
            font-weight: 800;
            border-bottom: 1px solid var(--slate-100);
            padding-bottom: 8px;
        }

        .address-card .name {
            font-size: 18px;
            font-weight: 700;
            color: var(--slate-950);
            margin-bottom: 8px;
        }

        .address-card .details {
            font-size: 14px;
            color: var(--slate-600);
            line-height: 1.6;
        }

        .details span {
            display: block;
            margin-bottom: 4px;
        }

        .details .icon-text {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        .items-table th {
            text-align: left;
            padding: 15px 20px;
            background: var(--slate-50);
            border-bottom: 2px solid var(--slate-200);
            color: var(--slate-800);
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table td {
            padding: 20px;
            border-bottom: 1px solid var(--slate-100);
            vertical-align: top;
        }

        .product-title {
            font-weight: 700;
            color: var(--slate-950);
            font-size: 15px;
            margin-bottom: 6px;
        }

        .batch-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .batch-pill {
            background: var(--slate-100);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            color: var(--slate-600);
            border: 1px solid var(--slate-200);
        }

        .summary-box {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 40px;
        }

        .notes-area {
            flex: 1;
            padding: 20px;
            background: #fff9f2;
            border: 1px dashed #fed7aa;
            border-radius: 12px;
            font-size: 13px;
            color: #9a3412;
            max-width: 400px;
        }

        .totals-card {
            width: 350px;
            background: var(--slate-50);
            border-radius: 16px;
            padding: 25px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .total-row.tax {
            color: var(--slate-400);
            font-size: 13px;
        }

        .total-row.grand {
            border-top: 2px solid var(--slate-200);
            padding-top: 15px;
            margin-top: 15px;
            font-size: 18px;
            font-weight: 800;
            color: var(--slate-950);
        }

        .total-row label {
            font-weight: 500;
        }

        .total-row value {
            font-weight: 700;
            color: var(--slate-950);
        }

        .footer {
            padding: 40px 50px;
            text-align: center;
            border-top: 1px solid var(--slate-100);
            color: var(--slate-400);
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .loyalty-banner {
            background: linear-gradient(90deg, #065f46 0%, #059669 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .loyalty-banner i {
            font-size: 24px;
            color: #34d399;
        }
        /* Print Optimization */
        @media print {
            body { background: #fff !important; color: #000 !important; }
            .invoice-card { box-shadow: none !important; border: 1px solid #e2e8f0 !important; background: #fff !important; }
            .invoice-card::before { display: none; }
            .totals-card { background: #fff !important; border: 1px solid #e2e8f0 !important; }
            .batch-pills span { background: #fff !important; border: 1px solid #e2e8f0 !important; color: #000 !important; }
            .status-badge { background: #fff !important; border: 1px solid #e2e8f0 !important; color: #000 !important; }
            .header-top, .header-meta, .billing-grid, .items-table th, .total-row.grand { border-color: #000 !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <div class="action-bar">
            <button class="btn-primary" onclick="window.print()">
                <i class="fa fa-print"></i> Print Invoice
            </button>
        </div>

        <div class="invoice-card">
            <div class="invoice-header">
                <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="width: 50%;">
                            <div class="logo-box">
                                <img src="{{ asset('admin/assets/images/logo/atom-logo-main-white.png') }}" alt="Atomed Wellness">
                            </div>
                        </td>
                        <td style="width: 50%;" class="invoice-title-wrapper">
                            <h1 class="invoice-title">INVOICE</h1>
                            <div class="invoice-meta">
                                Order No: <span>#{{ $retailerOrder->order_code }}</span><br>
                                Date: <span>{{ $retailerOrder->created_at->format('M d, Y') }}</span><br>
                                Payment Status: <span>{{ $retailerOrder->payment_status ?? 'PENDING' }}</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="invoice-content">
                <div class="address-grid">
                    <div class="address-card">
                        <h6>From (Distributor)</h6>
                        <div class="name">
                            {{ $retailerOrder->distributor->name ?? ($retailerOrder->distributor->user->name ?? 'Distributor') }}
                        </div>
                        <div class="details">
                            <span>{{ $retailerOrder->distributor->address ?? '' }}</span>
                            <span>{{ $retailerOrder->distributor->pincode ?? '' }}</span>
                            <span class="icon-text"><i class="fa fa-phone text-primary" style="font-size: 10px;"></i>
                                {{ $retailerOrder->distributor->contact_no ?? 'N/A' }}</span>
                            @if(!empty($retailerOrder->distributor->gst))
                                <span class="icon-text"><i class="fa fa-file-invoice text-primary"
                                        style="font-size: 10px;"></i> GST: {{ $retailerOrder->distributor->gst }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="address-card">
                        <h6>Bill To (Retailer)</h6>
                        <div class="name">{{ $retailerOrder->retailer->shop_name ?? 'Retailer Shop' }}</div>
                        <div class="details">
                            <span>{{ $retailerOrder->retailer->user->name ?? 'Retailer Name' }}</span>
                            <span>{{ $retailerOrder->retailer->address ?? '' }}</span>
                            <span class="icon-text"><i class="fa fa-phone text-success" style="font-size: 10px;"></i>
                                {{ $retailerOrder->retailer->contact_no ?? 'N/A' }}</span>
                            @if(!empty($retailerOrder->retailer->gst))
                                <span class="icon-text"><i class="fa fa-file-invoice text-success"
                                        style="font-size: 10px;"></i> GST: {{ $retailerOrder->retailer->gst }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($retailerOrder->status === 'pending' || $retailerOrder->status === 'processing')
                    <div
                        style="background: #fff9f2; border: 1px solid #fed7aa; border-radius: 12px; padding: 15px; margin-bottom: 30px; display: flex; gap: 15px; align-items: center;">
                        <i class="fa fa-info-circle" style="color: #ea580c; font-size: 24px;"></i>
                        <p style="margin: 0; font-size: 13px; color: #9a3412; font-weight: 500;">
                            <strong>Pro-forma Notice:</strong> This invoice is based on the initial order. Final quantities,
                            batches, and taxes will be updated upon physical billing and dispatch.
                        </p>
                    </div>
                @endif


                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Product Details</th>
                            <th style="text-align: center;">Price</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($retailerOrder->items as $item)
                            <tr>
                                <td>
                                    <div class="product-title">
                                        {{ $item->product->product_name ?? 'Product' }}
                                        @if($item->product && $item->product->product_code)
                                            <span style="font-size: 12px; color: var(--slate-400); font-weight: 500;">
                                                ({{ $item->product->product_code }})
                                            </span>
                                        @endif
                                    </div>
                                    @if($item->product && $item->product->generic_name)
                                        <div style="font-size: 12px; color: var(--slate-600); margin-bottom: 5px;">
                                            <i class="fa fa-flask me-1" style="font-size: 10px;"></i> {{ $item->product->generic_name }}
                                        </div>
                                    @endif
                                    @if($item->batches && $item->batches->count() > 0)
                                        <div class="batch-pills">
                                            @foreach($item->batches as $batch)
                                                <div class="batch-pill">
                                                    <i class="fa fa-tag me-1"></i> {{ $batch->batch_no }}
                                                    <span style="opacity: 0.5; margin: 0 5px;">|</span>
                                                    Exp: {{ \Carbon\Carbon::parse($batch->expiry_date)->format('m/Y') }}
                                                    <span style="opacity: 0.5; margin: 0 5px;">|</span>
                                                    Qty: {{ $batch->quantity }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div style="color: var(--slate-400); font-size: 12px; font-style: italic;">No batch
                                            allocated yet</div>
                                    @endif
                                </td>
                                <td style="text-align: center; vertical-align: middle; font-weight: 600;">
                                    ₹{{ number_format($item->unit_price, 2) }}</td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <span
                                        style="background: var(--slate-100); padding: 5px 12px; border-radius: 8px; font-weight: 700;">
                                        {{ $item->quantity }}
                                    </span>
                                    <div style="font-size: 11px; color: var(--slate-400); margin-top: 5px;">
                                        {{ $item->unit ?? 'nos' }}</div>
                                </td>
                                <td
                                    style="text-align: right; vertical-align: middle; font-weight: 800; color: var(--primary);">
                                    ₹{{ number_format($item->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="summary-box">
                    <div style="flex: 1;"></div>

                    <div class="totals-card">
                        @php
                            $cgstRate = $cgst ?? 9;
                            $sgstRate = $sgst ?? 9;
                            $totalTaxRate = $cgstRate + $sgstRate;
                            $divisor = 1 + ($totalTaxRate / 100);
                            $taxableAmount = $retailerOrder->total_amount / $divisor;
                            $cgstAmount = $taxableAmount * ($cgstRate / 100);
                            $sgstAmount = $taxableAmount * ($sgstRate / 100);
                        @endphp
                        <div class="total-row">
                            <label>Taxable Amount</label>
                            <value>₹{{ number_format($taxableAmount, 2) }}</value>
                        </div>
                        <div class="total-row tax">
                            <label>CGST ({{ $cgstRate }}%)</label>
                            <value>₹{{ number_format($cgstAmount, 2) }}</value>
                        </div>
                        <div class="total-row tax">
                            <label>SGST ({{ $sgstRate }}%)</label>
                            <value>₹{{ number_format($sgstAmount, 2) }}</value>
                        </div>
                        <div class="total-row grand">
                            <label>Grand Total</label>
                            <value>₹{{ number_format($retailerOrder->total_amount, 2) }}</value>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>

</html>