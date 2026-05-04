@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title text-start mb-4">
        <div class="row m-0">
          <div class="col-sm-6 p-0">
            <h4 class="mb-0 fw-bold">Distributor Performance Analysis</h4>
            <p class="text-muted mb-0 small">Ranking and sales volume analytics for registered distributors.</p>
          </div>
          <div class="col-sm-6 p-0 text-end">
              <nav aria-label="breadcrumb">
                  <ol class="breadcrumb justify-content-end mb-0 bg-transparent">
                      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                      <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                      <li class="breadcrumb-item active">Distributor Performance</li>
                  </ol>
              </nav>
          </div>
        </div>
    </div>

    <!-- Filters Section -->
    @include('admin.reports.partials._filters', [
        'reportType' => 'distributors',
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
                    <h5 class="mb-0 fw-bold text-primary">Performance Rankings</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive px-4 pb-4">
                        <table class="table table-hover w-100" id="distributorReportTable">
                            <thead>
                                <tr>
                                <tr>
                                    <th style="width: 50px;">Rank</th>
                                    <th>Distributor Details</th>
                                    <th>Network Reach</th>
                                    <th class="text-center">Total Orders</th>
                                    <th>Top Product</th>
                                    <th class="text-end">Sales Value</th>
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
        const table = $('#distributorReportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.reports.distributors') }}",
                data: function(d) {
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                    d.sales_manager_id = $('#sales_manager_id').val();
                    d.fieldstaff_id = $('#fieldstaff_id').val();
                    d.retailer_id = $('#retailer_id').val();
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
                    data: 'name', 
                    name: 'user.name', 
                    className: 'fw-bold',
                    render: (data, type, row) => `${data}<br><small class="text-muted fw-normal">${row.user?.email ?? ''}</small>`
                },
                { data: 'network_size', name: 'network_size', className: 'small fw-bold text-info', searchable: false },
                { data: 'total_orders', name: 'total_orders', className: 'text-center', searchable: false },
                { data: 'top_products', name: 'top_products', orderable: false, searchable: false },
                { 
                    data: 'total_sales', 
                    name: 'total_sales', 
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
                    title: 'Distributor Performance - ' + new Date().toLocaleDateString(),
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
            order: [[4, 'desc']],
            language: {
                processing: '<div class="spinner-border text-primary" role="status"></div>',
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
    #distributorReportTable thead th {
        font-size: 0.75rem !important;
        background-color: var(--med-bg-body) !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .bg-light-soft { background-color: rgba(0, 73, 122, 0.03) !important; }

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
