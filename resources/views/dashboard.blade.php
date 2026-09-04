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
                // Using jQuery to replace HTML ensures script tags are evaluated
                $('#dashboard-dynamic-content').html(html);
                container.style.opacity = '1';
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