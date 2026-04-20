@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
  <div class="page-title text-start mb-4">
    <div class="row m-0">
      <div class="col-sm-6 p-0">
        <h4 class="mb-0 text-primary fw-bold">Field Staff Performance Report</h4>
        <p class="text-muted small">Monitor individual staff sales targets and order productivity.</p>
      </div>
    </div>
  </div>

  <!-- Filter Dashboard -->
  <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
    <div class="card-header bg-light py-3 d-flex align-items-center justify-content-between cursor-pointer" data-bs-toggle="collapse" href="#filterCollapse">
        <h6 class="mb-0 fw-bold"><i class="fa fa-line-chart me-2 text-primary"></i> Performance Filters</h6>
        <i class="fa fa-chevron-down text-muted"></i>
    </div>
    <div class="collapse show" id="filterCollapse">
        <div class="card-body p-4">
            <form id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Select Period</label>
                    <select name="period" id="period" class="form-select rounded-3">
                        <option value="{{ now()->format('Y-m') }}">This Month ({{ now()->format('F Y') }})</option>
                        @for($i = 1; $i <= 12; $i++)
                            @php $date = now()->subMonths($i); @endphp
                            <option value="{{ $date->format('Y-m') }}">{{ $date->format('F Y') }}</option>
                        @endfor
                        <option value="all">Across All History</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Field Staff</label>
                    <select name="fieldstaff_id" id="fieldstaff_id" class="form-select rounded-3">
                        <option value="">All Staff</option>
                        @foreach($fieldStaffs as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-3">
                        <i class="fa fa-search me-1"></i> Search
                    </button>
                    <button type="button" id="resetFilters" class="btn btn-light border w-100 rounded-3">
                        <i class="fa fa-refresh me-1"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-12">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
          <div class="table-responsive custom-scrollbar">
            <table class="table display table-borderless" id="performance-reports-table">
              <thead>
                <tr>
                  <th class="text-center" style="width: 50px;">Rank</th>
                  <th>Staff Name</th>
                  <th>Sales Manager</th>
                  <th class="text-center">Total Orders</th>
                  <th class="text-end">Sales Generated (₹)</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    const table = $('#performance-reports-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('admin.reports.performance') }}",
        data: function (d) {
          d.period = $('#period').val();
          d.fieldstaff_id = $('#fieldstaff_id').val();
        }
      },
      columns: [
        { 
          data: null, 
          name: 'rank',
          orderable: false, 
          searchable: false,
          className: 'text-center fw-bold text-primary bg-light',
          render: function (data, type, row, meta) {
            return meta.row + meta.settings._iDisplayStart + 1;
          }
        },
        { data: 'name', name: 'name', className: 'fw-bold' },
        { data: 'manager', name: 'manager' },
        { data: 'total_orders', name: 'total_orders', className: 'text-center' },
        { 
          data: 'total_sales', 
          name: 'total_sales', 
          className: 'text-end fw-bold text-primary',
          render: function(data, type, row) {
             return '₹' + data;
          }
        },
      ],
      order: [[4, 'desc']], // Default sort by Sales (Rank 1 = Highest)
      dom: '<"d-flex justify-content-between align-items-center mb-4"Bf>rt<"d-flex justify-content-between align-items-center mt-4"ip>',
      buttons: [
        { extend: 'excel', text: '<i class="fa fa-file-excel-o"></i> Excel', className: 'btn btn-success btn-sm' },
        { extend: 'pdf', text: '<i class="fa fa-file-pdf-o"></i> PDF', className: 'btn btn-danger btn-sm' },
        { extend: 'print', text: '<i class="fa fa-print"></i> Print', className: 'btn btn-dark btn-sm' }
      ],
      language: {
        processing: '<div class="spinner-border text-primary" role="status"></div>',
        search: "_INPUT_",
        searchPlaceholder: "Search staff..."
      }
    });

    $('#filterForm').on('submit', function(e) {
      e.preventDefault();
      table.draw();
    });

    $('#resetFilters').on('click', function() {
      $('#filterForm')[0].reset();
      $('#from_date').val("{{ now()->startOfMonth()->format('Y-m-d') }}");
      $('#to_date').val("{{ now()->endOfMonth()->format('Y-m-d') }}");
      table.draw();
    });
  });
</script>
@endpush
