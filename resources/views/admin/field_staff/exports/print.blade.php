<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Print Staff Visits</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; line-height: 1.4; }
        .header { border-bottom: 2px solid #00497a; padding-bottom: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .logo-text { font-size: 24pt; font-weight: bold; color: #00497a; }
        .report-title { text-align: right; font-size: 16pt; color: #00497a; text-transform: uppercase; margin-bottom: 2px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; table-layout: fixed; word-wrap: break-word; }
        table.data-table th, table.data-table td { padding: 8px; border: 1px solid #e2e8f0; vertical-align: top; word-break: break-word; overflow-wrap: break-word; }
        table.data-table th { background-color: #f8fafc; color: #00497a; text-align: left; border-bottom: 2px solid #cbd5e1; text-transform: uppercase; font-size: 10px; font-weight: bold; }
        table.data-table tr:nth-child(even) { background-color: #fafbfc; }
        @media print {
            .no-print { display: none; }
            body { font-size: 10pt; }
            @page { size: landscape; margin: 1cm; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div style="display: flex; align-items: center;">
            <img src="{{ asset('admin/assets/images/logo/atom-logo-main.png') }}" style="height: 50px; margin-right: 15px;">
        </div>
        <div>
            <div class="report-title">Staff Visits Report</div>
            <div style="text-align: right; font-size: 9pt; color: #64748b; font-style: italic;">{{ $reportTitle ?? date('Y-m-d') }}</div>
        </div>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Staff Name</th>
                <th>Party Type</th>
                <th>Party Name</th>
                <th>Purpose</th>
                <th>Status</th>
                <th>Repeat Visit</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($visits as $visit)
            <tr>
                <td>{{ $visit->start_at ? $visit->start_at->format('Y-m-d') : '' }}</td>
                <td>{{ $visit->start_at ? $visit->start_at->format('h:i A') : '' }}</td>
                <td>{{ optional($visit->user)->name }}</td>
                <td>{{ ucfirst($visit->party_type) }}</td>
                <td>
                    @if($visit->party_type === 'retailer' || $visit->party_type === 'distributor')
                        {{ optional($visit->party)->shop_name ?? optional($visit->party)->name ?? 'Unknown' }}
                    @else
                        Party ID: {{ $visit->party_id }}
                    @endif
                </td>
                <td>{{ optional($visit->purpose)->name }}</td>
                <td>{{ ucfirst($visit->status) }}</td>
                <td>{{ $visit->is_repeat ? 'Yes' : 'No' }}</td>
                <td>{{ $visit->remarks }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
