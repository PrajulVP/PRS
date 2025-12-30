<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $retailerOrder->order_code }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
            font-size: 14px;
            line-height: 20px;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .invoice-box {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            background-color: #fff;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
            border-collapse: collapse;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }

        .print-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .print-btn:hover {
            background: #0056b3;
        }

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }

        @media print {

            /* Hide URL and Page Number */
            @page {
                size: auto;
                margin: 0mm;
            }

            .print-btn {
                display: none;
            }

            body {
                background-color: #fff;
                margin: 20mm;
                /* Add margin back for content */
            }

            .invoice-box {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <button class="print-btn" onclick="window.print()">Print Invoice</button>

        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="4">
                    <table>
                        <tr>
                            <td class="title">
                                <img src="{{ asset('admin/assets/images/logo/logo.png') }}" style="width:100%; max-width:150px;">
                            </td>

                            <td>
                                Invoice #: {{ $retailerOrder->order_code }}<br>
                                Created: {{ $retailerOrder->created_at->format('F d, Y') }}<br>
                                Status: {{ ucfirst(str_replace('_', ' ', $retailerOrder->status)) }}<br>
                                Payment: {{ ucfirst($retailerOrder->payment_status) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="4">
                    <table>
                        <tr>
                            <td>
                                <strong>PRS</strong><br>
                                1234 Main St<br>
                                City, State 12345
                            </td>

                            <td>
                                <strong>Bill To:</strong><br>
                                {{ $retailerOrder->retailer->user->name ?? 'Retailer Name' }}<br>
                                {{ $retailerOrder->retailer->user->email ?? 'email@example.com' }}<br>
                                {{ $retailerOrder->retailer->phone ?? '' }}<br>
                                {{ $retailerOrder->retailer->shop_name ?? '' }}<br>
                                {{ $retailerOrder->retailer->shop_address ?? '' }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="heading">
                <td style="width: 40%;">Item</td>
                <td style="text-align: center; width: 15%;">Unit Price</td>
                <td style="text-align: center; width: 15%;">Quantity</td>
                <td style="text-align: right; width: 30%;">Total</td>
            </tr>

            @foreach($retailerOrder->items as $item)
            <tr class="item">
                <td>{{ $item->product->product_name }}</td>
                <td style="text-align: center;">{{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align: center;">{{ $item->quantity }}</td>
                <td style="text-align: right;">{{ number_format($item->total_amount, 2) }}</td>
            </tr>
            @endforeach

            @php
            $cgstRate = $cgst ?? 9;
            $sgstRate = $sgst ?? 9;
            $totalTaxRate = $cgstRate + $sgstRate;
            $divisor = 1 + ($totalTaxRate / 100);
            $taxableAmount = $retailerOrder->total_amount / $divisor;
            $cgstAmount = $taxableAmount * ($cgstRate / 100);
            $sgstAmount = $taxableAmount * ($sgstRate / 100);
            @endphp

            <tr class="total">
                <td colspan="3" style="text-align: right; padding-right: 10px;">Taxable Amount:</td>
                <td style="text-align: right;">
                    {{ number_format($taxableAmount, 2) }}
                </td>
            </tr>
            <tr class="total">
                <td colspan="3" style="text-align: right; padding-right: 10px;">CGST ({{ $cgstRate }}%):</td>
                <td style="text-align: right;">
                    {{ number_format($cgstAmount, 2) }}
                </td>
            </tr>
            <tr class="total">
                <td colspan="3" style="text-align: right; padding-right: 10px;">SGST ({{ $sgstRate }}%):</td>
                <td style="text-align: right;">
                    {{ number_format($sgstAmount, 2) }}
                </td>
            </tr>
            <tr class="total">
                <td colspan="3" style="text-align: right; padding-right: 10px;"><strong>Grand Total:</strong></td>
                <td style="text-align: right;">
                    <strong>{{ number_format($retailerOrder->total_amount, 2) }}</strong>
                </td>
            </tr>
        </table>

        <br><br>
        <div style="width: 100%; text-align: center; margin-top: 50px;">
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>

</html>