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
                <div class="col-xl-8 col-lg-7">

                    {{-- 1. Input Section --}}
                    <div class="card shadow-sm border-0 mb-4 builder-main-card rounded-3">
                        <div class="card-body p-4">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-7">
                                    <label class="form-label fw-bold text-muted small text-uppercase mb-2">Search
                                        Product</label>
                                    <select id="productSelect" class="form-select select2">
                                        <option value="">Search by Name or POS Code...</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}">{{ $p->product_name }} - ₹{{ $p->pts }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12 my-2" id="variantWrapper" style="display: none;">
                                    <label class="form-label fw-bold text-muted small text-uppercase mb-2">Select Size / Variant</label>
                                    <div id="sizeSelector" class="d-flex flex-wrap gap-2">
                                        {{-- Size buttons will be injected here --}}
                                    </div>
                                    <input type="hidden" id="variantValue" value="">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold text-muted small text-uppercase mb-2">Quantity</label>
                                    <div class="input-group">
                                        <input type="number" id="qtyInput" class="form-control fw-bold rounded-start"
                                            value="1" min="1">
                                        <select
                                            class="form-select input-group-text bg-light-soft border-start-0 font-outfit rounded-end"
                                            id="unitSelect" style="max-width: 130px;">
                                            <option value="Carton">Carton</option>
                                            <option value="Box">Box</option>
                                            <option value="Strips">Strips</option>
                                            <option value="Nos">Nos</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 align-self-end text-end">
                                    <button type="button"
                                        class="btn btn-primary w-100 fw-bold py-2 shadow-sm font-outfit rounded-3"
                                        style="padding-top: 10px; padding-bottom: 10px;"
                                        id="btnAddItem">
                                        <i class="fa fa-plus me-1"></i> ADD
                                    </button>
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
                                                id="previewGeneric">Generic Name</p>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-muted fw-bold small text-uppercase d-block mb-1"
                                                id="ptsLabel">PTS (Per
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
                                                    style="font-size: 0.6rem;">Offer / Disc %</small>
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

                {{-- Right Column: Final Order Recap --}}
                <div class="col-xl-4 col-lg-5">
                    <div class="sticky-top" style="top: 20px; z-index: 5;">
                        <div class="card shadow-lg border-0 summary-card rounded-3">
                            <div class="card-header bg-dark text-white py-3">
                                <h5 class="card-title mb-0 fw-bold"><i class="fa fa-receipt me-2"></i>Order Recap</h5>
                            </div>
                            <div class="card-body p-4">
                                <div
                                    class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-light-dark">
                                    <div>
                                        <span class="text-dark fw-bold d-block h5 mb-0 label-font">Total Value (PTS)</span>
                                        <small class="text-muted"><i class="fa fa-info-circle text-warning"></i> GST & other
                                            charges will be calculated on the invoice</small>
                                    </div>
                                    <span id="grandTotal" class="h3 fw-bold text-primary mb-0 font-outfit">₹0.00</span>
                                </div>

                                <button type="submit"
                                    class="btn btn-success btn-lg w-100 py-2 fw-bold shadow-sm font-outfit btn-confirm rounded-3"
                                    id="btnSubmitOrder" disabled>
                                    <i class="fa fa-check-double me-2"></i> CONFIRM ORDER
                                </button>

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

                        // Updated Unit Logic: If product_code exists -> Nos, otherwise -> Strips
                        let isCount = !!(p.product_code && p.product_code.trim() !== '');
                        p.is_count = isCount;
                        currentProductDetails = p;

                        // Check for Variants in Product Name e.g. (S/M/L)
                        let pNameVar = p.product_name ? p.product_name : '';
                        let variantMatch = pNameVar.match(/\(([^)]*\/[^)]*)\)/);
                        
                        // Default sizes user requested
                        const standardSizes = ['S', 'M', 'L', 'XL', 'XXL', '3XL'];
                        let variants = [];

                        if (variantMatch) {
                            variants = variantMatch[1].split(/[/\,]/).map(v => v.trim());
                        } else if (pNameVar.toLowerCase().includes('size') || pNameVar.toLowerCase().includes('collar') || pNameVar.toLowerCase().includes('cap') || pNameVar.toLowerCase().includes('splint')) {
                            // If it matches common sized items but no specific brackets, show standard
                            variants = standardSizes;
                        }

                        if (variants.length > 0) {
                            let $sizeSel = $('#sizeSelector');
                            $sizeSel.empty();
                            variants.forEach(v => {
                                $sizeSel.append(`<button type="button" class="btn btn-outline-primary size-btn px-3 py-2 fw-bold" data-size="${v}">${v}</button>`);
                            });
                            $('#variantWrapper').fadeIn(200);
                            $('#variantValue').val(''); // Reset
                        } else {
                            $('#variantWrapper').hide();
                            $('#variantValue').val('');
                        }

                        let $unitSelect = $('#unitSelect');
                        $unitSelect.empty();
                        if (isCount) {
                            $unitSelect.append('<option value="Nos">Nos</option>');
                            $('#ptsLabel').text("PTS (Per Nos)");
                        } else {
                            $unitSelect.append('<option value="Strips">Strips</option>');
                            $unitSelect.append('<option value="Box">Box</option>');
                            $unitSelect.append('<option value="Carton">Carton</option>');
                            $unitSelect.append('<option value="Nos">Nos</option>'); // Added manual override
                            $('#ptsLabel').text("PTS (Per Strip)");
                        }

                        $('#previewName').text(p.product_name);
                        $('#previewMrp').text(parseFloat(p.pts || 0).toFixed(2));
                        $('#previewGeneric').text(p.generic_name || 'Generic Name N/A');
                        $('#previewCode').text('Product Code: ' + (p.product_code || '---'));
                        let offerDiscText = (parseFloat(p.offer || 0) + '% / ' + parseFloat(p.discount || 0) + '%');
                        $('#previewOfferDisc').text(offerDiscText);
                        $('#previewHsn').text(p.hsn_code || '---');
                        $('#previewBox').text((p.box_size || '1') + ' x ' + (p.carton_size || '1'));

                        if (p.image) $('#previewImage').attr('src', "{{ asset('storage') }}/" + p.image);
                        else $('#previewImage').attr('src', "https://placehold.co/400x400?text=No+Photo");
                    }
                });
            });

            $('#productSelect').on('select2:clear', () => {
                $('#productDetailsCard').fadeOut(200); currentProductDetails = null;
            });

            $('#btnAddItem').click(function () {
                let prodId = $('#productSelect').val();
                if (!prodId || !currentProductDetails) return showToast('info', 'Please search/select a product first');

                let qty = parseInt($('#qtyInput').val());
                let unit = $('#unitSelect').val();
                let variant = $('#variantWrapper').is(':visible') ? $('#variantValue').val() : null;

                if ($('#variantWrapper').is(':visible') && !variant) {
                    return showToast('warning', 'Please select a size/variant first');
                }

                if (qty < 1) return;

                let key = prodId + (variant ? '-' + variant : '');
                let mul = 1;
                if (unit === 'Box') mul = parseInt(currentProductDetails.box_size || 1);
                if (unit === 'Carton') mul = parseInt(currentProductDetails.box_size || 1) * parseInt(currentProductDetails.carton_size || 1);

                if (addedItems[key]) {
                    addedItems[key].qty += qty;
                    addedItems[key].unit = unit;
                    addedItems[key].multiplier = mul;
                } else {
                    addedItems[key] = {
                        id: prodId, name: currentProductDetails.product_name,
                        variant: variant,
                        price: parseFloat(currentProductDetails.pts),
                        qty: qty, unit: unit, multiplier: mul,
                        box_size: currentProductDetails.box_size,
                        carton_size: currentProductDetails.carton_size,
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
                                                                                        ${item.name} ${item.variant ? `<span class="badge bg-primary ms-1">${item.variant}</span>` : ''}
                                                                                    </div>
                                                                                    <input type="hidden" name="items[${key}][product_id]" value="${item.id}">
                                                                                    ${item.variant ? `<input type="hidden" name="items[${key}][variant]" value="${item.variant}">` : ''}
                                                                                </td>
                                                                                <td class="text-center">
                                                                                    <div class="input-group input-group-sm mx-auto" style="max-width: 160px;">
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
                            showToast('success', 'Order Placed Successfully');
                            window.location.href = "{{ route('admin.distributor-orders.index') }}";
                        } else {
                            showToast('error', 'Error in submission');
                            btn.prop('disabled', false).html('<i class="fa fa-check-double me-2"></i> CONFIRM ORDER');
                        }
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