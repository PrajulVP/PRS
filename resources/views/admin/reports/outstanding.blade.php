@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title text-start mb-4">
        <div class="row m-0">
          <div class="col-sm-6 p-0">
            <h4 class="mb-0 fw-bold">Outstanding & Debt Recovery Analytics</h4>
            <p class="text-muted mb-0 small">Tracking unpaid invoices and identifying regional credit risks across outlets.</p>
          </div>
          <div class="col-sm-6 p-0 text-end">
              <nav aria-label="breadcrumb">
                  <ol class="breadcrumb justify-content-end mb-0 bg-transparent">
                      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                      <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                      <li class="breadcrumb-item active">Outstanding</li>
                  </ol>
              </nav>
          </div>
        </div>
    </div>

    <!-- Filters Section -->
    @include('admin.reports.partials._filters', [
        'reportType' => 'outstanding',
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
                <div class="card-header border-0 py-3 bg-light-danger bg-opacity-10 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <h5 class="mb-0 fw-bold text-danger" id="reportTableHeading">
                        <i class="fa fa-exclamation-triangle me-2"></i>Unpaid Retailer Invoice Registry
                    </h5>
                    
                    <!-- Premium Order Source Toggle -->
                    <div class="custom-toggle-group shadow-sm">
                        <button type="button" class="toggle-btn active" data-type="retailer">
                            <i class="fa fa-hospital-o me-2"></i>Retailers
                        </button>
                        <button type="button" class="toggle-btn" data-type="distributor">
                            <i class="fa fa-truck me-2"></i>Distributors
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive px-4 pb-4">
                        <table class="table table-hover w-100" id="outstandingReportTable">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">Rank</th>
                                    <th>Entity Details</th>
                                    <th class="text-end">Total Business</th>
                                    <th class="text-end text-danger">Total Outstanding</th>
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
        const table = $('#outstandingReportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.reports.outstanding') }}",
                data: function(d) {
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                    d.sales_manager_id = $('#sales_manager_id').val();
                    d.period = $('.preset-btn.active').data('range');
                    d.order_type = $('input[name="order_type"]').val();
                }
            },
            columns: [
                { 
                    data: null, 
                    orderable: false, 
                    searchable: false,
                    className: 'text-center fw-bold text-muted bg-light-soft text-danger',
                    render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                },
                { data: 'entity_name', name: 'entity_name', searchable: true },
                { data: 'business', name: 'business', className: 'text-end', searchable: false },
                { data: 'outstanding', name: 'outstanding', className: 'text-end fw-bold text-danger', searchable: false },
                { data: 'risk_level', name: 'risk_level', className: 'text-center', searchable: false }
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
                    title: 'Outstanding Debt Report - ' + new Date().toLocaleDateString(),
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
                }
            ],
            pageLength: 25,
            order: [[3, 'desc']],
            language: {
                processing: '<div class="spinner-border text-danger" role="status"></div>',
                search: "_INPUT_",
                searchPlaceholder: "Search report data..."
            }
        });

        window.reportsTable = table;

        $('#applyFilters').on('click', function() {
            table.draw();
        });

        // Wire custom toggle buttons to update the hidden order_type input
        $('.toggle-btn').on('click', function() {
            $('.toggle-btn').removeClass('active');
            $(this).addClass('active');
            const type = $(this).data('type');
            $('input[name="order_type"]').val(type).trigger('change');
        });

        $('input[name="order_type"]').on('change', function() {
            const type = $(this).val();
            const sourceText = type === 'distributor' ? 'Distributor' : 'Retailer';
            $('#reportTableHeading').html(`<i class="fa fa-exclamation-triangle me-2"></i>Unpaid <span class="text-danger">${sourceText}</span> Invoice Registry`);
            table.draw();
        });
    });
</script>
@endpush

@push('styles')
<style>
    #outstandingReportTable thead th {
        font-size: 0.75rem !important;
        background-color: var(--med-bg-body) !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .bg-light-soft { background-color: rgba(231, 76, 60, 0.03) !important; }

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

    /* Premium Toggle Group Styling */
    .custom-toggle-group {
        display: inline-flex;
        background: rgba(0, 0, 0, 0.05);
        padding: 4px;
        border-radius: 100px;
        gap: 2px;
        border: 1.5px solid var(--med-border, #e2e8f0);
        align-items: center;
    }

    body.dark-only .custom-toggle-group {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
    }

    .toggle-btn {
        border: none;
        background: transparent;
        padding: 6px 20px;
        border-radius: 100px;
        font-size: 11px;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--med-text-muted, #64748b);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }

    .toggle-btn:hover {
        color: var(--med-primary, #00497a);
    }

    body.dark-only .toggle-btn:hover {
        color: #38bdf8;
    }

    .toggle-btn.active {
        background: var(--med-primary, #00497a) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(0, 73, 122, 0.15);
    }

    body.dark-only .toggle-btn.active {
        background: #38bdf8 !important;
        color: #0f172a !important;
        box-shadow: 0 4px 10px rgba(56, 189, 248, 0.25);
    }
</style>
@endpush
@endsection
