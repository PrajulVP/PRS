@extends('layouts.admin')

@section('title', 'Field Staff Tracking - ' . $user->name)

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 600px; border-radius: 12px; z-index: 1; border: 1px solid var(--med-border); }
        .tracking-info-card { height: 600px; overflow-y: auto; }
        
        /* Custom Marker Pin Styling */
        .marker-pin {
            width: 30px;
            height: 30px;
            border-radius: 50% 50% 50% 0;
            position: absolute;
            transform: rotate(-45deg);
            left: 50%;
            top: 50%;
            margin: -15px 0 0 -15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .marker-pin::after {
            content: '';
            width: 24px;
            height: 24px;
            margin: 3px 0 0 3px;
            background: rgba(0,0,0,0.15);
            position: absolute;
            border-radius: 50%;
        }
        .custom-div-icon i {
            z-index: 10;
        }

        /* Animated Path Effect */
        .animated-polyline {
            stroke-dasharray: 12, 12;
            animation: dash-animation 20s linear infinite;
        }
        @keyframes dash-animation {
            from { stroke-dashoffset: 240; }
            to { stroke-dashoffset: 0; }
        }

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
            background: white;
            background: rgba(255,255,255,0.95);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 12px;
            line-height: 24px;
            color: #333;
            border: 1px solid rgba(0,0,0,0.05);
            font-size: 11px;
            font-weight: 600;
        }
        .legend i {
            width: 12px;
            height: 12px;
            float: left;
            margin-right: 8px;
            margin-top: 6px;
            border-radius: 50%;
        }
    </style>
@endpush

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5><i class="fa fa-map-marked-alt text-primary me-2"></i>Route History: {{ $user->name }}</h5>
                        <p class="mb-0 text-muted">Movement tracking for <strong>{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</strong></p>
                    </div>
                    <form action="{{ route('admin.reports.fieldstaff.tracking') }}" method="GET" class="d-flex gap-2">
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
                        <a href="{{ route('admin.reports.fieldstaffs') }}" class="btn btn-sm btn-primary">Back</a>
                    </form>
                </div>
                <div class="card-body">
                    <!-- Premium Stats Summary Row -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-light rounded text-center border-start border-primary border-4 shadow-sm" style="background: linear-gradient(to right, #f8f9fa, #fff) !important;">
                                <h6 class="text-muted small mb-1 text-uppercase fw-700" style="letter-spacing: 0.5px;">Distance Covered</h6>
                                <h4 class="mb-0 text-primary fw-800">{{ number_format($totalDistance ?? 0, 2) }} <span class="small fw-normal">KM</span></h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-light rounded text-center border-start border-success border-4 shadow-sm" style="background: linear-gradient(to right, #f8f9fa, #fff) !important;">
                                <h6 class="text-muted small mb-1 text-uppercase fw-700" style="letter-spacing: 0.5px;">Punches</h6>
                                <h4 class="mb-0 text-success fw-800">{{ $punches->count() }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mt-md-0 mt-3">
                            <div class="p-3 bg-light rounded text-center border-start border-warning border-4 shadow-sm" style="background: linear-gradient(to right, #f8f9fa, #fff) !important;">
                                <h6 class="text-muted small mb-1 text-uppercase fw-700" style="letter-spacing: 0.5px;">Visits Captured</h6>
                                <h4 class="mb-0 text-warning fw-800">{{ $visits->count() }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mt-md-0 mt-3">
                            <div class="p-3 bg-light rounded text-center border-start border-info border-4 shadow-sm" style="background: linear-gradient(to right, #f8f9fa, #fff) !important;">
                                <h6 class="text-muted small mb-1 text-uppercase fw-700" style="letter-spacing: 0.5px;">Path Points</h6>
                                <h4 class="mb-0 text-info fw-800">{{ $locations->count() }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Map Column -->
                        <div class="col-xl-8 col-lg-7">
                            <div id="map"></div>
                        </div>

                        <!-- Sidebar Info Column -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card tracking-info-card border-0 shadow-none">
                                <div class="card-header pb-2 ps-0">
                                    <h6 class="mb-0">Activity Timeline</h6>
                                </div>
                                <div class="card-body p-0 pt-3">
                                    @php
                                        $allEvents = collect();
                                        $punches->each(fn($p) => $allEvents->push(['type' => 'punch', 'time' => $p->timestamp, 'data' => $p]));
                                        $visits->each(fn($v) => $allEvents->push(['type' => 'visit', 'time' => $v->check_in_at, 'data' => $v]));
                                        $sortedEvents = $allEvents->sortBy('time');
                                    @endphp

                                    @if($sortedEvents->isEmpty())
                                        <div class="text-center py-5">
                                            <i class="fa fa-walking-light fa-3x text-light mb-3"></i>
                                            <p class="text-muted">No punch or visit activity recorded for this day.</p>
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
                                                        <p class="mb-0 small text-dark">Punched at location (GPS Verified)</p>
                                                        @if($event['data']->is_mock_location)
                                                            <div class="badge badge-light-danger small mt-1"><i class="fa fa-exclamation-triangle me-1"></i>Mock GPS Detected!</div>
                                                        @endif
                                                        <div class="small text-muted mt-1">Device: {{ $event['data']->device_id ?? 'Unknown' }}</div>
                                                    @else
                                                        <p class="mb-0 fw-bold small text-primary">{{ $event['data']->customer_name }}</p>
                                                        <p class="mb-0 text-muted small">Category: {{ $event['data']->customer_category }}</p>
                                                        @if($event['data']->is_flagged)
                                                            <div class="badge badge-light-danger small mt-1"><i class="fa fa-map-marker-alt me-1"></i>Geofencing Alert: Outside Area!</div>
                                                        @endif
                                                        @if($event['data']->notes)
                                                            <div class="bg-light p-2 mt-1 rounded small italic">"{{ $event['data']->notes }}"</div>
                                                        @endif
                                                        @if($event['data']->photo_path)
                                                            <div class="mt-2 text-center">
                                                                <img src="{{ asset('storage/' . $event['data']->photo_path) }}" class="img-fluid rounded border" style="max-height: 100px;">
                                                            </div>
                                                        @endif
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var map;
        function flyToLocation(lat, lng) {
            if (map) {
                map.flyTo([lat, lng], 17, {
                    duration: 1.5,
                    easeLinearity: 0.25
                });
            }
        }

        $(function() {
            map = L.map('map').setView([20.5937, 78.9629], 5); // Default to India center

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Custom Icon Creator
            function createCustomIcon(color, iconClass) {
                return L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div style='background-color:${color};' class='marker-pin'></div><i class='fa ${iconClass}' style='color:white;position:absolute;top:7px;left:50%;transform:translateX(-50%);font-size:12px;'></i>`,
                    iconSize: [30, 42],
                    iconAnchor: [15, 42],
                    popupAnchor: [0, -35]
                });
            }

            var punchInIcon = createCustomIcon('#51bb25', 'fa-sign-in-alt');
            var punchOutIcon = createCustomIcon('#f73164', 'fa-sign-out-alt');
            var visitIcon = createCustomIcon('#7366ff', 'fa-store');

            var pathCoordinates = [];
            var bounds = L.latLngBounds();

            // 1. Plot continuous logs (Path) with animation
            @foreach($locations as $loc)
                pathCoordinates.push([{{ $loc->latitude }}, {{ $loc->longitude }}]);
            @endforeach

            if (pathCoordinates.length > 2) {
                // Background path (static ghost path)
                L.polyline(pathCoordinates, {color: '#7366ff', weight: 2, opacity: 0.2}).addTo(map);
                
                // Animated foreground path
                L.polyline(pathCoordinates, {
                    color: '#7366ff', 
                    weight: 4, 
                    opacity: 0.8, 
                    className: 'animated-polyline'
                }).addTo(map);
                
                bounds.extend(pathCoordinates);
            } else if (pathCoordinates.length > 0) {
                bounds.extend(pathCoordinates);
            }

            // 2. Plot Punches
            @foreach($punches as $p)
                var pIcon = "{{ $p->type == 'punch_in' }}" == "1" ? punchInIcon : punchOutIcon;
                var pMarker = L.marker([{{ $p->latitude }}, {{ $p->longitude }}], { icon: pIcon }).addTo(map);
                
                pMarker.bindPopup(`
                    <div class="p-1">
                        <h6 class="mb-1 fw-bold text-{{ $p->type == 'punch_in' ? 'success' : 'danger' }}">{{ ucfirst(str_replace('_', ' ', $p->type)) }}</h6>
                        <p class="mb-0 small text-dark"><i class="fa fa-clock me-1 text-muted"></i> {{ $p->timestamp->format('h:i A') }}</p>
                        @if($p->is_mock_location)
                            <div class="mt-2 badge badge-light-danger px-2 py-1"><i class="fa fa-exclamation-triangle"></i> Mock GPS Detected</div>
                        @endif
                    </div>
                `);
                bounds.extend([{{ $p->latitude }}, {{ $p->longitude }}]);
            @endforeach

            // 3. Plot Visits
            @foreach($visits as $v)
                var vMarker = L.marker([{{ $v->latitude }}, {{ $v->longitude }}], { icon: visitIcon }).addTo(map);
                vMarker.bindPopup(`
                    <div class="p-1" style="min-width: 150px;">
                        <h6 class="mb-1 fw-bold text-primary">{{ $v->customer_name }}</h6>
                        <p class="mb-1 small text-muted"><i class="fa fa-tag me-1"></i> {{ $v->customer_category }}</p>
                        <p class="mb-0 small text-dark"><i class="fa fa-clock me-1 text-muted"></i> {{ $v->check_in_at->format('h:i A') }}</p>
                        @if($v->is_flagged)
                            <div class="mt-2 badge badge-light-danger px-2 py-1 italic"><i class="fa fa-map-marker-alt"></i> Outside Geofence</div>
                        @endif
                        @if($v->notes)
                            <hr class="my-2 opacity-25">
                            <p class="mb-0 small italic text-muted">"{{ $v->notes }}"</p>
                        @endif
                        @if($v->photo_path)
                            <div class="mt-2 text-center">
                                <img src="{{ asset('storage/' . $v->photo_path) }}" class="rounded shadow-sm" style="width: 100%; max-height: 120px; object-fit: cover;">
                            </div>
                        @endif
                    </div>
                `);
                bounds.extend([{{ $v->latitude }}, {{ $v->longitude }}]);
            @endforeach

            // Add Map Legend
            var legend = L.control({position: 'bottomright'});
            legend.onAdd = function (map) {
                var div = L.DomUtil.create('div', 'legend');
                div.innerHTML += '<div class="mb-1"><i style="background: #51bb25"></i> Punch In</div>';
                div.innerHTML += '<div class="mb-1"><i style="background: #f73164"></i> Punch Out</div>';
                div.innerHTML += '<div class="mb-1"><i style="background: #7366ff"></i> Customer Visit</div>';
                div.innerHTML += '<div><i style="background: #7366ff; border-radius: 0; height: 2px; margin-top: 11px;"></i> Movement Path</div>';
                return div;
            };
            legend.addTo(map);

            if (bounds.isValid()) {
                map.fitBounds(bounds, {padding: [50, 50]});
            }
        });
    </script>
@endpush
