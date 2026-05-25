@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title text-start mb-4">
        <div class="row m-0">
          <div class="col-sm-6 p-0">
            <h4 class="mb-0 fw-bold">Area-wise Performance Report</h4>
            <p class="text-muted mb-0 small">Geographical sales distribution and outlet coverage analysis by area.</p>
          </div>
          <div class="col-sm-6 p-0 text-end">
              <nav aria-label="breadcrumb">
                  <ol class="breadcrumb justify-content-end mb-0 bg-transparent">
                      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                      <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                      <li class="breadcrumb-item active">Area Performance</li>
                  </ol>
              </nav>
          </div>
        </div>
    </div>

    <!-- Filters Section -->
    @include('admin.reports.partials._filters', [
        'reportType' => 'areas',
        'salesManagers' => $salesManagers,
        'showManager' => false,
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
                    <h5 class="mb-0 fw-bold text-primary">Territory Sales Intelligence</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive px-4 pb-4">
                        <table class="table table-hover w-100" id="areaReportTable">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No.</th>
                                    <th>Area Name</th>
                                    <th>District</th>
                                    <th class="text-center">Retailer Base</th>
                                    <th class="text-end">Aggregate Revenue</th>
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
        const table = $('#areaReportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.reports.areas') }}",
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
                    name: 'areas.name',
                    className: 'fw-bold text-dark',
                    render: function(data) {
                        return `<i class="fa fa-map-pin text-danger me-2 shadow-sm"></i>${data}`;
                    }
                },
                { 
                    data: 'district_name', 
                    name: 'districts.name', 
                    className: 'text-muted small'
                },
                { 
                    data: 'retailer_count', 
                    name: 'retailer_count', 
                    orderable: false,
                    render: (data) => `<span class="badge bg-light-primary text-primary px-3 border-primary">${data} Outlets</span>`
                },
                { 
                    data: 'total_revenue', 
                    name: 'total_revenue', 
                    className: 'fw-bold text-primary text-end',
                    searchable: false,
                    orderable: false
                }
            ],
            dom: '<"row mb-3 align-items-center"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6 text-end"f>>t<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
                {
                    extend: 'print',
                    text: '<i class="fa fa-print me-1"></i> Print',
                    className: 'btn btn-sm btn-info',
                    exportOptions: { columns: ':visible' }
                },
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel-o me-1"></i> Excel',
                    className: 'btn btn-sm btn-success',
                    exportOptions: { columns: ':visible' }
                }
            ],
            pageLength: 20,
            order: [[1, 'asc']],
            language: {
                processing: '<div class="spinner-border text-primary" role="status"></div>',
                search: "_INPUT_",
                searchPlaceholder: "Search areas..."
            }
        });

        window.reportsTable = table;

        $('#applyFilters').on('click', function() { table.draw(); });
    });
</script>
@endpush

@push('styles')
<style>
    #areaReportTable thead th {
        font-size: 0.75rem !important;
        background-color: var(--med-bg-body) !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .bg-light-soft { background-color: rgba(var(--bs-primary-rgb), 0.03) !important; }

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
