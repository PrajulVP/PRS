@extends('layouts.admin')

@section('title', 'Distributor Staff Ratings')

@section('page-body')
<div class="container-fluid py-4">
    <div class="page-title text-start mb-4">
        <div class="row m-0 align-items-center">
            <div class="col-sm-6 p-0">
                <h4 class="mb-1 fw-bold text-main-theme">Distributor Staff Ratings</h4>
                <p class="text-muted-theme mb-0 small">Review and export performance feedback given by distributors to the field staff.</p>
            </div>
            <div class="col-sm-6 p-0 text-md-end mt-3 mt-md-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-md-end mb-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active">Staff Ratings</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card border-0 shadow-sm mb-4 bg-card-theme" style="border-radius: 16px;">
        <div class="card-body p-3">
            <form id="filterForm" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold small text-muted text-uppercase mb-2"><i class="fa fa-sliders text-primary me-1"></i> Filter Type</label>
                    <div class="custom-toggle-group shadow-sm w-100" style="height: 44px; display: inline-flex; border: 1.5px solid var(--med-border, #e2e8f0); border-radius: 12px; padding: 4px; background-color: var(--med-bg-card, #fff);">
                        <button type="button" class="toggle-btn active flex-grow-1" data-filter="distributor" style="border: none; background: transparent; border-radius: 8px; font-size: 11px; font-weight: 750; text-transform: uppercase; letter-spacing: 0.5px; color: var(--med-text-muted, #64748b); transition: all 0.2s ease;">
                            <i class="fa fa-truck me-1"></i> Distributor
                        </button>
                        <button type="button" class="toggle-btn flex-grow-1" data-filter="staff" style="border: none; background: transparent; border-radius: 8px; font-size: 11px; font-weight: 750; text-transform: uppercase; letter-spacing: 0.5px; color: var(--med-text-muted, #64748b); transition: all 0.2s ease;">
                            <i class="fa fa-user me-1"></i> Staff Member
                        </button>
                    </div>
                </div>
                
                <!-- Distributor Filter -->
                <div class="col-12 col-md-5 col-lg-6 filter-container" id="distributor-filter-container">
                    <label for="distributor_id" class="form-label fw-bold small text-muted text-uppercase mb-2"><i class="fa fa-filter text-primary me-1"></i> Filter by Distributor</label>
                    <select id="distributor_id" name="distributor_id" class="form-select select2-industrial" style="width: 100%;">
                        <option value="">All Distributors</option>
                        @foreach($distributors as $distributor)
                            <option value="{{ $distributor->id }}">
                                {{ $distributor->user->name ?? 'N/A' }} 
                                @if($distributor->company_name || $distributor->name)
                                    ({{ $distributor->company_name ?: $distributor->name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Field Staff Filter (Hidden by default) -->
                <div class="col-12 col-md-5 col-lg-6 filter-container d-none" id="staff-filter-container">
                    <label for="field_staff_id" class="form-label fw-bold small text-muted text-uppercase mb-2"><i class="fa fa-filter text-primary me-1"></i> Filter by Field Staff</label>
                    <select id="field_staff_id" name="field_staff_id" class="form-select select2-industrial" style="width: 100%;">
                        <option value="">All Staff Members</option>
                        @foreach($fieldStaff as $staff)
                            <option value="{{ $staff->id }}">
                                {{ $staff->user->name ?? 'N/A' }}
                                @if($staff->contact_no)
                                    ({{ $staff->contact_no }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3 col-lg-2">
                    <button type="button" id="btnReset" class="btn btn-reset-theme w-100 fw-bold d-flex align-items-center justify-content-center gap-2">
                        <i class="fa fa-refresh"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Ratings Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-card-theme" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-main-theme"><i class="fa fa-star text-warning me-2"></i>All Ratings</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive px-4 pb-4 pt-3">
                        <table class="table table-hover align-middle mb-0 w-100" id="ratingsTable">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No.</th>
                                    <th>Distributor</th>
                                    <th>Field Staff</th>
                                    <th>Category</th>
                                    <th>Rating</th>
                                    <th>Comments</th>
                                    <th>Rated At</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Loaded via Ajax --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .star-rating {
        color: #ffc107;
        font-size: 1.1rem;
        letter-spacing: 2px;
    }
    .star-rating-empty {
        color: #e4e5e9;
        font-size: 1.1rem;
        letter-spacing: 2px;
    }
    body.dark-only .star-rating-empty {
        color: #3b4252;
    }
    #ratingsTable th {
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: var(--med-text-muted);
        border-bottom: 2px solid rgba(0, 0, 0, 0.05);
        padding: 15px 10px;
    }
    body.dark-only #ratingsTable th {
        border-bottom-color: rgba(255, 255, 255, 0.05);
    }
    #ratingsTable td {
        padding: 15px 10px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
    }
    body.dark-only #ratingsTable td {
        border-bottom-color: rgba(255, 255, 255, 0.03);
    }

    /* Select2 Professional Skin - Industrial Overhaul */
    .select2-industrial + .select2-container .select2-selection--single {
        border: 1.5px solid var(--med-border, #e2e8f0) !important;
        height: 44px !important;
        border-radius: 12px !important;
        background-color: var(--med-bg-card, #ffffff) !important;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
    }

    .select2-industrial + .select2-container .select2-selection__rendered {
        line-height: 38px !important;
        padding-left: 14px !important;
        font-weight: 600;
        color: var(--med-text-main, #334155) !important;
        font-size: 13px;
    }

    .select2-industrial + .select2-container .select2-selection__arrow {
        height: 38px !important;
        right: 10px !important;
    }

    .select2-industrial + .select2-container.select2-container--focus .select2-selection--single {
        border-color: var(--med-primary) !important;
        box-shadow: 0 0 0 3px rgba(0, 73, 122, 0.1) !important;
    }

    /* Fix DataTable length menu display breaking into newlines */
    .dataTables_length select {
        display: inline-block !important;
        width: auto !important;
        margin: 0 5px !important;
        padding: 6px 36px 6px 12px !important;
        border-radius: 8px !important;
    }
    .dataTables_length label {
        display: inline-flex !important;
        align-items: center !important;
        font-weight: 600 !important;
        color: var(--med-text-muted, #64748b) !important;
        font-size: 0.85rem !important;
    }

    /* Premium Reset Button Theme (Light & Dark Mode) */
    .btn-reset-theme {
        height: 44px;
        border-radius: 12px;
        border: 1.5px solid #ff9e88 !important;
        color: #ff6f4c !important;
        background-color: #ffe5dd !important;
        transition: all 0.2s ease;
    }
    .btn-reset-theme:hover {
        background-color: #ff6f4c !important;
        color: #ffffff !important;
    }

    body.dark-only .btn-reset-theme {
        border-color: rgba(239, 68, 68, 0.4) !important;
        color: #f87171 !important;
        background-color: rgba(239, 68, 68, 0.1) !important;
    }
    body.dark-only .btn-reset-theme:hover {
        background-color: #ef4444 !important;
        color: #ffffff !important;
    }

    /* Premium Toggle Group Active States */
    .custom-toggle-group .toggle-btn.active {
        background: var(--med-primary, #00497a) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(0, 73, 122, 0.15);
    }

    body.dark-only .custom-toggle-group {
        border-color: rgba(255, 255, 255, 0.1) !important;
        background-color: rgba(255, 255, 255, 0.05) !important;
    }

    body.dark-only .custom-toggle-group .toggle-btn.active {
        background: #38bdf8 !important;
        color: #0f172a !important;
        box-shadow: 0 4px 10px rgba(56, 189, 248, 0.25);
    }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const table = $('#ratingsTable').DataTable({
            processing: true,
            ajax: {
                url: "{{ route('admin.staff-ratings.index') }}",
                data: function(d) {
                    d.distributor_id = $('#distributor_id').val();
                    d.field_staff_id = $('#field_staff_id').val();
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
                    data: 'distributor', 
                    name: 'distributor.user.name',
                    render: function(data, type, row) {
                        let name = data && data.user ? data.user.name : 'N/A';
                        let company = data ? (data.company_name || data.name) : '';
                        if (type !== 'display') return name + (company ? ' (' + company + ')' : '');
                        return `<div>
                            <div class="fw-bold text-primary">${name}</div>
                            ${company ? `<div class="small text-muted" style="font-size: 0.7rem;">${company}</div>` : ''}
                        </div>`;
                    }
                },
                { 
                    data: 'field_staff', 
                    name: 'fieldStaff.user.name',
                    render: function(data, type, row) {
                        let name = data && data.user ? data.user.name : 'N/A';
                        let phone = data ? data.contact_no : '';
                        if (type !== 'display') return name + (phone ? ' (' + phone + ')' : '');
                        return `<div>
                            <div class="fw-bold text-main-theme">${name}</div>
                            ${phone ? `<div class="small text-muted" style="font-size: 0.7rem;"><i class="fa fa-phone me-1"></i>${phone}</div>` : ''}
                        </div>`;
                    }
                },
                { 
                    data: 'category', 
                    name: 'category',
                    render: function(data) {
                        if (!data) return '-';
                        // Beautify category string (e.g. response_time -> Response Time)
                        return data.split('_')
                            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                            .join(' ');
                    }
                },
                { 
                    data: 'rating', 
                    name: 'rating',
                    className: 'text-nowrap',
                    render: function(data, type) {
                        let rating = parseInt(data || 0);
                        if (type !== 'display') return rating + ' Stars';
                        
                        let starsHtml = '<span class="star-rating">';
                        for (let i = 1; i <= rating; i++) {
                            starsHtml += '★';
                        }
                        starsHtml += '</span><span class="star-rating-empty">';
                        for (let i = rating + 1; i <= 5; i++) {
                            starsHtml += '★';
                        }
                        starsHtml += '</span>';
                        return starsHtml;
                    }
                },
                { 
                    data: 'comments', 
                    name: 'comments',
                    render: function(data) {
                        return data ? `<span class="small text-muted-theme" style="white-space: normal; word-break: break-word;">${data}</span>` : '<span class="text-muted small">No comments</span>';
                    }
                },
                { 
                    data: 'created_at', 
                    name: 'created_at',
                    render: function(data, type) {
                        if (!data) return '-';
                        let parts = data.split('T');
                        let date = parts[0];
                        let time = parts[1] ? parts[1].substring(0, 5) : '';
                        let display = date + (time ? ' ' + time : '');
                        if (type !== 'display') return display;
                        return `<div class="small text-muted-theme">${display}</div>`;
                    }
                }
            ],
            order: [[6, 'desc']],
            dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-center p-3 gap-3"<"d-flex align-items-center"l><"d-flex align-items-center"B><"d-flex align-items-center"f>>t<"d-flex justify-content-between align-items-center p-3"ip>',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel me-1"></i> Excel',
                    className: 'btn btn-success btn-sm rounded-3 shadow-sm border-0 px-3 py-2 fw-bold text-white',
                    title: function() { return 'Distributor Staff Ratings'; },
                    messageTop: function() {
                        let activeToggle = $('.custom-toggle-group .toggle-btn.active').data('filter');
                        if (activeToggle === 'distributor') {
                            let dist = $('#distributor_id option:selected').text();
                            return $('#distributor_id').val() ? 'Filter: Distributor - ' + dist.trim() : 'Filter: All Distributors';
                        } else {
                            let staff = $('#field_staff_id option:selected').text();
                            return $('#field_staff_id').val() ? 'Filter: Staff - ' + staff.trim() : 'Filter: All Staff';
                        }
                    },
                    exportOptions: { columns: ':visible' }
                },
                {
                    extend: 'csv',
                    text: '<i class="fa fa-file-csv me-1"></i> CSV',
                    className: 'btn btn-info btn-sm rounded-3 shadow-sm border-0 px-3 py-2 fw-bold text-white',
                    title: function() { return 'Distributor Staff Ratings'; },
                    messageTop: function() {
                        let activeToggle = $('.custom-toggle-group .toggle-btn.active').data('filter');
                        if (activeToggle === 'distributor') {
                            let dist = $('#distributor_id option:selected').text();
                            return $('#distributor_id').val() ? 'Filter: Distributor - ' + dist.trim() : 'Filter: All Distributors';
                        } else {
                            let staff = $('#field_staff_id option:selected').text();
                            return $('#field_staff_id').val() ? 'Filter: Staff - ' + staff.trim() : 'Filter: All Staff';
                        }
                    },
                    exportOptions: { columns: ':visible' }
                },
                {
                    extend: 'pdf',
                    text: '<i class="fa fa-file-pdf me-1"></i> PDF',
                    className: 'btn btn-danger btn-sm rounded-3 shadow-sm border-0 px-3 py-2 fw-bold text-white',
                    title: function() { return 'Distributor Staff Ratings'; },
                    messageTop: function() {
                        let activeToggle = $('.custom-toggle-group .toggle-btn.active').data('filter');
                        if (activeToggle === 'distributor') {
                            let dist = $('#distributor_id option:selected').text();
                            return $('#distributor_id').val() ? 'Filter: Distributor - ' + dist.trim() : 'Filter: All Distributors';
                        } else {
                            let staff = $('#field_staff_id option:selected').text();
                            return $('#field_staff_id').val() ? 'Filter: Staff - ' + staff.trim() : 'Filter: All Staff';
                        }
                    },
                    exportOptions: { columns: ':visible' }
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print me-1"></i> Print',
                    className: 'btn btn-dark btn-sm rounded-3 shadow-sm border-0 px-3 py-2 fw-bold text-white',
                    title: function() { return 'Distributor Staff Ratings'; },
                    messageTop: function() {
                        let activeToggle = $('.custom-toggle-group .toggle-btn.active').data('filter');
                        if (activeToggle === 'distributor') {
                            let dist = $('#distributor_id option:selected').text();
                            return $('#distributor_id').val() ? 'Filter: Distributor - ' + dist.trim() : 'Filter: All Distributors';
                        } else {
                            let staff = $('#field_staff_id option:selected').text();
                            return $('#field_staff_id').val() ? 'Filter: Staff - ' + staff.trim() : 'Filter: All Staff';
                        }
                    },
                    exportOptions: { columns: ':visible' }
                }
            ],
            language: {
                lengthMenu: "Show _MENU_ entries",
                search: "",
                searchPlaceholder: "Search ratings...",
                emptyTable: `<div class="text-center py-5">
                    <i class="fa fa-star-o fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No staff ratings found.</p>
                </div>`
            }
        });

        // Initialize Select2 for Distributors and Staff
        $('#distributor_id').select2({
            placeholder: "All Distributors",
            allowClear: true,
            width: '100%'
        });

        $('#field_staff_id').select2({
            placeholder: "All Staff Members",
            allowClear: true,
            width: '100%'
        });

        // Filter Toggle Logic
        $('.custom-toggle-group .toggle-btn').on('click', function() {
            $('.custom-toggle-group .toggle-btn').removeClass('active');
            $(this).addClass('active');

            const filterType = $(this).data('filter');
            if (filterType === 'distributor') {
                $('#distributor-filter-container').removeClass('d-none');
                $('#staff-filter-container').addClass('d-none');
                $('#field_staff_id').val('').trigger('change');
            } else {
                $('#staff-filter-container').removeClass('d-none');
                $('#distributor-filter-container').addClass('d-none');
                $('#distributor_id').val('').trigger('change');
            }
        });

        $('#distributor_id, #field_staff_id').change(function() {
            table.ajax.reload();
        });

        $('#btnReset').click(function() {
            $('#distributor_id').val('').trigger('change');
            $('#field_staff_id').val('').trigger('change');
            $('.custom-toggle-group .toggle-btn[data-filter="distributor"]').trigger('click');
        });
    });
</script>
@endpush
