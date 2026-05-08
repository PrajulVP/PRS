@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title text-start mb-2">
        <div class="row m-0">
          <div class="col-sm-6 p-0">
            <h4 class="mb-0 fw-bold">Product Performance Analysis</h4>
            <p class="text-muted mb-0 small">SKU-level performance, popularity, and revenue generation metrics.</p>
          </div>
          <div class="col-sm-6 p-0 text-end">
              <nav aria-label="breadcrumb">
                  <ol class="breadcrumb justify-content-end mb-0 bg-transparent">
                      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                      <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                      <li class="breadcrumb-item active">Product Performance</li>
                  </ol>
              </nav>
          </div>
        </div>
    </div>

    <!-- Brand Summary Cards -->
    <div class="row g-3 mb-4">
        @foreach($brandStats as $stat)
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 mb-0 border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                    @php
                        $colors = ['#00497a', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#0ea5e9'];
                        $color = $colors[$loop->index % count($colors)];
                    @endphp
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 p-3 rounded-3" style="background-color: {{ $color }}15;">
                                <i class="fa fa-tag" style="color: {{ $color }}; font-size: 1.2rem;"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 text-uppercase fw-800" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ $stat['brand'] }}</h6>
                                <div class="d-flex align-items-baseline">
                                    <h4 class="mb-0 fw-800" style="color: var(--med-primary);">{{ number_format($stat['count']) }}</h4>
                                    <span class="ms-2 text-muted small">Products</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="height: 4px; background-color: {{ $color }}; width: 100%;"></div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filters Section -->
    @include('admin.reports.partials._filters', [
        'reportType' => 'products',
        'salesManagers' => $salesManagers,
        'showDistributor' => false,
        'showRetailer' => false,
        'showStaff' => false,
        'showStatus' => false
    ])

    <!-- Report Table -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header border-0 py-3">
                    <h5 class="mb-0 fw-bold text-info">Inventory Sales Velocity</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive px-4 pb-4">
                        <table class="table table-hover w-100" id="productReportTable">
                            <thead>
                                <tr>
                                <tr>
                                    <th style="width: 50px;">No.</th>
                                    <th>Product Details</th>
                                    <th>Brand</th>
                                    @role('admin|salesmanager')
                                    <th>Pricing Model</th>
                                    @endrole
                                    <th class="text-center">Turnover</th>
                                    <th class="text-center">Intensity</th>
                                    <th>Order Count</th>
                                    <th class="text-end">Revenue</th>
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
        const table = $('#productReportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.reports.products') }}",
                data: function(d) {
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                    d.sales_manager_id = $('#sales_manager_id').val();
                    d.distributor_id = $('#distributor_id').val();
                    d.fieldstaff_id = $('#fieldstaff_id').val();
                    d.retailer_id = $('#retailer_id').val();
                    d.brand = $('#brand').val();
                    d.period = $('.preset-btn.active').data('range');
                    d.order_type = $('input[name="order_type"]:checked').val();
                }
            },
            columns: [
                { 
                    data: null, 
                    orderable: false, 
                    searchable: false,
                    className: 'text-center fw-bold text-muted bg-light-soft',
                    render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                },
                { 
                    data: 'product_name', 
                    name: 'product_name',
                    render: function(data, type, row) {
                        let subLabel = row.product_code;
                        if (!subLabel || subLabel === 'null' || subLabel === 'NULL') {
                            subLabel = row.generic_name || '';
                        }
                        return `<div class="fw-bold">${data}</div><div class="small text-muted">${subLabel}</div>`;
                    }
                },
                { data: 'brand_display', name: 'brand', className: 'text-primary fw-bold small' },
                @role('admin|salesmanager')
                { data: 'pricing', name: 'pricing', className: 'small text-muted', searchable: false },
                @endrole
                { 
                    data: 'total_sold', 
                    name: 'total_sold', 
                    className: 'text-center',
                    searchable: false,
                    render: function(data, type, row) {
                        return `<div class="fw-bold text-info">${data} Units</div><div class="small text-muted">${row.total_free} Free</div>`;
                    }
                },
                { 
                    data: 'avg_units', 
                    name: 'avg_units', 
                    className: 'text-center fw-bold text-success',
                    searchable: false,
                    render: (data) => data + ' / ord'
                },
                { data: 'order_count', name: 'order_count', className: 'text-center', searchable: false },
                { 
                    data: 'total_revenue', 
                    name: 'total_revenue', 
                    className: 'fw-bold text-primary text-end',
                    searchable: false,
                    render: (data) => '₹' + parseFloat(data ?? 0).toLocaleString(undefined, {minimumFractionDigits: 2})
                }
            ],
            dom: '<"row mb-3 align-items-center"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6 text-end"f>>t<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
                {
                    extend: 'print',
                    text: '<i class="fa fa-print me-1"></i> Print',
                    className: 'btn btn-sm btn-info',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: { columns: ':visible' },
                    title: 'Product Sales Analysis - ' + new Date().toLocaleDateString(),
                    customize: function (win) {
                        $(win.document.body).addClass('landscape');
                        $(win.document.body).find('.dataTables_paginate, .pagination, .dataTables_info').hide();
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
            pageLength: 20,
            order: [[6, 'desc']],
            language: {
                processing: '<div class="spinner-border text-info" role="status"></div>',
                search: "_INPUT_",
                searchPlaceholder: "Search report data..."
            }
        });

        window.reportsTable = table;

        $('#applyFilters').on('click', function() {
            table.draw();
        });
    });
</script>
@endpush

@push('styles')
<style>
    #productReportTable thead th {
        font-size: 0.75rem !important;
        background-color: var(--med-bg-body) !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .bg-light-soft { background-color: rgba(var(--bs-info-rgb), 0.03) !important; }

    /* Search Filter Styling */
    .dataTables_filter {
        display: inline-block;
        margin-bottom: 0;
    }
    .dataTables_filter input {
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        padding: 6px 12px;
        width: 250px !important;
        font-size: 0.85rem;
        transition: all 0.2s;
    }
    .dataTables_filter input:focus {
        border-color: var(--med-primary);
        box-shadow: 0 0 0 0.2rem rgba(var(--med-primary-rgb), 0.15);
        outline: none;
    }
</style>
@endpush
@endsection
