@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-extrabold text-dark mb-1"><i class="fa fa-bolt me-2 text-warning"></i>Fastest Moving Molecules</h3>
            <p class="text-muted small mb-0">Sales-based metrics identifying secondary demand trends.</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
        <div class="card-body p-4 bg-light-soft">
            <form id="filterForm" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted small text-uppercase">Time Period</label>
                    <select name="period" id="periodSelect" class="form-select rounded-3">
                        <option value="this_month">This Month</option>
                        <option value="7days">Last 7 Days</option>
                        <option value="today">Today</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div class="col-md-4" id="customRange" style="display: none;">
                    <label class="form-label fw-bold text-muted small text-uppercase">Custom Range</label>
                    <div class="input-group">
                        <input type="date" name="from_date" class="form-control">
                        <input type="date" name="to_date" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold rounded-3">
                        <i class="fa fa-sync me-2"></i>Refresh
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- DataTable Card --}}
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 custom-report-table" id="moleculesTable">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="ps-4 py-4 text-uppercase small letter-spacing-wider">Molecule / Generic Name</th>
                            <th class="py-4 text-uppercase small letter-spacing-wider text-center">Total Units Sold</th>
                            <th class="py-4 text-uppercase small letter-spacing-wider text-center">Market Share (in Category)</th>
                            <th class="pe-4 py-4 text-uppercase small letter-spacing-wider text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTables Injected --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        const table = $('#moleculesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.reports.fastest-molecules') }}",
                data: function(d) {
                    d.from_date = $('input[name=from_date]').val();
                    d.to_date = $('input[name=to_date]').val();
                    d.period = $('#periodSelect').val();
                }
            },
            columns: [
                { 
                    data: 'generic_name', 
                    className: 'ps-4 fw-bold text-primary',
                    render: function(data) {
                        return `<div class="d-flex align-items-center"><i class="fa fa-microscope me-3 text-muted opacity-50"></i>${data}</div>`;
                    }
                },
                { data: 'total_sold', className: 'text-center fw-extrabold text-dark h5 mb-0' },
                { 
                    data: null, 
                    className: 'text-center',
                    render: function(data, type, row, meta) {
                        // Dummy visualization for market share
                        let val = Math.floor(Math.random() * 40) + 10; 
                        return `
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <div class="progress w-100" style="height: 4px; max-width: 100px;">
                                    <div class="progress-bar bg-success" style="width: ${val}%"></div>
                                </div>
                                <span class="small fw-bold text-muted">${val}%</span>
                            </div>
                        `;
                    }
                },
                { 
                    data: null, 
                    className: 'pe-4 text-end',
                    orderable: false,
                    render: function(data) {
                        return `<button class="btn btn-xs btn-outline-dark rounded-pill px-3 py-1 fw-bold">Trace Batch</button>`;
                    }
                }
            ],
            order: [[1, 'desc']],
            pageLength: 15,
            language: {
                processing: '<div class="spinner-border text-primary" role="status"></div>',
                search: "_INPUT_",
                searchPlaceholder: "Search Molecules..."
            }
        });

        $('#filterForm').submit(function(e) {
            e.preventDefault();
            table.draw();
        });

        $('#periodSelect').change(function() {
            if ($(this).val() === 'custom') {
                $('#customRange').fadeIn();
            } else {
                $('#customRange').fadeOut();
            }
        });
    });
</script>
<style>
    .bg-light-soft { background-color: #f8fafc; }
    .custom-report-table thead th { border-bottom: 0; }
    .custom-report-table tbody td { padding-top: 20px; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9; }
    .letter-spacing-wider { letter-spacing: 0.05em; }
    .fw-extrabold { font-weight: 800; }
</style>
@endpush
