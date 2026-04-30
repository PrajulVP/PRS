@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title text-start mb-4">
        <div class="row m-0">
          <div class="col-sm-6 p-0">
            <h4 class="mb-0 fw-bold">Field Staff Performance Report</h4>
            <p class="text-muted mb-0 small">Comprehensive analysis of staff productivity, retailer coverage, and revenue generation.</p>
          </div>
          <div class="col-sm-6 p-0 text-end">
              <nav aria-label="breadcrumb">
                  <ol class="breadcrumb justify-content-end mb-0 bg-transparent">
                      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                      <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                      <li class="breadcrumb-item active">Staff Performance</li>
                  </ol>
              </nav>
          </div>
        </div>
    </div>

    <!-- Filters Section -->
    @include('admin.reports.partials._filters', [
        'reportType' => 'fieldstaffs',
        'salesManagers' => $salesManagers,
        'showStaff' => false,
        'showRetailer' => false,
        'showDistributor' => false,
        'showStatus' => false
    ])

    <!-- Report Table -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary">Key Performance Indicators (KPI)</h5>
                    <span class="badge bg-soft-primary text-primary px-3 py-2"><i class="fa fa-info-circle me-1"></i> Data aggregated by staff member</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive px-4 pb-4">
                        <table class="table table-hover w-100" id="staffReportTable">
                            <thead>
                            <tr>
                                <th style="width: 50px;">No.</th>
                                <th>Staff Member</th>
                                <th>Sales Manager</th>
                                <th>Coverage & Visits</th>
                                <th class="text-center">Activity</th>
                                <th class="text-center">Orders</th>
                                <th class="text-end">AOV</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-center">Actions</th>
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
        const table = $('#staffReportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.reports.fieldstaffs') }}",
                data: function(d) {
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                    d.sales_manager_id = $('#sales_manager_id').val();
                    d.period = $('.preset-btn.active').data('range');
                    d.order_type = $('input[name="order_type"]:checked').val();
                }
            },
            columns: [
                { 
                    data: null, 
                    orderable: false, 
                    searchable: false,
                    className: 'text-center fw-bold text-muted bg-light-soft staff-rank',
                    render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                },
                { 
                    data: 'name', 
                    name: 'user.name', 
                    className: 'fw-bold'
                },
                { data: 'manager', name: 'salesManager.user.name', className: 'text-muted small' },
                { data: 'coverage_stats', name: 'total_retailers' },
                { data: 'activity', name: 'total_punches', orderable: false },
                { data: 'total_orders', name: 'total_orders', className: 'text-center fw-bold' },
                { data: 'aov', name: 'aov', className: 'text-end fw-bold text-info' },
                { 
                    data: 'total_revenue', 
                    name: 'total_revenue', 
                    className: 'fw-bold text-primary text-end'
                },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            dom: 'Brtip',
            buttons: [
                {
                    extend: 'print',
                    text: '<i class="fa fa-print me-1"></i> Print Report',
                    className: 'btn btn-sm btn-info',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: { columns: ':visible' },
                    title: 'Field Staff Performance - ' + new Date().toLocaleDateString(),
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
            pageLength: 25,
            order: [[5, 'desc']],
            language: {
                processing: '<div class="spinner-border text-primary" role="status"></div>'
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
    #staffReportTable thead th {
        font-size: 0.75rem !important;
        background-color: var(--med-bg-body) !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .staff-rank { border-right: 1px solid #f0f0f0; }
    .bg-soft-primary { background-color: rgba(var(--med-primary-rgb), 0.1) !important; }
</style>
@endpush
@endsection
