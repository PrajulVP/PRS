@extends('layouts.admin')

@push('styles')
<style>
    /* Premium Export Buttons styling */
    .export-buttons-wrapper .btn-custom-export {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 100px !important;
        padding: 6px 16px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03) !important;
        transition: all 0.2s ease-in-out !important;
        color: #334155 !important;
    }

    .export-buttons-wrapper .btn-custom-export:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 15px rgba(0, 73, 122, 0.08) !important;
        background: #f8fafc !important;
    }

    .btn-csv-custom {
        color: #475569 !important;
        border-color: #e2e8f0 !important;
    }
    .btn-csv-custom i { color: #475569 !important; }
    .btn-csv-custom:hover { border-color: #94a3b8 !important; }

    .btn-excel-custom {
        color: #15803d !important;
        border-color: rgba(21, 128, 61, 0.15) !important;
    }
    .btn-excel-custom i { color: #15803d !important; }
    .btn-excel-custom:hover {
        background: #f0fdf4 !important;
        border-color: #15803d !important;
    }

    .btn-pdf-custom {
        color: #b91c1c !important;
        border-color: rgba(185, 28, 28, 0.15) !important;
    }
    .btn-pdf-custom i { color: #b91c1c !important; }
    .btn-pdf-custom:hover {
        background: #fef2f2 !important;
        border-color: #b91c1c !important;
    }

    .btn-print-custom {
        color: #1d4ed8 !important;
        border-color: rgba(29, 78, 216, 0.15) !important;
    }
    .btn-print-custom i { color: #1d4ed8 !important; }
    .btn-print-custom:hover {
        background: #eff6ff !important;
        border-color: #1d4ed8 !important;
    }

    .dataTables_filter {
        margin-top: 1rem !important;
        margin-bottom: 0.5rem !important;
    }
</style>
@endpush

@section('page-body')
<div class="container-fluid">
    <div class="page-title text-start mb-4">
        <div class="row m-0">
          <div class="col-sm-6 p-0">
            <h4 class="mb-0 fw-bold">Field Visit & Coverage Analytics</h4>
            <p class="text-muted mb-0 small">Productivity metrics for field staff visits and retailer outlet coverage.</p>
          </div>
          <div class="col-sm-6 p-0 text-end">
              <nav aria-label="breadcrumb">
                  <ol class="breadcrumb justify-content-end mb-0 bg-transparent">
                      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                      <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                      <li class="breadcrumb-item active">Visits</li>
                  </ol>
              </nav>
          </div>
        </div>
    </div>

    <!-- Filters Section -->
    @include('admin.reports.partials._filters', [
        'reportType' => 'visits',
        'salesManagers' => $salesManagers,
        'showDistributor' => false,
        'showRetailer' => false,
        'showStaff' => false,
        'showStatus' => false,
        'showExports' => false
    ])

    <!-- Report Table -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header border-0 py-3">
                    <h5 class="mb-0 fw-bold text-info">Coverage Statistics</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive px-4 pb-4">
                        <div class="d-none" id="export-buttons-source">
                            <div class="export-buttons-wrapper d-inline-flex gap-2 align-items-center">
                                <button type="button" id="exportCsv" class="btn btn-sm btn-custom-export btn-csv-custom" title="Export to CSV"><i class="fa fa-file-text-o"></i> CSV</button>
                                <button type="button" id="exportExcel" class="btn btn-sm btn-custom-export btn-excel-custom" title="Export to Excel"><i class="fa fa-file-excel-o"></i> Excel</button>
                                <button type="button" id="exportPdf" class="btn btn-sm btn-custom-export btn-pdf-custom" title="Download PDF"><i class="fa fa-file-pdf-o"></i> PDF</button>
                                <button type="button" id="exportPrint" class="btn btn-sm btn-custom-export btn-print-custom" title="Print"><i class="fa fa-print"></i> Print</button>
                            </div>
                        </div>
                        <table class="table table-hover w-100" id="visitReportTable">
                            <thead>
                                        <tr>
                                            <th style="width: 50px;">Rank</th>
                                            <th>Fieldstaff</th>
                                            <th class="text-center">Total Visits</th>
                                            <th class="text-center">Unique Shops</th>
                                            <th class="text-center">Repeat Visits</th>
                                            <th class="text-center">Avg Duration</th>
                                            <th class="text-center">Completed</th>
                                            <th class="text-center">Ongoing</th>
                                            <th class="py-3 text-secondary fw-bold text-center">Shop Coverage</th>
                                            <th class="py-3 text-secondary fw-bold text-center">Productivity %</th>
                                            <th class="py-3 text-secondary fw-bold text-center">Action</th>
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
                const table = $('#visitReportTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('admin.reports.visits') }}",
                        data: function(d) {
                            d.from_date = $('#from_date').val();
                            d.to_date = $('#to_date').val();
                            d.sales_manager_id = $('#sales_manager_id').val();
                            d.period = $('.preset-btn.active').data('range');
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
                        { data: 'name', name: 'user.name', className: 'fw-bold' },
                        { data: 'total_visits', name: 'total_visits', className: 'text-center fw-bold text-primary', searchable: false },
                        { data: 'unique_shops', name: 'unique_shops', className: 'text-center fw-bold text-success', searchable: false },
                        { data: 'repeat_visits', name: 'repeat_visits', className: 'text-center fw-bold text-warning', searchable: false },
                        { data: 'avg_duration', name: 'avg_duration', className: 'text-center text-muted', searchable: false },
                        { data: 'completed_visits', name: 'completed_visits', className: 'text-center', searchable: false },
                        { data: 'ongoing_visits', name: 'ongoing_visits', className: 'text-center', searchable: false },
                        { data: 'coverage', name: 'coverage', className: 'text-center', searchable: false },
                        { data: 'productivity', name: 'productivity', className: 'text-center fw-bold text-info', searchable: false },
                        { data: 'action', name: 'action', className: 'text-center', searchable: false, orderable: false }
                    ],
                    dom: '<"d-none"B><"row mb-3 align-items-center"<"col-sm-12 col-md-6 custom-export-container"><"col-sm-12 col-md-6 text-end"f>>t<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                    buttons: [
                        {
                            extend: 'csv',
                            className: 'd-none buttons-csv',
                            exportOptions: { columns: ':visible' },
                            title: 'Visit Productivity Report - ' + new Date().toLocaleDateString()
                        },
                        {
                            extend: 'excel',
                            className: 'd-none buttons-excel',
                            exportOptions: { columns: ':visible' },
                            title: 'Visit Productivity Report - ' + new Date().toLocaleDateString()
                        },
                        {
                            extend: 'pdf',
                            className: 'd-none buttons-pdf',
                            orientation: 'landscape',
                            pageSize: 'A4',
                            exportOptions: { columns: ':visible' },
                            title: 'Visit Productivity Report - ' + new Date().toLocaleDateString()
                        },
                        {
                            extend: 'print',
                            className: 'd-none buttons-print',
                            orientation: 'landscape',
                            pageSize: 'A4',
                            exportOptions: { columns: ':visible' },
                            title: 'Visit Productivity Report - ' + new Date().toLocaleDateString(),
                            customize: function (win) {
                                $(win.document.body).addClass('landscape');
                                $(win.document.body).find('.dataTables_paginate, .pagination, .dataTables_info').hide();
                            }
                        }
                    ],
                    pageLength: 25,
                    order: [[2, 'desc']],
                    language: {
                        processing: '<div class="spinner-border text-info" role="status"></div>',
                        search: "_INPUT_",
                        searchPlaceholder: "Search report data..."
                    },
                    initComplete: function() {
                        $('.custom-export-container').html($('#export-buttons-source').html());
                        $('#export-buttons-source').remove();
                        
                        // Rebind events since we copied HTML
                        $('.custom-export-container #exportCsv').on('click', function(e) {
                            e.preventDefault();
                            const type = 'visits';
                            let params = $('#filterForm').serialize();
                            if (window.reportsTable) {
                                const order = window.reportsTable.order()[0];
                                if (order) {
                                    params += `&order_col=${order[0]}&order_dir=${order[1]}`;
                                }
                            }
                            window.location.href = "{{ route('admin.reports.export', ['format' => 'csv']) }}?report_type=" + type + "&" + params;
                        });
                        
                        $('.custom-export-container #exportExcel').on('click', function(e) {
                            e.preventDefault();
                            table.button('.buttons-excel').trigger();
                        });
                        
                        $('.custom-export-container #exportPdf').on('click', function(e) {
                            e.preventDefault();
                            const type = 'visits';
                            let params = $('#filterForm').serialize();
                            if (window.reportsTable) {
                                const order = window.reportsTable.order()[0];
                                if (order) {
                                    params += `&order_col=${order[0]}&order_dir=${order[1]}`;
                                }
                            }
                            window.location.href = "{{ route('admin.reports.export', ['format' => 'pdf']) }}?report_type=" + type + "&" + params;
                        });
                        
                        $('.custom-export-container #exportPrint').on('click', function (e) {
                            e.preventDefault();
                            const tableElement = $('.dataTable:visible');
                            if (tableElement.length > 0) {
                                const table = tableElement.DataTable();
                                const oldLen = table.page.len();
                                table.page.len(-1).draw();
                                const paginationElements = $('.dataTables_paginate, .pagination, .paging_simple_numbers, [id$="_paginate"]');
                                paginationElements.hide();
                                setTimeout(function() {
                                    window.print();
                                    paginationElements.show();
                                    table.page.len(oldLen).draw();
                                }, 800);
                            } else {
                                window.print();
                            }
                        });
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
    #visitReportTable thead th {
        font-size: 0.75rem !important;
        background-color: var(--med-bg-body) !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .bg-light-soft { background-color: rgba(0, 150, 136, 0.03) !important; }
    
    .dt-buttons { display: none !important; }

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
