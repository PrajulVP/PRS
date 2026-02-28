import codecs

distributor_content = """<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $distributorOrder->order_code }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            color: #334155;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            -webkit-font-smoothing: antialiased;
        }

        .invoice-wrapper {
            max-width: 850px;
            margin: 40px auto;
            background-color: #fff;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        .invoice-header {
            background-color: #0c1427; /* Deep Navy from sidebar */
            color: #fff;
            padding: 40px;
            display: flex; /* works in browser print */
        }
        
        .invoice-header table {
            width: 100%;
        }

        .invoice-header td {
            vertical-align: middle;
        }

        .company-logo {
            max-width: 180px;
            height: auto;
        }

        .invoice-title-wrapper {
            text-align: right;
        }

        .invoice-title {
            font-size: 32px;
            font-weight: 700;
            margin: 0 0 10px 0;
            color: #fff;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .invoice-meta {
            font-size: 14px;
            color: #94a3b8;
        }

        .invoice-meta span {
            color: #f1f5f9;
            font-weight: 500;
        }

        .invoice-body {
            padding: 40px;
        }

        .info-section {
            width: 100%;
            margin-bottom: 40px;
        }

        .info-section td {
            width: 50%;
            vertical-align: top;
        }

        .info-block {
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            height: 100%;
        }

        .info-title {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 8px;
        }

        .info-content {
            font-size: 14px;
        }

        .info-content strong {
            color: #0f172a;
            font-size: 16px;
            display: block;
            margin-bottom: 5px;
        }

        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .table-items th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            padding: 12px 15px;
            text-align: left;
            border-bottom: 2px solid #cbd5e1;
        }

        .table-items th.center { text-align: center; }
        .table-items th.right { text-align: right; }

        .table-items td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            vertical-align: middle;
        }

        .table-items td.center { text-align: center; }
        .table-items td.right { text-align: right; }

        .product-name {
            font-weight: 600;
            color: #0f172a;
        }

        .batch-info {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }

        .batch-badge {
            background-color: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            margin-right: 5px;
            color: #475569;
        }

        .totals-section {
            width: 100%;
            margin-top: 20px;
        }

        .totals-section td {
            vertical-align: top;
        }

        .totals-table {
            width: 100%;
            max-width: 350px;
            margin-left: auto;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 10px 15px;
            color: #475569;
        }

        .totals-table td:last-child {
            text-align: right;
            font-weight: 600;
            color: #0f172a;
        }

        .totals-table tr.tax-row td {
            font-size: 13px;
            color: #64748b;
            padding: 5px 15px;
        }

        .totals-table tr.grand-total td {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            border-top: 2px solid #cbd5e1;
            padding-top: 15px;
            margin-top: 5px;
        }

        .invoice-footer {
            text-align: center;
            padding: 30px 40px;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 13px;
        }

        .invoice-footer p {
            margin: 0;
            font-weight: 500;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-paid { background-color: #10b981; color: #fff; }
        .status-pending { background-color: #f59e0b; color: #fff; }

        .print-btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 24px;
            cursor: pointer;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            margin: 20px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }

        .print-btn:hover {
            background: #1d4ed8;
        }

        @media print {
            @page {
                size: A4;
                margin: 0;
            }
            body {
                background-color: #fff;
                margin: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .invoice-wrapper {
                box-shadow: none;
                margin: 0;
                border: none;
                border-radius: 0;
                max-width: 100%;
            }
            .print-btn {
                display: none !important;
            }
            .invoice-body {
                padding: 30px;
            }
            .invoice-header {
                padding: 30px;
            }
            .info-block {
                border: 1px solid #cbd5e1;
            }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        Print Invoice
    </button>

    <div class="invoice-wrapper">
        <div class="invoice-header">
            <table border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="width: 50%;">
                        <img src="{{ asset('admin/assets/images/logo/atom-logo-main-white.png') }}" class="company-logo" alt="PRS Logo">
                    </td>
                    <td style="width: 50%;" class="invoice-title-wrapper">
                        <h1 class="invoice-title">INVOICE</h1>
                        <div class="invoice-meta">
                            Order No: <span>#{{ $distributorOrder->order_code }}</span><br>
                            Date: <span>{{ $distributorOrder->created_at->format('F d, Y') }}</span><br>
                            Payment Status: <span class="status-badge {{ strtolower($distributorOrder->payment_status) == 'paid' ? 'status-paid' : 'status-pending' }}">{{ ucfirst($distributorOrder->payment_status) }}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="invoice-body">
            <table class="info-section" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="padding-right: 15px;">
                        <div class="info-block">
                            <div class="info-title">Company Information</div>
                            <div class="info-content">
                                <strong>PRS</strong>
                                1234 Main St<br>
                                City, State 12345<br>
                                Email: info@prs.com<br>
                                Phone: +1 234 567 8900
                            </div>
                        </div>
                    </td>
                    <td style="padding-left: 15px;">
                        <div class="info-block">
                            <div class="info-title">Bill To</div>
                            <div class="info-content">
                                <strong>{{ $distributorOrder->distributor->name ?? ($distributorOrder->distributor->user->name ?? 'Distributor Name') }}</strong>
                                {{ $distributorOrder->distributor->address ?? '' }} {{ $distributorOrder->distributor->pincode ?? '' }}<br>
                                Phone: {{ $distributorOrder->distributor->contact_no ?? ($distributorOrder->distributor->phone ?? 'N/A') }}<br>
                                {!! $distributorOrder->distributor->user->email ? 'Email: ' . $distributorOrder->distributor->user->email . '<br>' : '' !!}
                                @if(!empty($distributorOrder->distributor->gst)) GST: {{ $distributorOrder->distributor->gst }}<br> @endif
                                @if(!empty($distributorOrder->distributor->drug_license_no)) DL No: {{ $distributorOrder->distributor->drug_license_no }} @endif
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <table class="table-items">
                <thead>
                    <tr>
                        <th>Product Description</th>
                        <th class="center" width="15%">Price</th>
                        <th class="center" width="15%">Qty</th>
                        <th class="right" width="20%">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($distributorOrder->items as $item)
                    <tr>
                        <td>
                            <div class="product-name">{{ $item->product->product_name ?? 'Product' }}</div>
                            @if($item->batches && $item->batches->count() > 0)
                                <div class="batch-info">
                                    @foreach($item->batches as $batch)
                                        <span class="batch-badge">Batch: {{ $batch->batch_no }}</span> Exp: {{ \Carbon\Carbon::parse($batch->expiry_date)->format('m/Y') }} (Qty: {{ $batch->quantity }})<br>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="center">₹{{ number_format($item->price, 2) }}</td>
                        <td class="center">{{ $item->quantity }} {{ $item->unit }}</td>
                        <td class="right">₹{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="totals-section" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="width: 50%;">
                    </td>
                    <td style="width: 50%;">
                        @php
                            $cgstRate = $cgst ?? 9;
                            $sgstRate = $sgst ?? 9;
                            $totalTaxRate = $cgstRate + $sgstRate;
                            $divisor = 1 + ($totalTaxRate / 100);
                            $taxableAmount = $distributorOrder->total_amount / $divisor;
                            $cgstAmount = $taxableAmount * ($cgstRate / 100);
                            $sgstAmount = $taxableAmount * ($sgstRate / 100);
                        @endphp
                        <table class="totals-table">
                            <tr>
                                <td>Taxable Amount</td>
                                <td>₹{{ number_format($taxableAmount, 2) }}</td>
                            </tr>
                            <tr class="tax-row">
                                <td>CGST ({{ $cgstRate }}%)</td>
                                <td>₹{{ number_format($cgstAmount, 2) }}</td>
                            </tr>
                            <tr class="tax-row">
                                <td>SGST ({{ $sgstRate }}%)</td>
                                <td>₹{{ number_format($sgstAmount, 2) }}</td>
                            </tr>
                            <tr class="grand-total">
                                <td>Grand Total</td>
                                <td>₹{{ number_format($distributorOrder->total_amount, 2) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="invoice-footer">
            <p>Thank you for your business. For any inquiries, please contact info@prs.com</p>
        </div>
    </div>
</body>
</html>
"""

with codecs.open(r'c:\wamp64\www\prs\resources\views\admin\orders\distributors\invoice.blade.php', 'w', 'utf-8') as f:
    f.write(distributor_content)

print("Distributor invoice updated successfully.")
