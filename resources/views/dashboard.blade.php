@php
if (!function_exists('format_inr')) {
    function format_inr($num, $decimals = 0) {
        $num = number_format($num, $decimals, '.', '');
        return preg_replace('/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i', '$1,', $num);
    }
}
@endphp
@extends('layouts.admin')

@section('page-body')
    <!-- Container-fluid starts-->
    <div class="modal fade" id="showOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content"></div>
        </div>
    </div>
    <div class="container-fluid pt-4">
                <div id="dashboard-dynamic-content">
            @include('partials.dashboard_content')
        </div>
    </div>

@push('scripts')
<script>
    function fetchDashboardMonth(month) {
        // Show loading state
        const container = document.getElementById('dashboard-dynamic-content');
        if(container) {
            container.style.opacity = '0.5';
        }
        
        // Update URL
        const newUrl = new URL(window.location.href);
        newUrl.searchParams.set('month', month);
        window.history.pushState({month: month}, '', newUrl);

        fetch(`?month=${month}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            if(container) {
                // Destroy existing charts before replacing HTML to avoid memory leaks
                if (window.charts) {
                    Object.values(window.charts).forEach(c => { try { c.destroy(); } catch(e) {} });
                }
                // Using innerHTML to replace content AND execute embedded scripts
                container.innerHTML = html;
                container.style.opacity = '1';
                // Re-run any <script> tags in the new HTML (innerHTML doesn't auto-execute)
                container.querySelectorAll('script').forEach(oldScript => {
                    const newScript = document.createElement('script');
                    newScript.textContent = oldScript.textContent;
                    document.head.appendChild(newScript);
                    document.head.removeChild(newScript);
                });
                if (typeof initDashboardCharts === 'function') {
                    initDashboardCharts();
                }
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            }
        })
        .catch(err => {
            console.error('Error fetching dashboard:', err);
            if(container) container.style.opacity = '1';
        });
    }
</script>
@endpush
@endsection