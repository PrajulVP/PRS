<!-- Print-only Filter Header -->
<div class="d-none d-print-block mb-5 p-0">
    <div class="text-center mb-4">
        <div style="font-size: 32px; font-weight: 900; color: #000; text-transform: uppercase; letter-spacing: 2px;">Atomed Wellness</div>
    </div>

    <div style="border-bottom: 3px solid #000; margin-bottom: 25px;"></div>

    <div class="mb-3">
        <div id="printFilterSummary" class="text-dark" style="font-size: 12px; line-height: 1.6;">
            <!-- Will be populated via JS before print -->
        </div>
    </div>
</div>
<form id="filterForm" data-report-type="{{ $reportType ?? 'orders' }}">
    @if(($reportType ?? '') === 'orders')
    <script>
        $(document).ready(function() {
            function updateOrderSourceTheme() {
                const type = $('input[name="order_type"]').val();
                const container = $('.order-source-external');
                const heading = document.getElementById('reportTableHeading');
                
                if (type === 'distributor') {
                    if (container) container.removeClass('theme-retailer').addClass('theme-distributor');
                    if (heading) heading.innerText = 'Detailed Distributor Orders Records';
                } else {
                    if (container) container.removeClass('theme-distributor').addClass('theme-retailer');
                    if (heading) heading.innerText = 'Detailed Retailer Orders Records';
                }
            }

            // Initial call
            updateOrderSourceTheme();
        });
    </script>
    @endif
    @php
        $showSourceToggle = request()->routeIs('admin.reports.orders');
    @endphp

    <input type="hidden" name="order_type" value="{{ request('order_type', 'retailer') }}">

    <div class="card border-0 shadow-sm rounded-4 mb-4 filter-container overflow-visible">
    <div class="card-body p-0">
        @if(!($showMonthPicker ?? false))
        @php
            $showPresetsHeader = (($reportType ?? '') !== 'visits' || ($showExports ?? true));
        @endphp
        @if($showPresetsHeader)
        <!-- Quick Presets Header -->
        <div class="presets-header p-4 border-bottom bg-light-soft rounded-top-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    @if(($reportType ?? '') !== 'visits')
                    <label class="fw-bold small text-muted text-uppercase mb-0 me-2"><i class="fa fa-clock-o me-1"></i>
                        Quick Timeline:</label>
                    <div class="preset-toggle-group">
                        <button type="button" class="preset-btn" data-range="today">Today</button>
                        <button type="button" class="preset-btn" data-range="yesterday">Yesterday</button>
                        <button type="button" class="preset-btn" data-range="7days">Last 7 Days</button>
                        <button type="button" class="preset-btn" data-range="this_month">This Month</button>
                        <button type="button" class="preset-btn" data-range="this_year">This Year</button>
                        <button type="button" class="preset-btn active" data-range="all">All Time</button>
                    </div>
                    @endif
                </div>

                @if($showExports ?? true)
                <div class="export-fast-actions d-flex gap-2">
                    <button type="button" id="exportCsv" class="btn btn-export btn-csv" title="Export to CSV">
                        <i class="fa fa-file-text-o"></i> <span>CSV</span>
                    </button>
                    <button type="button" id="exportExcel" class="btn btn-export btn-excel" title="Export to Excel">
                        <i class="fa fa-file-excel-o"></i> <span>Excel</span>
                    </button>
                    <button type="button" id="exportPdf" class="btn btn-export btn-pdf" title="Download PDF">
                        <i class="fa fa-file-pdf-o"></i> <span>PDF</span>
                    </button>
                    <button type="button" id="exportPrint" class="btn btn-export btn-print"
                        title="Print">
                        <i class="fa fa-print"></i> <span>Print</span>
                    </button>
                </div>
                @endif
            </div>
        </div>
        @endif
        @endif

        <!-- Manual Filters Body -->
        <div class="p-4 {{ ($showPresetsHeader ?? false) ? 'border-top' : '' }}">
            <div class="row g-3 align-items-end">
                @if($showMonthPicker ?? false)
                <div class="col-xl-3 col-md-6">
                    <label class="form-label fw-bold small text-muted text-uppercase mb-2"><i class="fa fa-calendar me-1"></i> Analysis Month</label>
                    <div class="modern-range-container shadow-sm p-1">
                        <div class="range-field w-100">
                            <input type="month" name="analysis_month" id="analysis_month" class="range-input" style="outline: none;" value="{{ date('Y-m') }}">
                        </div>
                    </div>
                </div>
                @else
                <!-- Manual Date Range Redesign -->
                <div class="col-xl-4 col-md-6">
                    <label class="form-label fw-bold small text-muted text-uppercase mb-2"><i class="fa fa-calendar me-1"></i> Date Range</label>
                    <div class="modern-range-container shadow-sm">
                        <div class="range-field">
                            <i class="fa fa-calendar icon"></i>
                            <input type="text" name="from_date" id="from_date" class="range-input" placeholder="Start Date" readonly>
                        </div>
                        <div class="range-divider">
                            <i class="fa fa-long-arrow-right"></i>
                        </div>
                        <div class="range-field">
                            <input type="text" name="to_date" id="to_date" class="range-input" placeholder="End Date" readonly>
                        </div>
                    </div>
                </div>
                @endif


                <!-- Entity Filter Group (Hierarchy) -->
                @if(auth()->user()->hasAnyRole(['admin', 'superadmin']))
                    @if($showManager ?? true)
                    <div class="col-xl col-md-6">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-2"><i
                                class="fa fa-user-circle-o me-1"></i> Exec. Manager</label>
                        <select name="sales_manager_id" id="sales_manager_id" class="form-select form-select-sm select2-industrial">
                            <option value="">All Managers</option>
                            @foreach($salesManagers ?? [] as $sm)
                                <option value="{{ $sm->id }}">{{ $sm->user->name ?? $sm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                @endif

                @if($showStaff ?? true)
                    <div class="col-xl col-md-6">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-2"><i class="fa fa-users me-1"></i>Personnel</label>
                        <select name="fieldstaff_id" id="fieldstaff_id" class="form-select form-select-sm select2-industrial">
                            <option value="">All Staff</option>
                            @foreach($fieldStaffs ?? [] as $fs)
                                <option value="{{ $fs->id }}">{{ $fs->user->name ?? $fs->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if($showRetailer ?? true)
                    <div class="col-xl col-md-6 retailer-only-filter">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-2"><i
                                class="fa fa-hospital-o me-1"></i> Retailer</label>
                        <select name="retailer_id" id="retailer_id" class="form-select form-select-sm select2-industrial">
                            <option value="">All Retailers</option>
                            @foreach($retailers ?? [] as $r)
                                <option value="{{ $r->id }}">{{ $r->user->name ?? $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if($showDistributor ?? true)
                    @unless(auth()->user()->hasRole('distributor'))
                        <div class="col-xl col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-2"><i class="fa fa-truck me-1"></i>Distributor</label>
                            <select name="distributor_id" id="distributor_id" class="form-select form-select-sm select2-industrial">
                                <option value="">All Distributors</option>
                                @foreach($distributors ?? [] as $d)
                                    <option value="{{ $d->id }}">{{ $d->user->name ?? $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endunless
                @endif

                <!-- Status & Actions (Master Orders Only) -->
                @if($showBrand ?? false)
                    <div class="col-xl col-md-6">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-2"><i class="fa fa-tag me-1"></i>Brand</label>
                        <select name="brand" id="brand" class="form-select form-select-sm select2-industrial">
                            <option value="">All Brands</option>
                            @foreach(\App\Models\Brand::orderBy('name')->get() as $b)
                                <option value="{{ $b->name }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if($showStatus ?? true)
                    <div class="col-xl col-md-6">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-2"><i class="fa fa-tag me-1"></i>Status</label>
                        <select name="status" id="status" class="form-select form-select-sm select2-industrial">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ ($request->status ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ ($request->status ?? '') == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="approved" {{ ($request->status ?? '') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="delivered" {{ ($request->status ?? '') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ ($request->status ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="rejected" {{ ($request->status ?? '') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                @endif

                @if(isset($availableBrands) && count($availableBrands) > 0)
                    <div class="col-xl col-md-6">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-2"><i class="fa fa-bookmark me-1"></i>Brand</label>
                        <select name="brand" id="brand" class="form-select form-select-sm select2-industrial">
                            <option value="">All Brands</option>
                            @foreach($availableBrands as $b)
                                <option value="{{ $b }}" {{ ($request->brand ?? '') == $b ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if(($reportType ?? '') === 'orders')
                    <div class="col-xl-2 col-md-6">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-2"><i class="fa fa-credit-card me-1"></i>Payment Status</label>
                        <select name="payment_status" id="payment_status" class="form-select form-select-sm select2-industrial">
                            <option value="">All Payments</option>
                            <option value="paid" {{ ($request->payment_status ?? '') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="pending" {{ ($request->payment_status ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                @endif

                <!-- Action Buttons Container -->
                <div class="col-12 mt-4 pt-3 border-top text-end">
                    <button type="button" id="applyFilters" class="btn btn-update-industrial px-4 me-2 shadow-sm">
                        <i class="fa fa-filter me-2"></i>Apply Analytics
                    </button>
                    <button type="button" id="resetFilters" class="btn btn-reset-industrial shadow-sm px-3" title="Clear All Filters">
                        <i class="fa fa-refresh me-2"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>
</form>

@push('styles')
<style>
    /* Report Page Clean UI: Removing outer card borders */
    .container-fluid .card,
    .filter-container.card,
    .dataTables_wrapper .dataTables_paginate,
    .paging_simple_numbers {
        border: none !important;
        box-shadow: 0 10px 40px -10px rgba(0, 73, 122, 0.06) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: none !important;
    }

    /* Industrial Update Button */
    .btn-update-industrial {
        background: linear-gradient(135deg, var(--med-secondary) 0%, var(--med-primary) 100%) !important;
        color: white !important;
        border: none !important;
        padding: 10px 24px !important;
        border-radius: 12px !important;
        transition: all 0.3s ease !important;
    }

    .btn-update-industrial:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 73, 122, 0.3);
        filter: brightness(1.1);
    }

    .btn-reset-industrial {
        background: var(--med-bg-card) !important;
        color: var(--med-text-muted) !important;
        border: 1.5px solid var(--med-border) !important;
        padding: 10px 18px !important;
        border-radius: 12px !important;
        transition: all 0.3s ease !important;
    }

    .btn-reset-industrial:hover {
        background: #f1f5f9 !important;
        color: var(--med-primary) !important;
        transform: translateY(-2px);
    }
    
    body.dark-only .btn-reset-industrial:hover {
        background: rgba(255,255,255,0.05) !important;
    }

    .bg-light-soft {
        background-color: rgba(0, 0, 0, 0.03);
    }
    
    body.dark-only .bg-light-soft {
        background-color: rgba(255, 255, 255, 0.05);
    }

    /* Preset Toggles */
    .preset-toggle-group {
        display: inline-flex;
        background: rgba(0, 0, 0, 0.05);
        padding: 4px;
        border-radius: 100px;
        gap: 2px;
    }

    body.dark-only .preset-toggle-group {
        background: rgba(255, 255, 255, 0.05);
    }

    .preset-btn {
        border: none;
        background: transparent;
        padding: 6px 18px;
        border-radius: 100px;
        font-size: 13px;
        font-weight: 600;
        color: var(--med-text-muted);
        transition: all 0.2s ease;
    }

    .preset-btn:hover {
        color: var(--med-primary);
    }

    .preset-btn.active {
        background: var(--med-bg-card);
        color: var(--med-primary);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Export Buttons */
    .btn-export {
        border-radius: 12px;
        padding: 8px 16px;
        font-size: 12px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        border: 1.5px solid var(--med-border);
        background: var(--med-bg-card);
        color: var(--med-text-main);
    }

    .btn-csv { color: #475569; }
    .btn-excel { color: #15803d; }
    .btn-pdf { color: #b91c1c; }
    .btn-print { color: #1d4ed8; }

    body.dark-only .btn-excel { color: #4ade80; }
    body.dark-only .btn-pdf { color: #f87171; }
    body.dark-only .btn-print { color: #60a5fa; }

    /* Select2 Dark Mode Support */
    .select2-industrial + .select2-container .select2-selection--single {
        border: 1.5px solid var(--med-border) !important;
        height: 44px !important;
        border-radius: 12px !important;
        background-color: var(--med-bg-card) !important;
        color: var(--med-text-main) !important;
    }

    .select2-industrial + .select2-container .select2-selection__rendered {
        color: var(--med-text-main) !important;
    }

    /* === Modern Date Range UI === */
    .modern-range-container {
        display: flex;
        align-items: center;
        background: var(--med-bg-card, #fff);
        border-radius: 12px;
        padding: 2px 8px;
        height: 44px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1.5px solid var(--med-border, #e2e8f0) !important;
    }

    .modern-range-container:hover {
        border-color: #cbd5e1 !important;
    }

    .modern-range-container:focus-within {
        border-color: var(--med-primary) !important;
        box-shadow: 0 0 0 3px rgba(var(--med-primary-rgb), 0.1) !important;
    }

    .range-field {
        flex: 1;
        display: flex;
        align-items: center;
        padding: 0 8px;
        border: none !important; /* Explicitly remove field borders */
    }

    .range-field .icon {
        color: var(--med-primary);
        font-size: 0.9rem;
        margin-right: 10px;
        opacity: 0.8;
    }

    .range-input, .range-input + input,
    .modern-range-container input.range-input,
    .modern-range-container input.flatpickr-input,
    .modern-range-container input {
        width: 100%;
        border: none !important; /* Explicitly remove input borders */
        background: transparent !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        color: var(--med-text-main, #334155) !important;
        padding: 0 !important;
        margin: 0 !important;
        cursor: pointer !important;
        text-align: center !important;
        box-shadow: none !important; /* Remove any shadows that might look like borders */
        outline: none !important;
    }

    .range-input:focus, .range-input + input:focus,
    .modern-range-container input:focus {
        outline: none !important;
        border: none !important;
        box-shadow: none !important;
    }

    .range-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        padding: 0 4px;
        font-size: 11px;
    }

    .range-divider i {
        opacity: 0.5;
    }

    /* Date Inputs Refined - Industrial Professional */
    .custom-date-group {
        background: var(--med-bg-card, #ffffff);
        border-radius: 12px;
        overflow: hidden;
        border: 1.5px solid var(--med-border, #e2e8f0);
        transition: all 0.2s ease;
    }
    .custom-date-group:focus-within {
        border-color: var(--med-primary);
        box-shadow: 0 0 0 3px rgba(0, 73, 122, 0.1);
    }
    .custom-date-group .form-control {
        border: none !important;
        font-weight: 700 !important;
        color: #334155 !important;
        padding: 0.6rem 0.75rem !important;
        background-color: transparent !important;
        font-size: 13px !important;
        text-align: center;
    }
    .custom-date-group .input-group-text {
        background: #f8fafc !important;
        border: none !important;
        color: #94a3b8 !important;
        padding: 0 10px !important;
    }

    .custom-date-group .form-control:focus {
        border-color: var(--med-primary) !important;
        box-shadow: 0 0 0 3px rgba(var(--med-primary-rgb), 0.1) !important;
        outline: none !important;
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
        box-shadow: 0 0 0 3px rgba(var(--med-primary-rgb), 0.1) !important;
    }

    /* Consolidating styles into the block above */

    /* Flatpickr Premium Skin Override */
    .flatpickr-calendar {
        border-radius: 20px !important;
        border: 1px solid var(--med-border, rgba(0, 73, 122, 0.1)) !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15) !important;
        padding: 10px;
        background: var(--med-bg-card, #fff) !important;
        color: var(--med-text-main) !important;
    }

    .flatpickr-day.selected, 
    .flatpickr-day.startRange, 
    .flatpickr-day.endRange {
        background: var(--med-primary) !important;
        border-color: var(--med-primary) !important;
        box-shadow: 0 4px 10px rgba(0, 73, 122, 0.3);
    }

    .flatpickr-day:hover {
        background: #f1f5f9 !important;
    }

    .flatpickr-month {
        color: var(--med-text-main) !important;
        font-weight: 800;
    }

    /* Order Type Segment Minimal */
    .order-type-segment-minimal {
        display: flex;
        background: var(--med-bg-body, #f1f5f9);
        padding: 4px;
        border-radius: 12px;
        position: relative;
    }
    .order-type-segment-minimal input { display: none; }
    .order-type-segment-minimal label {
        flex: 1;
        padding: 12px 16px;
        text-align: center;
        cursor: pointer;
        font-weight: 700;
        font-size: 13px;
        color: var(--med-text-muted, #64748b);
        border-radius: 9px;
        transition: all 0.2s ease;
        margin-bottom: 0;
        text-transform: uppercase;
    }
    .order-type-segment-minimal input:checked + label {
        background: var(--med-bg-card, white);
        color: var(--med-primary);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    /* Action Buttons */
    .btn-update-industrial {
        background: var(--med-primary) !important;
        color: white !important;
        border: none !important;
        border-radius: 10px !important;
        padding: 8px 20px !important;
        font-size: 13px;
        transition: all 0.2s ease;
    }
    .btn-update-industrial:hover { filter: brightness(1.1); transform: translateY(-1px); }

    .btn-reset-industrial {
        background: white !important;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 10px !important;
        padding: 8px 16px !important;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b !important;
        font-size: 13px;
        transition: all 0.2s ease;
    }
    .btn-reset-industrial:hover { background: #fee2e2 !important; color: #ef4444 !important; border-color: #fecaca !important; }

    .preset-btn {
        transition: all 0.2s ease;
        border-radius: 10px !important;
        border: 1px solid var(--med-border, #e2e8f0) !important;
        background: var(--med-bg-card, white) !important;
        color: var(--med-text-muted, #64748b) !important;
        font-weight: 600 !important;
    }

    .preset-btn:hover {
        background: #f8fafc !important;
        color: #334155 !important;
    }

    .preset-btn.active {
        background: var(--med-primary) !important;
        border-color: var(--med-primary) !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(var(--med-primary-rgb), 0.25) !important;
    }

    /* Dropdown UI refinements */
    .select2-dropdown {
        border: none !important;
        border-radius: 16px !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        margin-top: 5px !important;
        padding: 8px !important;
    }

    .select2-results__option {
        border-radius: 10px !important;
        margin-bottom: 2px !important;
        padding: 6px 14px !important;
        font-size: 13px !important;
        font-weight: 500 !important;
    }

    @media print {
        /* Hide all non-report elements */
        .sidebar-wrapper,
        .page-header,
        .filter-container,
        .order-source-external,
        .filter-card,
        .footer,
        .btn,
        .breadcrumb,
        .page-title p,
        .card-header .badge,
        .dataTables_length,
        .dataTables_filter,
        .dataTables_info,
        .dataTables_paginate,
        div.dataTables_paginate,
        .pagination,
        .paging_simple_numbers,
        [id$="_paginate"],
        [class*="paginate"],
        .dt-buttons {
            display: none !important;
        }

        /* Standardize Headings for Print */
        h1, h2, h3, h4, h5, h6, .text-primary, .text-info, .text-success {
            color: #000 !important;
            font-weight: bold !important;
        }

        /* Ensure table content is visible and takes full width */
        .table-responsive, .card-body {
            overflow: visible !important;
            display: block !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }

        /* Remove icons from headings */
        h1 i, h2 i, h3 i, h4 i, h5 i, h6 i,
        h1 .fa, h2 .fa, h3 .fa, h4 .fa, h5 .fa, h6 .fa {
            display: none !important;
        }
        
        table.dataTable {
            width: 100% !important;
            display: table !important;
        }

        /* Remove Sorting Arrows */
        table.dataTable thead th::before,
        table.dataTable thead th::after,
        table.dataTable thead td::before,
        table.dataTable thead td::after,
        .sorting:after,
        .sorting_asc:after,
        .sorting_desc:after,
        .sorting:before,
        .sorting_asc:before,
        .sorting_desc:before {
            display: none !important;
            content: "" !important;
        }

        table.dataTable thead>tr>th.sorting,
        table.dataTable thead>tr>th.sorting_asc,
        table.dataTable thead>tr>th.sorting_desc {
            padding-right: 8px !important;
        }

        /* Reset margins and backgrounds */
        body {
            padding: 0 !important;
            margin: 0 !important;
            background: #fff !important;
        }

        .page-wrapper,
        .page-body-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            display: block !important;
        }

        .page-body {
            margin: 0 !important;
            padding: 20px !important;
        }

        .container-fluid {
            padding: 0 !important;
        }

        /* Table Styling for Print */
        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .table-responsive {
            overflow: visible !important;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        th,
        td {
            border: 1px solid #000 !important;
            padding: 8px !important;
            font-size: 11px !important;
            color: #000 !important;
            text-align: left !important;
        }

        th {
            background-color: #eee !important;
            font-weight: bold !important;
            text-transform: uppercase !important;
            -webkit-print-color-adjust: exact;
        }

        /* Page Breaks */
        tr {
            page-break-inside: avoid;
        }

        thead {
            display: table-header-group;
        }

        /* Titles & Header Refinement */
        .page-title h4 {
            font-size: 26px !important;
            margin-bottom: 20px !important;
            color: #000 !important;
            font-weight: 800 !important;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block !important;
            text-align: center !important;
        }

        .page-title {
            margin-bottom: 40px !important;
            border-bottom: 3px solid #000;
        }

        /* General Reset */
        .shadow-sm,
        .shadow,
        .card {
            box-shadow: none !important;
            border: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Flatpickr Initialization - Modern Skin
        const fpConfig = {
            altInput: true,
            altInputClass: "range-input",
            altFormat: "F j, Y",
            dateFormat: "Y-m-d",
            disableMobile: "true",
            animate: true,
            defaultDate: "{{ request('from_date') }}"
        };
        const fpConfig2 = {
            altInput: true,
            altInputClass: "range-input",
            altFormat: "F j, Y",
            dateFormat: "Y-m-d",
            disableMobile: "true",
            animate: true,
            defaultDate: "{{ request('to_date') }}"
        };
        const fromPicker = flatpickr("#from_date", fpConfig);
        const toPicker = flatpickr("#to_date", fpConfig2);

        // Select2 Industrial
        $('.select2-industrial').select2({
            width: '100%',
            dropdownParent: $('.filter-container')
        });

        // Quick presets logic
        $('.preset-btn').on('click', function () {
            $('.preset-btn').removeClass('active');
            $(this).addClass('active');

            const range = $(this).data('range');
            const today = new Date();
            let fromDate, toDate = today;

            const formatDate = (date) => {
                if (!date) return '';
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            };

            switch (range) {
                case 'today':
                    fromDate = today;
                    toDate = today;
                    break;
                case 'yesterday':
                    fromDate = new Date(today);
                    fromDate.setDate(today.getDate() - 1);
                    toDate = new Date(today);
                    toDate.setDate(today.getDate() - 1);
                    break;
                case 'this_week':
                    fromDate = new Date(today);
                    fromDate.setDate(today.getDate() - today.getDay());
                    toDate = today;
                    break;
                case 'this_month':
                    fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    toDate = today;
                    break;
                case 'this_year':
                    fromDate = new Date(today.getFullYear(), 0, 1);
                    toDate = today;
                    break;
                case 'all':
                default:
                    fromDate = null;
                    toDate = null;
                    break;
            }

            if (fromDate) fromPicker.setDate(fromDate);
            else fromPicker.clear();
            
            if (toDate) toPicker.setDate(toDate);
            else toPicker.clear();
            
            $('#applyFilters').trigger('click');
        });

        // Handle Order Type Switching
        $('input[name="order_type"]').on('change', function() {
            const type = $(this).val();
            if (type === 'distributor') {
                $('.retailer-only-filter').fadeOut(200);
            } else {
                $('.retailer-only-filter').fadeIn(200);
            }
            $('#applyFilters').trigger('click');
        });

        // Dependent Dropdown: Manager -> Staff
        $('#sales_manager_id').on('change', function () {
            const managerId = $(this).val();
            const staffSelect = $('#fieldstaff_id');

            if (!staffSelect.length) return;

            staffSelect.empty().append('<option value="">All Staff</option>');

            if (managerId) {
                $.ajax({
                    url: "{{ route('admin.reports.get-staff') }}",
                    data: { sales_manager_id: managerId },
                    success: function (data) {
                        data.forEach(staff => {
                            staffSelect.append($('<option>', {
                                value: staff.id,
                                text: staff.name
                            }));
                        });
                        staffSelect.trigger('change');
                    }
                });
            }
        });

        // Map Excel button to CSV logic (standard behavior in this app)
        $('#exportExcel').on('click', function () {
            $('#exportCsv').trigger('click');
        });

        // Global Export Logic
        function getExportParams() {
            let params = $('#filterForm').serialize();
            if (window.reportsTable) {
                const order = window.reportsTable.order()[0];
                if (order) {
                    params += `&order_col=${order[0]}&order_dir=${order[1]}`;
                }
            }
            return params;
        }

        $('#exportCsv').on('click', function(e) {
            e.preventDefault();
            const type = $('#filterForm').data('report-type') || 'orders';
            const params = getExportParams();
            window.location.href = "{{ route('admin.reports.export', ['format' => 'csv']) }}?report_type=" + type + "&" + params;
        });

        $('#exportPdf').on('click', function(e) {
            e.preventDefault();
            const type = $('#filterForm').data('report-type') || 'orders';
            const params = getExportParams();
            window.location.href = "{{ route('admin.reports.export', ['format' => 'pdf']) }}?report_type=" + type + "&" + params;
        });

        // Print Logic: Populate summary before printing
        window.onbeforeprint = function () {
            let context = [];
            const sm = $('#sales_manager_id option:selected').text();
            const fs = $('#fieldstaff_id option:selected').text();
            const ret = $('#retailer_id option:selected').text();
            const from = $('#from_date').val();
            const to = $('#to_date').val();

            // Only show source if explicitly needed, usually redundant with heading
            // const source = $('input[name="order_type"]:checked').val();
            // if (source) {
            //     const sourceLabel = source.charAt(0).toUpperCase() + source.slice(1);
            //     context.push("<span class='fw-bold'>Order Source:</span> " + sourceLabel);
            // }

            if ($('#sales_manager_id').val()) context.push("<span class='fw-bold'>Executive Manager:</span> " + sm);
            if ($('#fieldstaff_id').val()) context.push("<span class='fw-bold'>Field Personnel:</span> " + fs);
            if ($('#retailer_id').val()) context.push("<span class='fw-bold'>Retailer Shop:</span> " + ret);
            
            // Professional Period Label
            const periodType = $('.preset-btn.active').text();
            if (from && to) {
                context.push("<span class='fw-bold'>Period:</span> " + from + " to " + to);
            } else {
                context.push("<span class='fw-bold'>Report Scope:</span> " + periodType);
            }

            $('#printFilterSummary').html(context.join(" <span style='margin: 0 15px;'>•</span> "));
        };

        $('#exportPrint').on('click', function (e) {
            e.preventDefault();
            
            // Get the current visible table instance
            const tableElement = $('.dataTable:visible');
            if (tableElement.length > 0) {
                const table = tableElement.DataTable();
                const oldLen = table.page.len();
                
                // Expand to show all rows for print
                table.page.len(-1).draw();
                
                // Aggressive removal of all pagination elements from DOM before print
                const paginationElements = $('.dataTables_paginate, .pagination, .paging_simple_numbers, [id$="_paginate"]');
                paginationElements.hide(); // Hide via jQuery for extra safety

                // Delay slightly to ensure DOM is updated before print
                setTimeout(function() {
                    window.print();
                    
                    // Restore original pagination
                    paginationElements.show();
                    table.page.len(oldLen).draw();
                }, 800);
            } else {
                window.print();
            }
        });

        $('#resetFilters').on('click', function () {
            $('.select2-industrial').val(null).trigger('change');
            fromPicker.clear();
            toPicker.clear();
            $('.preset-btn').removeClass('active');
            $('.preset-btn[data-range="all"]').addClass('active');
            setTimeout(() => {
                $('#applyFilters').trigger('click');
            }, 50);
        });

        $('#applyFilters').on('click', function() {
            // Trigger table refresh if window.reportsTable exists
            if (window.reportsTable) {
                window.reportsTable.draw();
            } else if (typeof window.LaravelDataTables !== "undefined") {
                Object.values(window.LaravelDataTables).forEach(table => table.draw());
            } else {
                // Fallback: check if we can find any DataTable on the page
                if ($.fn.DataTable.isDataTable('.table')) {
                    $('.table').DataTable().draw();
                }
            }
        });
    });
</script>
@endpush