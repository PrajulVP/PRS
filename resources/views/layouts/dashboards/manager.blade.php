@extends('layouts.admin')

@section('page-body')

<style>
/* small spacing helpers */
.dashboard-row { margin-bottom: 1.25rem; }
.card .card-body canvas { width: 100% !important; height: 300px; } /* default chart height */
.list-group li { display: flex; justify-content: space-between; align-items: center; }
</style>

<div class="page-title">
    <div class="row">
        <div class="col-6"><h4>Manager Dashboard</h4></div>
        <div class="col-6">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">
                        <svg class="stroke-icon"><use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-home') }}"></use></svg>
                    </a>
                </li>
                <li class="breadcrumb-item">Dashboard</li>
                <li class="breadcrumb-item active">Manager</li>
            </ol>
        </div>
    </div>
</div>

<!-- Container-fluid starts -->
<div class="container-fluid">

    <!-- top widgets row -->
    <div class="row dashboard-row">
        <div class="col-xl-6 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body total-project border-b-primary border-2">
                    <span class="f-light f-w-500 f-14">Total Field Staff</span>
                    <div class="project-details">
                        <div class="project-counter"><h2 class="f-w-600">{{ $totalFieldStaff ?? 0 }}</h2></div>
                        <div class="product-sub bg-primary-light">
                            <svg class="invoice-icon"><use href="{{ asset('admin/assets/svg/icon-sprite.svg#color-swatch') }}"></use></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body total-Progress border-b-warning border-2">
                    <span class="f-light f-w-500 f-14">Total Retailers</span>
                    <div class="project-details">
                        <div class="project-counter"><h2 class="f-w-600">{{ $totalRetailers ?? 0 }}</h2></div>
                        <div class="product-sub bg-warning-light">
                            <svg class="invoice-icon"><use href="{{ asset('admin/assets/svg/icon-sprite.svg#tick-circle') }}"></use></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Layout B: top row: Pie | Bar -->
    <div class="row dashboard-row">
        <div class="col-xl-6 box-col-6">
            <div class="card">
                <div class="card-header"><h5>Order Status Distribution</h5></div>
                <div class="card-body">
                    <canvas id="orderStatusDistributionChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-6 box-col-6">
            <div class="card">
                <div class="card-header"><h5>Orders by Field Staff</h5></div>
                <div class="card-body">
                    <canvas id="ordersByFieldStaffChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- District chart full width -->
    <div class="row dashboard-row">
        <div class="col-xxl-12 box-col-12">
            <div class="card">
                <div class="card-header"><h5>Orders by District</h5></div>
                <div class="card-body">
                    <canvas id="ordersByDistrictChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Total orders over time full width -->
    <div class="row dashboard-row">
        <div class="col-xxl-12 box-col-12">
            <div class="card">
                <div class="card-header"><h5>Total Orders Over Time</h5></div>
                <div class="card-body">
                    <canvas id="totalOrdersOverTimeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Retailers / Distributors lists -->
    <div class="row dashboard-row">
        <div class="col-xl-6 box-col-6">
            <div class="card">
                <div class="card-header"><h5>Top 10 Retailers by Order Value</h5></div>
                <div class="card-body">
                    <ul id="topRetailersList" class="list-group">
                        <li class="list-group-item">Loading...</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-xl-6 box-col-6">
            <div class="card">
                <div class="card-header"><h5>Top 10 Distributors by Order Value</h5></div>
                <div class="card-body">
                    <ul id="topDistributorsList" class="list-group">
                        <li class="list-group-item">Loading...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div> <!-- container-fluid -->

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Utility: safe fetch and parse JSON
    async function fetchJson(url) {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error(`HTTP ${res.status} when fetching ${url}`);
        return res.json();
    }

    // PIE: Order Status Distribution
    (async function () {
        try {
            const data = await fetchJson('{{ route('dashboard.api.orderStatusDistribution') }}');
            const labels = Object.keys(data);
            const values = Object.values(data);
            new Chart(document.getElementById('orderStatusDistributionChart'), {
                type: 'pie',
                data: { labels, datasets: [{ data: values }] },
                options: { responsive: true, plugins: { title: { display: true, text: 'Order Status Distribution' } } }
            });
        } catch (e) { console.error(e); }
    })();

    // BAR: Orders by Field Staff
    (async function () {
        try {
            const data = await fetchJson('{{ route('dashboard.api.ordersByFieldStaff') }}');
            const labels = Object.keys(data);
            const values = Object.values(data);
            new Chart(document.getElementById('ordersByFieldStaffChart'), {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Orders', data: values }] },
                options: { responsive: true, scales: { y: { beginAtZero: true } } }
            });
        } catch (e) { console.error(e); }
    })();

    // BAR: Orders by District (full-width)
    (async function () {
        try {
            const data = await fetchJson('{{ route('dashboard.api.ordersByDistrict') }}');
            const labels = Object.keys(data);
            const values = Object.values(data);
            new Chart(document.getElementById('ordersByDistrictChart'), {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Orders', data: values }] },
                options: { responsive: true, scales: { y: { beginAtZero: true } } }
            });
        } catch (e) { console.error(e); }
    })();

    // LINE: Total Orders Over Time
    (async function () {
        try {
            const data = await fetchJson('{{ route('dashboard.api.totalOrdersOverTime') }}');
            const labels = Object.keys(data);
            const values = Object.values(data);
            new Chart(document.getElementById('totalOrdersOverTimeChart'), {
                type: 'line',
                data: { labels, datasets: [{ label: 'Orders', data: values, fill: false }] },
                options: { responsive: true, scales: { y: { beginAtZero: true } } }
            });
        } catch (e) { console.error(e); }
    })();

    // LIST: helper to render lists
    async function renderList(listId, apiUrl, labelKey = 'name', valueKey = 'value') {
        try {
            const data = await fetchJson(apiUrl);
            const el = document.getElementById(listId);
            el.innerHTML = '';
            if (!Array.isArray(data) || data.length === 0) {
                el.innerHTML = '<li class="list-group-item">No data available.</li>';
                return;
            }
            data.forEach(item => {
                const li = document.createElement('li');
                li.className = 'list-group-item';
                const name = document.createElement('span');
                name.textContent = item[labelKey] ?? '';
                const badge = document.createElement('span');
                badge.className = 'badge bg-primary rounded-pill';
                badge.textContent = item[valueKey] ?? '';
                li.appendChild(name);
                li.appendChild(badge);
                el.appendChild(li);
            });
        } catch (e) { console.error(e); }
    }

    renderList('topRetailersList', '{{ route('dashboard.api.topRetailers') }}', 'retailer_name', 'total_order_value');
    renderList('topDistributorsList', '{{ route('dashboard.api.topDistributors') }}', 'distributor_name', 'total_order_value');
});
</script>
@endpush
