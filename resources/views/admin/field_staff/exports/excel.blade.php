<table border="1">
    <thead>
        <tr>
            <th colspan="12" style="text-align: center; font-size: 16px; font-weight: bold;">
                Staff Visits Report ({{ $reportTitle ?? date('Y-m-d') }})
            </th>
        </tr>
        <tr>
            <th>Date</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Staff Name</th>
            <th>Manager Name</th>
            <th>Party Type</th>
            <th>Party Name</th>
            <th>Purpose</th>
            <th>Status</th>
            <th>Duration (Mins)</th>
            <th>Repeat Visit</th>
            <th>Remarks</th>
        </tr>
    </thead>
    <tbody>
        @foreach($visits as $visit)
        <tr>
            <td>{{ $visit->start_at ? $visit->start_at->format('Y-m-d') : '' }}</td>
            <td>{{ $visit->start_at ? $visit->start_at->format('h:i A') : '' }}</td>
            <td>{{ $visit->end_at ? $visit->end_at->format('h:i A') : '' }}</td>
            <td>{{ optional($visit->user)->name }}</td>
            <td>{{ optional(optional(optional($visit->user)->fieldStaff)->salesManager)->user->name ?? 'N/A' }}</td>
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
