@extends('layouts.admin')
<style>
    .action-buttons {
        display: flex !important;
        gap: 4px;
        align-items: center;
    }

    .action-buttons form {
        margin-bottom: 0;
    }

    .action-buttons .btn {
        margin: 0 !important;
    }

    /* Modal sizing and table compacting */
    .modal-xl {
        max-width: 1140px;
    }

    #orders-table td:last-child {
        white-space: nowrap !important;
    }

    /* Preview / full content helper */
    .preview-content {
        display: inline-block;
    }

    .full-content {
        display: block;
    }

    .full-content.d-none {
        display: none;
    }

    /* Fix DataTable Length Select Arrow Issue */
    .dataTables_length select.form-select {
        padding-right: 2.5rem !important;
        background-position: right 0.75rem center;
        width: auto !important;
        display: inline-block !important;
    }
    .dataTables_processing {
        display: none !important;
    }
</style>

@section('page-body')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fa fa-boxes me-2"></i>Stock</h5>
                        @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'distributor']) || Auth::user()->hasPermissionToCategory('inventories', 'add'))
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

                        <div id="distributor_filter_container" class="d-none">
                            @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
                                <select id="distributor_filter" class="form-select form-select-sm border-primary"
                                    style="width: 200px; display: inline-block;">
                                    <option value="">All Distributors</option>
                                    @foreach($distributors as $d)
                                        <option value="{{ $d->id }}">{{ $d->user->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div class="table-responsive">
                            <table class="display table table-hover" id="inventories-table">
                                <thead>
                                    <tr>
                                        <th style="display:none;">Updated At</th>
                                        <th>No.</th>

                                        <th>Product Code</th>
                                        <th>Product Name</th>
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

    <!-- Batch Management Modal -->
    <div class="modal fade" id="batchManageModal" tabindex="-1" aria-labelledby="batchManageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="batchManageModalLabel"><i class="fa fa-layer-group me-2"></i>Batch Breakdown & Management</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="batchManageInfo" class="mb-4">
                        <h5 class="fw-bold product-name-display text-primary mb-1"></h5>
                        <p class="text-muted small product-code-display mb-3"></p>
                        
                        <div class="card bg-light border-0">
                            <div class="card-body p-3">
                                <div class="row g-2" id="batchProdDetailList">
                                    <!-- Populated dynamically -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="batch-manage-table">
                            <thead class="bg-light">
                                <tr>
                                    <th>Batch No</th>
                                    <th>Expiry Date</th>
                                    <th>Distributor</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="batch-manage-body">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                            <label for="create_variant" class="form-label fw-bold">Size / Variant</label>
                            <select name="variant" id="create_variant" class="form-select">
                                <option value="">-- Select Size --</option>
                                <option value="S">S (Small)</option>
                                <option value="M">M (Medium)</option>
                                <option value="L">L (Large)</option>
                                <option value="XL">XL (Extra Large)</option>
                                <option value="XXL">XXL</option>
                                <option value="XXXL">XXXL</option>
                            </select>
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
                            <div class="col-md-8">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Product Name</label>
                                    <input type="text" id="edit_product_name" class="form-control bg-light border-0 fw-bold" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Variant</label>
                                    <input type="text" id="edit_variant" class="form-control bg-light border-0 fw-bold text-primary" readonly placeholder="None">
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

                        <!-- Stock Operation Card -->
                        <div class="stock-operation-container mb-4">
                            <label class="form-label fw-bold small text-uppercase text-muted mb-2">Operation Type</label>
                            <div class="premium-segmented-control">
                                <div class="segmented-option">
                                    <input type="radio" name="operation" id="edit_op_set" value="set" checked>
                                    <label for="edit_op_set">
                                        <span class="dot"></span> Overwrite
                                    </label>
                                </div>
                                <div class="segmented-option">
                                    <input type="radio" name="operation" id="edit_op_add" value="add">
                                    <label for="edit_op_add">
                                        <span class="plus">+</span> Add
                                    </label>
                                </div>
                                <div class="segmented-option">
                                    <input type="radio" name="operation" id="edit_op_subtract" value="subtract">
                                    <label for="edit_op_subtract">
                                        <span class="minus">-</span> Reduce
                                    </label>
                                </div>
                                <div class="control-glider"></div>
                            </div>
                            <div class="mt-2 text-center">
                                <span id="edit_op_info" class="badge rounded-pill px-3 py-2 bg-soft-primary text-primary">
                                    <i class="fa fa-info-circle me-1"></i> This will OVERWRITE current stock.
                                </span>
                            </div>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-md-8">
                                <div class="form-group mb-0">
                                    <label class="form-label fw-bold small text-uppercase text-muted" id="edit_stock_label_text">Set Total Quantity</label>
                                    <input type="number" name="stock" id="edit_stock" class="form-control form-control-lg" required min="0" step="0.01">
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
    {{-- Redundant Adjustment Modal Removed --}}

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

            function openBatchManageModal(row) {
                const modal = $('#batchManageModal');
                modal.find('.product-name-display').text(row.product_name);
                modal.find('.product-code-display').text(row.distributor_product_code);
                
                // Variant Handling
                let variant = row.variant;
                if (!variant && row.product_details) {
                    // Try to extract from product name if empty
                    const match = row.product_name.match(/\[(.*?)\]/);
                    if (match) variant = match[1];
                }
                
                let variantHtml = variant ? `<span class="variant-badge ms-2"><i class="fa fa-tag me-1"></i> Variant: ${variant}</span>` : '';
                modal.find('.product-name-display').append(variantHtml);

                // Populate Product Details
                const product = row.product_details || {};
                let detailsHtml = '';
                const detailFields = [
                    { label: 'Generic', value: product.generic_name },
                    { label: 'Pack', value: product.pack },
                    { label: 'MRP', value: product.mrp },
                    { label: 'PTR', value: product.ptr },
                    { label: 'HSN', value: product.hsn_code },
                    { label: 'Units/Strip', value: product.units_per_strip },
                    { label: 'Strips/Box', value: product.strips_per_box },
                    { label: 'Boxes/Ctn', value: product.boxes_per_carton }
                ];
                
                const isNos = window.checkIsNos(row.product_name, product.pack, product.box_size);
                
                detailFields.forEach(f => {
                    // Hide packaging fields for Nos products as requested
                    if (isNos && (f.label === 'Units/Strip' || f.label === 'Strips/Box' || f.label === 'Boxes/Ctn')) {
                        return;
                    }
                    // Allow 0, "0.00", and non-empty strings
                    if (f.value !== undefined && f.value !== null && f.value !== '') {
                        detailsHtml += `<div class="col-md-3 col-6 mb-2">
                            <span class="text-muted smaller d-block" style="font-size: 0.65rem; text-transform: uppercase; font-weight: 600;">${f.label}</span>
                            <span class="fw-bold small text-dark">${f.value}</span>
                        </div>`;
                    }
                });
                $('#batchProdDetailList').html(detailsHtml || '<div class="col-12 text-muted small text-center">No additional product details</div>');

                const body = $('#batch-manage-body');
                body.empty();
                
                const productDetails = row.product_details || {};
                const unitsPerStrip = productDetails.units_per_strip || 1;
                const baseStr = isNos ? 'Nos' : 'Str';

                if (row.batches && row.batches.length > 0) {
                    row.batches.forEach(b => {
                        let displayQty = isNos ? Math.round(b.stock * unitsPerStrip) : b.stock;
                        let rowHtml = `<tr>
                            <td class="fw-bold">${b.batch_no}</td>
                            <td>${b.expiry_date}</td>
                            <td>${b.distributor_name || 'N/A'}</td>
                            <td class="fw-bold text-success">${displayQty} ${baseStr}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary single-batch-edit-btn" 
                                        data-batch='${JSON.stringify(b).replace(/"/g, '&quot;')}'
                                        data-parent='${JSON.stringify(row).replace(/"/g, '&quot;')}'>
                                    <i class="fa fa-edit"></i> Edit
                                </button>
                            </td>
                        </tr>`;
                        body.append(rowHtml);
                    });
                } else {
                    body.append('<tr><td colspan="5" class="text-center text-muted">No batch data available.</td></tr>');
                }
                
                modal.modal('show');
            }

            // Delegated click handler for viewing batches
            $('#inventories-table').on('click', '.view-batches-btn', function(e) {
                e.preventDefault();
                let rowData = table.row($(this).closest('tr')).data();
                if (rowData) {
                    openBatchManageModal(rowData);
                }
            });

            // Delegated click handler for editing single batch from modal
            $('#batch-manage-body').on('click', '.single-batch-edit-btn', function() {
                let batch = $(this).data('batch');
                let parentRow = $(this).data('parent');
                triggerSingleBatchEdit(batch, parentRow);
            });

            window.triggerSingleBatchEdit = function(batch, parentRow) {
                // Close management modal
                $('#batchManageModal').modal('hide');
                
                // Construct a synthetic row for the edit modal
                const syntheticRow = {
                    ...parentRow,
                    id: batch.id,
                    batch_no: batch.batch_no,
                    expiry_date: batch.expiry_date,
                    raw_expiry_date: batch.raw_expiry_date,
                    stock: batch.stock // Sub-batch stock
                };
                
                // Wait for modal fade out
                setTimeout(() => {
                    openEditModal(syntheticRow);
                }, 400);
            };

            function generateBatchTable(batches, productDetails, isNos) {
                if (!batches || batches.length === 0) return '<div class="text-muted">No batch data available.</div>';
                
                let boxSize = productDetails.strips_per_box || 0;
                let unitsPerStrip = productDetails.units_per_strip || 1;
                let baseStr = isNos ? 'Nos' : 'Str';

                let html = '<table class="batch-popover-table">';
                html += '<thead><tr><th>Batch</th><th>Expiry</th><th>Stock</th></tr></thead><tbody>';
                
                batches.forEach(b => {
                    let qty = isNos ? Math.round(b.stock * unitsPerStrip) : b.stock;
                    html += `<tr>
                        <td class="fw-bold">${b.batch_no}</td>
                        <td>${b.expiry_date}</td>
                        <td class="text-end fw-bold">${qty} ${baseStr}</td>
                    </tr>`;
                });
                
                html += '</tbody></table>';
                return html;
            }

            var table = $('#inventories-table').DataTable({
                processing: false, // Disabled to prevent stuck "pill" loader
                serverSide: true,
                ajax: {
                    url: "{{ route('inventories.index') }}",
                    type: 'GET',
                    data: function (d) {
                        d.distributor_id = $('#distributor_filter').val();
                    },
                    data: 'data',
                    error: function (xhr, error, thrown) {
                        console.error('Inventories AJAX error:', xhr.responseText);
                    }
                },
                language: {
                    emptyTable: `<div class="selection-prompt">
                                    <i class="fa fa-hand-pointer fa-3x text-primary mb-3"></i>
                                    <h5>Selection Required</h5>
                                    <p class="text-muted">Please select a distributor from the filter to view their inventory.</p>
                                 </div>`,
                    zeroRecords: `<div class="text-center p-4">
                                    <i class="fa fa-search fa-2x text-muted mb-2"></i>
                                    <p>No matching products found for this distributor.</p>
                                  </div>`
                },
                drawCallback: function (settings) {
                    // Initialize popovers after each draw
                    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
                    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                        return new bootstrap.Popover(popoverTriggerEl, {
                            sanitize: false // Allow table HTML in popover
                        })
                    });

                    // Close other popovers when one is opened
                    $(document).on('click', function (e) {
                         $('[data-bs-toggle="popover"]').each(function () {
                            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                                $(this).popover('hide');
                            }
                        });
                    });
                },
                dom: "<'row mb-3'<'col-sm-12'B>>" +
                    "<'row mb-3'<'col-md-6'l><'col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row mt-3'<'col-sm-12 col-md-5 d-flex justify-content-center justify-content-md-start align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-center justify-content-md-end align-items-center'p>>",
                initComplete: function () {
                    var $filter = $('#distributor_filter_container');
                    $filter.removeClass('d-none').addClass('ms-4');
                    $('.dataTables_length').addClass('d-flex align-items-center').append($filter);
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
                    data: 'distributor_product_code',
                    name: 'distributor_product_code'
                },
                {
                    data: 'product_name',
                    name: 'product_name',
                    render: function (data, type, row) {
                        if (!data) return '-';
                        let cleanName = data.replace(/\s*\([^)]*\/[^)]*\)/g, '').replace(/\s*\[[^\]]*\/[^\]]*\]/g, '').trim();
                        
                        return `
                            <div class="product-info-cell">
                                <a href="javascript:void(0)" class="fw-bold product-main-name view-batches-btn" 
                                   style="text-decoration: none; color: inherit; border-bottom: 1px dotted #ccc;"
                                   title="Click to view batches & details">
                                    ${cleanName}
                                </a>
                                <div class="text-muted small">${row.distributor_product_code || ''}</div>
                            </div>
                        `;
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

                        let boxSize = row.product_details.strips_per_box || 0;
                        let cartonSize = row.product_details.boxes_per_carton || 0;
                        let unitsPerStrip = row.product_details.units_per_strip || 1;

                        let pPack = row.product_details.pack ? row.product_details.pack.toLowerCase() : '';
                        let pName = row.product_name ? row.product_name.toLowerCase() : '';
                        let boxSizeStr = row.product_details.box_size || '';
                        let isNos = boxSizeStr === "" || pPack.includes('nos') || pPack.includes('count') || pPack.includes('pair');
                        
                        let baseStr = isNos ? 'Nos' : 'Str';
                        let totalQty = isNos ? Math.round(data * unitsPerStrip) : data;

                        let html = `<div class="breakdown-container view-batches-btn" style="cursor: pointer;">`;
                        
                        if (isNos) {
                            html += `<div class="breakdown-main fw-bold text-primary">${totalQty} ${baseStr} <i class="fa fa-external-link-alt ms-1 small"></i></div>`;
                        } else {
                            let cartons = 0;
                            let remaining = data;
                            if (cartonSize > 0 && boxSize > 0) {
                                let stripsPerCarton = boxSize * cartonSize;
                                cartons = Math.floor(data / stripsPerCarton);
                                remaining = data % stripsPerCarton;
                            }
                            let boxes = boxSize > 0 ? Math.floor(remaining / boxSize) : 0;
                            let strips = boxSize > 0 ? remaining % boxSize : remaining;

                            let parts = [];
                            if (cartons > 0) parts.push(`${cartons} Ctn`);
                            if (boxes > 0) parts.push(`${boxes} Box`);
                            if (strips > 0 || parts.length === 0) parts.push(`${strips} ${baseStr}`);
                            
                            html += `<div class="breakdown-main fw-bold text-primary">${parts.join(', ')} <i class="fa fa-external-link-alt ms-1 small"></i></div>`;
                        }

                        // Packaging info line
                        if (isNos) {
                            if (unitsPerStrip > 1 || boxSize > 1) {
                                html += `<div class="packaging-info text-muted small" style="font-size: 0.75rem;">`;
                                html += `(${unitsPerStrip} Nos/Box | ${boxSize} Box/Ctn)`;
                                html += `</div>`;
                            }
                        } else {
                            html += `<div class="packaging-info text-muted small" style="font-size: 0.75rem;">`;
                            html += `(${boxSize} Str/Box | ${cartonSize} Box/Ctn)`;
                            html += `</div>`;
                        }
                        html += `</div>`;
                        return html;
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function (id, type, row) {
                        let deleteUrl = "{{ route('inventories.destroy', ':id') }}".replace(':id', id);
                        let csrf = "{{ csrf_token() }}";
                        let rowData = JSON.stringify(row).replace(/"/g, '&quot;');

                        let btns = `<div class="action-buttons">`;
                        
                        // Edit button - Now opens Batch management modal
                        if (canEdit) {
                            btns += `<button type="button" class="btn btn-sm btn-teal view-batches-btn me-1 rounded-pill px-3" title="View & Manage Batches"><i class="fa fa-layer-group me-1"></i> Edit/Batches</button>`;
                        }

                        // Delete button
                        if (canDelete) {
                            btns += `<form id="delete-form-${id}" action="${deleteUrl}" method="POST" style="display:inline;">
                                        <input type="hidden" name="_token" value="${csrf}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="${id}" title="Remove from Stock"><i class="fa fa-trash"></i></button>
                                    </form>`;
                        }
                        
                        btns += `</div>`;
                        return btns;
                    }
                }
                ]
            });

            // Filter Change Handler
            $('#distributor_filter').on('change', function () {
                table.ajax.reload();
            });

            // Edit Handler logic variables
            let currentStripsPerBox = 1;
            let currentBoxesPerCarton = 1;
            let currentUnitsPerStrip = 1;

            function calculateEditTotal() {
                let qty = parseFloat($('#edit_stock').val()) || 0;
                let unit = $('#edit_unit').val();
                let op = $('input[name="operation"]:checked').val();

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

                // Show Variant separately
                let variant = data.variant;
                if (!variant && data.product_name) {
                    const match = data.product_name.match(/\[(.*?)\]/);
                    if (match) variant = match[1];
                }
                $('#edit_variant').val(variant || 'N/A');
                
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

                $('input[name="operation"][value="set"]').prop('checked', true).trigger('change');
                
                // Set stock value
                let displayStock = isNos ? Math.round(data.stock * currentUnitsPerStrip) : data.stock;
                $('#edit_stock').val(displayStock);
                
                calculateEditTotal();
                $('#editInventoryModal').modal('show');
            }

            $('#inventories-table').on('click', '.edit-btn', function () {
                var data = $(this).data('inventory');
                openEditModal(data);
            });

            window.updateOperationUI = function(op) {
                const info = $('#edit_op_info');
                const label = $('#edit_stock_label_text');
                
                if (op === 'set') {
                    info.html('<i class="fa fa-info-circle me-1"></i> This will <b>OVERWRITE</b> the current stock value.').removeClass('text-success text-danger').addClass('text-primary');
                    label.text('Final Total Stock');
                    $('#edit_conv_info').hide();
                } else if (op === 'add') {
                    info.html('<i class="fa fa-plus-circle me-1"></i> This will <b>ADD</b> to the existing stock.').removeClass('text-primary text-danger').addClass('text-success');
                    label.text('Quantity to Add');
                    $('#edit_conv_info').show();
                    $('#edit_stock').val('');
                } else if (op === 'subtract') {
                    info.html('<i class="fa fa-minus-circle me-1"></i> This will <b>REDUCE</b> the existing stock.').removeClass('text-primary text-success').addClass('text-danger');
                    label.text('Quantity to Reduce');
                    $('#edit_conv_info').show();
                    $('#edit_stock').val('');
                }
                calculateEditTotal();
            }

            $('input[name="operation"]').on('change', function() {
                updateOperationUI($(this).val());
            });

            $('#edit_stock, #edit_unit').on('input change', calculateEditTotal);

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