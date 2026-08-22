@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title text-start mb-4">
        <div class="row m-0">
          <div class="col-sm-6 p-0">
            <h4 class="mb-0 fw-bold">Manager Target vs Achievement Analysis</h4>
            <p class="text-muted mb-0 small">Monitoring manager performance against assigned sales team targets.</p>
          </div>
          <div class="col-sm-6 p-0 text-end">
              <nav aria-label="breadcrumb">
                  <ol class="breadcrumb justify-content-end mb-0 bg-transparent">
                      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                      <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                      <li class="breadcrumb-item active">Manager Targets</li>
                  </ol>
              </nav>
          </div>
        </div>
    </div>

    <!-- Filters Section -->
    @include('admin.reports.partials._filters', [
        'reportType' => 'targets',
        'salesManagers' => [], // Unused for manager targets but passed for safety
        'showManager' => false,
        'showDistributor' => false,
        'showRetailer' => false,
        'showStaff' => false,
        'showStatus' => false,
        'showExports' => false,
        'showMonthPicker' => true,
        'showBrand' => true
    ])

    <!-- Report Table -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header border-0 py-3">
                    <h5 class="mb-0 fw-bold text-primary">Manager Performance Pipeline</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive px-4 pb-4">
                        <table class="table table-hover w-100" id="targetReportTable">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">Rank</th>
                                    <th>Sales Manager</th>
                                    <th class="text-center">Team Size</th>
                                    <th class="text-end">Total Target</th>
                                    <th class="text-end">Total Achievement</th>
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
        function getFormattedMonth() {
            let m = $('#analysis_month').val();
            if(m) {
                let d = new Date(m + '-01');
                return d.toLocaleString('default', { month: 'long', year: 'numeric' });
            }
            return new Date().toLocaleDateString();
        }

        const table = $('#targetReportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.reports.manager-targets') }}",
                data: function(d) {
                    d.month = $('#analysis_month').val();
                    d.brand = $('#brand').val();
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
                { data: 'team_size', name: 'team_size', className: 'text-center fw-bold', searchable: false },
                { data: 'target_display', name: 'target_display', className: 'text-end', searchable: false },
                { data: 'achievement_display', name: 'achievement_display', className: 'text-end fw-bold text-primary', searchable: false },
                { data: 'progress_bar', name: 'progress_bar', className: 'no-export', orderable: false, searchable: false },
                { data: 'variance', name: 'variance', className: 'text-center', searchable: false }
            ],
            dom: '<"row mb-3 align-items-center"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6 text-end"f>>t<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
                {
                    extend: 'csv',
                    text: '<i class="fa fa-file-text-o me-1"></i> CSV',
                    className: 'btn btn-sm btn-csv-custom',
                    exportOptions: { columns: ':visible:not(.no-export)' },
                    title: function() { return 'Manager Target Achievement Report - ' + getFormattedMonth(); }
                },
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel-o me-1"></i> Excel',
                    className: 'btn btn-sm btn-excel-custom',
                    exportOptions: { columns: ':visible:not(.no-export)' },
                    title: function() { return 'Manager Target Achievement Report - ' + getFormattedMonth(); }
                },
                {
                    extend: 'pdf',
                    text: '<i class="fa fa-file-pdf-o me-1"></i> PDF',
                    className: 'btn btn-sm btn-pdf-custom',
                    exportOptions: { columns: ':visible:not(.no-export)' },
                    title: function() { return 'Manager Target Achievement Report - ' + getFormattedMonth(); }
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print me-1"></i> Print',
                    className: 'btn btn-sm btn-print-custom',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: { columns: ':visible:not(.no-export)' },
                    title: function() { return 'Manager Target Achievement Report - ' + getFormattedMonth(); },
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
        margin-top: 5px;
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

    .dt-buttons .btn-csv-custom { color: #1e3a8a !important; border-color: #bfdbfe !important; }
    .dt-buttons .btn-excel-custom { color: #166534 !important; border-color: #bbf7d0 !important; }
    .dt-buttons .btn-pdf-custom { color: #991b1b !important; border-color: #fecaca !important; }
    .dt-buttons .btn-print-custom { color: #374151 !important; border-color: #e5e7eb !important; }

    .dt-buttons .btn-csv-custom:hover { background: #eff6ff !important; border-color: #93c5fd !important; transform: translateY(-2px) !important; box-shadow: 0 6px 15px rgba(59, 130, 246, 0.1) !important; }
    .dt-buttons .btn-excel-custom:hover { background: #f0fdf4 !important; border-color: #86efac !important; transform: translateY(-2px) !important; box-shadow: 0 6px 15px rgba(34, 197, 94, 0.1) !important; }
    .dt-buttons .btn-pdf-custom:hover { background: #fef2f2 !important; border-color: #fca5a5 !important; transform: translateY(-2px) !important; box-shadow: 0 6px 15px rgba(239, 68, 68, 0.1) !important; }
    .dt-buttons .btn-print-custom:hover { background: #f9fafb !important; border-color: #d1d5db !important; transform: translateY(-2px) !important; box-shadow: 0 6px 15px rgba(107, 114, 128, 0.1) !important; }
</style>
@endpush
@endsection
