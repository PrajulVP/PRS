@extends('layouts.admin')

@section('page-body')
<div class="container-fluid pt-4">
    <div class="page-title text-start mb-4">
        <div class="row m-0">
          <div class="col-sm-6 p-0">
            <h4 class="mb-0 fw-bold">Master Order Performance Report</h4>
            <p class="text-muted mb-0 small"><i class="fa fa-info-circle me-1"></i> Comprehensive view of all retail orders and product distribution metrics.</p>
          </div>
          <div class="col-sm-6 p-0 text-end">
              <nav aria-label="breadcrumb">
                  <ol class="breadcrumb justify-content-end mb-0 bg-transparent">
                      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                      <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                      <li class="breadcrumb-item active">Master Orders</li>
                  </ol>
              </nav>
          </div>
        </div>
    </div>

    <!-- Filters Section -->
    @include('admin.reports.partials._filters', [
        'reportType' => 'orders',
        'salesManagers' => $salesManagers,
        'distributors' => $distributors,
        'retailers' => $retailers,
        'fieldStaffs' => $fieldStaffs
    ])

    <!-- Report Table -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header border-0 py-3">
                    <h5 class="mb-0 fw-bold text-primary" id="reportTableHeading">Detailed Sales Records</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive px-4 pb-4">
                        <table class="table table-hover w-100" id="ordersReportTable">
                            <thead>
                                <tr>
                                    <th>Order Details</th>
                                    <th>Sales Context</th>
                                    <th>Volume</th>
                                    <th class="text-end">Value</th>
                                    <th class="text-center">Payment</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // DataTable Initialization
        const table = $('#ordersReportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.reports.orders') }}",
                data: function(d) {
                    d.order_type = $('input[name="order_type"]:checked').val();
                    d.payment_status = $('#payment_status').val();
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                    d.sales_manager_id = $('#sales_manager_id').val();
                    d.distributor_id = $('#distributor_id').val();
                    d.retailer_id = $('#retailer_id').val();
                    d.fieldstaff_id = $('#fieldstaff_id').val();
                    d.status = $('#status').val();
                    d.period = $('.preset-btn.active').data('range');
                }
            },
            columns: [
                { 
                    data: 'order_code', 
                    name: 'order_code',
                    render: function(data, type, row) {
                        return `<div class="fw-bold text-dark">${data}</div><div class="small text-muted">${row.placed_at}</div>`;
                    }
                },
                { 
                    data: 'retailer_name', 
                    name: 'retailer_name',
                    render: function(data, type, row) {
                        return `<div class="fw-bold" style="font-size: 0.85rem;">${data}</div><div class="small text-muted">via ${row.distributor_name}</div>`;
                    }
                },
                { 
                    data: 'total_quantity', 
                    name: 'total_quantity',
                    render: function(data, type, row) {
                        return `<div class="fw-bold">${data} Units</div><div class="small text-muted text-nowrap">${row.total_items} SKUs</div>`;
                    }
                },
                { data: 'total_amount', name: 'total_amount', className: 'fw-bold text-primary text-end' },
                { 
                    data: 'payment_status', 
                    name: 'payment_status',
                    className: 'text-center',
                    render: function(data) {
                        let status = (data || 'pending').toLowerCase();
                        let badgeClass = 'bg-light-secondary text-secondary';
                        if (status === 'paid') badgeClass = 'bg-light-success text-success';
                        else badgeClass = 'bg-light-warning text-warning';
                        return `<span class="badge ${badgeClass} text-uppercase px-2 py-1" style="font-size: 0.65rem;">${status}</span>`;
                    }
                },
                { 
                    data: 'status', 
                    name: 'status',
                    className: 'text-center',
                    render: function(data) {
                        let badgeClass = 'bg-light-primary text-primary';
                        if (data === 'delivered') badgeClass = 'bg-light-success text-success';
                        if (data === 'cancelled' || data === 'rejected') badgeClass = 'bg-light-danger text-danger';
                        if (data === 'pending') badgeClass = 'bg-light-warning text-warning';
                        return `<span class="badge ${badgeClass} text-uppercase px-2 py-1" style="font-size: 0.65rem;">${data}</span>`;
                    }
                }
            ],
            dom: 'Brtip', 
            buttons: [
                {
                    extend: 'print',
                    text: '<i class="fa fa-print me-1"></i> Print Report',
                    className: 'btn btn-sm btn-info',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: ':visible'
                    },
                    title: 'Orders Report - ' + new Date().toLocaleDateString(),
                    customize: function (win) {
                        $(win.document.body).addClass('landscape');
                        $(win.document.body).find('.dataTables_paginate, .pagination, .dataTables_info').hide();
                        $(win.document.body).find('table').addClass('compact').css('font-size', 'inherit');
                    }
                },
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel-o me-1"></i> Excel',
                    className: 'btn btn-sm btn-success',
                    exportOptions: { columns: ':visible' }
                },
                {
                    extend: 'csv',
                    text: '<i class="fa fa-file-text-o me-1"></i> CSV',
                    className: 'btn btn-sm btn-secondary',
                    exportOptions: { columns: ':visible' }
                }
            ],
            pageLength: 25,
            order: [[1, 'desc']],
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            }
        });

        window.reportsTable = table;

        // Apply Filters
        $('#applyFilters').on('click', function() {
            table.draw();
        });

        // Update Dynamic Heading based on Order Source
        function updateDynamicHeading() {
            const type = $('input[name="order_type"]:checked').val();
            const sourceText = type === 'distributor' ? 'Distributor' : 'Retailer';
            $('#reportTableHeading').html(`<i class="fa fa-list-alt me-2"></i>Detailed <span class="text-info">${sourceText}</span> Orders Sales Records`);
        }

        $('input[name="order_type"]').on('change', function() {
            updateDynamicHeading();
            
            // Sales Context is index 1
            const type = $(this).val();
            table.column(1).visible(type === 'retailer');
            
            table.draw();
        });

        // Initial update
        updateDynamicHeading();
        const initialType = $('input[name="order_type"]:checked').val();
        table.column(1).visible(initialType === 'retailer');
    });
</script>
@endpush

@push('styles')
<style>
    .bg-light-primary { background-color: rgba(var(--bs-primary-rgb), 0.1) !important; }
    .bg-light-success { background-color: rgba(var(--bs-success-rgb), 0.1) !important; }
    .bg-light-danger { background-color: rgba(var(--bs-danger-rgb), 0.1) !important; }
    .bg-light-warning { background-color: rgba(var(--bs-warning-rgb), 0.1) !important; }
    .bg-light-secondary { background-color: rgba(var(--bs-secondary-rgb), 0.1) !important; }
    
    #ordersReportTable thead th {
        font-size: 0.75rem !important;
        background-color: var(--med-bg-body) !important;
    }
</style>
@endpush
@endsection
