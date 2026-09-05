@extends('layouts.admin')

@section('title', 'Field Staff Tracking - ' . $user->name)

@push('styles')
    <style>
        @media print {
            body, html { background-color: #fff !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }
            .sidebar, .navbar, .page-header, .page-title, .breadcrumb, .footer-section, .header-wrapper { display: none !important; }
            .col-xl-8.col-lg-7, #map, .legend, .btn, form, .row.mb-4.g-3, .user-profile-block, .d-print-none { display: none !important; }
            .col-xl-4.col-lg-5 { width: 100% !important; max-width: 100% !important; flex: 0 0 100% !important; padding: 0 !important; margin: 0 !important; }
            .tracking-info-card { height: auto !important; overflow: visible !important; border: none !important; box-shadow: none !important; padding: 0 !important; margin: 0 !important; }
            .container-fluid { padding: 0 !important; margin: 0 !important; width: 100% !important; }
            .row { margin: 0 !important; padding: 0 !important; width: 100% !important; }
            .card, .card-body { box-shadow: none !important; border: none !important; margin: 0 !important; padding: 0 !important; }
            .card-header { display: none !important; }
            body .page-wrapper, body .page-body-wrapper, body .page-body, 
            body .page-wrapper.compact-wrapper .page-body-wrapper .page-body { margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
            .timeline-scroll-container { height: auto !important; overflow: visible !important; padding: 0 !important; margin: 0 !important; }
            @page { margin: 1cm; }
            
            /* PDF Export Styles */
            .pdf-header { border-bottom: 2px solid #7366ff; padding-bottom: 10px; margin-bottom: 20px; }
            .pdf-logo { font-size: 20pt; font-weight: bold; color: #7366ff; }
            .pdf-title { text-align: right; font-size: 14pt; font-weight: bold; }
            .pdf-info-block { background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e2e8f0; }
            .pdf-stats-grid { display: table; width: 100%; margin-bottom: 20px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; }
            .pdf-stat-item { display: table-cell; width: 20%; text-align: center; border-right: 1px solid #e2e8f0; padding: 10px; }
            .pdf-stat-item:last-child { border-right: none; }
            .pdf-stat-label { font-size: 8pt; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 5px; display: block; }
            .pdf-stat-value { font-size: 12pt; font-weight: bold; color: #1e293b; }
            .pdf-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            .pdf-table th { background: #f1f5f9; color: #475569; font-size: 8pt; text-transform: uppercase; padding: 8px; border: 1px solid #e2e8f0; text-align: left; }
            .pdf-table td { padding: 8px; border: 1px solid #e2e8f0; vertical-align: top; }
            .pdf-timeline-type { font-weight: bold; font-size: 9pt; }
            .pdf-timeline-details { font-size: 8pt; color: #64748b; }
            .pdf-badge { padding: 2px 6px; border-radius: 4px; font-size: 7pt; font-weight: bold; }
            .pdf-bg-success { background: #dcfce7 !important; color: #15803d !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .pdf-bg-warning { background: #fef3c7 !important; color: #b45309 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .pdf-bg-danger { background: #fee2e2 !important; color: #b91c1c !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .pdf-footer { width: 100%; text-align: center; font-size: 8pt; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 5px; margin-top: 40px; }
        }

        #map {
            height: 700px;
            border-radius: 12px;
            z-index: 1;
            border: 1px solid var(--med-border, #e2e8f0);
        }

        .tracking-info-card {
            background-color: transparent !important;
        }

        .timeline-scroll-container {
            height: 640px;
            overflow-y: auto;
            padding-right: 5px; /* space for scrollbar */
        }

        /* Custom Scrollbar for Timeline */
        .timeline-scroll-container::-webkit-scrollbar {
            width: 6px;
        }
        .timeline-scroll-container::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.02);
            border-radius: 8px;
        }
        .timeline-scroll-container::-webkit-scrollbar-thumb {
            background: rgba(108, 117, 125, 0.3);
            border-radius: 8px;
        }
        .timeline-scroll-container::-webkit-scrollbar-thumb:hover {
            background: rgba(108, 117, 125, 0.5);
        }

        /* Custom Marker Info Window Styling */
        .gm-style-iw-d {
            overflow: hidden !important;
        }

        .custom-info-window {
            padding: 10px;
            font-family: 'Montserrat', sans-serif;
        }

        /* Timeline refinement */
        .timeline-item {
            border-left: 2px dashed #e2e8f0;
            position: relative;
            padding-left: 24px;
            padding-right: 20px;
            padding-bottom: 20px;
            padding-top: 5px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .timeline-badge {
            min-width: 90px;
            text-align: center;
        }

        .timeline-item:hover {
            transform: translateX(5px);
        }

        .timeline-item:last-child {
            margin-bottom: 20px;
            border-left-color: transparent;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -7px;
            top: 10px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #7366ff;
            border: 2px solid #fff;
            box-shadow: 0 0 0 4px rgba(115, 102, 255, 0.15);
            z-index: 2;
        }

        .timeline-item.punch::before {
            background: #51bb25;
            box-shadow: 0 0 0 4px rgba(81, 187, 37, 0.15);
        }

        .timeline-item.punch_out::before {
            background: #e53935;
            box-shadow: 0 0 0 4px rgba(229, 57, 53, 0.15);
        }

        .timeline-item.alert::before {
            background: #dc3545;
            box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.15);
        }

        .timeline-item.visit::before {
            background: #7366ff;
            box-shadow: 0 0 0 4px rgba(115, 102, 255, 0.15);
        }

        .text-orange { color: #ff9800 !important; }
        .badge-light-orange {
            background-color: rgba(255, 152, 0, 0.1) !important;
            color: #ff9800 !important;
        }
        .bg-orange {
            background-color: #ff9800 !important;
            color: #ffffff !important;
        }

        .timeline-item.stop::before {
            background: #ff9800;
            box-shadow: 0 0 0 4px rgba(255, 152, 0, 0.15);
        }

        .timeline-item.offline::before {
            background: #6c757d;
            box-shadow: 0 0 0 4px rgba(108, 117, 125, 0.15);
        }

        .legend {
            padding: 12px;
            background: var(--med-bg-card, #ffffff);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            line-height: 24px;
            color: #333;
            border: 1px solid var(--med-border, rgba(0, 0, 0, 0.05));
            font-size: 11px;
            font-weight: 600;
            color: var(--med-text-main, #333);
            position: absolute;
            bottom: 30px;
            right: 10px;
            z-index: 1000;
        }

        .legend i {
            width: 12px;
            height: 12px;
            float: left;
            margin-right: 8px;
            margin-top: 6px;
            border-radius: 50%;
        }

        /* Dark Mode Fixes */
        .stats-card-modern {
            background: var(--med-bg-card, #f8f9fa) !important;
            border: 1px solid var(--med-border, #e2e8f0) !important;
        }

        /* Hide native date icon */
        input[type="date"]::-webkit-calendar-picker-indicator {
            background: transparent;
            bottom: 0;
            color: transparent;
            cursor: pointer;
            height: auto;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            width: auto;
        }

        .btn-pill-export:hover {
            background-color: #f8f9fa !important;
            transform: translateY(-1px);
        }
    </style>
@endpush

@section('page-body')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card bg-card-theme border-0 shadow-sm">
                    <div class="card-header bg-transparent py-4">
                        <div class="row align-items-center g-3">
                            <!-- Profile Column -->
                            <div class="col-xl-5 col-lg-12 mb-3 mb-xl-0">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                        style="width: 70px; height: 70px; border: 2px solid #fff;">
                                        <span
                                            class="fs-3 fw-bold text-primary">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                    </div>
                                    <div>
                                        <h4 class="mb-1 fw-bold text-dark d-flex align-items-center">
                                            {{ $user->name }}
                                            <span
                                                class="ms-2 badge rounded-pill {{ $isOnline ? 'bg-success' : 'bg-secondary' }} p-1 px-2"
                                                style="font-size: 0.65rem;">
                                                {{ $isOnline ? 'LIVE' : 'OFFLINE' }}
                                            </span>
                                        </h4>
                                        <div class="d-flex flex-wrap gap-2 text-muted small">
                                            <span><i class="fa fa-id-card me-1"></i>SM:
                                                {{ $user->fieldStaff->salesManager->user->name ?? 'N/A' }}</span>
                                            <span class="mx-1">•</span>
                                            <span><i class="fa fa-map-marker-alt me-1"></i>{{ $user->city ?? 'N/A' }}</span>
                                            <span class="mx-1 d-none d-md-inline">•</span>
                                            <span class="d-none d-md-inline"><i
                                                    class="fa fa-phone me-1"></i>{{ $user->contact_no ?? 'No Phone' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Controls Column -->
                            <div class="col-xl-7 col-lg-12">
                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-xl-end gap-3 flex-wrap">
                                    <!-- Enhanced Date Selector -->
                                    <div class="d-flex gap-2 align-items-center flex-wrap">
                                        <a href="{{ route('admin.field-staff.tracking.export', ['user_id' => $user->id, 'date' => $date, 'format' => 'csv']) }}"
                                            class="btn btn-primary shadow-sm rounded-3 d-flex align-items-center px-2"
                                            style="height: 32px; border: none; background: #1a3a63; font-size: 0.8rem;">
                                            <span class="fw-bold">CSV</span>
                                        </a>
                                        <a href="{{ route('admin.field-staff.tracking.export', ['user_id' => $user->id, 'date' => $date, 'format' => 'excel']) }}"
                                            class="btn btn-success shadow-sm rounded-3 d-flex align-items-center px-2"
                                            style="height: 32px; border: none; background: #28a745; font-size: 0.8rem;">
                                            <span class="fw-bold">Excel</span>
                                        </a>
                                        <a href="{{ route('admin.field-staff.tracking.export', ['user_id' => $user->id, 'date' => $date, 'format' => 'pdf']) }}"
                                            class="btn btn-danger shadow-sm rounded-3 d-flex align-items-center px-2"
                                            style="height: 32px; border: none; background: #dc3545; font-size: 0.8rem;">
                                            <span class="fw-bold">PDF</span>
                                        </a>
                                        <button onclick="window.print()"
                                            class="btn btn-dark shadow-sm rounded-3 d-flex align-items-center px-2 text-white"
                                            style="height: 32px; border: none; background: #2c3e50; font-size: 0.8rem;">
                                            <span class="fw-bold">Print</span>
                                        </button>

                                    </div>
                                    <div class="position-relative">
                                        <form action="{{ route('admin.field-staff.tracking-map') }}" method="GET"
                                            id="dateFilterForm">
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                            <div class="position-relative bg-white rounded-3 shadow-sm d-flex align-items-center"
                                                style="min-width: 140px; height: 32px; border: 1px solid #e0e0e0 !important;">
                                                <input type="date" name="date"
                                                    class="form-control fw-bold text-primary text-center"
                                                    value="{{ $date }}" onchange="this.form.submit()"
                                                    style="font-size: 0.85rem; cursor: pointer; border: none !important; background: transparent !important; outline: none !important; box-shadow: none !important; padding-left: 10px !important; height: 100%;">
                                                <i class="fa fa-calendar text-primary me-3 fs-6"></i>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="ms-1 ps-2 border-start">
                                        <a href="{{ route('admin.field-staff.tracking') }}"
                                            class="btn btn-primary rounded-3 px-3 d-flex align-items-center justify-content-center shadow-sm"
                                            style="height: 32px; font-weight: 700; background: #0d6efd; border: none; font-size: 0.85rem;">
                                            Back
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <!-- Stats Summary Row -->
                        <div class="row mb-4 g-3">
                            <div class="col-md col-6">
                                <div class="p-3 rounded-4 text-center border shadow-sm bg-white stats-card-modern h-100 d-flex flex-column justify-content-center">
                                    <h6 class="text-muted small mb-1 text-uppercase fw-bold">Distance</h6>
                                    <h4 class="mb-0 text-primary fw-bold" id="distanceCovered">
                                        {{ number_format($totalDistance ?? 0, 2) }} <span class="small fw-normal">KM</span>
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md col-6">
                                <div class="p-3 rounded-4 text-center border shadow-sm bg-white stats-card-modern h-100 d-flex flex-column justify-content-center">
                                    <h6 class="text-muted small mb-1 text-uppercase fw-bold">Punches</h6>
                                    <h4 class="mb-0 text-success fw-bold">{{ $punches->count() }}</h4>
                                </div>
                            </div>
                            <div class="col-md col-6">
                                <div class="p-3 rounded-4 text-center border shadow-sm bg-white stats-card-modern h-100 d-flex flex-column justify-content-center">
                                    <h6 class="text-muted small mb-1 text-uppercase fw-bold">Visits</h6>
                                    <h4 class="mb-0 text-warning fw-bold">{{ $visits->count() }}</h4>
                                </div>
                            </div>
                            <div class="col-md col-6">
                                <div class="p-3 rounded-4 text-center border shadow-sm bg-white stats-card-modern h-100 d-flex flex-column justify-content-center">
                                    <h6 class="text-muted small mb-1 text-uppercase fw-bold">Status</h6>
                                    <h4 class="mb-0 fw-bold">
                                        @if($isOnline)
                                            <span class="badge rounded-pill bg-success px-3" id="liveStatus">Online</span>
                                        @else
                                            <span class="badge rounded-pill bg-secondary px-3" id="liveStatus">Offline</span>
                                        @endif
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md col-6">
                                <div class="p-3 rounded-4 text-center border shadow-sm bg-white stats-card-modern h-100 d-flex flex-column justify-content-center">
                                    <h6 class="text-muted small mb-1 text-uppercase fw-bold">Alerts</h6>
                                    @if(isset($mockGpsCount) && $mockGpsCount > 0)
                                        <h4 class="mb-0 text-danger fw-bold" title="{{ $mockGpsCount }} Mock GPS triggers detected!">
                                            <i class="fa fa-exclamation-triangle me-1"></i>{{ $mockGpsCount }}
                                        </h4>
                                    @else
                                        <h4 class="mb-0 text-success fw-bold">0</h4>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Map Column -->
                            <div class="col-xl-8 col-lg-7 position-relative">
                                <div id="map"></div>
                                <div class="legend">
                                    <div class="mb-1"><i style="background: #51bb25"></i> Punch In</div>
                                    <div class="mb-1"><i style="background: #f73164"></i> Punch Out</div>
                                    <div class="mb-1"><i style="background: #7366ff"></i> Customer Visit</div>
                                    <div class="mb-1"><i style="background: #ff9800"></i> Stopped</div>
                                    <div><i
                                            style="background: #7366ff; border-radius: 0; height: 2px; margin-top: 11px;"></i>
                                        Route</div>
                                </div>
                            </div>

                            <!-- Sidebar Info Column -->
                            <div class="col-xl-4 col-lg-5">
                                <div class="card tracking-info-card border-0 shadow-none bg-transparent">
                                    <div class="card-header bg-transparent pb-2 ps-0">
                                        <h6 class="mb-0">Activity Timeline</h6>
                                    </div>
                                    <div class="card-body p-4 timeline-scroll-container" id="timelineContainer">
                                        @php
                                            $allEvents = collect();
                                            $punches->each(fn($p) => $allEvents->push(['type' => 'punch', 'time' => $p->timestamp, 'data' => $p]));
                                            
                                            // Ensure visits work for both VisitLog (check_in_at) and FieldVisit (start_at)
                                            $visits->each(function($v) use ($allEvents) {
                                                $time = $v->check_in_at ?? $v->start_at ?? $v->created_at;
                                                if ($time) $allEvents->push(['type' => 'visit', 'time' => $time, 'data' => $v]);
                                            });
                                            
                                            $locations->whereNotNull('remarks')->each(fn($l) => $allEvents->push(['type' => 'alert', 'time' => $l->timestamp, 'data' => $l]));
                                            $offlineLogs->each(fn($o) => $allEvents->push(['type' => 'offline', 'time' => $o->from_time, 'data' => $o]));
                                            
                                            // Include computed stops (> 5 mins) in the timeline
                                            if (isset($stops)) {
                                                $stops->each(function($s) use ($allEvents) {
                                                    $allEvents->push(['type' => 'stop', 'time' => $s['start_time'], 'data' => $s]);
                                                });
                                            }
                                            
                                            $sortedEvents = $allEvents->sortBy('time');
                                        @endphp

                                        <div class="d-print-none">
                                            @if($sortedEvents->isEmpty())
                                                <div class="text-center py-5 no-activity">
                                                    <i class="fa fa-walking-light fa-3x text-light mb-3"></i>
                                                    <p class="text-muted">No activity recorded yet.</p>
                                                </div>
                                            @else
                                                @foreach($sortedEvents as $event)
                                                    <div class="timeline-item {{ $event['type'] }} {{ $event['type'] == 'punch' ? $event['data']->type : '' }}"
                                                        @if(isset($event['data']->latitude) && isset($event['data']->longitude))
                                                            onclick="flyToLocation({{ $event['data']->latitude }}, {{ $event['data']->longitude }})"
                                                        @elseif(isset($event['data']['lat']) && isset($event['data']['lng']))
                                                            onclick="flyToLocation({{ $event['data']['lat'] }}, {{ $event['data']['lng'] }})"
                                                        @elseif(isset($event['data']->location_lat) && isset($event['data']->location_lng))
                                                            onclick="flyToLocation({{ $event['data']->location_lat }}, {{ $event['data']->location_lng }})"
                                                        @endif>
                                                        <div class="d-flex justify-content-between">
                                                            <span class="small fw-bold">{{ \Carbon\Carbon::parse($event['time'])->format('h:i A') }}</span>
                                                            @if($event['type'] == 'punch')
                                                                <span class="badge {{ $event['data']->type == 'punch_in' ? 'bg-success' : '' }} text-white small timeline-badge" 
                                                                      @if($event['data']->type != 'punch_in') style="background-color: #e53935 !important;" @endif>
                                                                    {{ str_replace('_', ' ', $event['data']->type) }}
                                                                </span>
                                                            @elseif($event['type'] == 'alert')
                                                                <span class="badge bg-danger text-white small timeline-badge">System Alert</span>
                                                            @elseif($event['type'] == 'offline')
                                                                <span class="badge text-white small timeline-badge" style="background-color: #6c757d !important;">Offline</span>
                                                            @elseif($event['type'] == 'stop')
                                                                <span class="badge bg-orange small timeline-badge">Stopped</span>
                                                            @else
                                                                <span class="badge bg-primary text-white small timeline-badge">Visit</span>
                                                            @endif
                                                        </div>
                                                        <div class="mt-1">
                                                            @if($event['type'] == 'punch')
                                                                <p class="mb-0 small text-dark">Punched at location</p>
                                                                @if($event['data']->is_mock_location)
                                                                    <div class="badge badge-light-danger small mt-1"><i
                                                                            class="fa fa-exclamation-triangle me-1"></i>Mock GPS!</div>
                                                                @endif
                                                            @elseif($event['type'] == 'alert')
                                                                <p class="mb-0 fw-bold small text-danger"><i class="fa fa-info-circle me-1"></i>{{ $event['data']->remarks }}</p>
                                                            @elseif($event['type'] == 'offline')
                                                                <p class="mb-0 fw-bold small text-secondary">
                                                                    <i class="fa fa-wifi me-1" style="text-decoration: line-through;"></i>Offline Period
                                                                </p>
                                                                <p class="mb-0 text-muted small">
                                                                    {{ \Carbon\Carbon::parse($event['data']->from_time)->format('h:i A') }} - {{ $event['data']->to_time ? \Carbon\Carbon::parse($event['data']->to_time)->format('h:i A') : 'Ongoing' }}
                                                                    @if($event['data']->reason)
                                                                        <br>Reason: {{ $event['data']->reason }}
                                                                    @endif
                                                                </p>
                                                            @elseif($event['type'] == 'stop')
                                                                <p class="mb-0 fw-bold small text-orange">
                                                                    <i class="fa fa-hand-paper me-1"></i>Stopped
                                                                </p>
                                                                <p class="mb-0 text-muted small">
                                                                    {{ \Carbon\Carbon::parse($event['data']['start_time'])->format('h:i A') }} - {{ \Carbon\Carbon::parse($event['data']['end_time'])->format('h:i A') }}
                                                                    <br>Duration: {{ \App\Http\Controllers\ReportController::formatDurationHumans($event['data']['start_time'], $event['data']['end_time']) }}
                                                                </p>
                                                            @else
                                                                <p class="mb-0 fw-bold small text-primary">
                                                                    {{ $event['data']->customer_name ?? $event['data']->party?->name ?? 'Customer Visit' }}</p>
                                                                @if(isset($event['data']->customer_category))
                                                                    <p class="mb-0 text-muted small">{{ ucfirst($event['data']->customer_category) }}</p>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>

                                        <div class="d-none d-print-block mt-4">
                                            <div class="pdf-header">
                                                <table style="border: none; margin-bottom: 0; width: 100%;">
                                                    <tr style="border: none;">
                                                        <td style="border: none; width: 50%; padding: 0;"><span class="pdf-logo">Atomed Wellness</span></td>
                                                        <td style="border: none; width: 50%; text-align: right; padding: 0;">
                                                            <div class="pdf-title">Field Staff Tracking Report</div>
                                                            <div style="font-size: 9pt; color: #64748b;">Report Date: {{ now()->format('M d, Y H:i') }}</div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>

                                            <div class="pdf-info-block">
                                                <table style="border: none; margin-bottom: 0; width: 100%;">
                                                    <tr style="border: none;">
                                                        <td style="border: none; width: 50%; padding: 0;">
                                                            <span class="pdf-stat-label">Field Personnel:</span><br>
                                                            <span style="font-size: 12pt; font-weight: bold;">{{ $user->name }}</span><br>
                                                            <span style="font-size: 9pt; color: #64748b;">SM: {{ $user->fieldStaff->salesManager->user->name ?? 'N/A' }}</span>
                                                        </td>
                                                        <td style="border: none; width: 50%; text-align: right; padding: 0;">
                                                            <span class="pdf-stat-label">Tracking Date:</span><br>
                                                            <span style="font-size: 12pt; font-weight: bold;">{{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>

                                            <div class="pdf-stats-grid">
                                                <div class="pdf-stat-item">
                                                    <div class="pdf-stat-label">Total Distance</div>
                                                    <div class="pdf-stat-value">{{ number_format($totalDistance ?? 0, 2) }} KM</div>
                                                </div>
                                                <div class="pdf-stat-item">
                                                    <div class="pdf-stat-label">Visits Completed</div>
                                                    <div class="pdf-stat-value">{{ $visits->count() }}</div>
                                                </div>
                                                <div class="pdf-stat-item">
                                                    <div class="pdf-stat-label">Attendance Logs</div>
                                                    <div class="pdf-stat-value">{{ $punches->count() }}</div>
                                                </div>
                                                <div class="pdf-stat-item">
                                                    <div class="pdf-stat-label">Offline Periods</div>
                                                    <div class="pdf-stat-value">
                                                        @if(isset($offlineLogs) && $offlineLogs->count() > 0)
                                                            {{ $offlineLogs->count() }} <span style="font-size: 8pt; color: #64748b;">({{ $offlineLogs->sum(function($o) { return \Carbon\Carbon::parse($o->from_time)->diffInMinutes($o->to_time ?? now()); }) }} mins)</span>
                                                        @else
                                                            0 <span style="font-size: 8pt; color: #64748b;">(0 mins)</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="pdf-stat-item">
                                                    <div class="pdf-stat-label">Status</div>
                                                    <div class="pdf-stat-value" style="font-size: 10pt;">
                                                        @php $last = $punches->sortByDesc('timestamp')->first(); @endphp
                                                        {{ $last ? strtoupper(str_replace('_', ' ', $last->type)) : 'NO ACTIVITY' }}
                                                    </div>
                                                </div>
                                            </div>

                                            <h4 style="border-left: 4px solid #7366ff; padding-left: 10px; color: #1e293b; margin-top: 30px; margin-bottom: 15px;">Activity Timeline</h4>
                                            <table class="pdf-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 15%;">Time</th>
                                                        <th style="width: 15%;">Type</th>
                                                        <th style="width: 45%;">Details</th>
                                                        <th style="width: 25%;">Coordinates</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if($sortedEvents->isEmpty())
                                                        <tr><td colspan="4" style="text-align: center; color: #64748b;">No activity recorded yet.</td></tr>
                                                    @else
                                                        @foreach($sortedEvents as $event)
                                                            <tr>
                                                                <td style="font-weight: bold;">{{ \Carbon\Carbon::parse($event['time'])->format('h:i A') }}</td>
                                                                <td>
                                                                    @if($event['type'] == 'punch')
                                                                        <span class="pdf-badge {{ $event['data']->type == 'punch_in' ? 'pdf-bg-success' : 'pdf-bg-danger' }}">
                                                                            {{ strtoupper(str_replace('_', ' ', $event['data']->type)) }}
                                                                        </span>
                                                                    @elseif($event['type'] == 'alert')
                                                                        <span class="pdf-badge pdf-bg-danger">ALERT</span>
                                                                    @elseif($event['type'] == 'offline')
                                                                        <span class="pdf-badge" style="background: #64748b !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact;">OFFLINE</span>
                                                                    @elseif($event['type'] == 'stop')
                                                                        <span class="pdf-badge pdf-bg-warning">STOP</span>
                                                                    @else
                                                                        <span class="pdf-badge pdf-bg-warning">VISIT</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($event['type'] == 'punch')
                                                                        <span class="pdf-timeline-type">Attendance Log</span><br>
                                                                        <span class="pdf-timeline-details">Location verified via registered device.</span>
                                                                        @if(isset($event['data']->is_mock_location) && $event['data']->is_mock_location)
                                                                            <br><span style="color: #b91c1c; font-size: 8pt;">Mock GPS detected!</span>
                                                                        @endif
                                                                    @elseif($event['type'] == 'alert')
                                                                        <span class="pdf-timeline-type">System Alert</span><br>
                                                                        <span class="pdf-timeline-details">{{ $event['data']->remarks }}</span>
                                                                    @elseif($event['type'] == 'offline')
                                                                        <span class="pdf-timeline-type">Offline Period</span><br>
                                                                        <span class="pdf-timeline-details">
                                                                            @if(isset($event['data']->reason) && $event['data']->reason)
                                                                                Disconnected: {{ $event['data']->reason }}<br>
                                                                            @endif
                                                                            Duration: {{ $event['data']->to_time ? \Carbon\Carbon::parse($event['data']->from_time)->diffInMinutes($event['data']->to_time) . ' mins' : 'Ongoing' }}<br>
                                                                            Resumed: {{ $event['data']->to_time ? \Carbon\Carbon::parse($event['data']->to_time)->format('h:i A') : 'N/A' }}
                                                                        </span>
                                                                    @elseif($event['type'] == 'stop')
                                                                        <span class="pdf-timeline-type">Stationary Stop</span><br>
                                                                        <span class="pdf-timeline-details">
                                                                            Duration: {{ \App\Http\Controllers\ReportController::formatDurationHumans($event['data']['start_time'], $event['data']['end_time']) }}<br>
                                                                            {{ \Carbon\Carbon::parse($event['data']['start_time'])->format('h:i A') }} - {{ \Carbon\Carbon::parse($event['data']['end_time'])->format('h:i A') }}
                                                                        </span>
                                                                    @else
                                                                        <span class="pdf-timeline-type">{{ $event['data']->customer_name ?? $event['data']->party?->name ?? 'Customer Visit' }}</span><br>
                                                                        <span class="pdf-timeline-details">{{ ucfirst($event['data']->customer_category ?? 'Retailer') }} visit logged.</span><br>
                                                                        <span class="pdf-timeline-details">
                                                                            <strong>Start:</strong> {{ isset($event['data']->check_in_at) ? \Carbon\Carbon::parse($event['data']->check_in_at)->format('h:i A') : (isset($event['data']->start_at) ? \Carbon\Carbon::parse($event['data']->start_at)->format('h:i A') : 'N/A') }} | 
                                                                            <strong>End:</strong> {{ isset($event['data']->check_out_at) ? \Carbon\Carbon::parse($event['data']->check_out_at)->format('h:i A') : 'Ongoing' }}
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                                <td style="font-family: monospace; font-size: 8pt;">
                                                                    @if(isset($event['data']->latitude) && isset($event['data']->longitude))
                                                                        {{ number_format($event['data']->latitude, 8) }},<br>{{ number_format($event['data']->longitude, 8) }}
                                                                    @elseif(isset($event['data']['lat']) && isset($event['data']['lng']))
                                                                        {{ number_format($event['data']['lat'], 8) }},<br>{{ number_format($event['data']['lng'], 8) }}
                                                                    @elseif(isset($event['data']->location_lat) && isset($event['data']->location_lng))
                                                                        {{ number_format($event['data']->location_lat, 8) }},<br>{{ number_format($event['data']->location_lng, 8) }}
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                            
                                            <div style="page-break-before: always;"></div>
                                            
                                            @if($locations->count() > 0)
                                            <h4 style="border-left: 4px solid #7366ff; padding-left: 10px; color: #1e293b; margin-top: 30px; margin-bottom: 15px;">Route Data Samples</h4>
                                            <p style="font-size: 8pt; color: #64748b; margin-bottom: 10px;">Displaying high-frequency location pings used for route reconstruction.</p>
                                            <table class="pdf-table">
                                                <thead>
                                                    <tr>
                                                        <th>Ping Time</th>
                                                        <th>Latitude</th>
                                                        <th>Longitude</th>
                                                        <th>Security Check</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($locations->take(15) as $loc)
                                                        <tr>
                                                            <td>{{ $loc->timestamp->format('H:i:s') }}</td>
                                                            <td style="font-family: monospace;">{{ number_format($loc->latitude, 8) }}</td>
                                                            <td style="font-family: monospace;">{{ number_format($loc->longitude, 8) }}</td>
                                                            <td>
                                                                @if($loc->is_mock_location)
                                                                    <span class="pdf-badge pdf-bg-danger">MOCK GPS DETECTED</span>
                                                                @else
                                                                    <span style="color: #10b981; font-size: 8pt;">✓ Valid GPS</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    @if($locations->count() > 15)
                                                        <tr>
                                                            <td colspan="4" style="text-align: center; font-size: 8pt; color: #94a3b8; font-style: italic;">... {{ $locations->count() - 15 }} additional pings omitted for brevity ...</td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                            @else
                                            <h4 style="border-left: 4px solid #7366ff; padding-left: 10px; color: #1e293b; margin-top: 30px; margin-bottom: 15px;">Route Data Samples</h4>
                                            <table class="pdf-table">
                                                <thead><tr><th>Ping Time</th><th>Latitude</th><th>Longitude</th><th>Security Check</th></tr></thead>
                                                <tbody><tr><td colspan="4" style="text-align: center; color: #64748b;">No telemetry data available.</td></tr></tbody>
                                            </table>
                                            @endif

                                            <div class="pdf-footer">
                                                Confidentially generated for Atomed Wellness Admin | This document contains verified GPS telemetry data.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key', env('GOOGLE_MAPS_API_KEY')) }}&libraries=geometry&callback=initMap"
        async defer></script>
    <script>
        let map, staffMarker;
        let routePaths = [];
        let currentRoutePath = null;
        let pathPoints = [];
        let snappedPoints = [];
        let markers = [];
        let lastTimestamp = null;

        function createNewPolyline() {
            let path = new google.maps.Polyline({
                path: [],
                geodesic: true,
                strokeColor: "#7366ff",
                strokeOpacity: 0.8,
                strokeWeight: 5,
                map: map
            });
            routePaths.push(path);
            return path;
        }
        const apiKey = "{{ config('services.google_maps.key', env('GOOGLE_MAPS_API_KEY')) }}";

        async function snapPathToRoads(points) {
            if (points.length < 2) return points;

            // Chunk points to stay within Roads API limits (max 100 per request)
            const chunks = [];
            for (let i = 0; i < points.length; i += 100) {
                chunks.push(points.slice(i, i + 100));
            }

            let allSnapped = [];
            for (const chunk of chunks) {
                const pathParam = chunk.map(p => `${p.lat},${p.lng}`).join('|');
                try {
                    const response = await fetch(`https://roads.googleapis.com/v1/snapToRoads?path=${pathParam}&interpolate=true&key=${apiKey}`);
                    const data = await response.json();
                    if (data.snappedPoints) {
                        allSnapped = allSnapped.concat(data.snappedPoints.map(p => ({
                            lat: p.location.latitude,
                            lng: p.location.longitude
                        })));
                    } else {
                        // Fallback if API returns error JSON (e.g., 403 Permission Denied)
                        allSnapped = allSnapped.concat(chunk);
                    }
                } catch (e) {
                    console.error('Snap to Roads chunk failed:', e);
                    allSnapped = allSnapped.concat(chunk);
                }
            }
            return allSnapped;
        }

        function initMap() {
            const defaultCenter = { lat: 20.5937, lng: 78.9629 };

            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 5,
                center: defaultCenter,
                styles: [
                    { "featureType": "poi", "stylers": [{ "visibility": "off" }] }
                ]
            });

            // Initial Plotting
            loadInitialData();

            // Initialize WebSocket Listener
            initRealTimeTracking();
        }

        async function loadInitialData() {
            const bounds = new google.maps.LatLngBounds();

            // 1. Plot History Path
            let pathSegments = [];
            let currentSegment = [];

            @foreach($locations as $loc)
                (function() {
                    let currentTimestamp = new Date("{{ str_replace('-', '/', $loc->timestamp) }}").getTime();
                    let point = { lat: {{ $loc->latitude }}, lng: {{ $loc->longitude }} };
                    
                    if (lastTimestamp) {
                        let diffMins = (currentTimestamp - lastTimestamp) / (1000 * 60);
                        if (diffMins > 15) {
                            if (currentSegment.length > 0) {
                                pathSegments.push(currentSegment);
                            }
                            currentSegment = [];
                        }
                    }
                    currentSegment.push(point);
                    lastTimestamp = currentTimestamp;
                    
                    pathPoints.push(point);
                })();
            @endforeach

            if (currentSegment.length > 0) {
                pathSegments.push(currentSegment);
            }

            if (pathSegments.length > 0) {
                for (let segment of pathSegments) {
                    if (segment.length > 0) {
                        let snappedSegment = await snapPathToRoads(segment);
                        snappedPoints = snappedPoints.concat(snappedSegment);
                        
                        currentRoutePath = createNewPolyline();
                        currentRoutePath.setPath(snappedSegment);
                        
                        snappedSegment.forEach(p => bounds.extend(p));
                    }
                }
                // Update frontend distance display based on snapped points
                updateDistanceDisplay(snappedPoints);
            }

            // 2. Add Current Position Marker (if today and has locations)
            if (pathPoints.length > 0) {
                const lastPos = pathPoints[pathPoints.length - 1];
                staffMarker = new google.maps.Marker({
                    position: lastPos,
                    map: map,
                    title: "Current Position",
                    icon: {
                        path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                        scale: 5,
                        fillColor: "#7366ff",
                        fillOpacity: 1,
                        strokeWeight: 2,
                        rotation: 0
                    }
                });
            }

            // 3. Plot Punches
            @foreach($punches as $p)
                addSpecialMarker({{ $p->latitude }}, {{ $p->longitude }}, "{{ $p->type == 'punch_in' ? '#51bb25' : '#f73164' }}", "fa-user", 1000, 6);
                bounds.extend({ lat: {{ $p->latitude }}, lng: {{ $p->longitude }} });
            @endforeach

            // 4. Plot Visits
            @foreach($visits as $v)
                addSpecialMarker({{ $v->latitude }}, {{ $v->longitude }}, "#7366ff", "fa-store", 500, 7);
                bounds.extend({ lat: {{ $v->latitude }}, lng: {{ $v->longitude }} });
            @endforeach

            // 5. Plot Stops (> 5 mins)
            @foreach($stops as $stop)
                addStopMarker({{ $stop['lat'] }}, {{ $stop['lng'] }}, '{{ \App\Http\Controllers\ReportController::formatDurationHumans($stop['start_time'], $stop['end_time']) }}', '{{ \Carbon\Carbon::parse($stop['start_time'])->format('h:i A') }}', '{{ \Carbon\Carbon::parse($stop['end_time'])->format('h:i A') }}');
                bounds.extend({ lat: {{ $stop['lat'] }}, lng: {{ $stop['lng'] }} });
            @endforeach

                if (!bounds.isEmpty()) {
                map.fitBounds(bounds);
                
                // Prevent extreme zoom level when there is only one point or points are very close
                google.maps.event.addListenerOnce(map, "idle", function() { 
                    if (map.getZoom() > 15) {
                        map.setZoom(15);
                    }
                });
            }
        }

        function addSpecialMarker(lat, lng, color, icon, zIndexParam = 500, scaleParam = 7) {
            new google.maps.Marker({
                position: { lat: lat, lng: lng },
                map: map,
                zIndex: zIndexParam,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: scaleParam,
                    fillColor: color,
                    fillOpacity: 1,
                    strokeColor: "#fff",
                    strokeWeight: 2
                }
            });
        }

        function addStopMarker(lat, lng, duration, startTime, endTime) {
            const marker = new google.maps.Marker({
                position: { lat: parseFloat(lat), lng: parseFloat(lng) },
                map: map,
                zIndex: 100,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 8,
                    fillColor: "#ff9800", // Orange
                    fillOpacity: 1,
                    strokeColor: "#fff",
                    strokeWeight: 2
                },
                title: `Stop`
            });
            
            const infoWindow = new google.maps.InfoWindow({
                content: `<div class="custom-info-window">
                            <h6 class="text-warning fw-bold mb-1"><i class="fa fa-hand-paper text-warning me-2"></i>Stopped</h6>
                            <div class="small"><b>Duration:</b> ${duration}</div>
                            <div class="small text-muted">${startTime} to ${endTime}</div>
                          </div>`
            });
            
            marker.addListener('click', () => {
                infoWindow.open(map, marker);
            });
        }

        function flyToLocation(lat, lng) {
            const pos = { lat: parseFloat(lat), lng: parseFloat(lng) };
            map.panTo(pos);
            map.setZoom(17);
        }

        function calculatePathDistance(points) {
            let total = 0;
            for (let i = 0; i < points.length - 1; i++) {
                const p1 = new google.maps.LatLng(points[i].lat, points[i].lng);
                const p2 = new google.maps.LatLng(points[i + 1].lat, points[i + 1].lng);
                total += google.maps.geometry.spherical.computeDistanceBetween(p1, p2);
            }
            return (total / 1000).toFixed(2); // Convert meters to KM
        }

        function updateDistanceDisplay(points) {
            const km = calculatePathDistance(points);
            $('#distanceCovered').html(`${km} <span class="small fw-normal">KM</span>`);
        }

        function initRealTimeTracking() {
            // Check if user ID matches current tracking and date is today
            const trackingUserId = "{{ $user->id }}";
            const trackingDate = "{{ $date }}";
            const today = new Date().toISOString().split('T')[0];

            if (trackingDate !== today) return;

            // Using Laravel Echo (assumed available globally from app.js)
            if (typeof Echo !== 'undefined') {
                Echo.channel('tracking')
                    .listen('.location.updated', (e) => {
                        console.log('Real-time update:', e);
                        if (e.userId == trackingUserId) {
                            updateLiveMap(e.latitude, e.longitude);
                        }
                    });
            }
        }

        async function updateLiveMap(lat, lng) {
            const newPos = { lat: parseFloat(lat), lng: parseFloat(lng) };
            const currentTimestamp = new Date().getTime();
            
            if (lastTimestamp) {
                let diffMins = (currentTimestamp - lastTimestamp) / (1000 * 60);
                if (diffMins > 15) {
                    currentRoutePath = createNewPolyline();
                }
            } else {
                if (!currentRoutePath) {
                    currentRoutePath = createNewPolyline();
                }
            }
            lastTimestamp = currentTimestamp;

            pathPoints.push(newPos);

            // Snap only the last segment for performance
            const lastTwo = pathPoints.slice(-2);
            let latestSnappedPoint = newPos;

            if (lastTwo.length === 2) {
                const snapped = await snapPathToRoads(lastTwo);
                if (snapped.length > 1) {
                    latestSnappedPoint = snapped[snapped.length - 1];
                }
            }
            
            snappedPoints.push(latestSnappedPoint);

            let currentPath = currentRoutePath.getPath();
            currentPath.push(new google.maps.LatLng(latestSnappedPoint.lat, latestSnappedPoint.lng));

            updateDistanceDisplay(snappedPoints);

            // Update Arrow Marker
            if (staffMarker) {
                staffMarker.setPosition(newPos);
            } else {
                staffMarker = new google.maps.Marker({
                    position: newPos,
                    map: map,
                    icon: {
                        path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                        scale: 5,
                        fillColor: "#7366ff",
                        fillOpacity: 1,
                        strokeWeight: 2
                    }
                });
            }

            // Smooth Pan
            map.panTo(newPos);

            // Visual feedback
            $('#liveStatus').text('Moving').removeClass('bg-success').addClass('bg-info');
            setTimeout(() => $('#liveStatus').text('Active').removeClass('bg-info').addClass('bg-success'), 3000);
        }
    </script>
@endpush