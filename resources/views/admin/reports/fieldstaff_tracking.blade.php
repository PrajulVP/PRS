@extends('layouts.admin')

@section('title', 'Field Staff Tracking - ' . $user->name)

@@push('styles')
    <style>
        #map { height: 600px; border-radius: 12px; z-index: 1; border: 1px solid var(--med-border, #e2e8f0); }
        .tracking-info-card { height: 600px; overflow-y: auto; background-color: transparent !important; }
        
        /* Custom Marker Info Window Styling */
        .gm-style-iw-d { overflow: hidden !important; }
        .custom-info-window { padding: 10px; font-family: 'Montserrat', sans-serif; }

        /* Timeline refinement */
        .timeline-item { 
            border-left: 2px solid #e0e0e0; 
            position: relative; 
            padding-left: 20px; 
            padding-bottom: 15px; 
            cursor: pointer;
            transition: all 0.2s ease;
            border-radius: 0 8px 8px 0;
        }
        .timeline-item:hover {
            background: rgba(115, 102, 255, 0.05);
            transform: translateX(5px);
        }
        .timeline-item::before { 
            content: ''; 
            position: absolute; 
            left: -6px; 
            top: 10px; 
            width: 10px; 
            height: 10px; 
            border-radius: 50%; 
            background: #7366ff; 
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px rgba(115, 102, 255, 0.2);
            z-index: 2;
        }
        .timeline-item.punch { border-left-color: #51bb25; }
        .timeline-item.punch::before { background: #51bb25; box-shadow: 0 0 0 2px rgba(81, 187, 37, 0.2); }
        .timeline-item.visit { border-left-color: #f8d62b; }
        .timeline-item.visit::before { background: #f8d62b; box-shadow: 0 0 0 2px rgba(248, 214, 43, 0.2); }
        
        .legend {
            padding: 12px;
            background: var(--med-bg-card, #ffffff);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 12px;
            line-height: 24px;
            color: #333;
            border: 1px solid var(--med-border, rgba(0,0,0,0.05));
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
        .dark-only .stats-card-modern,
        [data-theme="dark"] .stats-card-modern {
            background: rgba(255, 255, 255, 0.05) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #fff;
        }
    </style>
@endpush

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-card-theme border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <div>
                        <h5><i class="fa fa-map-marked-alt text-primary me-2"></i>Live Tracking: {{ $user->name }}</h5>
                        <p class="mb-0 text-muted">Real-time movement for <strong>{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</strong></p>
                    </div>
                    <form action="{{ route('admin.reports.fieldstaff.tracking') }}" method="GET" class="d-flex gap-2">
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
                        <a href="{{ route('admin.reports.fieldstaffs') }}" class="btn btn-sm btn-primary">Back</a>
                    </form>
                </div>
                <div class="card-body">
                    <!-- Stats Summary Row -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-6">
                            <div class="p-3 rounded text-center border-start border-primary border-4 shadow-sm stats-card-modern">
                                <h6 class="text-muted small mb-1 text-uppercase fw-700">Distance</h6>
                                <h4 class="mb-0 text-primary fw-800" id="distanceCovered">{{ number_format($totalDistance ?? 0, 2) }} <span class="small fw-normal">KM</span></h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 rounded text-center border-start border-success border-4 shadow-sm stats-card-modern">
                                <h6 class="text-muted small mb-1 text-uppercase fw-700">Punches</h6>
                                <h4 class="mb-0 text-success fw-800">{{ $punches->count() }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mt-md-0 mt-3">
                            <div class="p-3 rounded text-center border-start border-warning border-4 shadow-sm stats-card-modern">
                                <h6 class="text-muted small mb-1 text-uppercase fw-700">Visits</h6>
                                <h4 class="mb-0 text-warning fw-800">{{ $visits->count() }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mt-md-0 mt-3">
                            <div class="p-3 rounded text-center border-start border-info border-4 shadow-sm stats-card-modern">
                                <h6 class="text-muted small mb-1 text-uppercase fw-700">Status</h6>
                                <h4 class="mb-0 text-info fw-800"><span class="badge bg-success" id="liveStatus">Active</span></h4>
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
                                <div><i style="background: #7366ff; border-radius: 0; height: 2px; margin-top: 11px;"></i> Route</div>
                            </div>
                        </div>

                        <!-- Sidebar Info Column -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card tracking-info-card border-0 shadow-none bg-transparent">
                                <div class="card-header bg-transparent pb-2 ps-0">
                                    <h6 class="mb-0">Activity Timeline</h6>
                                </div>
                                <div class="card-body p-0 pt-3" id="timelineContainer">
                                    @php
                                        $allEvents = collect();
                                        $punches->each(fn($p) => $allEvents->push(['type' => 'punch', 'time' => $p->timestamp, 'data' => $p]));
                                        $visits->each(fn($v) => $allEvents->push(['type' => 'visit', 'time' => $v->check_in_at, 'data' => $v]));
                                        $sortedEvents = $allEvents->sortBy('time');
                                    @endphp

                                    @if($sortedEvents->isEmpty())
                                        <div class="text-center py-5 no-activity">
                                            <i class="fa fa-walking-light fa-3x text-light mb-3"></i>
                                            <p class="text-muted">No activity recorded yet.</p>
                                        </div>
                                    @else
                                        @foreach($sortedEvents as $event)
                                            <div class="timeline-item {{ $event['type'] }}" 
                                                 onclick="flyToLocation({{ $event['data']->latitude }}, {{ $event['data']->longitude }})">
                                                <div class="d-flex justify-content-between">
                                                    <span class="small fw-bold">{{ $event['time']->format('h:i A') }}</span>
                                                    @if($event['type'] == 'punch')
                                                        <span class="badge badge-light-{{ $event['data']->type == 'punch_in' ? 'success' : 'danger' }} small">
                                                            {{ str_replace('_', ' ', $event['data']->type) }}
                                                        </span>
                                                    @else
                                                        <span class="badge badge-light-warning small">Visit</span>
                                                    @endif
                                                </div>
                                                <div class="mt-1">
                                                    @if($event['type'] == 'punch')
                                                        <p class="mb-0 small text-dark">Punched at location</p>
                                                        @if($event['data']->is_mock_location)
                                                            <div class="badge badge-light-danger small mt-1"><i class="fa fa-exclamation-triangle me-1"></i>Mock GPS!</div>
                                                        @endif
                                                    @else
                                                        <p class="mb-0 fw-bold small text-primary">{{ $event['data']->customer_name }}</p>
                                                        <p class="mb-0 text-muted small">{{ $event['data']->customer_category }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
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
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key', env('GOOGLE_MAPS_API_KEY')) }}&callback=initMap" async defer></script>
    <script>
        let map, routePath, staffMarker;
        let pathPoints = [];
        let markers = [];

        function initMap() {
            const defaultCenter = { lat: 20.5937, lng: 78.9629 };
            
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 5,
                center: defaultCenter,
                styles: [
                    { "featureType": "poi", "stylers": [{ "visibility": "off" }] }
                ]
            });

            // Initialize Route Path (Polyline)
            routePath = new google.maps.Polyline({
                path: pathPoints,
                geodesic: true,
                strokeColor: "#7366ff",
                strokeOpacity: 1.0,
                strokeWeight: 4,
                map: map
            });

            // Initial Plotting
            loadInitialData();

            // Initialize WebSocket Listener
            initRealTimeTracking();
        }

        function loadInitialData() {
            const bounds = new google.maps.LatLngBounds();

            // 1. Plot History Path
            @foreach($locations as $loc)
                pathPoints.push({ lat: {{ $loc->latitude }}, lng: {{ $loc->longitude }} });
            @endforeach
            routePath.setPath(pathPoints);
            pathPoints.forEach(p => bounds.extend(p));

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
                addSpecialMarker({{ $p->latitude }}, {{ $p->longitude }}, "{{ $p->type == 'punch_in' ? '#51bb25' : '#f73164' }}", "fa-user");
                bounds.extend({ lat: {{ $p->latitude }}, lng: {{ $p->longitude }} });
            @endforeach

            // 4. Plot Visits
            @foreach($visits as $v)
                addSpecialMarker({{ $v->latitude }}, {{ $v->longitude }}, "#7366ff", "fa-store");
                bounds.extend({ lat: {{ $v->latitude }}, lng: {{ $v->longitude }} });
            @endforeach

            if (!bounds.isEmpty()) {
                map.fitBounds(bounds);
            }
        }

        function addSpecialMarker(lat, lng, color, icon) {
            new google.maps.Marker({
                position: { lat: lat, lng: lng },
                map: map,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 7,
                    fillColor: color,
                    fillOpacity: 1,
                    strokeColor: "#fff",
                    strokeWeight: 2
                }
            });
        }

        function flyToLocation(lat, lng) {
            const pos = { lat: parseFloat(lat), lng: parseFloat(lng) };
            map.panTo(pos);
            map.setZoom(17);
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

        function updateLiveMap(lat, lng) {
            const newPos = { lat: parseFloat(lat), lng: parseFloat(lng) };

            // Update Polyline
            pathPoints.push(newPos);
            routePath.setPath(pathPoints);

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
