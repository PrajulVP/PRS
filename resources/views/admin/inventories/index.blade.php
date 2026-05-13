@extends('layouts.admin')
@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .action-buttons {
        display: flex !important;
        gap: 6px;
        align-items: center;
    }

    .action-buttons .btn {
        margin: 0 !important;
        border-radius: 10px !important;
        padding: 5px 12px !important;
        font-weight: 600 !important;
    }

    /* Premium Inventory Filter Bar */
    .inventory-filter-card {
        background: var(--med-bg-card);
        border: 1px solid var(--med-border);
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 25px;
        position: relative;
        z-index: 1020; /* Lower than 1030 (standard navbar) to scroll under */
        box-shadow: var(--med-shadow-soft);
        transition: all 0.3s ease;
    }

    .inventory-filter-card:hover {
        box-shadow: var(--med-shadow-md);
    }

    .filter-label {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--med-text-muted);
        margin-bottom: 8px;
        display: block;
    }

    /* Select2 Custom Styling for Premium Look */
    .select2-container--default .select2-selection--single {
        background-color: var(--med-bg-body) !important;
        border: 1px solid var(--med-border) !important;
        border-radius: 12px !important;
        height: 48px !important;
        padding: 10px 15px !important;
        transition: all 0.3s ease !important;
    }

    .select2-container--default .select2-selection--single:hover {
        border-color: var(--med-primary) !important;
        background-color: var(--med-bg-card) !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--med-text-main) !important;
        font-weight: 600 !important;
        line-height: 26px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
        right: 10px !important;
    }

    .select2-dropdown {
        background-color: var(--med-bg-card) !important;
        border: 1px solid var(--med-border) !important;
        border-radius: 12px !important;
        box-shadow: var(--med-shadow-md) !important;
        overflow: hidden !important;
        z-index: 1060 !important; /* Above modals (1050) but below header (1070) */
    }

    .select2-results__option {
        padding: 10px 15px !important;
        color: var(--med-text-main) !important;
    }

    .select2-results__option--highlighted[aria-selected] {
        background-color: var(--med-primary) !important;
    }

    /* Modern Table Header */
    #inventories-table thead th {
        background-color: rgba(0, 73, 122, 0.04) !important;
        color: var(--med-text-main) !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.75rem !important;
        letter-spacing: 0.05em;
        padding: 15px !important;
        border-bottom: 1px solid var(--med-border) !important;
    }

    .selection-prompt {
        padding: 80px 40px;
        text-align: center;
        background: rgba(255, 255, 255, 0.03) !important;
        border: 1px solid var(--med-border);
        border-radius: 40px;
        margin: 40px auto;
        color: var(--med-text-main);
        backdrop-filter: blur(15px);
        width: 100%;
        min-width: 100%;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        z-index: 10;
    }

    body.dark-only .selection-prompt {
        background: rgba(255, 255, 255, 0.01) !important;
    }

    .selection-prompt::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(var(--med-primary-rgb), 0.05) 0%, transparent 70%);
        pointer-events: none;
    }

    .selection-prompt i {
        font-size: 5rem;
        background: linear-gradient(135deg, var(--med-primary) 0%, #3b82f6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 25px;
        display: inline-block;
        filter: drop-shadow(0 10px 15px rgba(0, 73, 122, 0.3));
    }

    .selection-prompt h4 {
        font-weight: 900;
        letter-spacing: -0.02em;
        margin-bottom: 15px;
    }

    .selection-prompt p {
        font-size: 1.1rem;
        opacity: 0.7;
        line-height: 1.6;
    }

    /* Fixed DataTable White Block Issue */
    .dataTables_wrapper, .dataTables_empty, #inventories-table, #inventories-table tbody {
        background-color: transparent !important;
    }

    #inventories-table tr {
        background-color: transparent !important;
    }

    #inventories-table td {
        border-color: var(--med-border) !important;
    }

    .bg-light-soft {
        background-color: var(--med-bg-body) !important;
        border: 1px solid var(--med-border) !important;
        transition: all 0.3s ease;
    }

    .bg-light-soft:focus {
        background-color: var(--med-bg-card) !important;
        border-color: var(--med-primary) !important;
        box-shadow: 0 0 0 4px rgba(0, 73, 122, 0.1) !important;
    }

    /* Theme-aware Table Body */
    #inventories-table, 
    #inventories-table tbody, 
    #inventories-table tr, 
    #inventories-table td {
        background-color: transparent !important;
        border-color: var(--med-border) !important;
    }

    .btn-edit-premium {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        color: white !important;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .btn-edit-premium:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        filter: brightness(1.1);
    }

    body.dark-only .dataTables_wrapper .dataTables_paginate .page-link {
        background-color: var(--med-bg-card);
        border-color: var(--med-border);
        color: var(--med-text-main);
    }

    /* Minimal Operation Toggle */
    .minimal-op-toggle {
        background: var(--med-bg-body);
        border: 1px solid var(--med-border);
        padding: 4px;
        border-radius: 50px;
        display: inline-flex;
        gap: 4px;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
    }
    .minimal-op-toggle .btn {
        border-radius: 40px;
        border: none;
        padding: 8px 24px;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        color: var(--med-text-muted);
        background: transparent;
    }
    .minimal-op-toggle .btn.active[data-op="add"] {
        background: var(--med-primary);
        color: white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    .minimal-op-toggle .btn.active[data-op="subtract"] {
        background: #ef4444;
        color: white;
        box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
    }
    .minimal-op-toggle .btn:not(.active):hover {
        background: rgba(0,0,0,0.05);
        color: var(--med-text-main);
    }
    
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: var(--med-bg-body) !important;
        color: var(--med-primary) !important;
    }

    /* Premium Button & Search Styling */
    .btn-view-premium {
        background: var(--med-bg-body) !important;
        color: var(--med-text-main) !important;
        border: 1px solid var(--med-border) !important;
        border-radius: 12px !important;
        font-weight: 700 !important;
        transition: all 0.2s ease !important;
        box-shadow: var(--med-shadow-soft) !important;
    }

    .btn-view-premium:hover {
        background: var(--med-primary) !important;
        color: white !important;
        border-color: var(--med-primary) !important;
        transform: translateY(-2px);
    }

    .inventory-search-group {
        position: relative;
        width: 100%;
    }

    .inventory-search-group i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--med-text-muted);
        pointer-events: none;
        z-index: 5;
    }

    .inventory-search-group input {
        padding-left: 45px !important;
        height: 48px !important;
        border-radius: 12px !important;
        background: var(--med-bg-body) !important;
        border: 1px solid var(--med-border) !important;
        font-weight: 600 !important;
    }

    .inventory-search-group input:focus {
        border-color: var(--med-primary) !important;
        background: var(--med-bg-card) !important;
        box-shadow: 0 0 0 4px rgba(var(--med-primary-rgb), 0.1) !important;
    }

    /* Hide redundant columns */
    .merged-badge {
        font-size: 0.65rem;
        padding: 3px 10px;
        border-radius: 6px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    /* Selection Hub Styles */
    .selection-hub-card {
        border-radius: 24px;
        background: linear-gradient(135deg, var(--med-bg-card) 0%, rgba(var(--med-primary-rgb), 0.02) 100%);
        border: 1px solid var(--med-border);
        box-shadow: var(--med-shadow-soft);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .distributor-pill {
        background: var(--med-bg-body);
        border: 1px solid var(--med-border);
        border-radius: 16px;
        padding: 12px 20px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
    }

    .distributor-pill:hover {
        border-color: var(--med-primary);
        background: var(--med-bg-card);
        transform: translateY(-2px);
        box-shadow: var(--med-shadow-sm);
    }

    .distributor-pill.active {
        background: var(--med-primary);
        border-color: var(--med-primary);
        color: white;
    }

    .distributor-pill.active .text-muted {
        color: rgba(255,255,255,0.7) !important;
    }

    .stat-mini-card {
        background: var(--med-bg-card);
        border-radius: 16px;
        padding: 15px;
        border: 1px solid var(--med-border);
        height: 100%;
        transition: all 0.3s ease;
    }

    .stat-mini-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--med-shadow-md);
        border-color: var(--med-primary);
    }

    .distributor-avatar {
        width: 45px;
        height: 45px;
        background: rgba(var(--med-primary-rgb), 0.1);
        color: var(--med-primary);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
    }

    .active .distributor-avatar {
        background: rgba(255,255,255,0.2);
        color: white;
    }

    .search-focus-mode {
        background: var(--med-bg-card);
        border-radius: 20px;
        padding: 40px;
        text-align: center;
        border: 2px dashed var(--med-border);
        transition: all 0.3s ease;
    }

    .search-focus-mode:hover {
        border-color: var(--med-primary);
        background: rgba(var(--med-primary-rgb), 0.01);
    }
</style>

@section('page-body')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fa fa-boxes me-2"></i>Stock</h5>
                        @if(auth()->user()->hasPermissionToCategory('inventories', 'add'))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#createInventoryModal">
                            <i class="fa fa-plus me-1"></i>Add Product
                        </button>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @unless(Auth::user()->hasRole('distributor'))
                            <div class="selection-hub-card mb-4 p-4">
                                <div id="distributor_selection_container">
                                    <div class="row align-items-center g-4">
                                        <div class="col-lg-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div>
                                                    <h6 class="fw-bold mb-0">Distributor Inventory</h6>
                                                    <p class="text-muted small mb-0">Select a partner to manage stock</p>
                                                </div>
                                            </div>
                                            <select id="distributor_filter" class="form-select select2-dist">
                                                <option value="">Search Distributor...</option>
                                                @foreach($distributors as $d)
                                                    <option value="{{ $d->id }}" 
                                                        data-name="{{ $d->user->name }}"
                                                        data-address="{{ $d->address }}"
                                                        data-phone="{{ $d->contact_no }}"
                                                        data-email="{{ $d->user->email }}"
                                                        data-gst="{{ $d->gst ?? 'N/A' }}"
                                                        data-license="{{ $d->drug_license_no ?? 'N/A' }}"
                                                        data-credit="{{ number_format($d->credit_balance ?? 0, 2) }}"
                                                        data-initials="{{ strtoupper(substr($d->user->name, 0, 2)) }}">
                                                        {{ $d->user->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-8 border-start-lg">
                                            <div id="distributor_welcome_msg" class="text-center py-2">
                                                <div class="opacity-50 mb-2">
                                                    <i class="fa fa-arrow-left-long me-2"></i> Select a distributor from the search to view their inventory profile
                                                </div>
                                            </div>
                                            <div id="active_distributor_profile" class="d-none">
                                                <div class="row g-3">
                                                    <div class="col-md-5">
                                                        <div class="d-flex align-items-start gap-3">
                                                            <div id="active_dist_avatar" class="distributor-avatar shadow-sm mt-1">--</div>
                                                            <div>
                                                                <h5 id="active_dist_name" class="fw-bold mb-1 text-primary">--</h5>
                                                                <p id="active_dist_address" class="text-muted small mb-2"><i class="fa fa-location-dot me-1"></i> --</p>
                                                                <div class="d-flex flex-wrap gap-2">
                                                                    <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.65rem;">GST: <b id="active_dist_gst">--</b></span>
                                                                    <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.65rem;">DL: <b id="active_dist_license">--</b></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-7">
                                                        <div class="row g-2">
                                                            <div class="col-6">
                                                                <div class="stat-mini-card">
                                                                    <div class="text-muted smaller fw-bold text-uppercase mb-1" style="font-size: 0.65rem;">Total SKU</div>
                                                                    <div id="active_dist_sku" class="h5 fw-bold mb-0 text-dark">--</div>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="stat-mini-card">
                                                                    <div class="text-muted smaller fw-bold text-uppercase mb-1" style="font-size: 0.65rem;">Credit Limit</div>
                                                                    <div id="active_dist_credit" class="h5 fw-bold mb-0 text-success">--</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="mt-2 d-flex align-items-center justify-content-end gap-3 text-muted" style="font-size: 0.7rem;">
                                                            <span><i class="fa fa-phone me-1"></i> <b id="active_dist_phone">--</b></span>
                                                            <span><i class="fa fa-envelope me-1"></i> <b id="active_dist_email">--</b></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endunless

                        <div class="row mb-4 align-items-center">
                            <div class="col-md-6">
                                <div class="inventory-search-group">
                                    <i class="fa fa-search"></i>
                                    <input type="text" id="custom-inventory-search" class="form-control" placeholder="Search by Product, Brand or Batch...">
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div id="table-buttons-container"></div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="display table table-hover" id="inventories-table">
                                <thead>
                                    <tr>
                                        <th style="display:none;">Updated At</th>
                                        <th>No.</th>

                                        <th>Product Name & Variations</th>
                                        <th>Brand</th>
                                        @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
                                            <th>Distributor</th>
                                        @endif
                                        <th>Stock (Total)</th>
                                        <th>Breakdown</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Create Inventory Modal --}}
    <div class="modal fade" id="createInventoryModal" tabindex="-1" aria-labelledby="createInventoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="createInventoryModalLabel"><i class="fa fa-plus-circle me-2"></i>Add Product to Inventory</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createInventoryForm">
                    @csrf
                    <div class="modal-body">


                        <div class="mb-3">
                            <label for="create_product_id" class="form-label">Product</label>
                            <select name="product_id" id="create_product_id" class="form-select" required>
                                <option value="">-- Select product --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" data-code="{{ $p->product_code }}"
                                        data-name="{{ $p->product_name }}"
                                        data-units-per-strip="{{ $p->units_per_strip ?? 1 }}"
                                        data-strips-per-box="{{ $p->strips_per_box ?? 1 }}"
                                        data-boxes-per-carton="{{ $p->boxes_per_carton ?? 1 }}"
                                        data-has-variants="{{ $p->has_variants ? '1' : '0' }}"
                                        data-box-size="{{ $p->box_size ?? '' }}"
                                        data-pack="{{ strtolower($p->pack ?? '') }}">
                                        {{ $p->product_name }}{{ (!empty(trim($p->product_code)) && strtoupper(trim($p->product_code)) !== 'N/A') ? ' ('.$p->product_code.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
                            <div class="mb-3">
                                <label for="create_distributor_id" class="form-label">Distributor</label>
                                <select name="distributor_id" id="create_distributor_id" class="form-select" required>
                                    <option value="">-- Select Distributor --</option>
                                    @foreach($distributors as $d)
                                        <option value="{{ $d->id }}">{{ $d->user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <script>
                             document.addEventListener('DOMContentLoaded', function () {
                                 var select = document.getElementById('create_product_id');
                                 var packInfo = document.getElementById('create_pack_info');
                                 var calcQty = document.getElementById('create_input_qty');
                                 var calcUnit = document.getElementById('create_input_unit');
                                 var totalInput = document.getElementById('create_stock');
                                 var variantContainer = document.getElementById('variant_container');

                                 function calculateCreateTotal() {
                                     var opt = select.options[select.selectedIndex];
                                     if (!opt || opt.value === "") return;

                                     var stripsPerBox = parseInt(opt.getAttribute('data-strips-per-box')) || 1;
                                     var boxesPerCarton = parseInt(opt.getAttribute('data-boxes-per-carton')) || 1;
                                     var unitsPerStrip = parseInt(opt.getAttribute('data-units-per-strip')) || 1;

                                     var qty = parseFloat(calcQty.value) || 0;
                                     var unit = calcUnit.value;

                                     var total = 0;
                                     if (unit === 'strip') {
                                         total = qty;
                                     } else if (unit === 'box') {
                                         total = qty * stripsPerBox;
                                     } else if (unit === 'carton') {
                                         total = qty * stripsPerBox * boxesPerCarton;
                                     }

                                     totalInput.value = total;
                                 }

                                 function calculateAdjustTotal() {
                 var product = currentInventory.product || {};
                 var stripsPerBox = parseInt(product.strips_per_box) || 1;
                 var boxesPerCarton = parseInt(product.boxes_per_carton) || 1;

                 var qty = parseFloat(adjustQtyInput.value) || 0;
                 var unit = adjustUnitSelect.value;
                 var action = adjustActionSelect.value;

                 var total = 0;
                 if (unit === 'strip') {
                     total = qty;
                 } else if (unit === 'box') {
                     total = qty * stripsPerBox;
                 } else if (unit === 'carton') {
                     total = qty * stripsPerBox * boxesPerCarton;
                 }

                 var currentStock = parseInt(currentInventory.stock) || 0;
                 var finalStock = action === 'add' ? currentStock + total : currentStock - total;

                 totalAdjustInput.value = total;
                 finalStockInput.value = finalStock;

                 // Dynamic label update
                 var boxSizeStr = product.box_size || '';
                 var pPack = (product.pack || '').toLowerCase();
                 var pName = (currentInventory.product_name || '').toLowerCase();
                 var isCount = boxSizeStr === "";
                 if (!isCount) {
                     isCount = pPack.includes('nos') || pPack.includes('count') ||
                         pPack.includes('pair') || pPack.includes('bottle') ||
                         pPack.includes('ml') || pPack.includes('gm') || pPack.includes('syp') ||
                         pName.includes('syp') || pName.includes('syrup') || pName.includes('drop') || 
                         pName.includes('ointment') || pName.includes('belt') ||
                         pName.includes('cap') || pName.includes('binder') ||
                         pName.includes('splint') || pName.includes('brace') ||
                         pName.includes('cuff') || pName.includes('walker');
                 }

                 var unitLabel = isCount ? 'Nos' : 'Strips';
                 document.getElementById('adjust_total_stock_label').innerText = "Adjust Total (" + unitLabel + ")";
                 document.getElementById('adjust_final_stock_label').innerText = "Final Stock (" + unitLabel + ")";
                 
                 // Rebuild unit select options if needed? 
                 // (Usually it's better to do it once when opening modal)
             }

                                 if (select) {
                                     select.addEventListener('change', function () {
                                         var selected = select.options[select.selectedIndex];
                                         if (selected && selected.value !== "") {
                                             var stripsPerBox = selected.getAttribute('data-strips-per-box') || 1;
                                             var boxesPerCarton = parseInt(selected.getAttribute('data-boxes-per-carton')) || 1;
                                             var unitsPerStrip = parseInt(selected.getAttribute('data-units-per-strip')) || 1;
                                             var hasVariants = selected.getAttribute('data-has-variants') == '1';
                                             var boxSizeStr = selected.getAttribute('data-box-size') || '';
                                             var pName = (selected.getAttribute('data-name') || '').toLowerCase();
                                             var pPack = (selected.getAttribute('data-pack') || '').toLowerCase();

                                             // Refined Unit Logic: Use global helper
                                             var isCount = window.checkIsNos(pName, pPack, boxSizeStr);

                                             var unitSelect = calcUnit;
                                             var totalLabel = document.getElementById('create_stock_label');

                                             // Rebuild unit options 
                                             unitSelect.innerHTML = '';
                                             if (isCount) {
                                                 unitSelect.innerHTML += '<option value="strip">Nos</option>';
                                                 totalLabel.innerText = "Converted Total (Nos)";
                                                 packInfo.innerHTML = `Packaging: <b>${unitsPerStrip} Nos/Unit</b> | <b>${stripsPerBox} Units/Box</b> | <b>${boxesPerCarton} Box/Ctn</b>`;
                                             } else {
                                                 unitSelect.innerHTML += '<option value="strip">Strips</option>';
                                                 totalLabel.innerText = "Converted Total (Strips)";
                                                 packInfo.innerHTML = `Packaging: <b>${unitsPerStrip} Tab/Str</b> | <b>${stripsPerBox} Str/Box</b> | <b>${boxesPerCarton} Box/Ctn</b>`;
                                                 unitSelect.innerHTML += '<option value="box">Boxes</option>';
                                                 unitSelect.innerHTML += '<option value="carton">Cartons</option>';
                                             }

                                             // Show/Hide variant and handle dynamic parsing
                                             var variantSelect = document.getElementById('create_variant');
                                             if (hasVariants || pName.includes('(')) {
                                                 variantContainer.classList.remove('d-none');
                                                 
                                                 // Dynamic parsing for (S/M/L) patterns
                                                 var match = pName.match(/\(([^)]+)\)/g);
                                                 if (match) {
                                                     var lastMatch = match[match.length - 1].replace('(', '').replace(')', '');
                                                     if (lastMatch.includes('/')) {
                                                         var sizes = lastMatch.split('/');
                                                         variantSelect.innerHTML = '<option value="">-- Select Size --</option>';
                                                         sizes.forEach(s => {
                                                             let size = s.trim().toUpperCase();
                                                             variantSelect.innerHTML += `<option value="${size}">${size}</option>`;
                                                         });
                                                     }
                                                 }
                                             } else {
                                                 variantContainer.classList.add('d-none');
                                                 variantSelect.value = '';
                                             }

                                             calculateCreateTotal();
                                         } else {
                                             packInfo.innerText = "Select a product to see packaging rules";
                                             variantContainer.classList.add('d-none');
                                         }
                                     });
                                 }

                                 $('#create_input_qty, #create_input_unit').on('input change', calculateCreateTotal);
                             });
                        </script>

                        <div id="variant_container" class="mb-3 d-none">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label for="create_side" class="form-label fw-bold">Side</label>
                                    <select name="side" id="create_side" class="form-select">
                                        <option value="">N/A</option>
                                        <option value="Left">Left</option>
                                        <option value="Right">Right</option>
                                        <option value="Both">Both</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="create_size" class="form-label fw-bold">Size</label>
                                    <select name="size" id="create_size" class="form-select">
                                        <option value="">N/A</option>
                                        <option value="S">S</option>
                                        <option value="M">M</option>
                                        <option value="L">L</option>
                                        <option value="XL">XL</option>
                                        <option value="XXL">XXL</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Batch No</label>
                                <input type="text" name="batch_no" class="form-control" placeholder="Enter Batch No"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Expiry Date</label>
                                <input type="date" name="expiry_date" class="form-control" required>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Quantity</label>
                                <input type="number" id="create_input_qty" class="form-control" placeholder="0" min="0" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Unit</label>
                                <select name="unit" id="create_input_unit" class="form-select">
                                    <option value="strip">Strips</option>
                                    <option value="box">Boxes</option>
                                    <option value="carton">Cartons</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="create_stock" id="create_stock_label"
                                class="form-label fw-bold text-muted small">Converted Total
                                (Strips)</label>
                            <input type="number" name="stock" id="create_stock" class="form-control bg-light" value="0"
                                readonly required>
                            <div id="create_pack_info" class="form-text small text-info opacity-75">Select a product to see
                                packaging rules</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Inventory Modal --}}
    <div class="modal fade" id="editInventoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fa fa-edit me-2"></i> Edit Stock Information</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editInventoryForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <!-- Premium Metadata Strip -->
                        <div class="product-metadata-strip mb-4">
                            <div class="row g-0 text-center">
                                <div class="col-3 metadata-item">
                                    <label>MRP</label>
                                    <div id="edit_detail_mrp" class="value">0.00</div>
                                </div>
                                <div class="col-3 metadata-item">
                                    <label>PTR</label>
                                    <div id="edit_detail_ptr" class="value">0.00</div>
                                </div>
                                <div class="col-3 metadata-item">
                                    <label>HSN</label>
                                    <div id="edit_detail_hsn" class="value">-</div>
                                </div>
                                <div class="col-3 metadata-item">
                                    <label>PACK</label>
                                    <div id="edit_detail_pack" class="value">-</div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Product Name</label>
                                    <input type="text" id="edit_product_name" class="form-control bg-light border-0 fw-bold" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Side</label>
                                    <input type="text" id="edit_side" class="form-control bg-light border-0 fw-bold text-primary" readonly placeholder="N/A">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Size</label>
                                    <input type="text" id="edit_size" class="form-control bg-light border-0 fw-bold text-primary" readonly placeholder="N/A">
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Dist. Product Code</label>
                                    <input type="text" name="distributor_product_code" id="edit_distributor_product_code" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Batch No</label>
                                    <input type="text" name="batch_no" id="edit_batch_no" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Expiry Date</label>
                                    <input type="date" name="expiry_date" id="edit_expiry_date" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="product_id" id="edit_product_id">

                        @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
                            <div class="mb-3">
                                <label for="edit_distributor_id" class="form-label fw-bold small text-uppercase text-muted">Distributor</label>
                                <select name="distributor_id" id="edit_distributor_id" class="form-select" required>
                                    <option value="">-- Select Distributor --</option>
                                    @foreach($distributors as $d)
                                        <option value="{{ $d->id }}">{{ $d->user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="distributor_id" id="edit_distributor_id">
                        @endif

                        <!-- Minimal Stock Operation Toggle -->
                        <div class="text-center mb-4">
                            <label class="form-label fw-bold small text-uppercase text-muted d-block mb-2">Adjust Quantity</label>
                            <div class="minimal-op-toggle">
                                <button type="button" class="btn op-btn" data-op="subtract">
                                    <i class="fa fa-minus me-1"></i> Reduce
                                </button>
                                <button type="button" class="btn op-btn active" data-op="add">
                                    <i class="fa fa-plus me-1"></i> Increase
                                </button>
                            </div>
                            <input type="hidden" name="operation" id="selected_op" value="add">
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-md-8">
                                <div class="form-group mb-0">
                                    <label class="form-label fw-bold small text-uppercase text-muted" id="edit_stock_label_text">Entry Quantity</label>
                                    <input type="number" name="stock" id="edit_stock" class="form-control form-control-lg" required min="0.01" step="0.01" placeholder="Enter amount to add/reduce">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Unit</label>
                                    <select name="unit" id="edit_unit" class="form-select form-select-lg">
                                        <option value="strip">Strips</option>
                                        <option value="box">Boxes</option>
                                        <option value="carton">Cartons</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Secondary Breakdown Row (Optional/Dynamic) -->
                        <div id="edit_conv_info" class="mt-3 p-3 rounded-3 bg-soft-info border border-info border-opacity-10" style="display:none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-info fw-bold small text-uppercase">Effective Change</span>
                                <span id="edit_total_strips" class="fs-5 fw-bold text-info">0</span>
                            </div>
                            <div id="edit_pack_info_text" class="text-muted smaller mt-1"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Batch List Modal --}}
    <div class="modal fade" id="batchListModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 28px;">
                <div class="modal-header bg-soft-primary border-0 p-4">
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="batchListProductName">Detailed Batch List</h5>
                        <span class="text-muted smaller fw-bold text-uppercase opacity-75">Inventory Breakdown</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-2">
                    <div class="table-responsive rounded-4 border overflow-hidden">
                        <table class="table table-hover mb-0" id="batchListingTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Batch Number</th>
                                    <th>Variation</th>
                                    <th>Expiry</th>
                                    <th class="text-center">Stock</th>
                                    {{-- <th class="text-end pe-4">Actions</th> --}}
                                </tr>
                            </thead>
                            <tbody id="batchListTableBody">
                                <!-- Populated via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Show Product Details Modal (Replicated from Products for seamless UX) --}}
    <div class="modal fade" id="showProductDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 28px; overflow: hidden;">
                <div class="modal-header border-0 p-4" style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.05) 0%, rgba(59, 130, 246, 0.05) 100%);">
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-primary" id="detailProductName">Product Specifications</h5>
                        <span class="text-muted smaller fw-bold text-uppercase opacity-75">Technical & Pricing Details</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-2" id="showProductDetailBody">
                    {{-- Load via JS --}}
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">Confirm Removal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3">Are you sure you want to remove this item from your stock?</p>
                    <div class="text-muted small">
                        <i class="fa fa-info-circle me-1"></i> The product definition will <strong>NOT</strong> be deleted.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn"><i class="fa fa-trash me-1"></i>
                        Remove Item</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Product Details Modal --}}
    <div class="modal fade" id="productDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="prodDetailName">Product Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- <div class="text-center mb-4">
                        <img id="prodDetailImage" src="" class="img-fluid rounded" style="max-height: 150px;"
                            alt="Product Image">
                    </div> --}}
                    <ul class="list-group list-group-flush" id="prodDetailList">
                        <!-- Details populated via JS -->
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <style>
        .batch-popover-table {
            font-size: 0.8rem;
            width: 100%;
            border-collapse: collapse;
        }
        .batch-popover-table th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            padding: 4px 8px;
            text-align: left;
        }
        .batch-popover-table td {
            border-bottom: 1px solid #eee;
            padding: 4px 8px;
        }
        .batch-popover-table tr:last-child td {
            border-bottom: none;
        }
        .popover {
            max-width: 400px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            border: none;
        }

        /* High-End DataTable Customization */
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 8px 15px 8px 38px;
            background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") no-repeat 15px center;
            transition: all 0.2s;
            min-width: 300px;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #3b82f6;
            outline: 0;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px;
        }

        /* Premium Metadata Strip */
        .product-metadata-strip {
            background: var(--med-bg-body);
            border: 1px solid var(--med-border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }
        .metadata-item {
            padding: 12px 5px;
            border-right: 1px solid var(--med-border);
        }
        .metadata-item:last-child {
            border-right: none;
        }
        .metadata-item label {
            display: block;
            font-size: 0.65rem;
            text-transform: uppercase;
            font-weight: 800;
            color: var(--med-text-muted);
            letter-spacing: 0.05em;
            margin-bottom: 2px;
        }
        .metadata-item .value {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--med-primary);
        }

        /* Premium Segmented Control */
        .premium-segmented-control {
            display: flex;
            background: var(--med-bg-body);
            padding: 6px;
            border-radius: 14px;
            position: relative;
            gap: 4px;
            border: 1px solid var(--med-border);
        }
        .segmented-option {
            flex: 1;
            position: relative;
            z-index: 2;
        }
        .segmented-option input {
            display: none;
        }
        .segmented-option label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 15px;
            margin: 0;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--med-text-muted);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 10px;
            gap: 8px;
        }
        .segmented-option label .dot, 
        .segmented-option label .plus, 
        .segmented-option label .minus {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }
        .segmented-option label .dot { background: var(--med-primary); }
        .segmented-option label .plus { color: #10b981; font-weight: 900; }
        .segmented-option label .minus { color: #ef4444; font-weight: 900; }

        .segmented-option input:checked + label {
            color: var(--med-text-main);
        }

        .control-glider {
            position: absolute;
            height: calc(100% - 12px);
            width: calc(33.33% - 8px);
            background: var(--med-bg-card);
            border-radius: 10px;
            z-index: 1;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 1px solid var(--med-border);
        }

        input[id="edit_op_set"]:checked ~ .control-glider { transform: translateX(0); }
        input[id="edit_op_add"]:checked ~ .control-glider { transform: translateX(100%); }
        input[id="edit_op_subtract"]:checked ~ .control-glider { transform: translateX(200%); }

        /* Theme-aware Soft Badges */
        .bg-soft-primary { background-color: rgba(59, 130, 246, 0.1) !important; color: #3b82f6 !important; }
        .bg-soft-success { background-color: rgba(16, 185, 129, 0.1) !important; color: #10b981 !important; }
        .bg-soft-danger { background-color: rgba(239, 68, 68, 0.1) !important; color: #ef4444 !important; }
        .bg-soft-info { background-color: rgba(6, 182, 212, 0.1) !important; color: #0891b2 !important; }

        body.dark-only .product-metadata-strip {
            background: rgba(255,255,255,0.03);
        }
        body.dark-only .metadata-item .value {
            color: var(--med-secondary);
        }
        body.dark-only .premium-segmented-control {
            background: rgba(0,0,0,0.2);
        }
        body.dark-only .control-glider {
            background: #1e293b;
            border-color: rgba(255,255,255,0.1);
        }
        
        /* Modal Dark Mode Fixes */
        body.dark-only .modal-content {
            background-color: #111827;
            color: #f3f4f6;
            border: 1px solid rgba(255,255,255,0.1);
        }
        body.dark-only .modal-header.bg-soft-primary {
            background-color: rgba(59, 130, 246, 0.15) !important;
        }
        body.dark-only .modal-header.bg-light,
        body.dark-only .bg-light {
            background-color: #1f2937 !important;
        }
        body.dark-only .table {
            color: #d1d5db;
        }
        body.dark-only .table-hover tbody tr:hover {
            background-color: rgba(255,255,255,0.03);
            color: #fff;
        }
        body.dark-only .btn-light {
            background-color: #4b5563;
            color: #ffffff !important;
            border: 1px solid #6b7280;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            font-weight: 600;
        }
        body.dark-only .btn-light:hover {
            background-color: #6b7280;
            color: #ffffff !important;
            border-color: #9ca3af;
        }
        body.dark-only .text-muted, 
        body.dark-only .text-muted-theme {
            color: #9ca3af !important;
        }
        body.dark-only .modal-header .btn-close {
            filter: invert(1) grayscale(1) brightness(2);
        }
        body.dark-only .table-responsive.border {
            border-color: #374151 !important;
        }
        
        .smaller { font-size: 0.7rem !important; }
    padding: 5px 10px;
        }
        .page-item.active .page-link {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%) !important;
            border: none !important;
            box-shadow: 0 4px 6px rgba(30, 58, 138, 0.2);
            color: white !important;
        }
        .page-link {
            border-radius: 8px !important;
            margin: 0 2px;
            color: #4b5563;
        }
        .breakdown-main {
            transition: all 0.2s;
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .breakdown-main:hover {
            background: rgba(var(--bs-primary-rgb), 0.1);
        }
        .text-center {
            text-align: center !important;
        }
        .smaller {
            font-size: 0.75rem;
        }
        .variant-badge {
            background: #eef2f7;
            color: #2c3e50;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            border: 1px solid #d1d9e6;
        }

        /* Premium Segmented Control - Executive Design */
        .stock-op-btn-group {
            display: flex;
            background: #f1f5f9;
            padding: 6px;
            border-radius: 14px;
            gap: 6px;
            border: 1px solid #e2e8f0;
        }
        .stock-op-btn-group .btn-check + .btn {
            border: none !important;
            padding: 12px 10px !important;
            border-radius: 10px !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
            font-weight: 700 !important;
            flex: 1;
            color: #64748b;
            background: transparent !important;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            letter-spacing: 0.01em;
        }
        
        /* Overwrite - Active */
        .stock-op-btn-group .btn-check:checked + .btn-outline-primary {
            background-color: #2563eb !important;
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25) !important;
        }
        
        /* Add - Active */
        .stock-op-btn-group .btn-check:checked + .btn-outline-success {
            background-color: #059669 !important; /* Rich emerald green */
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25) !important;
        }
        
        /* Reduce - Active */
        .stock-op-btn-group .btn-check:checked + .btn-outline-danger {
            background-color: #dc2626 !important;
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25) !important;
        }

        /* Inactive Hover Effect */
        .stock-op-btn-group .btn:hover:not(.checked) {
            background: rgba(255,255,255,0.5) !important;
            color: #1e293b;
        }
        
        .btn-teal {
            background-color: #00695c !important;
            color: white !important;
            border: none !important;
            transition: all 0.2s;
        }
        .btn-teal:hover {
            background-color: #004d40 !important;
            box-shadow: 0 4px 8px rgba(0, 105, 92, 0.2);
        }
        .modal-header.bg-primary {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%) !important;
        }
        .selection-prompt {
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            margin: 20px 0;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        @php
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $roles = ['admin', 'superadmin', 'distributor', 'salesmanager'];
            $canEdit = in_array($user->role, $roles) || $user->hasAnyRole($roles) || $user->hasPermissionToCategory('inventories', 'edit');
            $canDelete = in_array($user->role, ['admin', 'superadmin', 'salesmanager']) || $user->hasAnyRole(['admin', 'superadmin', 'salesmanager']) || $user->hasPermissionToCategory('inventories', 'delete');
            $isDistributor = $user->role === 'distributor' || $user->hasRole('distributor');
        @endphp
        $(document).ready(function () {
            const canEdit = @json($canEdit);
            const canDelete = @json($canDelete);
            const isDistributor = @json($isDistributor);

            // Global Helper for Segmented/Unit Logic
            window.getMedicineType = function(pName, pData) {
                const n = (pName || '').toUpperCase();
                pData = pData || {};
                const u = parseInt(String(pData.units_per_strip || pData.strip_size || 1).replace(/[^0-9]/g, '') || 1);
                
                // Explicit exclusions first
                if (n.includes('KNEE CAP')) return 'Medical Supply';
                
                // Name-based hints for common tablets/capsules (using startsWith/includes)
                if (n.includes('PANTO') || n.includes('PARA') || n.includes('PANTOPRAZOLE') || n.includes('PARACETAMOL')) return 'Tablet/Capsule';

                // If it has a generic name or multiple units per strip, it's likely a medicine
                if (pData.generic_name || u > 1) return 'Tablet / Capsule';
                
                // Boundary-aware keyword matching
                if (/\b(TAB|CAP|TABLET|CAPSULE|TABS|CAPS)\b/i.test(n)) return 'Tablet / Capsule';
                if (/\b(SYP|LIQ|SUSP|SYRUP|LIQUID)\b/i.test(n)) return 'Liquid / Syrup';
                if (/\b(INJ|INJECTION)\b/i.test(n)) return 'Injection';
                if (/\b(CRM|OINT|CREAM|OINTMENT)\b/i.test(n)) return 'Cream / Ointment';
                if (/\b(DROP|DROPS)\b/i.test(n)) return 'Drop / Spray';
                if (/\b(GEL)\b/i.test(n)) return 'Gel / Topical';
                if (/\b(POW|POWDER)\b/i.test(n)) return 'Powder';
                
                return 'Medical Supply';
            }

            window.checkIsNos = function(pName, pPack, boxSize) {
                pName = (pName || '').toLowerCase();
                pPack = (pPack || '').toLowerCase();
                let isNos = (boxSize === "" || boxSize === null || boxSize === undefined);
                if (!isNos) {
                    const nosKeywords = ['nos', 'count', 'pair', 'bottle', 'ml', 'gm', 'syp', 'syrup', 'drop', 'ointment', 'belt', 'cap', 'binder', 'splint', 'brace', 'cuff', 'walker'];
                    isNos = nosKeywords.some(kw => pPack.includes(kw) || pName.includes(kw));
                }
                return isNos;
            }

            window.formatStockBreakdown = function(data, productDetails, isNos, unitsPerStrip) {
                if (!data || data <= 0) return '0';
                
                // Robustly parse sizes even if they come as strings like "10x10" or "30ml"
                let boxSize = parseInt(productDetails.strips_per_box || productDetails.box_size || 0);
                let cartonSize = parseInt(productDetails.boxes_per_carton || productDetails.carton_size || 0);
                let baseStr = isNos ? 'Nos' : 'Str';
                let totalQty = isNos ? Math.round(data * unitsPerStrip) : data;

                // Fallback to plain unit view if no clear breakdown rules exist
                if (isNos || (boxSize <= 1 && cartonSize <= 1)) {
                    return `${totalQty} ${baseStr}`;
                }

                let cartons = 0;
                let remaining = data;
                
                // Process Cartons if rule exists
                if (cartonSize > 1 && boxSize > 0) {
                    let stripsPerCarton = boxSize * cartonSize;
                    cartons = Math.floor(data / stripsPerCarton);
                    remaining = data % stripsPerCarton;
                }

                // Process Boxes if rule exists
                let boxes = boxSize > 1 ? Math.floor(remaining / boxSize) : 0;
                let strips = boxSize > 1 ? remaining % boxSize : remaining;

                let parts = [];
                if (cartons > 0) parts.push(`<span class="fw-bold text-dark">${cartons}</span> Ctn`);
                if (boxes > 0) parts.push(`<span class="fw-bold text-dark">${boxes}</span> Box`);
                if (strips > 0 || parts.length === 0) parts.push(`<span class="fw-bold text-dark">${strips}</span> ${baseStr}`);

                let totalRes = parts.join(' | ');
                
                // Calculate total base units (Tablets/Nos)
                const units = parseInt(unitsPerStrip) || 1;
                const totalBaseUnits = Math.round(data * units);
                
                if (totalBaseUnits > 0) {
                    const unitLabel = isNos ? 'Nos' : 'Tabs';
                    totalRes += ` <span class="smaller text-muted" style="font-size: 0.7rem;">(${totalBaseUnits} ${unitLabel})</span>`;
                }

                return totalRes;
            }

            var table = $('#inventories-table').DataTable({
                processing: false,
                serverSide: true,
                ajax: {
                    url: "{{ route('inventories.index') }}",
                    type: 'GET',
                    data: function (d) {
                        const distFilter = $('#distributor_filter');
                        if (distFilter.length) {
                             d.distributor_id = distFilter.val();
                        }
                    },
                    dataSrc: 'data'
                },
                language: {
                    emptyTable: `<div class="p-5 text-center text-muted">
                                    <i class="fa fa-boxes-stacked fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">No inventory data available. Please select a distributor above.</p>
                                 </div>`,
                    zeroRecords: `<div class="text-center p-5">
                                    <i class="fa fa-search fa-3x text-muted mb-3"></i>
                                    <h5 class="fw-bold">No Products Found</h5>
                                    <p class="text-muted">We couldn't find any products in stock for the selected distributor matching your search.</p>
                                  </div>`,
                    info: "Showing _START_ to _END_ of _TOTAL_ products",
                    infoEmpty: "Showing 0 to 0 of 0 products",
                    lengthMenu: "Show _MENU_ entries"
                },
                preDrawCallback: function (settings) {
                    const distFilter = $('#distributor_filter');
                    const hasDistributor = isDistributor || (distFilter.length && distFilter.val() !== "");
                    
                    if (hasDistributor) {
                        settings.oLanguage.sEmptyTable = `<div class="p-5 text-center text-muted">
                                    <i class="fa fa-box-open fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">No products are currently registered in this inventory.</p>
                                  </div>`;
                    } else {
                        settings.oLanguage.sEmptyTable = `<div class="p-5 text-center text-muted">
                                    <i class="fa fa-boxes-stacked fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">Please select a distributor above to analyze stock levels.</p>
                                 </div>`;
                    }
                },
                drawCallback: function (settings) {
                    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
                    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                        return new bootstrap.Popover(popoverTriggerEl, {
                            sanitize: false
                        })
                    });
                },
                dom: "<'row'<'col-sm-12'tr>>" +
                    "<'row mt-4 align-items-center'<'col-md-4'l><'col-md-4 text-center'i><'col-md-4'p>>",
                initComplete: function () {
                    $('.dataTables_filter').hide();
                    
                    // Link custom search bar
                    $('#custom-inventory-search').on('keyup', function() {
                        table.search(this.value).draw();
                    });
                    
                    // Move buttons to custom container
                    table.buttons().container().appendTo('#table-buttons-container');
                    
                    // Initialize Select2
                    $('.select2-dist').select2({
                        placeholder: "Search & Select Distributor...",
                        allowClear: true,
                        width: '100%'
                    });
                },
                buttons: {
                    dom: {
                        button: {
                            className: 'btn btn-sm btn-icon'
                        }
                    },
                    buttons: [{
                        extend: 'copy',
                        className: 'btn btn-secondary btn-sm',
                        text: '<i class="fa fa-copy"></i> Copy'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-info btn-sm text-white',
                        text: '<i class="fa fa-file-csv"></i> CSV'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-success btn-sm',
                        text: '<i class="fa fa-file-excel"></i> Excel'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-danger btn-sm',
                        text: '<i class="fa fa-file-pdf"></i> PDF'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-dark btn-sm',
                        text: '<i class="fa fa-print"></i> Print'
                    }
                    ]
                },
                order: [
                    [0, 'desc']
                ],
                columns: [{
                    data: 'updated_at',
                    name: 'updated_at',
                    visible: false,
                    searchable: false
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'product_name',
                    name: 'product_name',
                    render: function (data, type, row) {
                        if (!data) return '-';
                        let cleanName = data.replace(/\s*\([^)]*\/[^)]*\)/g, '').replace(/\s*\[[^\]]*\/[^\]]*\]/g, '').trim();
                        let details = row.product_details || {};
                        let subInfo = [];
                        if (details.product_code) subInfo.push(`<span class="badge bg-light text-dark border-0 px-2 py-0" style="font-size: 0.65rem;">${details.product_code}</span>`);
                        if (details.generic_name) subInfo.push(details.generic_name);
                        if (details.pack) subInfo.push(details.pack);

                        // Add Variation badges if available
                        if (row.side && row.side !== '-' && row.side !== 'N/A') {
                            subInfo.push(`<span class="merged-badge bg-soft-primary text-primary">${row.side}</span>`);
                        }
                        if (row.size && row.size !== '-' && row.size !== 'N/A') {
                            subInfo.push(`<span class="merged-badge bg-soft-info text-info">${row.size}</span>`);
                        }
                        
                        return `
                            <div class="product-info-cell">
                                 <a href="javascript:void(0)" class="fw-bold product-main-name view-product-details text-primary" 
                                    style="text-decoration: none;"
                                    data-product='${JSON.stringify(row.product_details || {}).replace(/'/g, "&apos;")}'
                                    title="View Product Technical Details">
                                     ${cleanName}
                                 </a>
                                 <div class="text-muted d-flex flex-wrap align-items-center gap-2 mt-1" style="font-size: 0.72rem; opacity: 0.8;">
                                    ${subInfo.join(' <span class="opacity-25">|</span> ')}
                                 </div>
                            </div>
                        `;
                    }
                },
                {
                    data: 'product_details.brand',
                    name: 'product_details.brand',
                    render: function (data) {
                        return data ? `<span class="fw-bold" style="font-size: 0.8rem; color: #475569;">${data}</span>` : '<span class="text-muted small">N/A</span>';
                    }
                },
                    @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
                    {
                        data: 'distributor_name',
                        name: 'distributor_name'
                    },
                    @endif
                    {
                    data: 'stock',
                    name: 'stock',
                    render: function (data, type, row) {
                        if (!row.product_details) return data;
                        let unitsPerStrip = row.product_details.units_per_strip || 1;
                        let displayVal = data;
                        
                        let pPack = row.product_details.pack ? row.product_details.pack.toLowerCase() : '';
                        let pName = row.product_name ? row.product_name.toLowerCase() : '';
                        let boxSizeStr = row.product_details.box_size || '';
                        let isNos = boxSizeStr === "" || pPack.includes('nos') || pPack.includes('count') || pPack.includes('pair');
                        
                        if (isNos) {
                            displayVal = Math.round(data * unitsPerStrip);
                        }

                        return `<span class="fw-bold ${data > 0 ? 'text-success' : 'text-danger'}">${displayVal}</span>`;
                    },
                    className: 'text-center'
                },
                {
                    data: 'stock',
                    name: 'breakdown',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        if (!row.product_details) return '-';
                        const pData = row.product_details || {};
                        const isNos = window.checkIsNos(row.product_name, pData.pack, pData.box_size);
                        const unitsPerStrip = pData.units_per_strip || 1;
                        
                        const displayStr = window.formatStockBreakdown(data, pData, isNos, unitsPerStrip);
                        
                        return `<div class="breakdown-container view-batches-btn text-primary fw-bold" style="cursor: pointer;">
                                    <span>${displayStr} <i class="fa fa-external-link-alt ms-1 small opacity-50"></i></span>
                                </div>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        return `
                            <button type="button" class="btn btn-sm btn-view-premium px-4 view-batches-btn d-inline-flex align-items-center">
                                <span class="fw-bold">View</span>
                            </button>
                        `;
                    }
                }
                ]
            });

            // Filter Change Handler
            $('#distributor_filter').on('change', function () {
                const $selected = $(this).find(':selected');
                const val = $(this).val();
                
                if (val) {
                    $('#distributor_welcome_msg').addClass('d-none');
                    $('#active_distributor_profile').removeClass('d-none');
                    
                    // Update Profile Card
                    $('#active_dist_name').text($selected.data('name'));
                    $('#active_dist_address').html(`<i class="fa fa-location-dot me-1"></i> ${$selected.data('address')}`);
                    $('#active_dist_phone').text($selected.data('phone'));
                    $('#active_dist_email').text($selected.data('email'));
                    $('#active_dist_avatar').text($selected.data('initials'));
                    
                    $('#active_dist_gst').text($selected.data('gst'));
                    $('#active_dist_license').text($selected.data('license'));
                    $('#active_dist_credit').text('₹' + $selected.data('credit'));
                    
                    // We can also fetch real counts if needed, but for now we'll update after table load
                } else {
                    $('#distributor_welcome_msg').removeClass('d-none');
                    $('#active_distributor_profile').addClass('d-none');
                }
                
                table.ajax.reload();
            });

            // Update SKU count after table draw
            table.on('xhr', function (e, settings, json) {
                if (json && json.recordsTotal !== undefined) {
                    $('#active_dist_sku').text(json.recordsTotal);
                }
            });

            // Edit Handler logic variables
            let currentStripsPerBox = 1;
            let currentBoxesPerCarton = 1;
            let currentUnitsPerStrip = 1;

            function calculateEditTotal() {
                let qty = parseFloat($('#edit_stock').val()) || 0;
                let unit = $('#edit_unit').val();
                let op = $('#selected_op').val();

                let total = 0;
                if (unit === 'strip' || unit === 'nos') {
                    total = qty;
                } else if (unit === 'box') {
                    total = qty * currentStripsPerBox;
                } else if (unit === 'carton') {
                    total = qty * currentStripsPerBox * (currentBoxesPerCarton || 1);
                }

                $('#edit_total_strips').text(Math.round(total * 100) / 100);
                
                const pName = $('#edit_product_name').val();
                const isNos = checkIsNos(pName, '', ''); // Simplified here as detection is already done in openEditModal

                let packHtml = isNos ? 
                    `Packaging: <b>${currentUnitsPerStrip} Nos/Unit</b> | <b>${currentStripsPerBox} Units/Box</b>` :
                    `Packaging: <b>${currentStripsPerBox} Strips/Box</b> | <b>${currentBoxesPerCarton} Box/Ctn</b>`;
                $('#edit_pack_info_text').html(packHtml);
            }

            window.openEditModal = function (data) {
                if (typeof data === 'string') {
                    data = JSON.parse(data);
                }

                $('#edit_product_name').val(data.product_name);
                $('#edit_batch_no').val(data.batch_no);

                const product = data.product_details || {};
                const isNos = window.checkIsNos(data.product_name, product.pack, product.box_size);
                
                // Populate Detail Card with fallbacks
                $('#edit_detail_mrp').text(product.mrp ?? '0.00');
                $('#edit_detail_ptr').text(product.ptr ?? '0.00');
                $('#edit_detail_hsn').text(product.hsn_code ?? '-');
                $('#edit_detail_pack').text(product.pack ?? '-');
                
                // Row 2 Populating & Dynamic Hiding
                $('#edit_detail_units_per_strip').text(product.units_per_strip || '1');
                $('#edit_detail_strips_per_box').text(product.strips_per_box || '1');
                $('#edit_detail_boxes_per_carton').text(product.boxes_per_carton || '1');
                
                if (isNos) {
                    $('#edit_conv_row').hide();
                } else {
                    $('#edit_conv_row').show();
                }

                $('#edit_side').val(data.side || 'N/A');
                $('#edit_size').val(data.size || 'N/A');
                
                // Dynamic Unit Logic
                
                const unitLabel = isNos ? 'Nos' : 'Strips';
                const unitSelect = $('#edit_unit');
                unitSelect.empty();
                unitSelect.append(`<option value="strip" selected>${unitLabel}</option>`);
                if (!isNos) {
                    unitSelect.append('<option value="box">Boxes</option>');
                    unitSelect.append('<option value="carton">Cartons</option>');
                }

                $('#edit_expiry_date').val(data.raw_expiry_date || ''); 
                $('#edit_distributor_id').val(data.distributor_id);
                $('#edit_distributor_product_code').val(data.distributor_product_code);

                var url = "{{ route('inventories.update', ':id') }}".replace(':id', data.id);
                $('#editInventoryForm').attr('action', url);

                // Setup calculation context
                currentStripsPerBox = parseInt(product.strips_per_box) || 1;
                currentBoxesPerCarton = parseInt(product.boxes_per_carton) || 1;
                currentUnitsPerStrip = parseInt(product.units_per_strip) || 1;

                $('#edit_stock').val('');
                calculateEditTotal();
                
                // If opening edit from batch list, hide batch list first
                $('#batchListModal').modal('hide');
                $('#editInventoryModal').modal('show');
            }

            $('#inventories-table').on('click', '.view-batches-btn', function () {
                const tr = $(this).closest('tr');
                const row = table.row(tr).data();
                if (!row) return;

                const pData = row.product_details || {};
                const unitsPerStrip = parseInt(pData.units_per_strip || pData.strip_size || 1);
                const medType = window.getMedicineType(row.product_name, pData);
                const isTablet = medType === 'Tablet/Capsule';
                
                $('#batchListProductName').html(`${row.product_name} <span class="badge bg-soft-primary text-primary ms-2 rounded-pill" style="font-size: 0.65rem; vertical-align: middle;">${medType}</span>`);
                
                // Add Packaging Info as subtitle for context
                let packParts = [];
                if (isTablet) {
                    packParts.push(`Tab/Str: ${pData.strip_size || pData.units_per_strip + 's'}`);
                    packParts.push(`Str/Box: ${pData.box_size || pData.strips_per_box || 1}`);
                    if (pData.carton_size && pData.carton_size !== '-' || (pData.boxes_per_carton > 1)) {
                        packParts.push(`Box/Ctn: ${pData.carton_size || pData.boxes_per_carton}`);
                    }
                } else if (pData.pack && pData.pack !== '-') {
                    packParts.push(`Packaging: ${pData.pack}`);
                }
                
                $('#batchListModal .text-muted.smaller').text(packParts.length > 0 ? packParts.join(' | ') : 'Inventory');

                const isNos = window.checkIsNos(row.product_name, pData.pack, pData.box_size);
                const baseStr = isNos ? 'Nos' : 'Str';

                // Re-init the minimal toggle to Add
                $('.op-btn[data-op="add"]').addClass('active');
                $('.op-btn[data-op="subtract"]').removeClass('active');
                $('#selected_op').val('add');

                let html = '';
                row.batches.forEach(b => {
                    const displayStr = window.formatStockBreakdown(b.stock, pData, isNos, unitsPerStrip);
                    html += `<tr>
                        <td class="ps-4 align-middle">
                            <span class="fw-bold text-dark">${b.batch_no}</span>
                            <div class="smaller text-muted" style="font-size: 0.65rem;">${b.distributor_name}</div>
                        </td>
                        <td class="align-middle">
                            ${b.side && b.side !== '-' ? `<span class="badge bg-soft-primary text-primary rounded-pill px-2" style="font-size: 0.7rem;">${b.side}</span>` : ''}
                            ${b.size && b.size !== '-' ? `<span class="badge bg-soft-info text-info rounded-pill px-2 ms-1" style="font-size: 0.7rem;">${b.size}</span>` : ''}
                            ${(!b.side || b.side === '-') && (!b.size || b.size === '-') ? '<span class="text-muted opacity-50">-</span>' : ''}
                        </td>
                        <td class="align-middle fw-600 text-muted" style="font-size: 0.85rem;">${b.expiry_date}</td>
                        <td class="text-center align-middle">
                            <span class="badge ${b.stock > 0 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger'} rounded-pill px-3 fw-bold" style="font-size: 0.85rem; border: 1px solid rgba(0,0,0,0.05);">
                                ${displayStr}
                                ${isTablet ? `<span class="opacity-50 ms-1 fw-normal" style="font-size: 0.75rem;">(${unitsPerStrip}s)</span>` : ''}
                            </span>
                        </td>
                        {{-- 
                        <td class="text-end pe-4 align-middle">
                            <button class="btn btn-sm btn-primary rounded-pill px-3 edit-single-batch d-inline-flex align-items-center gap-2" 
                                    data-batch='${JSON.stringify({...row, ...b, product_details: pData}).replace(/'/g, "&apos;")}' title="Adjust this Batch">
                                <i class="fa fa-pencil-alt small"></i>
                                <span>Edit</span>
                            </button>
                        </td>
                        --}}
                    </tr>`;
                });

                $('#batchListTableBody').html(html);
                $('#batchListModal').modal('show');
            });

            // Handle Product Technical Details click (Pricing/Packaging)
            $(document).on('click', '.view-product-details', function(e) {
                e.preventDefault();
                const product = $(this).data('product');
                if (!product) return;

                const html = `
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <div class="p-4 rounded-4 text-center border-0 shadow-sm" style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.03) 0%, rgba(59, 130, 246, 0.03) 100%);">
                                <h3 class="fw-bold text-primary mb-3">${product.product_name || 'N/A'}</h3>
                                <div class="d-flex flex-wrap justify-content-center gap-3">
                                    <span class="badge bg-white text-dark border-0 shadow-sm px-3 py-2 rounded-pill"><i class="fa fa-tag me-1 text-primary"></i> ${product.product_code || 'N/A'}</span>
                                    <span class="badge bg-white text-dark border-0 shadow-sm px-3 py-2 rounded-pill"><i class="fa fa-dna me-1 text-info"></i> ${product.generic_name || 'Generic N/A'}</span>
                                    <span class="badge bg-white text-dark border-0 shadow-sm px-3 py-2 rounded-pill"><i class="fa fa-box me-1 text-warning"></i> Pack: ${product.pack || 'N/A'}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <div class="p-3 bg-white rounded-4 border-0 shadow-sm h-100">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2"><i class="fa fa-cubes me-2"></i>Stock & Packaging</h6>
                                <dl class="row mb-0 mt-2">
                                    ${(product.pack && product.pack !== '-') ? `
                                        <dt class="col-sm-5 text-muted small text-uppercase">Pack</dt>
                                        <dd class="col-sm-7 fw-bold">${product.pack}</dd>
                                    ` : ''}

                                    <dt class="col-sm-5 text-muted small text-uppercase">Tablet/Strip</dt>
                                    <dd class="col-sm-7 fw-bold">${product.units_per_strip || product.strip_size || '-'}</dd>

                                    <dt class="col-sm-5 text-muted small text-uppercase">Strip / Box</dt>
                                    <dd class="col-sm-7 fw-bold">${product.strips_per_box || product.box_size || '-'}</dd>

                                    ${(product.boxes_per_carton > 1 || (product.carton_size && product.carton_size !== '-')) ? `
                                        <dt class="col-sm-5 text-muted small text-uppercase">Box / Carton</dt>
                                        <dd class="col-sm-7 fw-bold">${product.carton_size || product.boxes_per_carton}</dd>
                                    ` : ''}

                                    <dt class="col-sm-5 text-muted small text-uppercase">HSN</dt>
                                    <dd class="col-sm-7 fw-bold">${product.hsn_code || '-'}</dd>
                                </dl>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 bg-white rounded-4 border-0 shadow-sm h-100">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2"><i class="fa fa-indian-rupee-sign me-2"></i>Pricing Details</h6>
                                <dl class="row mb-0 mt-2">
                                    <dt class="col-sm-5 text-muted small text-uppercase">MRP</dt>
                                    <dd class="col-sm-7 text-dark">₹${parseFloat(product.mrp || 0).toFixed(2)}</dd>

                                    <dt class="col-sm-5 text-muted small text-uppercase">PTR</dt>
                                    <dd class="col-sm-7 text-dark">₹${parseFloat(product.ptr || 0).toFixed(2)}</dd>

                                    <dt class="col-sm-5 text-muted small text-uppercase">PTS</dt>
                                    <dd class="col-sm-7 text-dark">₹${parseFloat(product.pts || 0).toFixed(2)}</dd>

                                    <dt class="col-sm-5 text-muted small text-uppercase">Loyalty %</dt>
                                    <dd class="col-sm-7 fw-bold text-info">${parseFloat(product.loyalty_point_percentage || 0).toFixed(2)}%</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                `;
                $('#showProductDetailBody').html(html);
                $('#showProductDetailsModal').modal('show');
            });

            $(document).on('click', '.edit-single-batch', function() {
                const data = $(this).data('batch');
                window.openEditModal(data);
            });

            window.updateOperationUI = function(op) {
                const label = $('#edit_stock_label_text');
                
                if (op === 'add') {
                    label.text('Quantity to Add');
                    $('#edit_conv_info').show();
                    $('#edit_stock').val('');
                } else if (op === 'subtract') {
                    label.text('Quantity to Reduce');
                    $('#edit_conv_info').show();
                    $('#edit_stock').val('');
                }
                calculateEditTotal();
            }

            $('.op-btn').on('click', function() {
                const op = $(this).data('op');
                $('.op-btn').removeClass('active');
                $(this).addClass('active');
                $('#selected_op').val(op);
                updateOperationUI(op);
            });

            // Delete Confirmation Handler
            let deleteFormId = null;
            $('#inventories-table').on('click', '.delete-btn', function () {
                let id = $(this).data('id');
                deleteFormId = '#delete-form-' + id;
                $('#deleteConfirmModal').modal('show');
            });

            $('#confirmDeleteBtn').click(function () {
                if (deleteFormId) {
                    let form = $(deleteFormId);
                    let url = form.attr('action');
                    let formData = form.serialize();

                    $.post(url, formData, function (res) {
                        $('#deleteConfirmModal').modal('hide');
                        if (typeof showToast === 'function') showToast('success', res.success);
                        table.ajax.reload(null, false);
                    }).fail(function (xhr) {
                        if (typeof showToast === 'function') showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Error deleting item');
                    });
                }
            });

            // Add handler for Create form
            $('#createInventoryForm').submit(function (e) {
                e.preventDefault();
                let url = "{{ route('inventories.store') }}";
                let formData = $(this).serialize();

                $.post(url, formData, function (res) {
                    $('#createInventoryModal').modal('hide');
                    $('#createInventoryForm')[0].reset();
                    if (typeof showToast === 'function') showToast('success', res.success);
                    table.ajax.reload(null, false);
                }).fail(function (xhr) {
                    if (typeof showToast === 'function') showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Error creating item');
                });
            });

            // Add handler for Edit form
            $('#editInventoryForm').submit(function (e) {
                e.preventDefault();
                let url = $(this).attr('action');
                let formData = $(this).serialize();

                $.post(url, formData, function (res) {
                    $('#editInventoryModal').modal('hide');
                    if (typeof showToast === 'function') showToast('success', res.success);
                    table.ajax.reload(null, false);
                }).fail(function (xhr) {
                    if (typeof showToast === 'function') showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Error updating item');
                });
            });

            // Remove redundant details handler if not needed, as it's now unified
            // $('#inventories-table').on('click', '.product-detail-link', ...

            $('#stockAdjustmentForm').submit(function (e) {
                e.preventDefault();
                let id = $('#stock_adj_id').val();
                let formData = $(this).serialize();
                let url = "{{ route('inventories.adjust-stock', ':id') }}".replace(':id', id);

                $.post(url, formData, function (res) {
                    $('#stockAdjustmentModal').modal('hide');
                    if (res.success) {
                        // alert(res.success); 
                        // Use toast if available or simple alert
                        // Assuming global showToast exists from layout
                        if (typeof showToast === 'function') showToast('success', res.success);
                        table.ajax.reload(null, false);
                    }
                }).fail(function (xhr) {
                    if (typeof showToast === 'function') showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Error updating stock');
                    else alert('Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Server Error'));
                });
            });

            // Delete Confirmation with SweetAlert2
            $('#inventories-table').on('click', '.delete-btn', function() {
                let id = $(this).data('id');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Confirm Deletion',
                        text: "This will permanently remove this batch from stock. Are you sure?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, Delete',
                        cancelButtonText: 'Cancel',
                        customClass: {
                            popup: 'rounded-4 shadow-lg'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $(`#delete-form-${id}`).submit();
                        }
                    });
                } else {
                    if (confirm('Are you sure you want to remove this from stock?')) {
                        $(`#delete-form-${id}`).submit();
                    }
                }
            });

        });
    </script>
@endpush