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
                <div class="col-xl-8 col-lg-7">

                    {{-- 1. Input Section --}}
                    <div class="card shadow-sm border-0 mb-4 builder-main-card rounded-3">
                        <div class="card-body p-4">
                            <!-- AI Prescription Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div
                                        class="p-3 border rounded-3 bg-light d-flex align-items-center justify-content-between shadow-xs">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 p-2 rounded-circle mr-3">
                                                <i class="fa fa-robot text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">Have a handwritten prescription?</h6>
                                                <small class="text-muted">Upload it and AI will automatically add medicines
                                                    to your cart.</small>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div id="ocrLoader" class="mr-3 d-none">
                                                <i class="fa fa-spinner fa-spin text-primary"></i> <small
                                                    class="text-primary fw-bold">AI Extracting...</small>
                                            </div>
                                            <input type="file" id="prescriptionInput" class="d-none"
                                                accept=".jpg,.jpeg,.png,.pdf">
                                            <button type="button" class="btn btn-primary px-4 fw-bold shadow-sm"
                                                id="btnUploadPrescription"
                                                style="border-radius: 8px; background: var(--med-primary);">
                                                <i class="fa fa-upload me-1"></i> UPLOAD & EXTRACT
                                            </button>
                                        </div>
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

                                {{-- Row 2: Qty and Add Button --}}
                                <div class="col-md-6 offset-md-6">
                                    <div class="row g-3">
                                        <div class="col-7">
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
                                        <div class="col-5 align-self-end text-end">
                                            <button type="button"
                                                class="btn btn-primary fw-bold shadow-sm font-outfit rounded-3"
                                                id="btnAddItem">
                                                <i class="fa fa-plus me-1"></i> ADD
                                            </button>
                                        </div>
                                    </div>
                                </div>
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
                        <img id="previewImage" src="https://placehold.co/400x400?text=No+Image" class="img-fluid"
                            style="max-height: 140px; width: auto; object-fit: contain;">
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-primary px-3 py-1 shadow-sm small mb-2 rounded-pill"
                                id="previewCode">Product Code: -</span>
                            <h3 id="previewName" class="fw-bold mb-1 text-dark label-font">Product Name</h3>
                            <p class="text-primary fw-bold small mb-3 text-uppercase font-outfit" id="previewGeneric">
                                Generic Name</p>
                        </div>
                        <div class="text-end">
                            <span class="text-muted fw-bold small text-uppercase d-block mb-1" id="ptrLabel">PTR (Per
                                Unit)</span>
                            <span class="h3 fw-bold text-success mb-0 font-outfit">₹<span id="previewMrp">0.00</span></span>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-4">
                            <div class="p-2 border bg-light-soft text-center dark-bg-dark border-light-dark rounded-3">
                                <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.6rem;">Offer /
                                    Disc %</small>
                                <span id="previewOfferDisc" class="fw-bold text-dark small mb-0">-</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-2 border bg-light-soft text-center dark-bg-dark border-light-dark rounded-3">
                                <small class="text-muted d-block fw-bold text-uppercase"
                                    style="font-size: 0.6rem;">HSN</small>
                                <span id="previewHsn" class="fw-bold text-dark small mb-0">-</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-2 border bg-light-soft text-center dark-bg-dark border-light-dark rounded-3">
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
                            <th width="170" class="text-uppercase small fw-bold text-muted py-3 text-center sharp-th">
                                Qty Builder</th>
                            <th class="text-uppercase small fw-bold text-muted py-3 sharp-th">PTR</th>
                            <th class="text-uppercase small fw-bold text-muted py-3 sharp-th">Unit Price
                            </th>
                            <th width="80" class="text-center text-uppercase small fw-bold text-muted py-3 sharp-th">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <tr id="emptyRow">
                            <td colspan="7" class="text-center py-5">
                                <div class="py-4 opacity-50">
                                    <i class="fa fa-cart-arrow-down fa-4x mb-3 text-muted"></i>
                                    <h5 class="text-muted fw-bold">Bundle is Empty</h5>
                                    <p class="text-muted small">Choose a retailer and add items to your
                                        cart.</p>
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
    <div class="col-xl-4 col-lg-5">
        <div class="sticky-top" style="top: 20px; z-index: 5;">
            <div class="card shadow-lg border-0 summary-card rounded-3">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="fa fa-receipt me-2"></i>Grand Total</h5>
                </div>
                <div class="card-body p-4">
                    <div
                        class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-light-dark">
                        <div>
                            <span class="text-dark fw-bold d-block h5 mb-0 label-font">Total Value (PTR)</span>
                            <small class="text-muted"><i class="fa fa-info-circle text-warning"></i> GST & other
                                charges will be calculated on the invoice</small>
                        </div>
                        <span id="grandTotal" class="h3 fw-bold text-primary mb-0 font-outfit">₹0.00</span>
                    </div>

                    <button type="submit"
                        class="btn btn-success btn-lg w-100 py-2 fw-bold shadow-sm font-outfit btn-confirm rounded-3"
                        id="btnSubmitOrder" disabled>
                        <i class="fa fa-check-circle me-2"></i> CONFIRM ORDER
                    </button>

                    <div class="mt-4 p-3 bg-light-soft border dark-bg-dark border-light-dark rounded-3">
                        <ul class="list-unstyled mb-0 small text-muted">
                            <li class="mb-2"><i class="fa fa-info-circle me-2"></i> Multi-distributor order
                                splitting enabled.</li>
                            <li><i class="fa fa-shield-alt me-2"></i> Inventory stocks verified in real-time.
                            </li>
                        </ul>
                    </div>
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
    </style>

    <script>
        $(document).ready(function () {
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

            var addedItems = {};
            var currentProductDetails = null;

            $('#productSelect').on('select2:select', function (e) {
                let prodId = $(this).val();
                let retailerId = $('#retailer_id').val();
                if (!retailerId) {
                    showToast('error', 'Select a retailer first');
                    $(this).val(null).trigger('change');
                    return;
                }

                $('#productDetailsCard').fadeIn(400);
                $('#distributorSelect').empty().append('<option value="">Loading...</option>');

                $.ajax({
                    url: "{{ route('admin.retailer.product-details', ':id') }}".replace(':id', prodId),
                    type: 'GET',
                    data: { retailer_id: retailerId },
                    success: function (res) {
                        let p = res.product;

                        let pPack = p.pack ? p.pack.toLowerCase() : '';
                        let pName = p.product_name ? p.product_name.toLowerCase() : '';
                        let isCount = (parseInt(p.box_size) === 1 && parseInt(p.carton_size) === 1) ||
                            pPack.includes('nos') || pPack.includes('count') ||
                            pPack.includes('pair') || pPack.includes('bottle') ||
                            pPack.includes('ml') || pPack.includes('gm') || pPack.includes('syp') ||
                            pName.includes('syp') || pName.includes('syrup') || pName.includes('drop') || pName.includes('ointment');
                        p.is_count = isCount;
                        currentProductDetails = p;

                        let $unitSelect = $('#unitSelect');
                        $unitSelect.empty();
                        if (isCount) {
                            $unitSelect.append('<option value="Nos">Nos</option>');
                            $('#ptrLabel').text("PTR (Per Nos)");
                        } else {
                            $unitSelect.append('<option value="Strips">Strips</option>');
                            $unitSelect.append('<option value="Box">Box</option>');
                            $unitSelect.append('<option value="Carton">Carton</option>');
                            $unitSelect.append('<option value="Nos">Nos</option>'); // Added manual override
                            $('#ptrLabel').text("PTR (Per Strip)");
                        }

                        $('#previewName').text(p.product_name);
                        $('#previewMrp').text(parseFloat(p.ptr || 0).toFixed(2));
                        $('#previewGeneric').text(p.generic_name || 'Generic Name N/A');
                        $('#previewCode').text('Product Code: ' + (p.product_code || '---'));
                        let offerDiscText = (parseFloat(p.offer || 0) + '% / ' + parseFloat(p.discount || 0) + '%');
                        $('#previewOfferDisc').text(offerDiscText);
                        $('#previewHsn').text(p.hsn_code || '---');
                        $('#previewBox').text((p.box_size || '1') + ' x ' + (p.carton_size || '1'));

                        if (p.image) $('#previewImage').attr('src', "{{ asset('storage') }}/" + p.image);
                        else $('#previewImage').attr('src', "https://placehold.co/400x400?text=No+Photo");

                        let distSelect = $('#distributorSelect');
                        distSelect.empty();
                        if (res.distributors && res.distributors.length > 0) {
                            res.distributors.forEach(d => {
                                let name = d.user ? d.user.name : 'ID: ' + d.id;
                                let dist = d.distance ? parseFloat(d.distance).toFixed(2) : null;
                                let stock = d.pivot ? d.pivot.stock : 0;
                                distSelect.append(`<option value="${d.id}" data-stock-raw="${stock}" data-distance="${dist}">${name}</option>`);
                            });
                        } else {
                            distSelect.append('<option value="">No stock available</option>');
                        }
                    }
                });
            });

            $('#btnAddItem').click(function () {
                let prodId = $('#productSelect').val();
                let distId = $('#distributorSelect').val();
                let qty = parseInt($('#qtyInput').val());
                let unit = $('#unitSelect').val();

                if (!prodId || !distId || qty < 1) return showToast('info', 'Complete selections first');

                let distName = $('#distributorSelect option:selected').text();
                let key = prodId + '-' + distId;
                let mul = 1;
                if (unit === 'Box') mul = parseInt(currentProductDetails.box_size || 1);
                if (unit === 'Carton') mul = parseInt(currentProductDetails.box_size || 1) * parseInt(currentProductDetails.carton_size || 1);

                if (addedItems[key]) {
                    addedItems[key].qty += qty;
                } else {
                    addedItems[key] = {
                        id: prodId, distId: distId, distName: distName,
                        name: currentProductDetails.product_name,
                        price: parseFloat(currentProductDetails.ptr),
                        qty: qty, unit: unit, multiplier: mul,
                        box_size: currentProductDetails.box_size,
                        carton_size: currentProductDetails.carton_size,
                        is_count: currentProductDetails.is_count
                    };
                }
                renderTable(key);
                $('#productSelect').val(null).trigger('change');
                $('#distributorSelect').empty().append('<option value="">Select Product First</option>').trigger('change');
                $('#productDetailsCard').fadeOut(300);
                $('#qtyInput').val(1);
                currentProductDetails = null;
            });

            // --- Prescription OCR Logic ---
            $('#btnUploadPrescription').click(function () {
                $('#prescriptionInput').click();
            });

            $('#prescriptionInput').change(function () {
                let file = this.files[0];
                if (!file) return;

                let retailerId = $('#retailer_id').val();
                if (!retailerId) {
                    showToast('error', 'Select a retailer first');
                    return;
                }

                let formData = new FormData();
                formData.append('prescription', file);
                formData.append('retailer_id', retailerId);
                formData.append('_token', '{{ csrf_token() }}');

                $('#btnUploadPrescription').prop('disabled', true);
                $('#ocrLoader').removeClass('d-none');

                $.ajax({
                    url: "{{ route('ocr.extract-prescription') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.success && res.medicines.length > 0) {
                            showToast('success', 'AI Extracted ' + res.medicines.length + ' medicines');

                            res.medicines.forEach(function (item) {
                                let p = item.product;
                                let d = item.distributor;
                                let key = p.id + '-' + d.id;

                                // Basic multiplier logic
                                let mul = 1;
                                if (item.unit === 'Box') mul = parseInt(p.box_size || 1);
                                if (item.unit === 'Carton') mul = parseInt(p.box_size || 1) * parseInt(p.carton_size || 1);

                                if (addedItems[key]) {
                                    addedItems[key].qty += parseInt(item.quantity);
                                } else {
                                    addedItems[key] = {
                                        id: p.id,
                                        distId: d.id,
                                        distName: d.user ? d.user.name : 'Unknown',
                                        name: p.product_name,
                                        price: parseFloat(p.ptr),
                                        qty: parseInt(item.quantity),
                                        unit: item.unit,
                                        multiplier: mul,
                                        box_size: p.box_size,
                                        carton_size: p.carton_size,
                                        is_count: item.unit === 'Nos'
                                    };
                                }
                            });
                            renderTable();
                        } else {
                            showToast('error', 'No medicines could be extracted or matched.');
                        }
                    },
                    error: function (xhr) {
                        let msg = 'OCR Extraction failed';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        showToast('error', msg);
                    },
                    complete: function () {
                        $('#btnUploadPrescription').prop('disabled', false);
                        $('#ocrLoader').addClass('d-none');
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
                                                                                                <div class="fw-bold text-dark font-outfit">${item.name}</div>
                                                                                                <div class="small text-muted text-uppercase" style="font-size: 0.65rem;">System Verified</div>
                                                                                                <input type="hidden" name="items[${key}][product_id]" value="${item.id}">
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
                if ($(this).hasClass('qty-change')) item.qty = parseInt($(this).val()) || 1;
                else {
                    let val = $(this).val();
                    let mul = 1;
                    if (val === 'Box') mul = parseInt(item.box_size || 1);
                    if (val === 'Carton') mul = parseInt(item.box_size || 1) * parseInt(item.carton_size || 1);
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
                            showToast('success', 'Retail Order Placed');
                            window.location.href = "{{ route('admin.retailer.index') }}";
                        } else {
                            showToast('error', 'Update Failed');
                            btn.prop('disabled', false).html('<i class="fa fa-check-circle me-2"></i> CONFIRM ORDER');
                        }
                    }
                });
            });
        });
    </script>
@endpush