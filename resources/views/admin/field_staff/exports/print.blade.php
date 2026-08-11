<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Print Staff Visits</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #eee; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <h2>Staff Visits Report</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Staff Name</th>
                <th>Party Type</th>
                <th>Party Name</th>
                <th>Purpose</th>
                <th>Status</th>
                <th>Kilometers</th>
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
                <td>{{ $visit->distance_km }}</td>
                <td>{{ $visit->is_repeat ? 'Yes' : 'No' }}</td>
                <td>{{ $visit->remarks }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
