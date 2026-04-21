@extends('layouts.admin')

@section('title', 'Field Staff Monitoring Dashboard')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
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
    </style>
@endpush

@section('page-body')
<div class="container-fluid">
    <div class="page-title text-start mb-3">
        <div class="row m-0 align-items-center">
          <div class="col-sm-6 p-0">
            <h4 class="mb-0 fw-bold">Live Field Operations monitoring</h4>
            <div class="live-indicator mt-1">
                <span class="live-dot"></span> Real-time: <span id="last-update">...</span>
            </div>
          </div>
          <div class="col-sm-6 p-0 text-end">
              <div class="btn-group">
                <button class="btn btn-outline-primary btn-sm" onclick="fetchData()"><i class="fa fa-sync-alt me-1"></i> Refresh</button>
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    <script>
        var map, markers, staffMarkers = {};
        
        function initMap() {
            map = L.map('monitoring-map').setView([20.5937, 78.9629], 5);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);
            markers = L.markerClusterGroup();
            map.addLayer(markers);
        }

        function fetchData() {
            $.get("{{ route('admin.reports.monitoring.data') }}", function(data) {
                $('#last-update').text(data.timestamp);
                updateStaffList(data.staff);
                updateMapMarkers(data.staff);
                processAlerts(data.alerts);
            });
        }

        function updateStaffList(staff) {
            let html = '';
            staff.forEach(s => {
                html += `
                    <div class="staff-item p-3 border-bottom" onclick="focusStaff(${s.id})" id="staff-card-${s.id}">
                        <div class="d-flex align-items-center">
                            <img src="${s.avatar}" class="staff-avatar me-3 shadow-sm border">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold small text-dark">${s.name}</h6>
                                    <span class="badge badge-light-${s.status === 'visiting' ? 'warning' : (s.status === 'idle' ? 'danger' : 'success')} small fs-10" style="font-size: 8px;">${s.status.toUpperCase()}</span>
                                </div>
                                <p class="mb-0 text-muted small" style="font-size: 11px;">Manager: ${s.manager}</p>
                                <div class="mt-2 d-flex gap-2">
                                    <span class="text-primary small" style="font-size: 10px;"><i class="fa fa-walking me-1"></i>${s.stats.distance}</span>
                                    <span class="text-info small" style="font-size: 10px;"><i class="fa fa-store me-1"></i>${s.stats.visits} Visits</span>
                                </div>
                                ${s.ongoing_visit ? `<div class="mt-1 small bg-soft-warning p-1 rounded" style="font-size: 10px;"><i class="fa fa-map-marker-alt me-1"></i>At: ${s.ongoing_visit}</div>` : ''}
                            </div>
                        </div>
                    </div>`;
            });
            $('#staff-list').html(html);
        }

        function updateMapMarkers(staff) {
            markers.clearLayers();
            staff.forEach(s => {
                if (s.lat && s.lng) {
                    const icon = L.divIcon({
                        className: 'custom-map-marker',
                        html: `<div style="background-color:${s.status_color}; border: 3px solid white; width:15px; height:15px; border-radius:50%; box-shadow: 0 0 10px ${s.status_color};"></div>`,
                        iconSize: [20, 20]
                    });

                    const m = L.marker([s.lat, s.lng], { icon: icon });
                    m.bindPopup(`
                        <div class="p-2" style="min-width: 150px;">
                            <div class="d-flex align-items-center mb-2">
                                <img src="${s.avatar}" class="staff-avatar me-2" style="width:30px; height:30px;">
                                <h6 class="mb-0 fw-bold">${s.name}</h6>
                            </div>
                            <p class="mb-1 text-muted small">Status: <span class="fw-bold" style="color:${s.status_color}">${s.status}</span></p>
                            <p class="mb-1 text-muted small">Daily Distance: ${s.stats.distance}</p>
                            <p class="mb-2 text-muted small">Last Seen: ${s.last_seen}</p>
                            <a href="/admin/reports/fieldstaffs/tracking?user_id=${s.user_id}" class="btn btn-primary btn-xs w-100 text-white">Full History</a>
                        </div>
                    `);
                    markers.addLayer(m);
                    staffMarkers[s.id] = m;
                }
            });
        }

        function focusStaff(id) {
            $('.staff-item').removeClass('active');
            $(`#staff-card-${id}`).addClass('active');
            if (staffMarkers[id]) {
                const marker = staffMarkers[id];
                map.flyTo(marker.getLatLng(), 15);
                marker.openPopup();
            }
        }

        function processAlerts(alerts) {
            const container = $('#alert-container');
            alerts.forEach(a => {
                const alertId = `alert-${a.staff_id}-${a.type}`;
                if ($(`#${alertId}`).length === 0) {
                    const html = `
                        <div class="alert alert-light-danger border-danger shadow p-3 mb-2 animate-fade-in" id="${alertId}" style="border-left: 5px solid #e74c3c !important;">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1 text-danger fw-bold"><i class="fa fa-exclamation-triangle me-2"></i> ${a.type === 'mock_gps' ? 'Fake GPS Alert' : 'Inactivity Alert'}</h6>
                                <button type="button" class="btn-close btn-xs" onclick="$(this).parent().parent().remove()"></button>
                            </div>
                            <p class="mb-0 small">${a.message}</p>
                            <small class="text-muted">${a.time}</small>
                        </div>`;
                    container.prepend(html);
                    // play sound or notify if needed
                }
            });
        }

        $(function() {
            initMap();
            fetchData();
            setInterval(fetchData, 60000); // Pulse every 60 seconds

            $('#staff-search').on('keyup', function() {
                const val = $(this).val().toLowerCase();
                $('.staff-item').each(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
                });
            });
        });
    </script>
@endpush
