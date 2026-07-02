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

                            <div class="row g-3 mb-3">
                                <div class="col-lg-12 col-md-12">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label fw-bold text-muted small text-uppercase mb-0">1. Select Product</label>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="small text-muted font-outfit" style="font-size: 0.8rem;">Filter Brand:</span>
                                            <select id="brandSelect" class="form-select form-select-sm border-0 bg-transparent text-primary fw-bold py-0 ps-1 pe-4" style="width: auto; height: auto !important; min-height: unset; font-size: 0.8rem !important; box-shadow: none !important; cursor: pointer; display: inline-block;">
                                                <option value="">All Brands</option>
                                                @foreach($brands as $brand)
                                                    <option value="{{ $brand }}">{{ $brand }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <select id="productSelect" class="form-select select2">
                                        <option value="">Search Product...</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" data-brand="{{ $p->brand }}">{{ $p->product_name }}{{ trim($p->pack) && $p->pack != '' ? " ($p->pack)" : "" }} - ₹{{ number_format($p->ptr, 2) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Variant Selection - Floating Bar Style --}}
                            <div id="variantWrapper" class="mb-3 p-3 border rounded-4 bg-white shadow-sm border-primary border-opacity-10" style="display: none;">
                                <div id="variantLevelsContainer" class="d-flex flex-wrap gap-4"></div>
                                <input type="hidden" id="variantValue" value="">
                            </div>

                            <div class="row g-3">
                                <div class="col-lg-5 col-md-6" id="selectionDetails" style="display: none;">
                                    <label class="form-label fw-bold text-muted small text-uppercase mb-2">2. Enter Quantity</label>
                                    <div class="input-group">
                                        <input type="number" id="qtyInput" class="form-control fw-bold border-end-0" min="1" style="height: 38px;">
                                        <select class="form-select fw-bold bg-light" id="unitSelect" style="height: 38px; max-width: 100px; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                            <option value="Strips">Str</option>
                                            <option value="Box">Box</option>
                                            <option value="Nos">Nos</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-7 col-md-6" id="distributorContainer" style="display: none;">
                                    <label class="form-label fw-bold text-muted small text-uppercase mb-2">3. Select Distributor</label>
                                    <div class="d-flex gap-2 align-items-center">
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <select id="distributorSelect" class="form-select select2" disabled>
                                                <option value="">Select Product First</option>
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-primary fw-bold shadow-sm font-outfit rounded-3 px-4" id="btnAddItem" style="height: 38px; background: var(--med-primary); opacity: 0.5;" disabled>
                                            <i class="fa fa-plus me-1"></i> ADD
                                        </button>
                                    </div>
                                </div>
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

                    {{-- 3. Bundle Table (Moved into Left Column) --}}
                    <div class="card shadow-sm border-0 mb-4 overflow-hidden rounded-3 mt-4">
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
                                    class="btn btn-success btn-lg w-100 py-3 fw-bold shadow-sm font-outfit btn-confirm rounded-3 border-0 transition-all hover-shadow d-flex align-items-center justify-content-center"
                                    style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); white-space: nowrap;"
                                    id="btnSubmitOrder" disabled>
                                    <i class="fa fa-check-circle me-2"></i> CONFIRM ORDER
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Free Variant Modal -->
    <div class="modal fade" id="freeVariantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold text-dark font-outfit"><i class="fa fa-gift text-primary me-2"></i>Select Free Variants</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-white" id="freeVariantModalBody">
                    <!-- Dynamic variants builder will be injected here -->
                </div>
                <div class="modal-footer border-top-0 bg-light">
                    <button type="button" class="btn btn-primary fw-bold px-4 rounded-pill" data-bs-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
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
            height: 38px;
            border-radius: 8px !important;
            padding-top: 5px;
            border-color: #e2e8f0;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
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

        /* Hide number arrows for custom inputs */
        .free-variant-input::-webkit-outer-spin-button,
        .free-variant-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .free-variant-input {
            -moz-appearance: textfield;
        }
    </style>

    <script>
        $(document).ready(function () {
            // Force reset on page load to prevent cached selections
            setTimeout(() => {
                $('#productSelect').val('').trigger('change');
                $('#qtyInput').val('');
            }, 100);

            let addedItems = {};
            let lastAiResponse = null; 
            var currentProductDetails = null;

            function updateDistributorStock(prodId, retailerId, side = null, size = null, preFetchedDistributors = null) {
                let $distSelect = $('#distributorSelect');
                let currentVal = $distSelect.val(); // Keep selection if possible
                
                const renderDistributors = (distributors) => {
                    $distSelect.empty();
                    
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
                        let validCount = 0;
                        let lastValidId = null;
                        let optionsHtml = '';

                        distributors.forEach(d => {
                            let stock = d.pivot ? parseFloat(d.pivot.stock) : 0;
                            
                            // Deduct quantity already in the bundle (cart)
                            let key = prodId + '-' + d.id;
                            if (addedItems[key]) {
                                stock -= (addedItems[key].qty * addedItems[key].multiplier);
                            }
                            
                            if (stock > 0 && stock >= requiredStock) {
                                optionsHtml += `<option value="${d.id}" data-stock-raw="${stock}" ${currentVal == d.id ? 'selected' : ''}>${d.shop_name || d.name}</option>`;
                                addedAny = true;
                                validCount++;
                                lastValidId = d.id;
                            }
                        });
                        
                        if (!addedAny) {
                            $distSelect.append('<option value="empty" disabled selected>⚠️ Out of stock for this quantity</option>');
                        } else if (validCount === 1 && !currentVal) {
                            $distSelect.append('<option value="empty" disabled>Pick Distributor (1 Available)...</option>');
                            $distSelect.append(optionsHtml);
                            $distSelect.val(lastValidId); // Auto-select if only 1 distributor exists
                        } else {
                            $distSelect.append(`<option value="empty" disabled selected>Pick Distributor (${validCount} Available)...</option>`);
                            $distSelect.append(optionsHtml);
                        }
                    } else {
                        $distSelect.append('<option value="empty" disabled selected>🚫 No distributor available for this product</option>');
                    }
                    
                    $distSelect.prop('disabled', false).trigger('change.select2');
                    
                    // Auto-open dropdown if there are multiple options, but ONLY if the container is currently visible 
                    // (prevents JS errors when variants hide the container)
                    if ($('#distributorContainer').is(':visible')) {
                        if (validCount > 1 || (!currentVal && validCount > 0)) {
                            setTimeout(() => {
                                $distSelect.select2('open');
                            }, 100);
                        }
                    }
                    checkAddButtonState();
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

            // Smart Add Button State Manager
            function checkAddButtonState() {
                let prodId = $('#productSelect').val();
                let distId = $('#distributorSelect').val();
                if (distId === 'empty') distId = ''; // Treat custom placeholder as empty

                let qty = parseFloat($('#qtyInput').val());
                let hasVariantSelect = $('#variantWrapper').is(':visible');
                let variantSelected = hasVariantSelect ? $('#variantValue').val() !== '' : true;

                if (prodId && distId && qty > 0 && variantSelected) {
                    $('#btnAddItem').prop('disabled', false).css('opacity', '1');
                } else {
                    $('#btnAddItem').prop('disabled', true).css('opacity', '0.5');
                }
            }

            $('#productSelect, #distributorSelect, #qtyInput, #unitSelect').on('change input', checkAddButtonState);
            $(document).on('click', '.variant-btn', checkAddButtonState);

            // Strictly restrict quantity input to positive integers only during typing/pasting
            $('#qtyInput').on('keypress', function(e) {
                if (e.which < 48 || e.which > 57) {
                    e.preventDefault(); // Block -, ., e, etc.
                }
            }).on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, ''); // Strip non-digits from pastes
            });

            // Enforce strictly positive values on blur for better UX
            $('#qtyInput').on('blur', function() {
                let val = $(this).val();
                if (val !== '' && parseInt(val) < 1) {
                    $(this).val(1).trigger('change');
                }
            });

            // Real-time stock filter with Debounce & Loading state
            let qtyDebounceTimer;
            let lastFetchKey = null;

            function triggerStockCheck() {
                let prodId = $('#productSelect').val();
                let retailerId = $('#retailer_id').val();
                let qty = parseFloat($('#qtyInput').val());
                let unit = $('#unitSelect').val();
                let side = $('.variant-btn.active[data-attr="Side"]').data('value') || null;
                let size = $('.variant-btn.active[data-attr="Size"]').data('value') || null;
                
                let currentKey = `${prodId}-${retailerId}-${qty}-${unit}-${side}-${size}`;
                
                if (prodId && retailerId && qty > 0) {
                    if (lastFetchKey === currentKey) return; // Prevent duplicate trigger
                    lastFetchKey = currentKey;

                    $('#distributorContainer').fadeIn(200);
                    // Immediately show loading state to indicate refresh based on new qty
                    $('#distributorSelect').prop('disabled', true).empty().append('<option value="empty" disabled selected>Recalculating Stock...</option>').trigger('change.select2');
                    checkAddButtonState();

                    clearTimeout(qtyDebounceTimer);
                    qtyDebounceTimer = setTimeout(() => {
                        updateDistributorStock(prodId, retailerId, side, size);
                    }, 400); // Wait 400ms after user stops typing
                } else {
                    lastFetchKey = null;
                    $('#distributorContainer').fadeOut(200);
                    $('#btnAddItem').prop('disabled', true).css('opacity', '0.5');
                }
            }

            // Only listen to 'input' for typing, NOT 'change' (which fires on blur and causes the double-load bug)
            $('#qtyInput').on('input', triggerStockCheck);
            $('#unitSelect').on('change', triggerStockCheck);
            const isValid = (val, type) => {
                if (!val || val === 'null' || val === null) return false;
                let s = val.toString().toLowerCase().trim();
                if (s === '' || s === 'n/a' || s === '---') return false;
                if (type === 'generic' && (s === 'generic name n/a' || s === 'generic n/a')) return false;
                if (type === 'pack' && s === 'pack n/a') return false;
                return true;
            };

            $('.select2').select2({ placeholder: "Search...", allowClear: true });

            $('#retailer_id').on('change', function() {
                // Clear active product selection and hide details/variants/distributors
                $('#productSelect').val(null).trigger('change');
                $('#productDetailsCard').fadeOut(200);
                $('#selectionDetails').hide();
                $('#distributorContainer').hide();
                $('#variantWrapper').hide();
                $('#variantLevelsContainer').empty();
                $('#qtyInput').val('');
                lastFetchKey = null;
            });

            $('#brandSelect').on('change', function() {
                let brand = $(this).val();
                $('#productDetailsCard').fadeOut(200);
                $('#selectionDetails').hide();
                $('#distributorContainer').hide();
                $('#variantWrapper').hide();
                $('#variantLevelsContainer').empty();
                
                $.ajax({
                    url: "{{ route('admin.retailer-orders.get-products') }}",
                    method: 'GET',
                    data: { brand: brand },
                    success: function(res) {
                        let options = '<option value="">Search Product...</option>';
                        res.forEach(function(p) {
                            let packSuffix = (p.pack && p.pack.trim() !== '') ? ' (' + p.pack + ')' : '';
                            let price = parseFloat(p.ptr).toFixed(2);
                            options += `<option value="${p.id}" data-brand="${p.brand || ''}">${p.product_name}${packSuffix} - ₹${price}</option>`;
                        });
                        $('#productSelect').html(options).val(null).trigger('change');
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        if (typeof showToast === 'function') {
                            showToast('error', 'Failed to fetch products for the selected brand');
                        }
                    }
                });
            });

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
                let distText = distance ? `<small class="text-muted"><i class="fa fa-map-marker-alt"></i> ${distance}km</small>` : '';

                @if(Auth::user()->hasRole('retailer'))
                    return $(`<div class="d-flex justify-content-between align-items-center"><span>${opt.text}</span><div class="ms-2">${distText}</div></div>`);
                @else
                    if (stockRaw !== undefined && stockRaw !== null) {
                        let stock = parseFloat(stockRaw);
                        let stockBadge = stock > 0 ? 'bg-success' : 'bg-danger';
                        let displayStock = Math.round(stock);
                        return $(`<div class="d-flex justify-content-between align-items-center"><span>${opt.text}</span><div class="ms-2">${distText} <span class="badge ${stockBadge} ms-1">${displayStock}</span></div></div>`);
                    }
                @endif
                return opt.text;
            }

            const eligibleFreeProducts = @json($eligibleFreeProducts);

            // var currentProductDetails = null; // Moved up

            $('#productSelect').on('select2:select', function (e) {
                let prodId = $(this).val();
                let retailerId = $('#retailer_id').val();
                
                if (!retailerId) {
                    showToast('error', 'Please select a retailer first');
                    $(this).val(null).trigger('change');
                    return;
                }

                // Reset variants and quantity when product changes
                $('#variantValue').val('');
                $('#sideValue').val('');
                $('#sizeValue').val('');
                $('#variantWrapper').hide();
                $('#variantLevelsContainer').empty();
                $('#qtyInput').val(''); // Ensure quantity starts blank
                lastFetchKey = null; // Reset fetch cache

                // Set loading state without triggering redundant change events
                $('#distributorSelect').prop('disabled', true).empty().append('<option value="">Searching Distributors...</option>');
                $('#selectionDetails').hide();
                $('#distributorContainer').hide();
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
                            $unitSelect.empty().append('<option value="Nos">No.</option>').hide();
                            if ($('#unitText').length === 0) {
                                $unitSelect.after('<span class="input-group-text fw-bold bg-light text-muted px-3" id="unitText" style="height: 38px;">No.</span>');
                            }
                            $('#ptrLabel').text(`PTR (Per No.)`);
                        } else {
                            $unitSelect.empty().append('<option value="Strips">Strips</option><option value="Box">Box</option>').show();
                            $('#unitText').remove();
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
                            $('#variantWrapper').show();
                            // Keep quantity and distributor hidden until variant is selected
                        } else {
                            $('#variantWrapper').hide();
                            $('#selectionDetails').show(); // Show for qty/unit
                            $('#qtyInput').focus();
                            $('#distributorContainer').hide(); // Hidden until qty entered
                        }

                        // We let the retailer enter quantity first, as distributor stock filters by quantity.
                        // $('#distributorSelect').prop('disabled', false); 
                        $('#productDetailsCard').fadeIn(300);
                    }
                });
            });

            $(document).on('click', '.variant-btn', function() {
                let $btn = $(this);
                let levelIdx = parseInt($btn.data('level'));
                let levelVal = $btn.data('value');

                // Clear cache on variant selection changes so it's forced to recheck
                lastFetchKey = null;

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
                    $('#selectionDetails').hide();
                    $('#distributorContainer').hide();
                } else {
                    // Final level reached - Assemble full variant string from all active buttons
                    let finalVariant = $('.variant-level:visible .variant-btn.active').map(function() {
                        return $(this).data('value');
                    }).get().join(' - '); 

                    $('#variantValue').val(finalVariant);
                    
                    // Show quantity details
                    $('#selectionDetails').fadeIn(200);
                    
                    let qty = parseFloat($('#qtyInput').val());
                    if (qty > 0) {
                        triggerStockCheck();
                    } else {
                        $('#distributorContainer').hide();
                        $('#qtyInput').focus();
                    }
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
                let key = prodId + '-' + distId;
                
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
                let side = $('.variant-btn.active[data-attr="Side"]').data('value') || null;
                let size = $('.variant-btn.active[data-attr="Size"]').data('value') || null;

                if (addedItems[key]) {
                    let existingVarIndex = addedItems[key].variants.findIndex(v => v.side === side && v.size === size && v.variant === variant);
                    if (existingVarIndex !== -1) {
                        addedItems[key].variants[existingVarIndex].qty += qty;
                    } else {
                        addedItems[key].variants.push({ side: side, size: size, variant: variant, qty: qty });
                    }
                    addedItems[key].qty += qty;
                    addedItems[key].unit = unit; // update to latest unit
                } else {
                    addedItems[key] = {
                        id: prodId, distId: distId, distName: distName,
                        name: prodFullName,
                        variants: [{ side: side, size: size, variant: variant, qty: qty }],
                        price: parseFloat(currentProductDetails.ptr),
                        qty: qty, unit: unit, multiplier: mul,
                        brand: currentProductDetails.brand,
                        free_qty_buy: currentProductDetails.free_qty_buy,
                        strips_per_box: stripsPerBox,
                        boxes_per_carton: boxesPerCarton,
                        units_per_strip: currentProductDetails.units_per_strip,
                        pack: currentProductDetails.pack,
                        has_variants: currentProductDetails.has_variants,
                        is_count: $('#unitSelect').val() === 'Nos',
                        maxStock: maxStockRaw
                    };
                }
                renderTable(key);
                $('#productSelect').val(null).trigger('change');
                $('#distributorSelect').empty().append('<option value="">Select Product First</option>').trigger('change');
                $('#productDetailsCard').fadeOut(300);
                $('#qtyInput').val('');
                
                // Reset variants
                $('#variantWrapper').hide();
                $('#variantLevelsContainer').empty();
                $('.variant-btn').removeClass('active');
                $('#variantValue').val('');
                
                currentProductDetails = null;
                showToast('success', 'Product added to bundle');
                
                // Focus Trap: Automatically reopen product search for rapid ordering
                setTimeout(() => {
                    $('#productSelect').select2('open');
                }, 400);
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
                                                            ${item.name} 
                                                        </div>
                                                        <div class="mt-2">
                                                            ${item.variants.map((v, vIdx) => {
                                                                let parts = [];
                                                                if (v.side) parts.push(v.side);
                                                                if (v.size) parts.push(v.size);
                                                                if (v.variant && !v.side && !v.size) parts.push(v.variant);
                                                                let vLabel = parts.length > 0 ? parts.join('/') : 'Reg';
                                                                return `
                                                                <div class="d-inline-flex align-items-center gap-1 bg-light border rounded-pill px-2 py-1 shadow-sm mb-1 me-2" style="border-color: rgba(0,0,0,0.08) !important;">
                                                                    <span class="text-primary fw-bold" style="font-size: 0.7rem;">${vLabel}</span>
                                                                    <div class="bg-secondary opacity-25 mx-1" style="width: 1px; height: 10px;"></div>
                                                                    <span class="text-dark fw-bold me-1" style="font-size: 0.7rem;">${v.qty}</span>
                                                                    <div class="remove-variant-btn d-flex align-items-center justify-content-center bg-danger rounded-circle shadow-sm" data-key="${key}" data-vidx="${vIdx}" style="width: 14px; height: 14px; cursor: pointer; opacity: 0.85; transition: opacity 0.2s;">
                                                                        <i class="fa fa-times text-white" style="font-size: 0.45rem;"></i>
                                                                    </div>
                                                                </div>`;
                                                            }).join('')}
                                                        </div>
                                                        <div class="small text-muted mt-1 font-outfit">
                                                            ${item.brand ? `<span class="me-2"><i class="fa fa-tag me-1 text-secondary"></i>${item.brand}</span>` : ''}
                                                            ${item.pack ? `<span class="me-2"><i class="fa fa-box me-1 text-warning"></i>${item.pack}</span>` : ''}
                                                            ${!item.is_count && item.units_per_strip ? `<span class="me-2"><i class="fa fa-pills me-1 text-primary"></i>${item.units_per_strip} Tab/Str</span>` : ''}
                                                            ${!item.is_count && item.strips_per_box ? `<span class="me-2"><i class="fa fa-layer-group me-1 text-info"></i>${item.strips_per_box} Str/Box</span>` : ''}
                                                        </div>
                                                    </td>
                                                    <td class="small fw-medium text-muted">${item.distName}</td>
                                                    <td class="text-center">
                                                        <div class="py-2">
                                                            <span class="fw-extrabold text-dark font-outfit h6 mb-0">${item.qty}</span>
                                                            <span class="text-muted small ms-1">${item.unit}</span>
                                                            ${(item.brand === 'Atomeds' || item.brand === 'Atomets' || item.brand === 'Sudhneelgiri') ? 
                                                                `<div class="text-success small fw-bold mt-1"><i class="fa fa-gift"></i> + Auto Free</div>` 
                                                            : ''}
                                                            ${(() => {
                                                                if (!(item.free_qty_buy > 0 && item.qty >= item.free_qty_buy)) return '';
                                                                
                                                                let getQty = 1;
                                                                let fpInfo = eligibleFreeProducts.find(p => p.id == item.id);
                                                                if (fpInfo && fpInfo.free_qty_get) getQty = fpInfo.free_qty_get;
                                                                let freeAmt = Math.floor(item.qty / item.free_qty_buy) * getQty;
                                                                if (freeAmt <= 0) return '';
                                                                
                                                                let totalSelected = 0;
                                                                let summaries = [];
                                                                if (item.free_selections) {
                                                                    Object.keys(item.free_selections).forEach(attr => {
                                                                        let attrGroup = [];
                                                                        Object.entries(item.free_selections[attr]).forEach(([v, q]) => {
                                                                            if (q > 0) {
                                                                                totalSelected += q;
                                                                                attrGroup.push(`${q}x${v}`);
                                                                            }
                                                                        });
                                                                        if (attrGroup.length > 0) {
                                                                            summaries.push(attr.toUpperCase() + ': ' + attrGroup.join(', '));
                                                                        }
                                                                    });
                                                                }
                                                                
                                                                let isComplete = totalSelected >= freeAmt;
                                                                let btnClass = isComplete ? 'btn-outline-success' : 'btn-outline-primary';
                                                                let iconHtml = isComplete ? '<i class="fa fa-check-circle me-1"></i>' : '<i class="fa fa-gift me-1"></i>';
                                                                
                                                                return `
                                                                    <div class="mt-2 text-center">
                                                                        <span class="badge bg-success text-white px-2 py-1 shadow-sm mb-2 d-inline-block" style="font-size: 0.75rem; letter-spacing: 0.3px; border-radius: 6px;">
                                                                            <i class="fa fa-gift me-1"></i>+ ${freeAmt} FREE
                                                                        </span><br>
                                                                        <button type="button" class="btn btn-sm ${btnClass} fw-bold open-free-variant-modal mb-1 px-3 shadow-sm font-outfit" data-key="${key}" style="border-radius: 8px;">
                                                                            ${iconHtml} Click to Select
                                                                        </button>
                                                                        ${summaries.length > 0 ? `<div class="small fw-bold mt-1" style="color: #6c757d; font-size: 0.7rem; line-height: 1.2;">${summaries.join('<br>')}</div>` : ''}
                                                                    </div>
                                                                `;
                                                            })()}
                                                        </div>
                                                        ${(() => {
                                                            let inputsHtml = '';
                                                            let freeAttached = false;
                                                            let getQty = 1;
                                                            let fpInfo = eligibleFreeProducts.find(p => p.id == item.id);
                                                            if (fpInfo && fpInfo.free_qty_get) getQty = fpInfo.free_qty_get;
                                                            let freeAmt = (item.free_qty_buy > 0 && item.qty >= item.free_qty_buy) ? Math.floor(item.qty / item.free_qty_buy) * getQty : 0;
                                                            
                                                            item.variants.forEach((v, vIdx) => {
                                                                let subKey = key + '_' + vIdx;
                                                                inputsHtml += `
                                                                    <input type="hidden" name="items[${subKey}][product_id]" value="${item.id}">
                                                                    <input type="hidden" name="items[${subKey}][distributor_id]" value="${item.distId}">
                                                                    ${v.side ? `<input type="hidden" name="items[${subKey}][side]" value="${v.side}">` : ''}
                                                                    ${v.size ? `<input type="hidden" name="items[${subKey}][size]" value="${v.size}">` : ''}
                                                                    ${v.variant ? `<input type="hidden" name="items[${subKey}][variant]" value="${v.variant}">` : ''}
                                                                    <input type="hidden" name="items[${subKey}][quantity]" value="${v.qty}">
                                                                    <input type="hidden" name="items[${subKey}][unit]" value="${item.unit}">
                                                                `;
                                                                if (!freeAttached && freeAmt > 0) {
                                                                    inputsHtml += `<input type="hidden" name="items[${subKey}][free_product_id]" value="${item.id}">`;
                                                                    inputsHtml += `<input type="hidden" name="items[${subKey}][free_quantity]" value="${freeAmt}">`;
                                                                    let fSideStr = [];
                                                                    if (item.free_selections && item.free_selections['side']) {
                                                                        Object.entries(item.free_selections['side']).forEach(([v, q]) => {
                                                                            if (q > 0) fSideStr.push(`${q}x${v}`);
                                                                        });
                                                                    }
                                                                    let fSizeStr = [];
                                                                    if (item.free_selections && item.free_selections['size']) {
                                                                        Object.entries(item.free_selections['size']).forEach(([v, q]) => {
                                                                            if (q > 0) fSizeStr.push(`${q}x${v}`);
                                                                        });
                                                                    }
                                                                    let finalSide = fSideStr.join(', ');
                                                                    let finalSize = fSizeStr.join(', ');
                                                                    
                                                                    if (finalSide) inputsHtml += `<input type="hidden" name="items[${subKey}][free_side]" value="${finalSide}">`;
                                                                    if (finalSize) inputsHtml += `<input type="hidden" name="items[${subKey}][free_size]" value="${finalSize}">`;
                                                                    freeAttached = true;
                                                                }
                                                            });
                                                            return inputsHtml;
                                                        })()}
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

            $(document).on('change', '.free-product-select', function() {
                let key = $(this).data('key');
                let val = $(this).val();
                let item = addedItems[key];
                item.free_product_id = val;
                item.free_side = null;
                item.free_size = null;
                renderTable();
            });
            
            // --- New Modal Logic ---
            let currentFreeVariantKey = null;
            let currentFreeVariantAvailable = null;

            function renderFreeVariantModal(key, availableVariantsData = null) {
                if (availableVariantsData !== null) {
                    currentFreeVariantAvailable = availableVariantsData;
                }
                let availableData = currentFreeVariantAvailable;
                
                let availableSides = new Set();
                let availableSizes = new Set();
                if (availableData) {
                    availableData.forEach(v => {
                        if (v.side) availableSides.add(v.side.toUpperCase());
                        if (v.size) availableSizes.add(v.size.toUpperCase());
                    });
                }

                let item = addedItems[key];
                if (!item) return;

                let getQty = 1;
                let fpInfo = eligibleFreeProducts.find(p => p.id == item.id);
                if (fpInfo && fpInfo.free_qty_get) getQty = fpInfo.free_qty_get;
                let freeAmt = Math.floor(item.qty / item.free_qty_buy) * getQty;

                let variantsHtml = '';
                let fp = eligibleFreeProducts.find(p => p.id == item.id);
                if (fp) {
                    let pName = (fp.product_name || '').toLowerCase();
                    let dynamicVariants = [];
                    let match = pName.match(/\(([^)]+)\)/g);
                    if (match) {
                        let lastMatch = match[match.length - 1].replace('(', '').replace(')', '');
                        if (lastMatch.includes('/')) {
                            dynamicVariants = lastMatch.split('/').map(s => s.trim().toUpperCase());
                        }
                    }
                    let hasV = fp.has_variants || dynamicVariants.length > 0 || (fp.variant_options && Object.keys(fp.variant_options).length > 0);
                    if (hasV) {
                        variantsHtml += `<div class="free-variants-container" id="free_variants_${key}">`;
                        
                        let allocated = 0;
                        if (item.free_selections) {
                            Object.values(item.free_selections).forEach(attrObj => {
                                Object.values(attrObj).forEach(q => allocated += q);
                            });
                        }
                        
                        variantsHtml += `
                            <div class="alert alert-info py-2 px-3 mb-3 d-flex justify-content-between align-items-center" style="border-radius: 12px; font-size: 0.85rem;">
                                <span><i class="fa fa-info-circle me-1"></i> Allocated: <strong>${allocated}</strong> of <strong>${freeAmt}</strong></span>
                            </div>
                        `;

                        let unavailableVariants = [];

                        if (fp.variant_options && Object.keys(fp.variant_options).length > 0) {
                            Object.keys(fp.variant_options).forEach(attrName => {
                                let options = fp.variant_options[attrName];
                                let purged = false;
                                if (availableData) {
                                    options = options.filter(v => {
                                        let valid = true;
                                        if (attrName.toUpperCase() === 'SIDE') valid = availableSides.has(v.toUpperCase());
                                        if (attrName.toUpperCase() === 'SIZE') valid = availableSizes.has(v.toUpperCase());
                                        if (!valid) {
                                            unavailableVariants.push(v);
                                            if (item.free_selections && item.free_selections[attrName.toLowerCase()] && item.free_selections[attrName.toLowerCase()][v]) {
                                                delete item.free_selections[attrName.toLowerCase()][v];
                                                purged = true;
                                            }
                                        }
                                        return valid;
                                    });
                                }
                                if (purged) renderTable();
                                if (options.length === 0) return;

                                let currentVals = item.free_selections && item.free_selections[attrName.toLowerCase()] ? item.free_selections[attrName.toLowerCase()] : {};
                                variantsHtml += `
                                    <div class="d-flex flex-column align-items-start mb-2 p-2 rounded bg-light dark-bg-dark border border-light-dark w-100">
                                        <div class="d-flex align-items-center mb-2">
                                            <div style="width: 3px; height: 12px; background-color: var(--bs-primary); margin-right: 6px; border-radius: 2px;"></div>
                                            <span class="fw-bold text-uppercase text-secondary" style="font-size: 0.7rem; letter-spacing: 0.5px;">${attrName}</span>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 w-100">
                                            ${options.map(v => {
                                                let cQty = currentVals[v] || '';
                                                return `
                                                <div class="d-flex align-items-center bg-white dark-bg-transparent border border-light-dark rounded shadow-sm" style="flex: 1 1 calc(50% - 0.5rem); min-width: 110px; padding: 4px;">
                                                    <span class="fw-bold text-center ms-1 me-2 text-dark dark-text-light" style="font-size: 0.75rem; min-width: 24px;">${v}</span>
                                                    <div class="d-flex align-items-center rounded bg-light dark-bg-dark border border-light-dark ms-auto" style="overflow: hidden;">
                                                        <button type="button" class="btn btn-sm btn-light border-0 p-0 free-qty-btn d-flex align-items-center justify-content-center" data-action="minus" data-key="${key}" data-attr="${attrName.toLowerCase()}" data-val="${v}" style="width: 26px; height: 26px; border-radius: 0; background: transparent;">
                                                            <i class="fa fa-minus text-secondary" style="font-size: 0.6rem;"></i>
                                                        </button>
                                                        <span class="fw-bold text-primary text-center px-1 border-start border-end border-light-dark bg-white dark-bg-transparent" style="font-size: 0.8rem; line-height: 26px; min-width: 26px;">${cQty || 0}</span>
                                                        <button type="button" class="btn btn-sm btn-light border-0 p-0 free-qty-btn d-flex align-items-center justify-content-center" data-action="plus" data-key="${key}" data-attr="${attrName.toLowerCase()}" data-val="${v}" style="width: 26px; height: 26px; border-radius: 0; background: transparent;">
                                                            <i class="fa fa-plus text-secondary" style="font-size: 0.6rem;"></i>
                                                        </button>
                                                    </div>
                                                </div>`;
                                            }).join('')}
                                        </div>
                                    </div>
                                `;
                            });
                        } else {
                            let variantsToUse = dynamicVariants.length > 0 ? dynamicVariants : ['S', 'M', 'L', 'XL'];
                            let purged = false;
                            if (availableData) {
                                variantsToUse = variantsToUse.filter(v => {
                                    let valid = availableSizes.has(v.toUpperCase());
                                    if (!valid) {
                                        unavailableVariants.push(v);
                                        if (item.free_selections && item.free_selections['size'] && item.free_selections['size'][v]) {
                                            delete item.free_selections['size'][v];
                                            purged = true;
                                        }
                                    }
                                    return valid;
                                });
                            }
                            if (purged) renderTable();
                            if (variantsToUse.length > 0) {
                                let currentVals = item.free_selections && item.free_selections['size'] ? item.free_selections['size'] : {};
                                variantsHtml += `
                                    <div class="d-flex flex-column align-items-start mb-2 p-2 rounded bg-light dark-bg-dark border border-light-dark w-100">
                                        <div class="d-flex align-items-center mb-2">
                                            <div style="width: 3px; height: 12px; background-color: var(--bs-primary); margin-right: 6px; border-radius: 2px;"></div>
                                            <span class="fw-bold text-uppercase text-secondary" style="font-size: 0.7rem; letter-spacing: 0.5px;">SIZE</span>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 w-100">
                                            ${variantsToUse.map(v => {
                                                let cQty = currentVals[v] || '';
                                                return `
                                                <div class="d-flex align-items-center bg-white dark-bg-transparent border border-light-dark rounded shadow-sm" style="flex: 1 1 calc(50% - 0.5rem); min-width: 110px; padding: 4px;">
                                                    <span class="fw-bold text-center ms-1 me-2 text-dark dark-text-light" style="font-size: 0.75rem; min-width: 24px;">${v}</span>
                                                    <div class="d-flex align-items-center rounded bg-light dark-bg-dark border border-light-dark ms-auto" style="overflow: hidden;">
                                                        <button type="button" class="btn btn-sm btn-light border-0 p-0 free-qty-btn d-flex align-items-center justify-content-center" data-action="minus" data-key="${key}" data-attr="size" data-val="${v}" style="width: 26px; height: 26px; border-radius: 0; background: transparent;">
                                                            <i class="fa fa-minus text-secondary" style="font-size: 0.6rem;"></i>
                                                        </button>
                                                        <span class="fw-bold text-primary text-center px-1 border-start border-end border-light-dark bg-white dark-bg-transparent" style="font-size: 0.8rem; line-height: 26px; min-width: 26px;">${cQty || 0}</span>
                                                        <button type="button" class="btn btn-sm btn-light border-0 p-0 free-qty-btn d-flex align-items-center justify-content-center" data-action="plus" data-key="${key}" data-attr="size" data-val="${v}" style="width: 26px; height: 26px; border-radius: 0; background: transparent;">
                                                            <i class="fa fa-plus text-secondary" style="font-size: 0.6rem;"></i>
                                                        </button>
                                                    </div>
                                                </div>`;
                                            }).join('')}
                                        </div>
                                    </div>
                                `;
                            }
                        }
                        variantsHtml += `</div>`;
                        
                        if (unavailableVariants.length > 0) {
                            let uniqueUnavailable = [...new Set(unavailableVariants)];
                            variantsHtml += `<div class="text-muted small mt-2 font-outfit" style="font-size: 0.75rem; line-height: 1.4;">These variants are currently out of stock for this distributor: <strong class="text-dark dark-text-light">${uniqueUnavailable.join(', ')}</strong></div>`;
                        }

                        if (variantsHtml.indexOf('d-flex flex-column align-items-start') === -1) {
                            variantsHtml = `<div class="alert alert-warning mb-0 font-outfit"><i class="fa fa-exclamation-triangle me-1"></i> The distributor does not have any variants in stock for this free product.</div>`;
                        }
                    } else {
                        variantsHtml = `<div class="alert alert-secondary mb-0">No variants available for this free product.</div>`;
                    }
                }
                $('#freeVariantModalBody').html(variantsHtml);
            }

            $(document).on('click', '.open-free-variant-modal', function() {
                let key = $(this).data('key');
                let item = addedItems[key];
                if (!item) return;

                let btn = $(this);
                let originalHtml = btn.html();
                btn.html('<i class="fa fa-spinner fa-spin me-1"></i> Loading...').prop('disabled', true);
                
                let distId = $('#distributorSelect').val(); // Retailer selects distributor
                if (!distId && item.distId) distId = item.distId; // fallback
                let fpInfo = eligibleFreeProducts.find(p => p.id == item.id);
                let fpId = fpInfo ? fpInfo.id : item.id;
                
                $.ajax({
                    url: "{{ route('admin.retailer.distributor-variants', ':id') }}".replace(':id', fpId),
                    type: 'GET',
                    data: { distributor_id: distId },
                    success: function(response) {
                        currentFreeVariantKey = key;
                        let availableVariants = response.variants || [];
                        renderFreeVariantModal(key, availableVariants);
                        let modal = new bootstrap.Modal(document.getElementById('freeVariantModal'));
                        modal.show();
                    },
                    error: function() {
                        if (typeof showToast === 'function') showToast('error', 'Failed to fetch available variants.');
                        // Fallback to render without filter
                        currentFreeVariantKey = key;
                        renderFreeVariantModal(key, null);
                        let modal = new bootstrap.Modal(document.getElementById('freeVariantModal'));
                        modal.show();
                    },
                    complete: function() {
                        btn.html(originalHtml).prop('disabled', false);
                    }
                });
            });
            // --- End Modal Logic ---

            $(document).on('click', '.free-qty-btn', function() {
                let key = $(this).data('key');
                let attr = $(this).data('attr');
                let val = $(this).data('val').toString();
                let action = $(this).data('action');
                
                let item = addedItems[key];
                if (!item.free_selections) item.free_selections = {};
                if (!item.free_selections[attr]) item.free_selections[attr] = {};
                
                let fpInfo = eligibleFreeProducts.find(p => p.id == item.id);
                let getQty = fpInfo && fpInfo.free_qty_get ? fpInfo.free_qty_get : 1;
                let freeAmt = (item.free_qty_buy > 0 && item.qty >= item.free_qty_buy) ? Math.floor(item.qty / item.free_qty_buy) * getQty : 0;
                
                let currentQty = item.free_selections[attr][val] || 0;
                
                let allocated = 0;
                Object.values(item.free_selections).forEach(attrObj => {
                    Object.values(attrObj).forEach(q => allocated += q);
                });
                
                if (action === 'plus') {
                    if (allocated < freeAmt) {
                        item.free_selections[attr][val] = currentQty + 1;
                    } else {
                        if (typeof showToast === 'function') showToast('error', `You only have ${freeAmt} free items available.`);
                    }
                } else if (action === 'minus') {
                    if (currentQty > 0) {
                        item.free_selections[attr][val] = currentQty - 1;
                    }
                }
                
                renderTable(); // Update the main table silently
                renderFreeVariantModal(key); // Refresh the modal body
            });

            $(document).on('click', '.remove-variant-btn', function() {
                let key = $(this).data('key');
                let vIdx = $(this).data('vidx');
                let item = addedItems[key];
                
                if (item) {
                    let removedVariant = item.variants[vIdx];
                    item.qty -= removedVariant.qty; 
                    item.variants.splice(vIdx, 1);
                    
                    if (item.variants.length === 0 || item.qty <= 0) {
                        delete addedItems[key];
                    }
                    renderTable();
                }
            });

            $(document).on('click', '.remove-btn', function () {
                let key = $(this).data('key');
                let row = $(this).closest('tr');
                row.addClass('remove-row');
                setTimeout(() => { delete addedItems[key]; renderTable(); }, 400);
            });

            $('#createOrderForm').submit(function (e) {
                e.preventDefault();
                
                // Validate free item variants
                let hasIncompleteFree = false;
                $.each(addedItems, function(key, item) {
                    let getQty = 1;
                    let fpInfo = eligibleFreeProducts.find(p => p.id == item.id);
                    if (fpInfo && fpInfo.free_qty_get) getQty = fpInfo.free_qty_get;
                    let freeAmt = (item.free_qty_buy > 0 && item.qty >= item.free_qty_buy) ? Math.floor(item.qty / item.free_qty_buy) * getQty : 0;
                    
                    if (freeAmt > 0 && fpInfo && fpInfo.variant_options) {
                        let attrs = Object.keys(fpInfo.variant_options);
                        if (attrs.length > 0) {
                            let totalSelected = 0;
                            if (item.free_selections) {
                                attrs.forEach(attr => {
                                    let attrTotal = 0;
                                    if (item.free_selections[attr]) {
                                        Object.values(item.free_selections[attr]).forEach(q => attrTotal += parseInt(q) || 0);
                                    }
                                    if (attrTotal < freeAmt) hasIncompleteFree = true;
                                });
                            } else {
                                hasIncompleteFree = true;
                            }
                        }
                    }
                });

                if (hasIncompleteFree) {
                    showToast('warning', 'Please complete all variant selections for your free items before placing the order.');
                    return false;
                }

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
                        brand: p.brand,
                        free_qty_buy: p.free_qty_buy,
                        multiplier: mul,
                        strips_per_box: stripsPerBox,
                        boxes_per_carton: boxesPerCarton,
                        units_per_strip: p.units_per_strip,
                        pack: p.pack,
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