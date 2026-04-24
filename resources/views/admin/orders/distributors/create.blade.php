@extends('layouts.admin')

@section('page-body')
    <div class="container-fluid">
        <form id="createOrderForm" method="POST" action="{{ route('admin.distributor-orders.store') }}">
            @csrf
            <input type="hidden" name="status" value="pending">

            @if(Auth::user()->distributor)
                <input type="hidden" name="distributor_id" value="{{ Auth::user()->distributor->id }}">
            @endif

            <div class="row">
                {{-- Left Column: Product Spotlight, Picker & Table --}}
                <div class="col-xl-9 col-lg-8">
                    {{-- 1. Input Section --}}
                    <div class="card shadow-sm border-0 mb-4 builder-main-card rounded-3">
                        <div class="card-body p-4">
                            <div class="row g-4">
                                {{-- Row 1: Product and Distributor (for Admin) --}}
                                <div class="col-md-{{ Auth::user()->distributor ? '12' : '6' }}">
                                    <label class="form-label fw-bold text-muted small text-uppercase mb-2">Find
                                        Product</label>
                                    <select id="productSelect" class="form-select select2">
                                        <option value="">Search by Name or POS Code...</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}">{{ $p->product_name }}{{ $p->pack ? ' ('.$p->pack.')' : '' }} - ₹{{ $p->pts }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @if(!Auth::user()->distributor)
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small text-uppercase mb-2">Select Distributor</label>
                                    <select name="distributor_id" id="distributor_id" class="form-select select2">
                                        <option value="">Pick Distributor...</option>
                                        @foreach($distributors as $d)
                                            <option value="{{ $d->id }}">{{ $d->name ?? $d->user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                {{-- Row 2: Qty and Add Button --}}
                                <div class="col-md-12">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-5">
                                            <label class="form-label fw-bold text-muted small text-uppercase mb-2">Quantity & Type</label>
                                            <div class="input-group">
                                                <input type="number" id="qtyInput" class="form-control fw-bold rounded-start"
                                                    value="1" min="1" style="height: 48px;">
                                                <select
                                                    class="form-select input-group-text bg-light-soft border-start-0 font-outfit rounded-end"
                                                    id="unitSelect" style="max-width: 120px; height: 48px;">
                                                    <option value="Strips">Strips</option>
                                                    <option value="Box">Box</option>
                                                    <option value="Nos">Nos</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <button type="button"
                                                class="btn btn-primary w-100 fw-bold py-2 shadow-sm font-outfit rounded-3"
                                                style="height: 48px;"
                                                id="btnAddItem">
                                                <i class="fa fa-plus me-1"></i> ADD
                                            </button>
                                        </div>

                                        {{-- Variant Wrapper - stays here for flow --}}
                                        <div class="col-md-12 my-2" id="variantWrapper" style="display: none;">
                                            <div id="variantLevelsContainer">
                                                {{-- Dynamic variant levels will be injected here --}}
                                            </div>
                                            <input type="hidden" id="variantValue" value="">
                                        </div>
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
                                            <span class="text-primary fw-extrabold small text-uppercase d-block mb-2 font-outfit letter-spacing-wider" id="ptsLabel" style="opacity: 0.8;">PTS (Per Unit)</span>
                                            <span class="text-success mb-0 font-outfit fw-extrabold display-6" style="letter-spacing: -1px;">₹<span id="previewMrp">0.00</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Final Order Recap --}}
                <div class="col-xl-3 col-lg-4">
                    <div class="sticky-top" style="top: 20px; z-index: 5;">
                        <div class="card shadow-lg border-0 summary-card rounded-3">
                            <div class="card-header bg-dark text-white py-3">
                                <h5 class="card-title mb-0 fw-bold"><i class="fa fa-receipt me-2"></i>Order Recap</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-4 text-center">
                                    <label class="text-muted small fw-bold text-uppercase d-block mb-2">Total Order Value (PTS)</label>
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
                                    <i class="fa fa-check-double me-2"></i> CONFIRM ORDER
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Full Width Order Cart Table --}}
                <div class="col-xl-12 mt-4">
                    {{-- 3. Order Bundle Table --}}
                    <div class="card shadow-sm border-0 mb-4 overflow-hidden rounded-3">
                        <div
                            class="card-header bg-white dark-bg-transparent py-3 d-flex justify-content-between align-items-center border-bottom border-light-dark">
                            <h5 class="card-title mb-0 fw-bold text-dark label-font">Order cart</h5>
                            <span
                                class="badge bg-soft-primary text-primary px-3 py-2 small border border-primary border-opacity-25 rounded-pill">
                                <i class="fa fa-shopping-cart me-2"></i><span id="itemCount">0</span> Items Selected
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
                                            <th width="180"
                                                class="text-uppercase small fw-bold text-muted py-3 text-center sharp-th">
                                                Change Qty</th>
                                            <th class="text-uppercase small fw-bold text-muted py-3 sharp-th">Unit Price
                                            </th>
                                            <th class="text-uppercase small fw-bold text-muted py-3 sharp-th">Value (PTS)
                                            </th>
                                            <th width="80"
                                                class="text-center text-uppercase small fw-bold text-muted py-3 sharp-th">
                                                Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        <tr id="emptyRow">
                                            <td colspan="6" class="text-center py-5">
                                                <div class="py-4">
                                                    <div class="bg-light-soft dark-bg-dark d-inline-flex align-items-center justify-content-center mb-4 rounded-circle"
                                                        style="width: 100px; height: 100px;">
                                                        <i class="fa fa-shopping-basket fa-3x text-muted opacity-25"></i>
                                                    </div>
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

        /* Sharp TH - Rounded Everything Else */
        .sharp-th {
            border-radius: 0 !important;
        }

        /* === Modern UI Elements === */
        .bg-light-soft {
            background-color: #f8fafc !important;
        }

        .spotlight-image-wrapper {
            transition: transform 0.4s ease;
            background-color: white !important;
        }

        .spotlight-card:hover .spotlight-image-wrapper {
            transform: scale(1.02);
        }

        .builder-main-card {
            border-left: 5px solid var(--bs-primary) !important;
        }

        .btn-confirm {
            letter-spacing: 1px;
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
        .meta-capsule i {
            font-size: 1rem;
            opacity: 1;
        }
        .meta-label {
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            margin-right: 2px;
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

        /* === Select2 Customization === */
        .select2-container .select2-selection--single {
            height: 48px;
            border-radius: 8px !important;
            padding-top: 10px;
            border-color: #e2e8f0;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px;
        }

        /* === Animations === */
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

        /* Primary Square Remove Button Style */
        .remove-btn {
            border-radius: 4px !important;
            transition: all 0.2s ease;
            display: flex !important;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .remove-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>

    <script>
        $(document).ready(function () {
            const isValid = (val, type) => {
                if (!val || val === 'null' || val === null) return false;
                let s = val.toString().toLowerCase().trim();
                if (s === '' || s === 'n/a' || s === '---') return false;
                if (type === 'generic' && (s === 'generic name n/a' || s === 'generic n/a')) return false;
                if (type === 'pack' && s === 'pack n/a') return false;
                return true;
            };

            $('.select2').select2({ placeholder: "Find product...", allowClear: true });

            var addedItems = {};
            var currentProductDetails = null;

            $('#productSelect').on('select2:select', function (e) {
                let id = $(this).val();
                if (!id) return;

                $('#productDetailsCard').fadeIn(400);
                $.ajax({
                    url: "{{ route('admin.distributor-orders.product-details', ':id') }}".replace(':id', id),
                    type: 'GET',
                    success: function (res) {
                        let p = res.product;

                        // Dynamic Variant Parsing from product name
                        let pNameVar = (p.product_name || '').toLowerCase();
                        let dynamicVariants = [];
                        let match = pNameVar.match(/\(([^)]+)\)/g);
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
                                // Structured Variants logic (hierarchical)
                                let levelIdx = 0;
                                Object.keys(p.variant_options).forEach(attrName => {
                                    let vals = p.variant_options[attrName];
                                    let levelHtml = `
                                        <div class="variant-level mb-3" id="levelContainer_${levelIdx}" style="${levelIdx > 0 ? 'display:none;' : ''}">
                                            <label class="form-label fw-bold text-muted small text-uppercase mb-2">Select ${attrName}</label>
                                            <div class="d-flex flex-wrap gap-2">
                                                ${vals.map(v => `<button type="button" class="btn btn-outline-primary variant-btn px-3 py-2 fw-bold" data-level="${levelIdx}" data-value="${v}">${v}</button>`).join('')}
                                            </div>
                                        </div>`;
                                    $container.append(levelHtml);
                                    levelIdx++;
                                });
                            } else {
                                // Fallback to name-based parsing
                                let variantsToUse = dynamicVariants.length > 0 ? dynamicVariants : ['S', 'M', 'L', 'XL', 'XXL', 'XXXL'];
                                let levelHtml = `
                                    <div class="variant-level mb-3">
                                        <label class="form-label fw-bold text-muted small text-uppercase mb-2">Select Size / Variant</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            ${variantsToUse.map(v => `<button type="button" class="btn btn-outline-primary variant-btn px-3 py-2 fw-bold" data-level="0" data-value="${v}">${v}</button>`).join('')}
                                        </div>
                                    </div>`;
                                $container.append(levelHtml);
                            }
                        } else {
                            $('#variantWrapper').hide();
                            $('#variantValue').val('');
                        }
                        // Unit Logic: box_size empty = Nos
                        let boxSizeStr = p.box_size || '';
                        let isCount = boxSizeStr === "";
                        
                        // Fallback patterns: If not already Nos, check keywords
                        if (!isCount) {
                            let pName = (p.product_name || '').toLowerCase();
                            let pPack = (p.pack || '').toLowerCase();
                            isCount = pPack.includes('nos') || pPack.includes('count') || 
                                     pPack.includes('pair') || pPack.includes('bottle') || 
                                     pPack.includes('ml') || pPack.includes('gm') || 
                                     pPack.includes('syp') || pName.includes('syp') || 
                                     pName.includes('syrup') || pName.includes('drop') || 
                                     pName.includes('ointment') || pName.includes('belt') ||
                                     pName.includes('cap') || pName.includes('binder') ||
                                     pName.includes('splint') || pName.includes('brace') ||
                                     pName.includes('cuff') || pName.includes('walker');
                        }
                        
                        currentProductDetails = p;
                        currentProductDetails.is_count = isCount;

                        let $unitSelect = $('#unitSelect');
                        $unitSelect.empty();
                        if (isCount) {
                            $unitSelect.append('<option value="Nos">Nos</option>');
                            $('#ptsLabel').text("PTS (Per Nos)");
                        } else {
                            $unitSelect.append('<option value="Strips">Strips</option>');
                            $unitSelect.append('<option value="Box">Box</option>');
                            $unitSelect.append('<option value="Nos">Nos</option>'); 
                            $('#ptsLabel').text("PTS (Per Strip)");
                        }

                        let displayName = p.product_name;
                        $('#previewName').html(displayName);

                        if (isValid(p.pack, 'pack')) {
                            $('#previewPackSpan').text(p.pack);
                            $('#previewPackCapsule').show();
                        } else {
                            $('#previewPackCapsule').hide();
                        }
                        
                        $('#previewMrp').text(parseFloat(p.pts || 0).toFixed(2));
                        
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

                        let hasStrips = isValid(p.strip_size) || (p.units_per_strip > 1);
                        let hasBoxes = isValid(p.box_size) || (p.strips_per_box > 1);

                        if (!isCount && (hasStrips || hasBoxes)) {
                            let packInfoText = `${p.units_per_strip || 1} Tab/Str | ${p.strips_per_box || 1} Str/Box`;
                            $('#previewBoxSpan').text(packInfoText);
                            $('#previewBoxCapsule').show();
                        } else {
                            $('#previewBoxCapsule').hide();
                        }
                    }
                });
            });

            $('#productSelect').on('select2:clear', () => {
                $('#productDetailsCard').fadeOut(200); currentProductDetails = null;
            });

            $(document).on('click', '.variant-btn', function() {
                let $btn = $(this);
                let levelIdx = parseInt($btn.data('level'));
                
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
                }
            });

            $('#btnAddItem').click(function () {
                let prodId = $('#productSelect').val();
                if (!prodId || !currentProductDetails) return showToast('info', 'Please search/select a product first');

                let qty = parseInt($('#qtyInput').val());
                let unit = $('#unitSelect').val();
                let side = null;
                let size = null;
                if ($('#variantWrapper').is(':visible')) {
                    $('.variant-level:visible').each(function() {
                        let label = $(this).find('label').text().toLowerCase();
                        let activeVal = $(this).find('.variant-btn.active').data('value');
                        if (label.includes('side')) side = activeVal;
                        else if (label.includes('size')) size = activeVal;
                        else if (!side) side = activeVal; // Fallback to first if not labeled
                        else if (!size) size = activeVal; // Fallback to second if not labeled
                    });
                }

                if ($('#variantWrapper').is(':visible') && !side && !size) {
                    return showToast('warning', 'Please select a size/variant first');
                }

                if (qty < 1) return;

                let key = prodId + (side ? '-' + side : '') + (size ? '-' + size : '');
                let mul = 1;
                if (unit === 'Box') mul = parseInt(currentProductDetails.strips_per_box || 1);
                else if (unit === 'Nos') mul = 1 / (parseInt(currentProductDetails.units_per_strip || 1));

                if (addedItems[key]) {
                    addedItems[key].qty += qty;
                    addedItems[key].unit = unit;
                    addedItems[key].multiplier = mul;
                } else {
                    addedItems[key] = {
                        id: prodId, name: currentProductDetails.product_name,
                        side: side,
                        size: size,
                        price: parseFloat(currentProductDetails.pts),
                        qty: qty, unit: unit, multiplier: mul,
                        strips_per_box: currentProductDetails.strips_per_box,
                        boxes_per_carton: currentProductDetails.boxes_per_carton,
                        is_count: currentProductDetails.is_count
                    };
                }
                renderTable(key);
                $('#productSelect').val(null).trigger('change');
                $('#productDetailsCard').fadeOut(300);
                $('#qtyInput').val(1);
                currentProductDetails = null;
            });

            function renderTable(lastAddedKey) {
                let tbody = $('#orderTable tbody');
                tbody.empty();
                let total = 0; let hasItems = false; let index = 1;

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
                                    ${item.side ? `<span class="badge bg-primary ms-1">${item.side}</span>` : ''}
                                    ${item.size ? `<span class="badge bg-info ms-1">${item.size}</span>` : ''}
                                </div>
                                <input type="hidden" name="items[${key}][product_id]" value="${item.id}">
                                ${item.side ? `<input type="hidden" name="items[${key}][side]" value="${item.side}">` : ''}
                                ${item.size ? `<input type="hidden" name="items[${key}][size]" value="${item.size}">` : ''}
                            </td>
                            <td class="text-center">
                                <div class="py-2">
                                    <span class="fw-extrabold text-dark font-outfit h6 mb-0">${item.qty}</span>
                                    <span class="text-muted small ms-1">${item.unit}</span>
                                </div>
                                <input type="hidden" name="items[${key}][quantity]" value="${item.qty}">
                                <input type="hidden" name="items[${key}][unit]" value="${item.unit}">
                            </td>
                            <td class="fw-medium">₹${item.price.toFixed(2)}</td>
                            <td class="fw-bold text-primary">₹${lineTotal.toFixed(2)}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm remove-btn mx-auto" 
                                    data-key="${key}" style="width: 38px; height: 32px;">X
                                    <i class="fa fa-trash-alt" style="font-size: 13px;"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });

                if (!hasItems) {
                    tbody.append($('#emptyRow').clone().show());
                    $('#btnSubmitOrder').prop('disabled', true);
                } else {
                    $('#btnSubmitOrder').prop('disabled', false);
                }

                $('#grandTotal').text('₹' + total.toFixed(2));
                $('#itemCount').text(Object.keys(addedItems).length);
            }

            $(document).on('change', '.qty-change, .unit-change', function () {
                let key = $(this).data('key');
                let item = addedItems[key];
                if ($(this).hasClass('qty-change')) item.qty = parseInt($(this).val()) || 1;
                else {
                    let val = $(this).val();
                    let mul = 1;
                    if (val === 'Box') mul = parseInt(item.strips_per_box || 1);
                    item.unit = val;
                    item.multiplier = mul;
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
                            showToast('success', 'Order Placed Successfully');
                            window.location.href = "{{ route('admin.distributor-orders.index') }}";
                        } else {
                            showToast('error', res.error || 'Error in submission');
                            btn.prop('disabled', false).html('<i class="fa fa-check-double me-2"></i> CONFIRM ORDER');
                        }
                    },
                    error: function (xhr) {
                        let msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Server error occurred during submission.';
                        showToast('error', msg);
                        btn.prop('disabled', false).html('<i class="fa fa-check-double me-2"></i> CONFIRM ORDER');
                    }
                });
            });
            $(document).on('click', '.size-btn', function() {
                $('.size-btn').removeClass('btn-primary text-white').addClass('btn-outline-primary');
                $(this).removeClass('btn-outline-primary').addClass('btn-primary text-white');
                $('#variantValue').val($(this).data('size'));
            });
        });
    </script>
@endpush