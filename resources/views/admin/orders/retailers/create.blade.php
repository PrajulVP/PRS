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

                            <div class="row g-4">
                                {{-- Row 1: Product and Distributor --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small text-uppercase mb-2">Find
                                        Product</label>
                                    <select id="productSelect" class="form-select select2">
                                        <option value="">Search Products...</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}">{{ $p->product_name }} - ₹{{ $p->ptr }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small text-uppercase mb-2">Select
                                        Distributor</label>
                                    <select id="distributorSelect" class="form-select select2">
                                        <option value="">Select Product First</option>
                                    </select>
                                </div>

                                {{-- Row 2: Variant, Qty and Add Button --}}
                                <div class="col-md-12">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-12" id="variantWrapper" style="display: none;">
                                            <label class="form-label fw-bold text-muted small text-uppercase mb-2">Select Size / Variant</label>
                                            <div id="sizeSelector" class="d-flex flex-wrap gap-2">
                                                {{-- Size buttons will be injected here --}}
                                            </div>
                                            <input type="hidden" id="variantValue" value="">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-muted small text-uppercase mb-2">Quantity
                                                & Type</label>
                                            <div class="input-group">
                                                <input type="number" id="qtyInput"
                                                    class="form-control fw-bold rounded-start" value="1" min="1">
                                                <select
                                                    class="form-select bg-light-soft border-start-0 font-outfit rounded-end"
                                                    id="unitSelect" style="max-width: 130px;">
                                                    <option value="Strips">Strips</option>
                                                    <option value="Box">Box</option>
                                                    <option value="Carton">Carton</option>
                                                    <option value="Nos">Nos</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button"
                                                class="btn btn-primary w-100 fw-bold shadow-sm font-outfit rounded-3 py-2"
                                                id="btnAddItem">
                                                <i class="fa fa-plus me-1"></i> ADD
                                            </button>
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
                                <div class="col-md-3 text-center mb-3 mb-md-0">
                                    <div class="bg-white p-3 shadow-sm border spotlight-image-wrapper rounded-3">
                                        <img id="previewImage" src="https://placehold.co/400x400?text=No+Image"
                                            class="img-fluid" style="max-height: 140px; width: auto; object-fit: contain;">
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge bg-primary px-3 py-1 shadow-sm small mb-2 rounded-pill"
                                                id="previewCode">Product Code: -</span>
                                            <h3 id="previewName" class="fw-bold mb-1 text-dark label-font">Product Name</h3>
                                            <p class="text-primary fw-bold small mb-3 text-uppercase font-outfit"
                                                id="previewGeneric">
                                                Generic Name</p>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-muted fw-bold small text-uppercase d-block mb-1"
                                                id="ptrLabel">PTR (Per
                                                Unit)</span>
                                            <span class="h3 fw-bold text-success mb-0 font-outfit">₹<span
                                                    id="previewMrp">0.00</span></span>
                                        </div>
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <div
                                                class="p-2 border bg-light-soft text-center dark-bg-dark border-light-dark rounded-3">
                                                <small class="text-muted d-block fw-bold text-uppercase"
                                                    style="font-size: 0.6rem;">Offer /
                                                    Disc %</small>
                                                <span id="previewOfferDisc" class="fw-bold text-dark small mb-0">-</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div
                                                class="p-2 border bg-light-soft text-center dark-bg-dark border-light-dark rounded-3">
                                                <small class="text-muted d-block fw-bold text-uppercase"
                                                    style="font-size: 0.6rem;">HSN</small>
                                                <span id="previewHsn" class="fw-bold text-dark small mb-0">-</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div
                                                class="p-2 border bg-light-soft text-center dark-bg-dark border-light-dark rounded-3">
                                                <small class="text-muted d-block fw-bold text-uppercase"
                                                    style="font-size: 0.6rem;">Packing</small>
                                                <span id="previewBox" class="fw-bold text-dark small mb-0">-</span>
                                            </div>
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
    </style>

    <script>
        $(document).ready(function () {
            let addedItems = {};
            let lastAiResponse = null; 
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
                let stock = el.data('stock-raw');
                let distance = el.data('distance');
                if (stock !== undefined) {
                    let stockBadge = stock > 0 ? 'bg-success' : 'bg-danger';
                    let distText = distance ? `<small class="text-muted"><i class="fa fa-map-marker-alt"></i> ${distance}km</small>` : '';
                    return $(`<div class="d-flex justify-content-between align-items-center"><span>${opt.text}</span><div class="ms-2">${distText} <span class="badge ${stockBadge} ms-1">${stock}</span></div></div>`);
                }
                return opt.text;
            }

            var currentProductDetails = null;

            function loadDistributors(prodId, retailerId, variant) {
                let distSelect = $('#distributorSelect');
                distSelect.empty().append('<option value="">Loading...</option>');

                $.ajax({
                    url: "{{ route('admin.retailer.product-details', ':id') }}".replace(':id', prodId),
                    type: 'GET',
                    data: { 
                        retailer_id: retailerId,
                        variant: variant
                    },
                    success: function (res) {
                        distSelect.empty();
                        if (res.distributors && res.distributors.length > 0) {
                            res.distributors.forEach(d => {
                                let name = d.user ? d.user.name : 'ID: ' + d.id;
                                let stock = d.pivot ? d.pivot.stock : 0;
                                distSelect.append(`<option value="${d.id}" data-stock-raw="${stock}">${name}</option>`);
                            });
                        } else {
                            distSelect.append('<option value="">No stock available</option>');
                        }
                    }
                });
            }

            $('#productSelect').on('select2:select', function (e) {
                let prodId = $(this).val();
                let retailerId = $('#retailer_id').val();
                if (!retailerId) {
                    showToast('error', 'Select a retailer first');
                    $(this).val(null).trigger('change');
                    return;
                }

                $('#productDetailsCard').fadeIn(400);
                $('#distributorSelect').empty().append('<option value="">Select Variant First...</option>');

                $.ajax({
                    url: "{{ route('admin.retailer.product-details', ':id') }}".replace(':id', prodId),
                    type: 'GET',
                    data: { retailer_id: retailerId },
                    success: function (res) {
                        let p = res.product;
                        currentProductDetails = p;

                        // Dynamic Variant Parsing from product name
                        let pName = (p.product_name || '').toLowerCase();
                        let dynamicVariants = [];
                        let match = pName.match(/\(([^)]+)\)/g);
                        if (match) {
                            let lastMatch = match[match.length - 1].replace('(', '').replace(')', '');
                            if (lastMatch.includes('/')) {
                                dynamicVariants = lastMatch.split('/').map(s => s.trim().toUpperCase());
                            }
                        }

                        let hasVariants = p.has_variants || dynamicVariants.length > 0;

                        if (hasVariants) {
                            let $sizeSel = $('#sizeSelector');
                            $sizeSel.empty();
                            
                            let variantsToUse = dynamicVariants.length > 0 ? dynamicVariants : ['S', 'M', 'L', 'XL', 'XXL', 'XXXL'];
                            
                            variantsToUse.forEach(v => {
                                $sizeSel.append(`<button type="button" class="btn btn-outline-primary size-btn px-3 py-2 fw-bold" data-size="${v}">${v}</button>`);
                            });
                            $('#variantWrapper').fadeIn(200);
                            $('#variantValue').val(''); // Reset
                            $('#distributorSelect').empty().append('<option value="">Select Size Above...</option>');
                        } else {
                            $('#variantWrapper').hide();
                            $('#variantValue').val('');
                            loadDistributors(prodId, retailerId, null);
                        }

                        // Unit Logic: box_size empty = Nos
                        let $unitSelect = $('#unitSelect');
                        $unitSelect.empty();
                        
                        let pPack = (p.pack || '').toLowerCase();
                        let boxSizeStr = p.box_size || '';
                        let isCount = boxSizeStr === "";
                        
                        // Fallback patterns: If not already Nos, check keywords
                        if (!isCount) {
                            let pName = (p.product_name || '').toLowerCase();
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
                        
                        if (isCount) {
                            $unitSelect.append('<option value="Strips">Nos</option>');
                            $('#ptrLabel').text(`PTR (Per Nos)`);
                        } else {
                            $unitSelect.append('<option value="Strips">Strips</option>');
                            $unitSelect.append('<option value="Box">Box</option>');
                            $unitSelect.append('<option value="Carton">Carton</option>');
                            $('#ptrLabel').text(`PTR (Per Strip)`);
                        }

                        $('#previewName').text(p.product_name);
                        $('#previewMrp').text(parseFloat(p.ptr || 0).toFixed(2));
                        $('#previewGeneric').text(p.generic_name || 'Generic Name N/A');
                        $('#previewCode').text('Product Code: ' + (p.product_code || '---'));
                        let offerDiscText = (parseFloat(p.offer || 0) + '% / ' + parseFloat(p.discount || 0) + '%');
                        $('#previewOfferDisc').text(offerDiscText);
                        $('#previewHsn').text(p.hsn_code || '---');
                        
                        // Dynamic Packaging Info
                        let packInfoText = '';
                        if (isCount) {
                            packInfoText = `${p.units_per_strip || 1} Nos/Unit | ${p.strips_per_box || 1} Unit/Box | ${p.boxes_per_carton || 1} Box/Ctn`;
                        } else {
                            packInfoText = `${p.units_per_strip || 1} Tab/Str | ${p.strips_per_box || 1} Str/Box | ${p.boxes_per_carton || 1} Box/Ctn`;
                        }
                        $('#previewBox').text(packInfoText);

                        if (p.image) $('#previewImage').attr('src', "{{ asset('storage') }}/" + p.image);
                        else $('#previewImage').attr('src', "https://placehold.co/400x400?text=No+Photo");
                    }
                });
            });

            $(document).on('click', '.size-btn', function() {
                $('.size-btn').removeClass('active');
                $(this).addClass('active');
                let selectedVariant = $(this).data('size');
                $('#variantValue').val(selectedVariant);

                let prodId = $('#productSelect').val();
                let retailerId = $('#retailer_id').val();
                if (prodId && retailerId) {
                    loadDistributors(prodId, retailerId, selectedVariant);
                } else {
                    $('#distributorSelect').empty().append('<option value="">Select Product and Retailer</option>');
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
                    return showToast('error', `Insufficient stock. Please select another distributor.`);
                }

                if (addedItems[key]) {
                    addedItems[key].qty += qty;
                    // Note: unit/multiplier might change, but we keep the key based on prod-dist-variant
                } else {
                    addedItems[key] = {
                        id: prodId, distId: distId, distName: distName,
                        name: currentProductDetails.product_name,
                        variant: variant,
                        price: parseFloat(currentProductDetails.ptr),
                        qty: qty, unit: unit, multiplier: mul,
                        strips_per_box: stripsPerBox,
                        boxes_per_carton: boxesPerCarton,
                        units_per_strip: currentProductDetails.units_per_strip,
                        has_variants: currentProductDetails.has_variants,
                        is_count: $('#unitSelect').val() === 'Strips' && $('#unitSelect option:selected').text() === 'Nos',
                        maxStock: maxStockRaw
                    };
                }
                renderTable(key);
                $('#productSelect').val(null).trigger('change');
                $('#distributorSelect').empty().append('<option value="">Select Product First</option>').trigger('change');
                $('#productDetailsCard').fadeOut(300);
                $('#qtyInput').val(1);
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
                                let d = item.distributor;
                                let distName = d.shop_name || d.name || 'N/A';
                                let options = `<option value="${p.id}" selected>${p.product_name} (₹${p.ptr})</option>`;
                                
                                let distHtml = `
                                    <select class="form-select select2-ai ai-dist-select" data-pid="${p.id}">
                                        <option value="${d.id}" data-stock="${d.stock}" selected>${distName} - Stock: ${d.stock}</option>
                                    </select>`;

                                let rowHtml = `
                                    <div class="ai-result-row p-4 bg-white rounded-4 shadow-sm mb-3 border border-light-dark overflow-hidden transition-all hover-shadow">
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
                                                <label class="text-muted small fw-bold text-uppercase mb-1 d-block"><i class="fa fa-truck me-1 text-primary"></i> Select Distributor</label>
                                                ${distHtml}
                                            </div>
                                            
                                            <!-- Action & Qty -->
                                            <div class="col-lg-2 col-md-4 col-6">
                                                <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Quantity</label>
                                                <input type="number" class="form-control ai-qty fw-bold" value="${item.quantity}" min="1" style="height: 42px;">
                                            </div>
                                            <div class="col-lg-2 col-md-4 col-6">
                                                <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Unit</label>
                                                <select class="form-select ai-unit fw-medium" style="height: 42px;">
                                                    <option value="Strips" ${item.unit === 'Strips' ? 'selected' : ''}>Strips</option>
                                                    <option value="Box" ${item.unit === 'Box' ? 'selected' : ''}>Box</option>
                                                    <option value="Carton" ${item.unit === 'Carton' ? 'selected' : ''}>Carton</option>
                                                    <option value="Nos" ${item.unit === 'Nos' ? 'selected' : ''}>Nos</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-8 col-md-4 col-12 d-flex align-items-end">
                                                <button type="button" class="btn btn-primary ai-add-btn w-100 fw-bold shadow-sm" 
                                                    style="height: 42px; border-radius: 10px; background: var(--med-primary);"
                                                    data-idx="${idx}">
                                                    <i class="fa fa-plus-circle me-1"></i> ADD TO CART
                                                </button>
                                            </div>
                                        </div>
                                    </div>`;
                                resultsList.append(rowHtml);
                            });

                            $('.select2-ai').select2({ width: '100%', dropdownParent: $('#aiResultsContainer') });

                            // Handle Out of Stock Items
                            if (res.out_of_stock_items && res.out_of_stock_items.length > 0) {
                                let stockMsg = res.out_of_stock_items.map(i => `<span class="badge bg-soft-warning text-warning me-2" style="font-size: 0.85rem; border: 1px solid rgba(255,193,7,0.2);">${i.product_name} (${i.original_name})</span>`).join('');
                                $('#unmatchedList').append(`<hr class="my-3 text-muted opacity-25"><div class="text-muted fw-bold mb-2" style="font-size: 0.95rem;"><i class="fa fa-exclamation-triangle me-1 text-warning"></i> Matched products currently NOT in stock:</div><div class="d-flex flex-wrap">${stockMsg}</div>`);
                            }

                            if (res.unmatched_items && res.unmatched_items.length > 0) {
                                let names = res.unmatched_items.map(i => `<span class="badge bg-soft-danger text-danger me-2" style="font-size: 0.9rem; border: 1px solid rgba(220,53,69,0.2);">${i.name}</span>`).join('');
                                $('#unmatchedList').html(`<hr class="my-3 text-muted opacity-25"><div class="text-muted fw-bold mb-2" style="font-size: 0.95rem;"><i class="fa fa-info-circle me-1 text-danger"></i> Molecule(s) not found in our current product catalog:</div><div class="d-flex flex-wrap">${names}</div>`);
                            }
                            
                            showToast('success', `AI identified multiple options. Please review and add them.`);
                        } else {
                            resultsList.html('<div class="text-center py-3 text-muted">No items matched in our database.</div>');
                            if (res.unmatched_items && res.unmatched_items.length > 0) {
                                let names = res.unmatched_items.map(i => `<span class="badge bg-soft-danger text-danger me-2" style="font-size: 0.9rem; border: 1px solid rgba(220,53,69,0.2);">${i.name}</span>`).join('');
                                $('#unmatchedList').html(`<hr class="my-3 text-muted opacity-25"><div class="text-muted fw-bold mb-2" style="font-size: 0.95rem;"><i class="fa fa-info-circle me-1 text-danger"></i> Molecule(s) not found in our current product catalog:</div><div class="d-flex flex-wrap">${names}</div>`);
                            }
                            showToast('error', 'AI could not find any of these items in stock.');
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
                                                        ${item.variant ? `<input type="hidden" name="items[${key}][variant]" value="${item.variant}">` : ''}
                                                        <input type="hidden" name="items[${key}][distributor_id]" value="${item.distId}">
                                                    </td>
                                                    <td class="small fw-medium text-muted">${item.distName}</td>
                                                    <td class="text-center">
                                                        <div class="input-group input-group-sm mx-auto" style="max-width: 150px;">
                                                            <input type="number" class="form-control qty-change font-outfit" data-key="${key}" value="${item.qty}" name="items[${key}][quantity]" min="1" style="border-radius: 4px 0 0 4px;">
                                                            <select class="form-select unit-change font-outfit bg-light-soft" data-key="${key}" name="items[${key}][unit]" style="border-radius: 0 4px 4px 0;">
                                                                ${item.is_count ? `<option value="Nos" selected>Nos</option>` : `
                                                                    <option value="Carton" ${item.unit === 'Carton' ? 'selected' : ''}>Carton</option>
                                                                    <option value="Box" ${item.unit === 'Box' ? 'selected' : ''}>Box</option>
                                                                    <option value="Strips" ${item.unit === 'Strips' ? 'selected' : ''}>Strips</option>
                                                                    <option value="Nos" ${item.unit === 'Nos' ? 'selected' : ''}>Nos</option>
                                                                `}
                                                            </select>
                                                        </div>
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
                            showToast('error', 'Update Failed');
                            btn.prop('disabled', false).html('<i class="fa fa-check-circle me-2"></i> CONFIRM ORDER');
                        }
                    }
                });
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

                let requestedStrips = qty * mul;
                let key = p.id + '-' + distId;

                // Existing strips check
                let existingStrips = addedItems[key] ? (addedItems[key].qty * addedItems[key].multiplier) : 0;
                
                if ((existingStrips + requestedStrips) > maxStockRaw) {
                    showToast('error', `Insufficient stock for ${p.product_name}. Max available is ${maxStockRaw}.`);
                    return;
                }

                if (addedItems[key]) {
                    addedItems[key].qty += qty;
                } else {
                    addedItems[key] = {
                        id: p.id,
                        distId: distId,
                        distName: distName,
                        name: p.product_name,
                        variant: null,
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
                    loadDistributors(prodId, retailerId, variant);
                }
            });
        });
    </script>
@endpush