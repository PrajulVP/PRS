@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title text-start mb-4">
        <div class="row m-0">
          <div class="col-sm-6 p-0">
            <h4 class="mb-0 fw-bold">Target vs Achievement Analysis</h4>
            <p class="text-muted mb-0 small">Monitoring personnel performance against assigned sales targets.</p>
          </div>
          <div class="col-sm-6 p-0 text-end">
              <nav aria-label="breadcrumb">
                  <ol class="breadcrumb justify-content-end mb-0 bg-transparent">
                      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                      <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                      <li class="breadcrumb-item active">Targets</li>
                  </ol>
              </nav>
          </div>
        </div>
    </div>

    <!-- Filters Section -->
    @include('admin.reports.partials._filters', [
        'reportType' => 'targets',
        'salesManagers' => $salesManagers,
        'showDistributor' => false,
        'showRetailer' => false,
        'showStaff' => false,
        'showStatus' => false,
        'showExports' => false,
        'showMonthPicker' => true
    ])

    <!-- Report Table -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header border-0 py-3">
                    <h5 class="mb-0 fw-bold text-primary">Performance Pipeline</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive px-4 pb-4">
                        <table class="table table-hover w-100" id="targetReportTable">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">Rank</th>
                                    <th>Field Personnel</th>
                                    <th class="text-end">Target Amt</th>
                                    <th class="text-end">Achievement</th>
                                    <th class="text-center" style="width: 200px;">Progress</th>
                                    <th class="text-center">Variance</th>
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
        const table = $('#targetReportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.reports.targets') }}",
                data: function(d) {
                    d.month = $('#analysis_month').val();
                    d.sales_manager_id = $('#sales_manager_id').val();
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
                    className: 'fw-bold'
                },
                { data: 'target_display', name: 'target_display', className: 'text-end', searchable: false },
                { data: 'achievement_display', name: 'achievement_display', className: 'text-end fw-bold text-primary', searchable: false },
                { data: 'progress_bar', name: 'progress_bar', orderable: false, searchable: false },
                { data: 'variance', name: 'variance', className: 'text-center', searchable: false }
            ],
            dom: '<"row mb-3 align-items-center"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6 text-end"f>>t<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
                {
                    extend: 'csv',
                    text: '<i class="fa fa-file-text-o me-1"></i> CSV',
                    className: 'btn btn-sm btn-csv-custom',
                    exportOptions: { columns: ':visible' },
                    title: function() { return 'Target Achievement Report - ' + ($('#analysis_month').val() || new Date().toLocaleDateString()); }
                },
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel-o me-1"></i> Excel',
                    className: 'btn btn-sm btn-excel-custom',
                    exportOptions: { columns: ':visible' },
                    title: function() { return 'Target Achievement Report - ' + ($('#analysis_month').val() || new Date().toLocaleDateString()); }
                },
                {
                    extend: 'pdf',
                    text: '<i class="fa fa-file-pdf-o me-1"></i> PDF',
                    className: 'btn btn-sm btn-pdf-custom',
                    exportOptions: { columns: ':visible' },
                    title: function() { return 'Target Achievement Report - ' + ($('#analysis_month').val() || new Date().toLocaleDateString()); }
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print me-1"></i> Print',
                    className: 'btn btn-sm btn-print-custom',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: { columns: ':visible' },
                    title: function() { return 'Target Achievement Report - ' + ($('#analysis_month').val() || new Date().toLocaleDateString()); },
                    customize: function (win) {
                        $(win.document.body).addClass('landscape');
                        $(win.document.body).find('.dataTables_paginate, .pagination, .dataTables_info').hide();
                    }
                }
            ],
            pageLength: 25,
            order: [[3, 'desc']],
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
    #targetReportTable thead th {
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

    /* Premium DataTables Export Buttons styling */
    .dt-buttons {
        display: flex !important;
        gap: 10px !important;
        flex-wrap: wrap;
        margin-bottom: 0 !important;
    }

    .dt-buttons .btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 100px !important;
        padding: 8px 24px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03) !important;
        transition: all 0.2s ease-in-out !important;
        margin: 0 !important;
        color: #334155 !important;
    }

    .dt-buttons .btn:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 15px rgba(0, 73, 122, 0.08) !important;
        background: #f8fafc !important;
    }

    .dt-buttons .btn-csv-custom {
        color: #475569 !important;
        border-color: #e2e8f0 !important;
    }
    .dt-buttons .btn-csv-custom i {
        color: #475569 !important;
    }
    .dt-buttons .btn-csv-custom:hover {
        border-color: #94a3b8 !important;
    }

    .dt-buttons .btn-excel-custom {
        color: #15803d !important;
        border-color: rgba(21, 128, 61, 0.15) !important;
    }
    .dt-buttons .btn-excel-custom i {
        color: #15803d !important;
    }
    .dt-buttons .btn-excel-custom:hover {
        background: #f0fdf4 !important;
        border-color: #15803d !important;
    }

    .dt-buttons .btn-pdf-custom {
        color: #b91c1c !important;
        border-color: rgba(185, 28, 28, 0.15) !important;
    }
    .dt-buttons .btn-pdf-custom i {
        color: #b91c1c !important;
    }
    .dt-buttons .btn-pdf-custom:hover {
        background: #fef2f2 !important;
        border-color: #b91c1c !important;
    }

    .dt-buttons .btn-print-custom {
        color: #1d4ed8 !important;
        border-color: rgba(29, 78, 216, 0.15) !important;
    }
    .dt-buttons .btn-print-custom i {
        color: #1d4ed8 !important;
    }
    .dt-buttons .btn-print-custom:hover {
        background: #eff6ff !important;
        border-color: #1d4ed8 !important;
    }

    body.dark-only .dt-buttons .btn {
        background: #121b2a !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        color: #f8fafc !important;
    }

    body.dark-only .dt-buttons .btn:hover {
        background: rgba(255, 255, 255, 0.03) !important;
    }

    body.dark-only .dt-buttons .btn-csv-custom {
        color: #94a3b8 !important;
    }
    body.dark-only .dt-buttons .btn-csv-custom i {
        color: #94a3b8 !important;
    }

    body.dark-only .dt-buttons .btn-excel-custom {
        color: #4ade80 !important;
        border-color: rgba(74, 222, 128, 0.1) !important;
    }
    body.dark-only .dt-buttons .btn-excel-custom i {
        color: #4ade80 !important;
    }
    body.dark-only .dt-buttons .btn-excel-custom:hover {
        background: rgba(74, 222, 128, 0.05) !important;
        border-color: #4ade80 !important;
    }

    body.dark-only .dt-buttons .btn-pdf-custom {
        color: #f87171 !important;
        border-color: rgba(248, 113, 113, 0.1) !important;
    }
    body.dark-only .dt-buttons .btn-pdf-custom i {
        color: #f87171 !important;
    }
    body.dark-only .dt-buttons .btn-pdf-custom:hover {
        background: rgba(248, 113, 113, 0.05) !important;
        border-color: #f87171 !important;
    }

    body.dark-only .dt-buttons .btn-print-custom {
        color: #60a5fa !important;
        border-color: rgba(96, 165, 250, 0.1) !important;
    }
    body.dark-only .dt-buttons .btn-print-custom i {
        color: #60a5fa !important;
    }
    body.dark-only .dt-buttons .btn-print-custom:hover {
        background: rgba(96, 165, 250, 0.05) !important;
        border-color: #60a5fa !important;
    }
</style>
@endpush
@endsection
