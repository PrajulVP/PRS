<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Staff Visits</title>
    <style>
        @page { margin: 15px 15px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; word-wrap: break-word; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; overflow-wrap: break-word; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Staff Visits Report ({{ date('Y-m-d') }})</h2>
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
                <th>Duration</th>
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
                <td>{{ $visit->start_at && $visit->end_at ? $visit->start_at->diffInMinutes($visit->end_at) : '' }}</td>
                <td>{{ $visit->is_repeat ? 'Yes' : 'No' }}</td>
                <td>{{ $visit->remarks }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
