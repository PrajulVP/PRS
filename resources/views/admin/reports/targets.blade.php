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
        'showStatus' => false
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
                { 
                    data: 'name', 
                    name: 'name', 
                    className: 'fw-bold'
                },
                { data: 'target_display', name: 'target_display', className: 'text-end' },
                { data: 'achievement_display', name: 'achievement_display', className: 'text-end fw-bold text-primary' },
                { data: 'progress_bar', name: 'progress_bar', orderable: false, searchable: false },
                { data: 'variance', name: 'variance', className: 'text-center' }
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
                    title: 'Target Achievement Report - ' + new Date().toLocaleDateString(),
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
    #targetReportTable thead th {
        font-size: 0.75rem !important;
        background-color: var(--med-bg-body) !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .bg-light-soft { background-color: rgba(0, 73, 122, 0.03) !important; }
</style>
@endpush
@endsection
