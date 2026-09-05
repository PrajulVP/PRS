<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tracking Report - {{ $user->name }} - {{ $date }}</title>
    <style>
        @page { size: a4 portrait; margin: 1cm; }
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 10pt; line-height: 1.5; }
        .header { border-bottom: 2px solid #7366ff; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { font-size: 20pt; font-weight: bold; color: #7366ff; }
        .title { text-align: right; font-size: 14pt; font-weight: bold; }
        .info-block { background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e2e8f0; }
        .stats-grid { display: table; width: 100%; margin-bottom: 20px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; }
        .stat-item { display: table-cell; width: 20%; text-align: center; border-right: 1px solid #e2e8f0; padding: 10px; }
        .stat-item:last-child { border-right: none; }
        .stat-label { font-size: 8pt; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 5px; display: block; }
        .stat-value { font-size: 12pt; font-weight: bold; color: #1e293b; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f1f5f9; color: #475569; font-size: 8pt; text-transform: uppercase; padding: 8px; border: 1px solid #e2e8f0; text-align: left; }
        td { padding: 8px; border: 1px solid #e2e8f0; vertical-align: top; }
        .timeline-type { font-weight: bold; font-size: 9pt; }
        .timeline-details { font-size: 8pt; color: #64748b; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 7pt; font-weight: bold; }
        .bg-success { background: #dcfce7; color: #15803d; }
        .bg-warning { background: #fef3c7; color: #b45309; }
        .bg-danger { background: #fee2e2; color: #b91c1c; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8pt; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 5px; }
        .map-placeholder { background: #f1f5f9; border: 1px dashed #cbd5e1; height: 100px; text-align: center; padding-top: 40px; color: #64748b; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="border: none; margin-bottom: 0;">
            <tr style="border: none;">
                <td style="border: none; width: 50%;"><span class="logo">Atomed Wellness</span></td>
                <td style="border: none; width: 50%; text-align: right;">
                    <div class="title">Field Staff Tracking Report</div>
                    <div style="font-size: 9pt; color: #64748b;">Report Date: {{ $reportDate }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="info-block">
        <table style="border: none; margin-bottom: 0;">
            <tr style="border: none;">
                <td style="border: none; width: 50%;">
                    <span class="stat-label">Field Personnel:</span><br>
                    <span style="font-size: 12pt; font-weight: bold;">{{ $user->name }}</span><br>
                    <span style="font-size: 9pt; color: #64748b;">SM: {{ $user->fieldStaff->salesManager->user->name ?? 'N/A' }}</span>
                </td>
                <td style="border: none; width: 50%; text-align: right;">
                    <span class="stat-label">Tracking Date:</span><br>
                    <span style="font-size: 12pt; font-weight: bold;">{{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-label">Total Distance</div>
            <div class="stat-value">{{ number_format($totalDistance, 2) }} KM</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Visits Completed</div>
            <div class="stat-value">{{ $visits->count() }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Attendance Logs</div>
            <div class="stat-value">{{ $punches->count() }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Offline Periods</div>
            <div class="stat-value">{{ $offlineCount }} <span style="font-size: 8pt; color: #64748b;">({{ $totalOfflineMinutes }} mins)</span></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Status</div>
            <div class="stat-value" style="font-size: 10pt;">
                @php $last = $punches->sortByDesc('timestamp')->first(); @endphp
                {{ $last ? strtoupper(str_replace('_', ' ', $last->type)) : 'NO ACTIVITY' }}
            </div>
        </div>
    </div>

    <h4 style="border-left: 4px solid #7366ff; padding-left: 10px; color: #1e293b;">Activity Timeline</h4>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Time</th>
                <th style="width: 15%;">Type</th>
                <th style="width: 45%;">Details</th>
                <th style="width: 25%;">Coordinates</th>
            </tr>
        </thead>
        <tbody>
            @php
                $allEvents = collect();
                $punches->each(fn($p) => $allEvents->push(['type' => 'punch', 'time' => $p->timestamp, 'data' => $p]));
                $visits->each(fn($v) => $allEvents->push(['type' => 'visit', 'time' => $v->check_in_at, 'data' => $v]));
                if(isset($offlineLogs)) {
                    $offlineLogs->each(fn($o) => $allEvents->push(['type' => 'offline', 'time' => $o->from_time, 'data' => $o]));
                }
                $sortedEvents = $allEvents->sortBy('time');
            @endphp

            @foreach($sortedEvents as $event)
                <tr>
                    <td class="fw-bold">{{ \Carbon\Carbon::parse($event['time'])->format('h:i A') }}</td>
                    <td>
                        @if($event['type'] == 'punch')
                            <span class="badge {{ $event['data']->type == 'punch_in' ? 'bg-success' : 'bg-danger' }}">
                                {{ strtoupper(str_replace('_', ' ', $event['data']->type)) }}
                            </span>
                        @elseif($event['type'] == 'offline')
                            <span class="badge" style="background: #64748b; color: white;">OFFLINE</span>
                        @else
                            <span class="badge bg-warning">VISIT</span>
                        @endif
                    </td>
                    <td>
                        @if($event['type'] == 'punch')
                            <span class="timeline-type">Attendance Log</span><br>
                            <span class="timeline-details">Location verified via registered device.</span>
                        @elseif($event['type'] == 'offline')
                            <span class="timeline-type">Offline Period</span><br>
                            <span class="timeline-details">
                                @if(!empty($event['data']->reason))
                                    Disconnected: {{ $event['data']->reason }}<br>
                                @endif
                                Duration: {{ $event['data']->to_time ? $event['data']->from_time->diffInMinutes($event['data']->to_time) . ' mins' : 'Ongoing' }}<br>
                                Resumed: {{ $event['data']->to_time ? $event['data']->to_time->format('h:i A') : 'N/A' }}
                            </span>
                        @else
                            <span class="timeline-type">{{ $event['data']->customer_name }}</span><br>
                            <span class="timeline-details">{{ $event['data']->customer_category }} visit logged.</span>
                        @endif
                    </td>
                    <td style="font-family: monospace; font-size: 8pt;">
                        @if($event['type'] == 'offline')
                            {{ $event['data']->latitude ?? 'N/A' }},<br>{{ $event['data']->longitude ?? 'N/A' }}
                        @else
                            {{ $event['data']->latitude }},<br>{{ $event['data']->longitude }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($locations->count() > 0)
    <h4 style="border-left: 4px solid #7366ff; padding-left: 10px; color: #1e293b; margin-top: 30px;">Route Data Samples</h4>
    <p style="font-size: 8pt; color: #64748b;">Displaying high-frequency location pings used for route reconstruction.</p>
    <table>
        <thead>
            <tr>
                <th>Ping Time</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Security Check</th>
            </tr>
        </thead>
        <tbody>
            @foreach($locations->take(20) as $loc)
                <tr>
                    <td>{{ $loc->timestamp->format('H:i:s') }}</td>
                    <td>{{ $loc->latitude }}</td>
                    <td>{{ $loc->longitude }}</td>
                    <td>
                        @if($loc->is_mock_location)
                            <span class="badge bg-danger">MOCK GPS DETECTED</span>
                        @else
                            <span style="color: #10b981; font-size: 8pt;">✓ Valid GPS</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if($locations->count() > 20)
        <p style="text-align: center; font-size: 8pt; color: #94a3b8;">+ {{ $locations->count() - 20 }} more location pings recorded for this date.</p>
    @endif
    @endif

    <div class="footer">
        Confidentially generated for Atomed Wellness Admin | This document contains verified GPS telemetry data.
    </div>
</body>
</html>
