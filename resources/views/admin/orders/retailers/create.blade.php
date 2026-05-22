@extends('layouts.admin')

@section('page-body')
    <div class="container-fluid">
        <form id="createOrderForm" method="POST" action="{{ route('admin.retailer.store') }}">
            @csrf
            <input type="hidden" name="status" value="pending">

            @if(Auth::user()->retailer)
                <input type="hidden" name="retailer_id" id="retailer_id" value="{{ Auth::user()->retailer->id }}">
            @else
                {{-- Retailer Selection Header --}}
                <div class="card mb-4 shadow-sm border-0 builder-card overflow-hidden rounded-3">
                    <div class="card-body p-4 bg-light-soft">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <h6 class="mb-0 fw-bold text-dark text-uppercase small label-font"><i
                                        class="fa fa-user-circle me-2"></i>Select Retailer</h6>
                            </div>
                            <div class="col-md-10">
                                <select name="retailer_id" id="retailer_id" class="form-select select2" required>
                                    <option value="">Search by Shop Name or Contact...</option>
                                    @foreach($retailers as $r)
                                        <option value="{{ $r->id }}">{{ $r->user->name }} - {{ $r->user->contact_no }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                {{-- Left Side: Picker, Spotlight & Bundle Table --}}
                <div class="col-xl-9 col-lg-8">
                    {{-- 1. Input Section --}}
                    <div class="card shadow-sm border-0 mb-4 builder-main-card rounded-3">
                        <div class="card-body p-4">
                            <!-- AI Prescription Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div
                                        class="p-4 border rounded-4 bg-white d-flex flex-column flex-md-row align-items-center justify-content-between shadow-sm mb-0 border-primary border-opacity-10 gap-3">
                                        <div class="text-center text-md-start">
                                            <h5 class="mb-1 fw-bold text-dark">Have a handwritten prescription?</h5>
                                            <p class="text-muted small mb-0">Upload it and our AI will automatically
                                                identify medicines for you.</p>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div id="aiLoader" class="me-3 d-none">
                                                <div class="spinner-border spinner-border-sm text-primary me-2"
                                                    role="status"></div>
                                                <small class="text-primary fw-bold">AI Processing...</small>
                                            </div>
                                            <input type="file" id="prescriptionInput" class="d-none"
                                                accept=".jpg,.jpeg,.png,.pdf">
                                            <button type="button" class="btn btn-primary px-4 py-2 fw-bold shadow-sm"
                                                id="btnUploadPrescription"
                                                style="border-radius: 10px; background: var(--med-primary);">
                                                <i class="fa fa-upload me-2"></i> UPLOAD PRESCRIPTION
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- AI Results Interactive Container -->
                                    <div id="aiResultsContainer" class="mt-3 d-none border rounded-4 bg-light-soft p-3 shadow-sm">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold mb-0 text-primary"><i class="fa fa-magic me-2"></i>AI Detection Results</h6>
                                            <button type="button" class="btn btn-xs btn-outline-danger border-0 fw-bold px-3 py-1" 
                                                style="border-radius: 8px; background: rgba(220, 53, 69, 0.05);"
                                                onclick="$('#aiResultsContainer').addClass('d-none')">
                                                <i class="fa fa-times me-1"></i> Dismiss
                                            </button>
                                        </div>
                                        <div id="aiResultsList">
                                            <!-- Result rows injected here -->
                                        </div>
                                        <div id="unmatchedList" class="mt-2 small text-muted"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label fw-bold text-muted small text-uppercase mb-2">1. Select Product</label>
                                    <select id="productSelect" class="form-select select2">
                                        <option value="">Search Product...</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}">{{ $p->product_name }}{{ trim($p->pack) && $p->pack != '' ? " ($p->pack)" : "" }} - ₹{{ number_format($p->ptr, 2) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label fw-bold text-muted small text-uppercase mb-2">2. Select Distributor</label>
                                    <select id="distributorSelect" class="form-select select2" disabled>
                                        <option value="">Select Product First</option>
                                    </select>
                                </div>
                                <div class="col-lg-4 col-md-12" id="selectionDetails" style="display: none;">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-6">
                                            <label class="form-label fw-bold text-muted small text-uppercase mb-2">Qty</label>
                                            <div class="input-group">
                                                <input type="number" id="qtyInput" class="form-control fw-bold border-end-0" value="1" min="1" style="height: 48px;">
                                                <select class="form-select fw-bold bg-light" id="unitSelect" style="height: 48px; max-width: 90px; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                                    <option value="Strips">Str</option>
                                                    <option value="Box">Box</option>
                                                    <option value="Nos">Nos</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <button type="button" class="btn btn-primary w-100 fw-bold shadow-sm font-outfit rounded-3 py-2" id="btnAddItem" style="height: 48px; background: var(--med-primary);">
                                                <i class="fa fa-plus me-1"></i> ADD
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Variant Selection - Floating Bar Style --}}
                            <div id="variantWrapper" class="mt-3 p-3 border rounded-4 bg-white shadow-sm border-primary border-opacity-10" style="display: none;">
                                <div id="variantLevelsContainer" class="d-flex flex-wrap gap-4"></div>
                                <input type="hidden" id="variantValue" value="">
                            </div>
                        </div>
                    </div>

                    {{-- 2. Product Spotlight Card --}}
                    <div class="card mb-4 shadow-sm border-0 border-start border-primary border-4 spotlight-card rounded-3"
                        id="productDetailsCard" style="display: none;">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-12">
                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                                        <div class="mb-3 mb-md-0">
                                            <h2 id="previewName" class="fw-extrabold mb-1 text-dark label-font tracking-tight" style="font-size: 1.75rem;">Product Name</h2>
                                            <p class="text-primary fw-bold small mb-3 text-uppercase font-outfit letter-spacing-wider" id="previewGeneric">Generic Name</p>
                                            
                                            <div class="d-flex flex-wrap gap-3 mt-4" id="previewBadges">
                                                <div class="meta-capsule" id="previewCodeCapsule">
                                                    <i class="fa fa-tag text-primary"></i>
                                                    <span id="previewCodeSpan">-</span>
                                                </div>
                                                <div class="meta-capsule" id="previewHsnCapsule">
                                                    <i class="fa fa-file-invoice text-info"></i>
                                                    <span id="previewHsnSpan">-</span>
                                                </div>
                                                <div class="meta-capsule" id="previewPackCapsule">
                                                    <i class="fa fa-box text-warning"></i>
                                                    <span id="previewPackSpan">-</span>
                                                </div>
                                                <div class="meta-capsule bg-primary text-white border-0 shadow-sm" id="previewBoxCapsule" style="display:none;">
                                                    <i class="fa fa-layer-group text-white-50"></i>
                                                    <span id="previewBoxSpan">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end py-0 px-0" style="min-width: 150px;">
                                            <span class="text-primary fw-extrabold small text-uppercase d-block mb-2 font-outfit letter-spacing-wider" id="ptrLabel" style="opacity: 0.8;">PTR (Per Unit)</span>
                                            <span class="text-success mb-0 font-outfit fw-extrabold display-6" style="letter-spacing: -1px;">₹<span id="previewMrp">0.00</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Side: Recap & Submit --}}
                <div class="col-xl-3 col-lg-4">
                    <div class="sticky-top" style="top: 20px; z-index: 5;">
                        <div class="card shadow-lg border-0 summary-card rounded-3 overflow-hidden">
                            <div class="card-header bg-dark text-white py-3">
                                <h5 class="card-title mb-0 fw-bold"><i class="fa fa-receipt me-2"></i>Grand Total</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-4 text-center">
                                    <label class="text-muted small fw-bold text-uppercase d-block mb-2">Total Order Value (PTR)</label>
                                    <div class="d-flex align-items-center justify-content-center gap-3">
                                        <span id="grandTotal" class="h1 fw-bold text-primary mb-0 font-outfit" style="letter-spacing: -1px;">₹0.00</span>
                                    </div>
                                </div>
                                
                                <div class="p-3 bg-light-soft rounded-3 border border-light-dark mb-4 text-center">
                                    <small class="text-muted d-block line-height-sm">
                                        <i class="fa fa-info-circle text-warning me-1"></i> 
                                        GST & other charges will be calculated on the final invoice.
                                    </small>
                                </div>

                                <div class="mb-4">
                                    <label class="text-muted small fw-bold text-uppercase d-block mb-2">Delivery Notes (Optional)</label>
                                    <textarea name="delivery_notes" class="form-control" rows="2" placeholder="Any special instructions for delivery..."></textarea>
                                </div>

                                <button type="submit"
                                    class="btn btn-success btn-lg w-100 py-3 fw-bold shadow-sm font-outfit btn-confirm rounded-3 border-0 transition-all hover-shadow"
                                    style="background: linear-gradient(135deg, #28a745 0%, #218838 100%);"
                                    id="btnSubmitOrder" disabled>
                                    <i class="fa fa-check-circle me-2"></i> CONFIRM ORDER
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Full Width Bundle Table --}}
                <div class="col-xl-12 mt-4">
                    {{-- 3. Bundle Table --}}
                    <div class="card shadow-sm border-0 mb-4 overflow-hidden rounded-3">
                        <div
                            class="card-header bg-white dark-bg-transparent py-3 border-bottom border-light-dark d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 fw-bold text-dark label-font">Current Order Bundle</h5>
                            <span
                                class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill small border border-primary border-opacity-25 shadow-sm">
                                <i class="fa fa-shopping-basket me-2"></i><span id="itemCount">0</span> Items
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 custom-builder-table" id="orderTable">
                                    <thead class="bg-light dark-bg-dark">
                                        <tr>
                                            <th class="ps-4 text-uppercase small fw-bold text-muted py-3 sharp-th">#</th>
                                            <th class="text-uppercase small fw-bold text-muted py-3 sharp-th">Product
                                                Description</th>
                                            <th class="text-uppercase small fw-bold text-muted py-3 sharp-th">Source</th>
                                            <th width="170"
                                                class="text-uppercase small fw-bold text-muted py-3 text-center sharp-th">
                                                Qty Builder</th>
                                            <th class="text-uppercase small fw-bold text-muted py-3 sharp-th">PTR</th>
                                            <th class="text-uppercase small fw-bold text-muted py-3 sharp-th">Unit Price
                                            </th>
                                            <th width="80"
                                                class="text-center text-uppercase small fw-bold text-muted py-3 sharp-th">
                                                Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        <tr id="emptyRow">
                                            <td colspan="7" class="text-center py-5">
                                                <div class="py-4 opacity-50">
                                                    <i class="fa fa-cart-arrow-down fa-4x mb-3 text-muted"></i>
                                                    <h5 class="text-muted fw-bold">Cart is Empty</h5>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap');

        .font-outfit {
            font-family: 'Outfit', sans-serif !important;
        }

        .label-font {
            font-family: 'Outfit', sans-serif;
            letter-spacing: -0.2px;
        }

        /* Sharp TH Only */
        .sharp-th {
            border-radius: 0 !important;
        }

        .bg-light-soft {
            background-color: #f8fafc !important;
        }

        .hover-shadow {
            transition: all 0.3s ease;
        }

        .hover-shadow:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
        }

        .spotlight-image-wrapper {
            background-color: white !important;
            transition: transform 0.4s ease;
        }

        .spotlight-card:hover .spotlight-image-wrapper {
            transform: scale(1.02);
        }

        .builder-main-card {
            border-left: 5px solid var(--bs-primary) !important;
        }

        /* === Dark Mode Adjustments === */
        body.dark-only .text-dark {
            color: #f1f5f9 !important;
        }

        body.dark-only .bg-white {
            background-color: #1a2234 !important;
        }

        body.dark-only .bg-light-soft {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }

        body.dark-only .dark-bg-dark {
            background-color: #121826 !important;
        }

        body.dark-only .dark-bg-transparent {
            background-color: transparent !important;
        }

        body.dark-only .border-light-dark {
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        body.dark-only .dark-border-soft {
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        body.dark-only .form-control,
        body.dark-only .form-select {
            background-color: #1a2234;
            border-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        body.dark-only .select2-container--default .select2-selection--single {
            background-color: #1a2234;
            border-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px !important;
        }

        body.dark-only .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #fff;
        }

        .select2-container .select2-selection--single {
            height: 48px;
            border-radius: 8px !important;
            padding-top: 10px;
            border-color: #e2e8f0;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px;
        }

        @keyframes slideUpFade {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .new-row {
            animation: slideUpFade 0.4s ease-out forwards;
            background-color: rgba(25, 135, 84, 0.04) !important;
        }

        .remove-row {
            opacity: 0;
            transform: scale(0.95);
            transition: all 0.4s ease;
        }

        /* Fixed Remove Button */
        .remove-btn {
            border-radius: 4px !important;
            transition: all 0.2s ease;
            display: flex !important;
            align-items: center;
            justify-content: center;
            gap: 4px;
            font-weight: bold;
        }

        .remove-btn:hover {
            transform: translateY(-1px);
            opacity: 0.9;
        }

        /* Summary Card Enhancements */
        .bg-light-soft {
            background-color: #f8fafc;
        }
        .bg-soft-success {
            background-color: #ecfdf5;
        }
        .text-success {
            color: #059669 !important;
        }
        .line-height-sm {
            line-height: 1.4;
        }
        .transition-all {
            transition: all 0.3s ease;
        }
        .hover-shadow:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            transform: translateY(-2px);
        }
        .meta-capsule {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 6px 16px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            font-weight: 700;
            color: #111827;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.2s ease;
        }
        .meta-capsule:hover {
            background: #fff;
            border-color: var(--bs-primary);
            color: var(--bs-primary);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        body.dark-only .meta-capsule, .dark-only .meta-capsule {
            background: rgba(30, 41, 59, 0.7) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #f1f5f9 !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
        }

        body.dark-only .meta-capsule i {
            color: var(--med-primary) !important;
        }
        .meta-capsule i {
            font-size: 1rem;
            opacity: 1;
        }
    </style>

    <script>
        $(document).ready(function () {
            let addedItems = {};
            let lastAiResponse = null; 
            var currentProductDetails = null;

            function updateDistributorStock(prodId, retailerId, side = null, size = null, preFetchedDistributors = null) {
                let $distSelect = $('#distributorSelect');
                let currentVal = $distSelect.val(); // Keep selection if possible
                
                const renderDistributors = (distributors) => {
                    $distSelect.empty().append('<option value="">Select Distributor (Top matches first)</option>');
                    
                    let qty = parseFloat($('#qtyInput').val()) || 1;
                    let unit = $('#unitSelect').val() || 'Strips';
                    let mul = 1;
                    if (currentProductDetails) {
                        let stripsPerBox = parseInt(currentProductDetails.strips_per_box || 1);
                        let boxesPerCarton = parseInt(currentProductDetails.boxes_per_carton || 1);
                        if (unit === 'Box') {
                            mul = stripsPerBox;
                        } else if (unit === 'Carton') {
                            mul = stripsPerBox * boxesPerCarton;
                        } else if (unit === 'Nos') {
                            mul = 1 / (max(1, parseInt(currentProductDetails.units_per_strip || 1)));
                        }
                    }
                    let requiredStock = qty * mul;
                    
                    if (distributors && distributors.length > 0) {
                        let addedAny = false;
                        distributors.forEach(d => {
                            let stock = d.pivot ? parseFloat(d.pivot.stock) : 0;
                            
                            // Deduct quantity already in the bundle (cart)
                            let variantStr = (side || size) ? [side, size].filter(Boolean).join(' - ') : null;
                            let key = prodId + '-' + d.id + (variantStr ? '-' + variantStr : '');
                            if (addedItems[key]) {
                                stock -= (addedItems[key].qty * addedItems[key].multiplier);
                            }
                            
                            if (stock > 0 && stock >= requiredStock) {
                                $distSelect.append(`<option value="${d.id}" data-stock-raw="${stock}" ${currentVal == d.id ? 'selected' : ''}>${d.shop_name || d.name}</option>`);
                                addedAny = true;
                            }
                        });
                        if (!addedAny) {
                            $distSelect.append('<option value="" disabled>No stockists with sufficient stock found in your area</option>');
                        }
                    } else {
                        $distSelect.append('<option value="" disabled>No stockists found for this variant in your area</option>');
                    }
                    
                    $distSelect.prop('disabled', false).trigger('change.select2');
                };

                // Use pre-fetched data if available to avoid redundant AJAX (prevents double refresh)
                if (preFetchedDistributors) {
                    renderDistributors(preFetchedDistributors);
                    return;
                }

                let qty = parseFloat($('#qtyInput').val()) || 1;
                let unit = $('#unitSelect').val() || 'Strips';

                $distSelect.prop('disabled', true);
                $.ajax({
                    url: "{{ route('admin.retailer.product-details', ':id') }}".replace(':id', prodId),
                    type: 'GET',
                    data: { 
                        retailer_id: retailerId,
                        side: side,
                        size: size,
                        quantity: qty,
                        unit: unit
                    },
                    success: function (res) {
                        renderDistributors(res.distributors);
                    },
                    error: function() {
                        $distSelect.prop('disabled', false);
                    }
                });
            }

            // Shared Javascript helper for Nos max limit
            function max(a, b) {
                return a > b ? a : b;
            }

            // Real-time stock filter when quantity or unit changes
            $('#qtyInput, #unitSelect').on('input change', function() {
                let prodId = $('#productSelect').val();
                let retailerId = $('#retailer_id').val();
                if (prodId && retailerId) {
                    let side = $('.variant-btn.active[data-attr="Side"]').data('value') || null;
                    let size = $('.variant-btn.active[data-attr="Size"]').data('value') || null;
                    updateDistributorStock(prodId, retailerId, side, size);
                }
            });
            const isValid = (val, type) => {
                if (!val || val === 'null' || val === null) return false;
                let s = val.toString().toLowerCase().trim();
                if (s === '' || s === 'n/a' || s === '---') return false;
                if (type === 'generic' && (s === 'generic name n/a' || s === 'generic n/a')) return false;
                if (type === 'pack' && s === 'pack n/a') return false;
                return true;
            };

            $('.select2').select2({ placeholder: "Search...", allowClear: true });

            $('#distributorSelect').select2({
                placeholder: "Pick Distributor...",
                templateResult: formatDistributor,
                templateSelection: formatDistributor,
                escapeMarkup: function (m) { return m; }
            });

            function formatDistributor(opt) {
                if (!opt.id) return opt.text;
                let el = $(opt.element);
                let stockRaw = el.data('stock-raw');
                let distance = el.data('distance');
                if (stockRaw !== undefined && stockRaw !== null) {
                    let stock = parseFloat(stockRaw);
                    let stockBadge = stock > 0 ? 'bg-success' : 'bg-danger';
                    let distText = distance ? `<small class="text-muted"><i class="fa fa-map-marker-alt"></i> ${distance}km</small>` : '';
                    let displayStock = Math.round(stock);
                    return $(`<div class="d-flex justify-content-between align-items-center"><span>${opt.text}</span><div class="ms-2">${distText} <span class="badge ${stockBadge} ms-1">${displayStock}</span></div></div>`);
                }
                return opt.text;
            }

            // var currentProductDetails = null; // Moved up

            $('#productSelect').on('select2:select', function (e) {
                let prodId = $(this).val();
                let retailerId = $('#retailer_id').val();
                
                if (!retailerId) {
                    showToast('error', 'Please select a retailer first');
                    $(this).val(null).trigger('change');
                    return;
                }

                // Reset variants when product changes
                $('#variantValue').val('');
                $('#sideValue').val('');
                $('#sizeValue').val('');
                $('#variantWrapper').hide();
                $('#variantLevelsContainer').empty();

                // Set loading state without triggering redundant change events
                $('#distributorSelect').prop('disabled', true).empty().append('<option value="">Searching Stockists...</option>');
                $('#selectionDetails').hide();
                $('#productDetailsCard').hide();

                $.ajax({
                    url: "{{ route('admin.retailer.product-details', ':id') }}".replace(':id', prodId),
                    type: 'GET',
                    data: { retailer_id: retailerId },
                    success: function (res) {
                        let p = res.product;
                        currentProductDetails = p;
                        // Pass pre-fetched distributors to avoid second AJAX and double refresh
                        updateDistributorStock(prodId, retailerId, null, null, res.distributors);
                        
                        let pName = (p.product_name || '').toLowerCase();
                        let dynamicVariants = [];
                        let match = pName.match(/\(([^)]+)\)/g);
                        if (match) {
                            let lastMatch = match[match.length - 1].replace('(', '').replace(')', '');
                            if (lastMatch.includes('/')) {
                                dynamicVariants = lastMatch.split('/').map(s => s.trim().toUpperCase());
                            }
                        }

                        let hasVariants = p.has_variants || dynamicVariants.length > 0 || (p.variant_options && Object.keys(p.variant_options).length > 0);
                        if (hasVariants) {
                            let $container = $('#variantLevelsContainer');
                            $container.empty();
                            $('#variantValue').val('');
                            $('#variantWrapper').show();
                            
                            if (p.variant_options && Object.keys(p.variant_options).length > 0) {
                                let levelIdx = 0;
                                Object.keys(p.variant_options).forEach(attrName => {
                                    let vals = p.variant_options[attrName];
                                    $container.append(`<div class="variant-level" id="levelContainer_${levelIdx}" style="${levelIdx > 0 ? 'display:none;' : ''}" data-attr-name="${attrName}">
                                        <label class="form-label fw-bold text-muted small text-uppercase mb-1" style="font-size: 0.7rem;">${attrName}</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            ${vals.map(v => `<button type="button" class="btn btn-xs btn-outline-primary variant-btn px-2 py-1 fw-bold" style="font-size: 0.8rem; border-radius: 6px;" data-level="${levelIdx}" data-attr="${attrName}" data-value="${v}">${v}</button>`).join('')}
                                        </div>
                                    </div>`);
                                    levelIdx++;
                                });
                            } else {
                                let variantsToUse = dynamicVariants.length > 0 ? dynamicVariants : ['S', 'M', 'L', 'XL'];
                                $container.append(`<div class="variant-level mb-3" data-attr-name="Size">
                                    <label class="form-label fw-bold text-muted small text-uppercase mb-2">Select Size / Variant</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        ${variantsToUse.map(v => `<button type="button" class="btn btn-outline-primary variant-btn px-3 py-2 fw-bold" data-level="0" data-attr="Size" data-value="${v}">${v}</button>`).join('')}
                                    </div>
                                </div>`);
                            }
                        } else {
                            $('#variantWrapper').hide();
                            $('#variantValue').val('');
                        }

                        // Unit Logic: If has code, it is a single item (No.)
                        let $unitSelect = $('#unitSelect');
                        $unitSelect.empty();
                        let pPack = (p.pack || '').toLowerCase();
                        let hasCode = p.product_code && p.product_code !== '---' && p.product_code.trim() !== '';
                        let isCount = hasCode || p.box_size === "" || pPack.includes('nos') || pPack.includes('count');

                        if (isCount) {
                            $unitSelect.append('<option value="Nos">No.</option>');
                            $('#ptrLabel').text(`PTR (Per No.)`);
                        } else {
                            $unitSelect.append('<option value="Strips">Strips</option><option value="Box">Box</option>');
                            $('#ptrLabel').text(`PTR (Per Strip)`);
                        }

                        $('#previewName').html(p.product_name);
                        $('#previewMrp').text(parseFloat(p.ptr || 0).toFixed(2));


                        if (isValid(p.pack, 'pack')) {
                            $('#previewPackSpan').text(p.pack);
                            $('#previewPackCapsule').show();
                        } else {
                            $('#previewPackCapsule').hide();
                        }
                        
                        $('#previewMrp').text(parseFloat(p.ptr || 0).toFixed(2));
                        
                        if (isValid(p.generic_name, 'generic')) {
                            $('#previewGeneric').text(p.generic_name).show();
                        } else {
                            $('#previewGeneric').hide();
                        }

                        if (isValid(p.product_code)) {
                            $('#previewCodeSpan').text(p.product_code);
                            $('#previewCodeCapsule').show();
                        } else {
                            $('#previewCodeCapsule').hide();
                        }

                        if (isValid(p.hsn_code)) {
                            $('#previewHsnSpan').text(p.hsn_code);
                            $('#previewHsnCapsule').show();
                        } else {
                            $('#previewHsnCapsule').hide();
                        }

                        // Dynamic Packaging Info
                        let hasStrips = isValid(p.strip_size) || (p.units_per_strip > 1);
                        let hasBoxes = isValid(p.box_size) || (p.strips_per_box > 1);

                        // Only show breakdown for tablet products
                        if (!isCount && (hasStrips || hasBoxes)) {
                            let packInfoText = `${p.units_per_strip || 1} Tab/Str | ${p.strips_per_box || 1} Str/Box`;
                            $('#previewBoxSpan').text(packInfoText);
                            $('#previewBoxCapsule').show();
                        } else {
                            $('#previewBoxCapsule').hide();
                        }

                        if (hasVariants) {
                            $('#selectionDetails').show();
                            $('#variantWrapper').show();
                        } else {
                            $('#variantWrapper').hide();
                            $('#selectionDetails').show(); // Still show for qty/unit/add button
                            $('#distributorSelect').select2('open');
                        }

                        $('#distributorSelect').prop('disabled', false);
                        $('#productDetailsCard').fadeIn(300);
                    }
                });
            });

            $(document).on('click', '.variant-btn', function() {
                let $btn = $(this);
                let levelIdx = parseInt($btn.data('level'));
                let levelVal = $btn.data('value');

                // Toggle active class in current level
                $btn.closest('.variant-level').find('.variant-btn').removeClass('active');
                $btn.addClass('active');

                // Hide and reset subsequent levels
                let $allLevels = $('.variant-level');
                $allLevels.each(function(idx) {
                    if (idx > levelIdx) {
                        $(this).hide().find('.variant-btn').removeClass('active');
                    }
                });

                // Show next level if exists
                let $nextLevel = $(`#levelContainer_${levelIdx + 1}`);
                if ($nextLevel.length > 0) {
                    $nextLevel.fadeIn(200);
                    $('#variantValue').val(''); // Incomplete selection
                } else {
                    // Final level reached - Assemble full variant string from all active buttons
                    let finalVariant = $('.variant-level:visible .variant-btn.active').map(function() {
                        return $(this).data('value');
                    }).get().join(' - '); 

                    $('#variantValue').val(finalVariant);
                    
                    // Re-fetch stock for specific variant
                    let side = $('.variant-btn.active[data-attr="Side"]').data('value') || null;
                    let size = $('.variant-btn.active[data-attr="Size"]').data('value') || null;
                    
                    let prodId = $('#productSelect').val();
                    let retailerId = $('#retailer_id').val();
                    
                    updateDistributorStock(prodId, retailerId, side, size);
                    
                    // Delay open to let stock update (optional, but smoother)
                    setTimeout(() => {
                        $('#distributorSelect').select2('open');
                    }, 500);
                }
            });

            $('#btnAddItem').click(function () {
                let prodId = $('#productSelect').val();
                let distId = $('#distributorSelect').val();
                let qty = parseFloat($('#qtyInput').val());
                let unit = $('#unitSelect').val();
                let variant = $('#variantWrapper').is(':visible') ? $('#variantValue').val() : null;

                if ($('#variantWrapper').is(':visible') && !variant) {
                    return showToast('warning', 'Please select a size/variant first');
                }
                let distOption = $('#distributorSelect option:selected');
                let maxStockRaw = parseInt(distOption.data('stock-raw') || 0);

                if (!prodId || !distId || qty <= 0) return showToast('info', 'Complete selections first');

                let distName = distOption.text();
                let key = prodId + '-' + distId + (variant ? '-' + variant : '');
                
                let stripsPerBox = parseInt(currentProductDetails.strips_per_box || 1);
                let boxesPerCarton = parseInt(currentProductDetails.boxes_per_carton || 1);
                
                let mul = 1;
                if (unit === 'Box') mul = stripsPerBox;
                else if (unit === 'Carton') mul = stripsPerBox * boxesPerCarton;
                else if (unit === 'Nos') mul = 1 / (parseInt(currentProductDetails.units_per_strip || 1));

                let totalProposedStrips = qty * mul;
                let existingQtyStrips = addedItems[key] ? (addedItems[key].qty * addedItems[key].multiplier) : 0;

                if ((existingQtyStrips + totalProposedStrips) > maxStockRaw) {
                    return showToast('error', `You cannot select a quantity greater than the available stock in the selected distributor.`);
                }

                let prodFullName = currentProductDetails.product_name;
                if (isValid(currentProductDetails.pack, 'pack')) {
                    prodFullName += ` (${currentProductDetails.pack})`;
                }

                if (addedItems[key]) {
                    addedItems[key].qty += qty;
                } else {
                    let side = $('.variant-btn.active[data-attr="Side"]').data('value') || null;
                    let size = $('.variant-btn.active[data-attr="Size"]').data('value') || null;

                    addedItems[key] = {
                        id: prodId, distId: distId, distName: distName,
                        name: prodFullName,
                        variant: variant,
                        side: side,
                        size: size,
                        price: parseFloat(currentProductDetails.ptr),
                        qty: qty, unit: unit, multiplier: mul,
                        strips_per_box: stripsPerBox,
                        boxes_per_carton: boxesPerCarton,
                        units_per_strip: currentProductDetails.units_per_strip,
                        has_variants: currentProductDetails.has_variants,
                        is_count: $('#unitSelect').val() === 'Nos',
                        maxStock: maxStockRaw
                    };
                }
                renderTable(key);
                $('#productSelect').val(null).trigger('change');
                $('#distributorSelect').empty().append('<option value="">Select Product First</option>').trigger('change');
                $('#productDetailsCard').fadeOut(300);
                $('#qtyInput').val(1);
                
                // Reset variants
                $('#variantWrapper').hide();
                $('.variant-btn').removeClass('active');
                $('#variantValue').val('');
                
                currentProductDetails = null;
                showToast('success', 'Product added to bundle');
            });

            // --- Prescription OCR Logic ---
            $(document).on('click', '#btnUploadPrescription', function () {
                console.log('Upload Button Clicked');
                $('#prescriptionInput').click();
            });

            $(document).on('change', '#prescriptionInput', function () {
                let file = this.files[0];
                console.log('File Input Changed:', file ? file.name : 'No file');
                if (!file) return;

                let retailerId = $('#retailer_id').val();
                console.log('Retailer ID during upload:', retailerId);

                if (!retailerId) {
                    if (typeof showToast === 'function') {
                        showToast('warning', 'Please select a retailer first to process the prescription.');
                    } else {
                        alert('Please select a retailer first');
                    }

                    if ($('#retailer_id').hasClass('select2-hidden-accessible')) {
                        $('#retailer_id').select2('open');
                    } else {
                        $('#retailer_id').focus();
                    }
                    $(this).val('');
                    return;
                }

                let formData = new FormData();
                formData.append('prescription', file);
                formData.append('retailer_id', retailerId);
                formData.append('_token', '{{ csrf_token() }}');

                $('#btnUploadPrescription').prop('disabled', true);
                $('#aiLoader').removeClass('d-none');

                console.log('Initiating AI AJAX call...');
                $.ajax({
                    url: "{{ route('ai.extract-prescription') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        console.log('AI Success Full Response:', JSON.stringify(res, null, 2));
                        lastAiResponse = res;
                        
                        $('#aiResultsContainer').removeClass('d-none');
                        let resultsList = $('#aiResultsList');
                        resultsList.empty();
                        $('#unmatchedList').empty();

                        if (res.success && res.matched_items && res.matched_items.length > 0) {
                            res.matched_items.forEach(function (item, idx) {
                                let p = item.product;
                                let hasStock = item.has_stock;
                                let d = (item.distributors && item.distributors.length > 0) ? item.distributors[0] : (item.distributor || null);
                                
                                let distName = d ? (d.shop_name || d.name || 'N/A') : 'No Distributor with Stock';
                                let pPack = (p.pack && p.pack.trim() !== '') ? ` (${p.pack})` : '';
                                let options = `<option value="${p.id}" selected>${p.product_name}${pPack} (₹${p.ptr})</option>`;
                                
                                let stockNum = d ? (parseFloat(d.stock) || 0) : 0;
                                let distHtml = '';
                                
                                if (hasStock) {
                                    distHtml = `
                                        <select class="form-select select2-ai ai-dist-select" data-pid="${p.id}">
                                            <option value="${d.id}" data-stock="${stockNum}" selected>${distName} - Stock: ${Math.round(stockNum)}</option>
                                        </select>`;
                                } else {
                                    distHtml = `
                                        <div class="p-2 bg-soft-danger text-danger rounded-3 border border-danger border-opacity-10 small fw-bold">
                                            <i class="fa fa-exclamation-circle me-1"></i> Not in stock with any distributor
                                        </div>
                                        <select class="form-select select2-ai ai-dist-select d-none" data-pid="${p.id}">
                                            <option value="" selected>No Stock</option>
                                        </select>`;
                                }

                                let variantHtml = '';
                                if (p.variant_options && typeof p.variant_options === 'object') {
                                    Object.keys(p.variant_options).forEach(attrName => {
                                        let vals = p.variant_options[attrName];
                                        variantHtml += `
                                            <div class="col-12 ai-variant-level mt-2" data-attr="${attrName}">
                                                <label class="text-muted small fw-bold text-uppercase mb-1 d-block">${attrName}</label>
                                                <div class="d-flex flex-wrap gap-2">
                                                    ${vals.map(v => `<button type="button" class="btn btn-sm btn-outline-primary ai-variant-btn px-2 py-1 fw-bold" data-value="${v}">${v}</button>`).join('')}
                                                </div>
                                            </div>`;
                                    });
                                }

                                let rowHtml = `
                                    <div class="ai-result-row p-4 bg-white rounded-4 shadow-sm mb-3 border ${hasStock ? 'border-light-dark' : 'border-danger border-opacity-25'} overflow-hidden transition-all hover-shadow" data-pid="${p.id}" style="${hasStock ? '' : 'background-color: #fffafb !important;'}">
                                        <div class="row g-3">
                                            <!-- Main Details -->
                                            <div class="col-lg-4 col-md-6">
                                                <label class="text-muted small fw-bold text-uppercase mb-1 d-block"><i class="fa fa-flask me-1 text-primary"></i> Rx Molecule</label>
                                                <div class="p-2 bg-light-soft rounded-3 border">
                                                    <span class="fw-bold text-dark d-block text-truncate" title="${item.original_name}">${item.original_name}</span>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-md-6">
                                                <label class="text-muted small fw-bold text-uppercase mb-1 d-block"><i class="fa fa-box me-1 text-primary"></i> Suggested Product</label>
                                                <select class="form-select select2-ai ai-prod-select">
                                                    ${options}
                                                </select>
                                            </div>
                                            <div class="col-lg-4 col-md-6">
                                                <label class="text-muted small fw-bold text-uppercase mb-1 d-block"><i class="fa fa-truck me-1 text-primary"></i> Availability</label>
                                                ${distHtml}
                                            </div>

                                            ${variantHtml}
                                            
                                            <!-- Action & Qty -->
                                            <div class="col-lg-2 col-md-4 col-6">
                                                <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Quantity</label>
                                                <input type="number" class="form-control ai-qty fw-bold" value="${item.quantity}" min="1" style="height: 42px;" ${hasStock ? '' : 'disabled'}>
                                            </div>
                                            <div class="col-lg-2 col-md-4 col-6">
                                                <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Unit</label>
                                                <select class="form-select ai-unit fw-medium" style="height: 42px;" ${hasStock ? '' : 'disabled'}>
                                                    <option value="Strips" ${item.unit === 'Strips' ? 'selected' : ''}>Strips</option>
                                                    <option value="Box" ${item.unit === 'Box' ? 'selected' : ''}>Box</option>
                                                    <option value="Nos" ${item.unit === 'Nos' ? 'selected' : ''}>Nos</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-8 col-md-4 col-12 d-flex align-items-end">
                                                <button type="button" class="btn ${hasStock ? 'btn-primary' : 'btn-secondary'} ai-add-btn w-100 fw-bold shadow-sm" 
                                                    style="height: 42px; border-radius: 10px; ${hasStock ? 'background: var(--med-primary);' : 'cursor: not-allowed;'}"
                                                    data-idx="${idx}" ${hasStock ? '' : 'disabled'}>
                                                    <i class="fa ${hasStock ? 'fa-plus-circle' : 'fa-times-circle'} me-1"></i> 
                                                    ${hasStock ? 'ADD TO CART' : 'OUT OF STOCK'}
                                                </button>
                                            </div>
                                        </div>
                                    </div>`;
                                resultsList.append(rowHtml);
                            });

                            $('.select2-ai').select2({ width: '100%', dropdownParent: $('#aiResultsContainer') });

                            if (res.unmatched_items && res.unmatched_items.length > 0) {
                                let names = res.unmatched_items.map(i => `<span class="badge bg-soft-danger text-danger me-2" style="font-size: 0.9rem; border: 1px solid rgba(220,53,69,0.2);">${i.name}</span>`).join('');
                                $('#unmatchedList').html(`<hr class="my-3 text-muted opacity-25"><div class="text-muted fw-bold mb-2" style="font-size: 0.95rem;"><i class="fa fa-info-circle me-1 text-danger"></i> The following items from the prescription were NOT found in our catalog:</div><div class="d-flex flex-wrap">${names}</div>`);
                            }
                            
                            showToast('success', `Prescription processed successfully.`);
                        } else {
                            resultsList.html('<div class="text-center py-4 bg-light rounded-4 border"><i class="fa fa-search-minus fa-2x text-muted mb-2"></i><div class="text-muted fw-bold">No matches found for this prescription.</div><div class="text-muted small">Please check the items below or add products manually.</div></div>');
                            if (res.unmatched_items && res.unmatched_items.length > 0) {
                                let names = res.unmatched_items.map(i => `<span class="badge bg-soft-danger text-danger me-2" style="font-size: 0.9rem; border: 1px solid rgba(220,53,69,0.2);">${i.name}</span>`).join('');
                                $('#unmatchedList').html(`<hr class="my-3 text-muted opacity-25"><div class="text-muted fw-bold mb-2" style="font-size: 0.95rem;"><i class="fa fa-info-circle me-1 text-danger"></i> Items NOT found in catalog:</div><div class="d-flex flex-wrap">${names}</div>`);
                            }
                            showToast('warning', 'No products found in stock for the items in this prescription.');
                        }
                    },
                    error: function (xhr) {
                        console.error('AI AJAX Error:', xhr);
                        let msg = 'AI Extraction failed';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        if (typeof showToast === 'function') {
                            showToast('error', msg);
                        } else {
                            alert(msg);
                        }
                    },
                    complete: function () {
                        $('#btnUploadPrescription').prop('disabled', false);
                        $('#aiLoader').addClass('d-none');
                        $('#prescriptionInput').val('');
                    }
                });
            });

            // Auto-trigger prescription upload if redirected from dashboard
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('action') === 'upload_prescription') {
                setTimeout(function () {
                    $('#btnUploadPrescription').click();
                }, 500);
            }

            function renderTable(lastAddedKey) {
                let tbody = $('#orderTable tbody');
                tbody.empty();
                let total = 0; let index = 1; let hasItems = false;

                $.each(addedItems, function (key, item) {
                    hasItems = true;
                    let lineTotal = item.qty * item.multiplier * item.price;
                    total += lineTotal;
                    let rowClass = (key === lastAddedKey) ? 'new-row' : '';

                    tbody.append(`
                                                <tr class="${rowClass}">
                                                    <td class="ps-4 text-muted fw-bold small">${index++}</td>
                                                    <td>
                                                        <div class="fw-bold text-dark font-outfit" style="max-width:250px; white-space:normal; line-height:1.2;">
                                                            ${item.name} ${item.variant ? `<span class="badge bg-primary ms-1">${item.variant}</span>` : ''}
                                                        </div>
                                                        <input type="hidden" name="items[${key}][product_id]" value="${item.id}">
                                                        ${item.side ? `<input type="hidden" name="items[${key}][side]" value="${item.side}">` : ''}
                                                        ${item.size ? `<input type="hidden" name="items[${key}][size]" value="${item.size}">` : ''}
                                                        ${item.variant ? `<input type="hidden" name="items[${key}][variant]" value="${item.variant}">` : ''}
                                                        <input type="hidden" name="items[${key}][distributor_id]" value="${item.distId}">
                                                    </td>
                                                    <td class="small fw-medium text-muted">${item.distName}</td>
                                                    <td class="text-center">
                                                        <div class="py-2">
                                                            <span class="fw-extrabold text-dark font-outfit h6 mb-0">${item.qty}</span>
                                                            <span class="text-muted small ms-1">${item.unit}</span>
                                                        </div>
                                                        <input type="hidden" name="items[${key}][quantity]" value="${item.qty}">
                                                        <input type="hidden" name="items[${key}][unit]" value="${item.unit}">
                                                    </td>
                                                    <td class="fw-medium">₹${item.price.toFixed(2)}</td>
                                                    <td class="fw-bold text-primary font-outfit">₹${lineTotal.toFixed(2)}</td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-danger btn-sm remove-btn mx-auto" 
                                                            data-key="${key}" style="width: 50px; height: 32px;">
                                                            X <i class="fa fa-trash-alt" style="font-size: 11px;"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            `);
                });

                if (!hasItems) tbody.append($('#emptyRow').clone().show());
                $('#btnSubmitOrder').prop('disabled', !hasItems);
                $('#grandTotal').text('₹' + total.toFixed(2));
                $('#itemCount').text(Object.keys(addedItems).length);
            }

            $(document).on('change', '.qty-change, .unit-change', function () {
                let key = $(this).data('key');
                let item = addedItems[key];
                let oldQty = item.qty;
                let oldUnit = item.unit;
                let oldMul = item.multiplier;

                if ($(this).hasClass('qty-change')) {
                    item.qty = parseFloat($(this).val()) || 1;
                } else {
                    let val = $(this).val();
                    let mul = 1;
                    if (val === 'Box') mul = parseInt(item.strips_per_box || 1);
                    if (val === 'Carton') mul = parseInt(item.strips_per_box || 1) * parseInt(item.boxes_per_carton || 1);
                    item.unit = val;
                    item.multiplier = mul;
                }

                // Stock Check (in strips)
                if ((item.qty * item.multiplier) > item.maxStock) {
                    showToast('error', `Insufficient stock. Please select another distributor.`);
                    item.qty = oldQty;
                    item.unit = oldUnit;
                    item.multiplier = oldMul;
                }

                renderTable();
            });

            $(document).on('click', '.remove-btn', function () {
                let key = $(this).data('key');
                let row = $(this).closest('tr');
                row.addClass('remove-row');
                setTimeout(() => { delete addedItems[key]; renderTable(); }, 400);
            });

            $('#createOrderForm').submit(function (e) {
                e.preventDefault();
                let btn = $('#btnSubmitOrder');
                if (btn.prop('disabled')) return;
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i> processing...');
                $.ajax({
                    url: $(this).attr('action'), type: 'POST', data: $(this).serialize(),
                    success: function (res) {
                        if (res.success) {
                            showToast('success', 'Retail Order Placed');
                            window.location.href = "{{ route('admin.retailer.index') }}";
                        } else {
                            showToast('error', res.error || 'Update Failed');
                            btn.prop('disabled', false).html('<i class="fa fa-check-circle me-2"></i> CONFIRM ORDER');
                        }
                    },
                    error: function (xhr) {
                        let msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Server error occurred during submission.';
                        showToast('error', msg);
                        btn.prop('disabled', false).html('<i class="fa fa-check-circle me-2"></i> CONFIRM ORDER');
                    }
                });
            });

            // Toggle AI variants
            $(document).on('click', '.ai-variant-btn', function() {
                $(this).closest('.d-flex').find('.ai-variant-btn').removeClass('active btn-primary text-white').addClass('btn-outline-primary');
                $(this).addClass('active btn-primary text-white').removeClass('btn-outline-primary');
            });

            // Handle AI Result Add Button
            $(document).on('click', '.ai-add-btn', function() {
                let btn = $(this);
                let idx = btn.data('idx');
                let row = btn.closest('.ai-result-row');
                
                if (!lastAiResponse || !lastAiResponse.matched_items[idx]) return;
                
                let item = lastAiResponse.matched_items[idx];
                let p = item.product;
                let distSelect = row.find('.ai-dist-select');
                let distId = distSelect.val();
                
                if (!distId) {
                    showToast('error', 'Please select a distributor with stock.');
                    return;
                }

                let distName = distSelect.find('option:selected').text().split(' - ')[0];
                let maxStockRaw = parseInt(distSelect.find('option:selected').data('stock'));
                
                let qty = parseInt(row.find('.ai-qty').val()) || 1;
                let unit = row.find('.ai-unit').val();
                
                let stripsPerBox = parseInt(p.strips_per_box || 1);
                let boxesPerCarton = parseInt(p.boxes_per_carton || 1);
                
                let mul = 1;
                if (unit === 'Box') mul = stripsPerBox;
                if (unit === 'Carton') mul = stripsPerBox * boxesPerCarton;

                // Capture Variants from buttons
                let variants = [];
                row.find('.ai-variant-level').each(function() {
                    let activeBtn = $(this).find('.ai-variant-btn.active');
                    if (activeBtn.length > 0) {
                        variants.push(activeBtn.data('value'));
                    }
                });
                
                // If product has variants but none selected
                if (p.variant_options && Object.keys(p.variant_options).length > 0 && variants.length < Object.keys(p.variant_options).length) {
                    showToast('warning', 'Please select all variants (Side/Size) for ' + p.product_name);
                    return;
                }

                let variantStr = variants.length > 0 ? variants.join(' - ') : null;
                let requestedStrips = qty * mul;
                let key = p.id + '-' + distId + (variantStr ? '-' + variantStr : '');

                // Existing strips check
                let existingStrips = addedItems[key] ? (addedItems[key].qty * addedItems[key].multiplier) : 0;
                
                if ((existingStrips + requestedStrips) > maxStockRaw) {
                    showToast('error', `Insufficient stock for ${p.product_name}. Max available is ${maxStockRaw}.`);
                    return;
                }

                if (addedItems[key]) {
                    addedItems[key].qty += qty;
                } else {
                    // Try to split variantStr if it contains ' - ' (common pattern in this UI)
                    let side = null;
                    let size = null;
                    
                    if (variantStr) {
                        if (variantStr.includes(' - ')) {
                            let parts = variantStr.split(' - ');
                            // Heuristic: if first part is Left/Right, it's side
                            if (['LEFT', 'RIGHT'].includes(parts[0].toUpperCase())) {
                                side = parts[0];
                                size = parts[1];
                            } else {
                                size = parts[0];
                                side = parts[1];
                            }
                        } else {
                            // If only one part, check if it's side
                            if (['LEFT', 'RIGHT'].includes(variantStr.toUpperCase())) {
                                side = variantStr;
                            } else {
                                size = variantStr;
                            }
                        }
                    }

                    addedItems[key] = {
                        id: p.id,
                        distId: distId,
                        distName: distName,
                        name: p.product_name,
                        variant: variantStr,
                        side: side,
                        size: size,
                        price: parseFloat(p.ptr),
                        qty: qty,
                        unit: unit,
                        multiplier: mul,
                        strips_per_box: stripsPerBox,
                        boxes_per_carton: boxesPerCarton,
                        units_per_strip: p.units_per_strip,
                        has_variants: p.has_variants,
                        is_count: unit === 'Nos',
                        maxStock: maxStockRaw
                    };
                }

                renderTable(key);
                btn.removeClass('btn-primary').addClass('btn-success').html('<i class="fa fa-check"></i> Added').prop('disabled', true);
                showToast('success', `${p.product_name} added to order.`);
            });

            $(document).on('click', '.size-btn', function () {
                $('.size-btn').removeClass('btn-primary text-white').addClass('btn-outline-primary');
                $(this).removeClass('btn-outline-primary').addClass('btn-primary text-white');
                let variant = $(this).data('size');
                $('#variantValue').val(variant);
                
                // Trigger distributor load for this variant
                let prodId = $('#productSelect').val();
                let retailerId = $('#retailer_id').val();
                if (prodId && retailerId) {
                    updateDistributorStock(prodId, retailerId, null, variant);
                }
            });
        });
    </script>
@endpush