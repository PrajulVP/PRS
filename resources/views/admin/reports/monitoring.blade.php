@extends('layouts.admin')

@section('title', 'Field Staff Monitoring Dashboard')

@push('styles')
    <style>
        #monitoring-map { height: calc(100vh - 180px); border-radius: 12px; z-index: 1; border: 1px solid var(--med-border); }
        .staff-sidebar-card { height: calc(100vh - 180px); overflow-y: auto; }
        
        .staff-item {
            cursor: pointer;
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
        }
        .staff-item:hover { background: rgba(0,0,0,0.02); }
        .staff-item.active { 
            background: rgba(115, 102, 255, 0.05); 
            border-left-color: #7366ff;
        }
        
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
            animation: pulse-status 2s infinite;
        }
        
        @keyframes pulse-status {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .alert-toast-container {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
            width: 300px;
        }
        
        .live-indicator {
            display: inline-flex;
            align-items: center;
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .live-dot {
            width: 6px;
            height: 6px;
            background: #e74c3c;
            border-radius: 50%;
            margin-right: 6px;
            animation: blinker 1s linear infinite;
        }
        @keyframes blinker { 50% { opacity: 0; } }

        .staff-avatar {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 10px;
        }

        /* Custom Marker Styling */
        .staff-marker-container {
            position: relative;
            width: 30px;
            height: 30px;
        }
        .staff-marker-inner {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }
    </style>
@endpush

@section('page-body')
<div class="container-fluid">
    <div class="page-title text-start mb-3">
        <div class="row m-0 align-items-center">
          <div class="col-sm-6 p-0">
            <h4 class="mb-0 fw-bold">Live Field Operations Monitoring</h4>
            <div class="live-indicator mt-1">
                <span class="live-dot"></span> Real-time Sync: <span id="last-update">...</span>
            </div>
          </div>
          <div class="col-sm-6 p-0 text-end">
              <div class="btn-group">
                <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-bold" onclick="fetchData()" style="background: #00497a; border: none; transition: all 0.3s;">
                    <i class="fa fa-sync-alt me-1"></i> Sync Now
                </button>
              </div>
          </div>
        </div>
    </div>

    <!-- Alert Container -->
    <div class="alert-toast-container" id="alert-container"></div>

    <div class="row">
        <!-- Sidebar: Staff List -->
        <div class="col-xl-3 col-lg-4">
            <div class="card staff-sidebar-card border-0 shadow-sm rounded-4">
                <div class="card-header border-0 py-3 bg-transparent">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0"><i class="fa fa-search text-muted"></i></span>
                        <input type="text" id="staff-search" class="form-control border-start-0" placeholder="Search staff...">
                    </div>
                </div>
                <div class="card-body p-0" id="staff-list">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Locating personnel...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main: Live Map -->
        <div class="col-xl-9 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div id="monitoring-map"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key', env('GOOGLE_MAPS_API_KEY')) }}"></script>
    <script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
    <script>
        let map, markerCluster;
        let staffMarkers = {};
        let staffData = {};

        function initMap() {
            const defaultLoc = { lat: 20.5937, lng: 78.9629 };
            map = new google.maps.Map(document.getElementById("monitoring-map"), {
                zoom: 5,
                center: defaultLoc,
                styles: [
                    { "featureType": "poi", "stylers": [{ "visibility": "off" }] }
                ]
            });

            markerCluster = new markerClusterer.MarkerClusterer({ map, markers: [] });
            
            // Initial Fetch
            fetchData();

            // Setup Real-time Listener
            if (typeof Echo !== 'undefined') {
                Echo.channel('tracking')
                    .listen('.location.updated', (e) => {
                        console.log('Monitoring update:', e);
                        handleRealTimeUpdate(e);
                    });

                Echo.channel('attendance')
                    .listen('.attendance.logged', (e) => {
                        console.log('Attendance update:', e);
                        fetchData(); // Refresh list when someone punches in/out
                    });
            }
        }

        function fetchData() {
            $.get("{{ route('admin.reports.monitoring.data') }}", function(data) {
                $('#last-update').text(data.timestamp);
                updateStaffList(data.staff);
                updateMapMarkers(data.staff);
                processAlerts(data.alerts);
            }).fail(function() {
                $('#last-update').text('Sync Failed');
            });
        }

        function handleRealTimeUpdate(e) {
            // Update staff marker if it exists
            if (staffMarkers[e.userId]) {
                const pos = { lat: parseFloat(e.latitude), lng: parseFloat(e.longitude) };
                staffMarkers[e.userId].setPosition(pos);
                
                // Update small stats in list if visible
                const $distanceEl = $(`#staff-card-${e.userId} .distance-val`);
                if ($distanceEl.length) {
                    // We don't have new distance here easily without API call, 
                    // but we can at least show activity
                    $(`#staff-card-${e.userId}`).addClass('active');
                    setTimeout(() => $(`#staff-card-${e.userId}`).removeClass('active'), 2000);
                }
            } else {
                // If marker doesn't exist, maybe they just logged in? Refresh all.
                fetchData();
            }
        }

        function updateStaffList(staff) {
            let html = '';
            staff.forEach(s => {
                staffData[s.user_id] = s;
                html += `
                    <div class="staff-item p-3 border-bottom" onclick="focusStaff(${s.user_id})" id="staff-card-${s.user_id}">
                        <div class="d-flex align-items-center">
                            <img src="${s.avatar || 'https://via.placeholder.com/40'}" class="staff-avatar me-3 shadow-sm border">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold small text-dark">${s.name}</h6>
                                    <span class="badge bg-light text-dark small" style="background-color: ${s.status_color} !important; color: white !important; font-size: 8px;">${s.status.toUpperCase()}</span>
                                </div>
                                <p class="mb-0 text-muted small" style="font-size: 11px;">Manager: ${s.manager}</p>
                                <div class="mt-2 d-flex gap-2">
                                    <span class="text-primary small distance-val" style="font-size: 10px;"><i class="fa fa-user me-1"></i>${s.stats.distance}</span>
                                    <span class="text-info small" style="font-size: 10px;"><i class="fa fa-shopping-cart me-1"></i>${s.stats.visits} Visits</span>
                                </div>
                                ${s.ongoing_visit ? `<div class="mt-1 small p-1 rounded" style="font-size: 10px; background: rgba(155, 89, 182, 0.1); color: #9b59b6;"><i class="fa fa-map-marker me-1"></i>At: ${s.ongoing_visit}</div>` : ''}
                            </div>
                        </div>
                    </div>`;
            });
            $('#staff-list').html(html);
        }

        function updateMapMarkers(staff) {
            // Remove existing markers from cluster
            markerCluster.clearMarkers();
            
            staff.forEach(s => {
                if (s.lat && s.lng) {
                    const pos = { lat: parseFloat(s.lat), lng: parseFloat(s.lng) };
                    
                    const marker = new google.maps.Marker({
                        position: pos,
                        title: s.name,
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 8,
                            fillColor: s.status_color,
                            fillOpacity: 1,
                            strokeColor: "#fff",
                            strokeWeight: 3
                        }
                    });

                    const infoWindow = new google.maps.InfoWindow({
                        content: `
                            <div class="p-2" style="min-width: 150px; font-family: 'Montserrat', sans-serif;">
                                <div class="d-flex align-items-center mb-2">
                                    <img src="${s.avatar || 'https://via.placeholder.com/30'}" class="staff-avatar me-2" style="width:30px; height:30px;">
                                    <h6 class="mb-0 fw-bold">${s.name}</h6>
                                </div>
                                <p class="mb-1 text-muted small">Status: <span class="fw-bold" style="color:${s.status_color}">${s.status}</span></p>
                                <p class="mb-1 text-muted small">Daily Distance: ${s.stats.distance}</p>
                                <p class="mb-2 text-muted small">Last Seen: ${s.last_seen}</p>
                                <a href="/admin/reports/fieldstaffs/tracking?user_id=${s.user_id}" class="btn btn-primary btn-sm w-100 text-white" style="font-size: 10px;">Full History</a>
                            </div>
                        `
                    });

                    marker.addListener("click", () => {
                        infoWindow.open({ anchor: marker, map });
                    });

                    staffMarkers[s.user_id] = marker;
                    markerCluster.addMarker(marker);
                }
            });
        }

        function focusStaff(userId) {
            $('.staff-item').removeClass('active');
            $(`#staff-card-${userId}`).addClass('active');
            
            if (staffMarkers[userId]) {
                const marker = staffMarkers[userId];
                map.panTo(marker.getPosition());
                map.setZoom(15);
                google.maps.event.trigger(marker, 'click');
            }
        }

        function processAlerts(alerts) {
            const container = $('#alert-container');
            alerts.forEach(a => {
                const alertId = `alert-${a.staff_id}-${a.type}`;
                if ($(`#${alertId}`).length === 0) {
                    const html = `
                        <div class="alert alert-danger border-danger shadow p-3 mb-2" id="${alertId}" style="border-left: 5px solid #e74c3c !important; background: #fff;">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1 text-danger fw-bold small"><i class="fa fa-exclamation-triangle me-2"></i> ${a.type === 'mock_gps' ? 'Fake GPS Alert' : 'Inactivity Alert'}</h6>
                                <button type="button" class="btn-close" onclick="$(this).parent().parent().remove()" style="font-size: 10px;"></button>
                            </div>
                            <p class="mb-0 small text-dark" style="font-size: 11px;">${a.message}</p>
                            <small class="text-muted" style="font-size: 9px;">${a.time}</small>
                        </div>`;
                    container.prepend(html);
                }
            });
        }

        // Initialize when Google Maps is ready
        $(function() {
            initMap();
            
            $('#staff-search').on('keyup', function() {
                const val = $(this).val().toLowerCase();
                $('.staff-item').each(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
                });
            });
        });
    </script>
@endpush
