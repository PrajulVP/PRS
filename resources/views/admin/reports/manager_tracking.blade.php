@extends('layouts.admin')

@section('title', 'Manager Tracking - ' . $user->name)

@push('styles')
    <style>
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

        .timeline-item.stop::before {
            background: #ff9800;
            box-shadow: 0 0 0 4px rgba(255, 152, 0, 0.15);
        }

        .timeline-item.offline::before {
            background: #6c757d;
            box-shadow: 0 0 0 4px rgba(108, 117, 125, 0.15);
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
        .timeline-item.stop {
            border-left-color: #ff9800;
        }
        .timeline-item.stop::before {
            background: #ff9800;
            box-shadow: 0 0 0 2px rgba(255, 152, 0, 0.2);
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
                                        <a href="{{ route('admin.manager.tracking.export', ['user_id' => $user->id, 'date' => $date, 'format' => 'csv']) }}"
                                            class="btn btn-primary shadow-sm rounded-3 d-flex align-items-center px-2"
                                            style="height: 32px; border: none; background: #1a3a63; font-size: 0.8rem;">
                                            <span class="fw-bold">CSV</span>
                                        </a>
                                        <a href="{{ route('admin.manager.tracking.export', ['user_id' => $user->id, 'date' => $date, 'format' => 'csv']) }}"
                                            class="btn btn-success shadow-sm rounded-3 d-flex align-items-center px-2"
                                            style="height: 32px; border: none; background: #28a745; font-size: 0.8rem;">
                                            <span class="fw-bold">Excel</span>
                                        </a>
                                        <a href="{{ route('admin.manager.tracking.export', ['user_id' => $user->id, 'date' => $date, 'format' => 'pdf']) }}"
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
                                        <form action="{{ route('admin.manager.tracking-map') }}" method="GET"
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
                                        <a href="{{ route('admin.manager.tracking') }}"
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
                                            $visits->each(fn($v) => $allEvents->push(['type' => 'visit', 'time' => $v->check_in_at, 'data' => $v]));
                                            $locations->whereNotNull('remarks')->each(fn($l) => $allEvents->push(['type' => 'alert', 'time' => $l->timestamp, 'data' => $l]));
                                            $sortedEvents = $allEvents->sortBy('time');
                                        @endphp

                                        @if($sortedEvents->isEmpty())
                                            <div class="text-center py-5 no-activity">
                                                <i class="fa fa-walking-light fa-3x text-light mb-3"></i>
                                                <p class="text-muted">No activity recorded yet.</p>
                                            </div>
                                        @else
                                            @foreach($sortedEvents as $event)
                                                <div class="timeline-item {{ $event['type'] }} {{ $event['type'] == 'punch' ? $event['data']->type : '' }}"
                                                    onclick="flyToLocation({{ $event['data']->latitude }}, {{ $event['data']->longitude }})">
                                                    <div class="d-flex justify-content-between">
                                                        <span class="small fw-bold">{{ $event['time']->format('h:i A') }}</span>
                                                        @if($event['type'] == 'punch')
                                                            <span class="badge {{ $event['data']->type == 'punch_in' ? 'bg-success' : '' }} text-white small timeline-badge" 
                                                                  @if($event['data']->type != 'punch_in') style="background-color: #e53935 !important;" @endif>
                                                                {{ str_replace('_', ' ', $event['data']->type) }}
                                                            </span>
                                                        @elseif($event['type'] == 'alert')
                                                            <span class="badge bg-danger text-white small timeline-badge">System Alert</span>
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
