@extends('layouts.admin')

@section('title', 'Field Staff Tracking - ' . $user->name)

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 600px; border-radius: 12px; z-index: 1; }
        .tracking-info-card { height: 600px; overflow-y: auto; }
        .timeline-item { border-left: 2px solid #e0e0e0; position: relative; padding-left: 20px; padding-bottom: 15px; }
        .timeline-item::before { 
            content: ''; 
            position: absolute; 
            left: -6px; 
            top: 0; 
            width: 10px; 
            height: 10px; 
            border-radius: 50%; 
            background: #7366ff; 
        }
        .timeline-item.punch { border-left-color: #51bb25; }
        .timeline-item.punch::before { background: #51bb25; }
        .timeline-item.visit { border-left-color: #f8d62b; }
        .timeline-item.visit::before { background: #f8d62b; }
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
                        <a href="{{ route('admin.reports.fieldstaffs') }}" class="btn btn-sm btn-outline-secondary">Back to List</a>
                    </form>
                </div>
                <div class="card-body">
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
                                            <div class="timeline-item {{ $event['type'] }}">
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
                                                    @else
                                                        <p class="mb-0 fw-bold small">{{ $event['data']->customer_name }}</p>
                                                        <p class="mb-0 text-muted small">Category: {{ $event['data']->customer_category }}</p>
                                                        @if($event['data']->notes)
                                                            <div class="bg-light p-2 mt-1 rounded small italic">"{{ $event['data']->notes }}"</div>
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
        $(function() {
            var map = L.map('map').setView([20.5937, 78.9629], 5); // Default to India center

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            var pathCoordinates = [];
            var bounds = L.latLngBounds();

            // 1. Plot continuous logs (Path)
            @foreach($locations as $loc)
                pathCoordinates.push([{{ $loc->latitude }}, {{ $loc->longitude }}]);
            @endforeach

            if (pathCoordinates.length > 0) {
                var polyline = L.polyline(pathCoordinates, {color: '#7366ff', weight: 4, opacity: 0.7, dashArray: '5, 10'}).addTo(map);
                bounds.extend(pathCoordinates);
            }

            // 2. Plot Punches
            @foreach($punches as $p)
                var markerColor = "{{ $p->type == 'punch_in' ? '#51bb25' : '#f73164' }}";
                var pMarker = L.circleMarker([{{ $p->latitude }}, {{ $p->longitude }}], {
                    radius: 8,
                    fillColor: markerColor,
                    color: "#fff",
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.8
                }).addTo(map);
                
                pMarker.bindPopup("<b>{{ ucfirst(str_replace('_', ' ', $p->type)) }}</b><br>Time: {{ $p->timestamp->format('h:i A') }}<br>Mock: {{ $p->is_mock_location ? 'YES!!' : 'No' }}");
                bounds.extend([{{ $p->latitude }}, {{ $p->longitude }}]);
            @endforeach

            // 3. Plot Visits
            @foreach($visits as $v)
                var vMarker = L.marker([{{ $v->latitude }}, {{ $v->longitude }}]).addTo(map);
                vMarker.bindPopup("<b>Visit: {{ $v->customer_name }}</b><br>Category: {{ $v->customer_category }}<br>Time: {{ $v->check_in_at->format('h:i A') }}");
                bounds.extend([{{ $v->latitude }}, {{ $v->longitude }}]);
            @endforeach

            if (bounds.isValid()) {
                map.fitBounds(bounds, {padding: [50, 50]});
            }
        });
    </script>
@endpush
