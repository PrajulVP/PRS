@extends('layouts.admin')
@section('page-body')
    <!-- <div class="container-fluid">
                                                                                            <div class="page-title">
                                                                                                <div class="row">
                                                                                                    <div class="col-6">
                                                                                                        <h3>Create New Order</h3>
                                                                                                    </div>
                                                                                                    <div class="col-6">
                                                                                                        <ol class="breadcrumb">
                                                                                                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i data-feather="home"></i></a></li>
                                                                                                            <li class="breadcrumb-item"><a href="{{ route('admin.retailer-orders.index') }}">Orders</a></li>
                                                                                                            <li class="breadcrumb-item active">Create</li>
                                                                                                        </ol>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div> -->

    <div class="container-fluid">
        <form id="createOrderForm" method="POST" action="{{ route('admin.retailer-orders.store') }}">
            @csrf
            @if(Auth::user()->retailer)
                <input type="hidden" name="retailer_id" id="retailer_id" value="{{ Auth::user()->retailer->id }}">
            @else
                {{-- For Admin/Manager: Select Retailer --}}
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0"><i class="fa fa-user me-2"></i>Select Retailer</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <select name="retailer_id" id="retailer_id" class="form-select select2" required>
                                    <option value="">Search for a retailer...</option>
                                    @foreach($retailers as $r)
                                        <option value="{{ $r->id }}">{{ $r->user->name }} - {{ $r->user->contact_no }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <input type="hidden" name="status" value="pending">

            {{-- Top Section: Product Details Preview --}}
            <div class="card mb-4 shadow-sm border-0" id="productDetailsCard" style="display: none;">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title mb-0 text-primary">Selected Product Details</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <img id="previewImage" src="https://placehold.co/400x300?text=Product+Image"
                                class="img-fluid rounded shadow-sm" style="max-height: 120px; object-fit: contain;">
                        </div>
                        <div class="col-md-10">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h4 id="previewName" class="fw-bold mb-0">Product Name</h4>
                                <div>
                                    <span class="badge bg-success fs-6 me-2"><span id="previewLabel">PTR</span>: ₹<span
                                            id="previewMrp">0.00</span></span>
                                </div>
                            </div>
                            <p class="text-muted small mb-3" id="previewGeneric">Generic Name</p>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="p-2 border rounded bg-light text-center">
                                        <small class="text-muted d-block text-uppercase fw-bold">Pack</small>
                                        <span id="previewPack" class="fw-bold">-</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 border rounded bg-light text-center">
                                        <small class="text-muted d-block text-uppercase fw-bold">HSN Code</small>
                                        <span id="previewHsn" class="fw-bold">-</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 border rounded bg-light text-center">
                                        <small class="text-muted d-block text-uppercase fw-bold">Box Size</small>
                                        <span id="previewBox" class="fw-bold">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Middle Section: Input Area --}}
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title mb-0"><i class="fa fa-cart-plus me-2"></i>Add Items to Order</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Search Product</label>
                            <select id="productSelect" class="form-select select2">
                                <option value="">Search for a product...</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->product_name }} - ₹{{ $p->ptr }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Select Distributor</label>
                            <select id="distributorSelect" class="form-select select2">
                                <option value="">Waiting for Product...</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Quantity</label>
                            <div class="input-group">
                                <input type="number" id="qtyInput" class="form-control" value="1" min="1">
                                <select class="form-select input-group-text" id="unitSelect" style="max-width: 100px;">
                                    <option value="Strips">Strips</option>
                                    <option value="Carton">Carton</option>
                                    <option value="Box">Box</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary w-100 fw-bold py-2" id="btnAddItem">
                                <i class="fa fa-plus-circle me-1"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom Section: Order List --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fa fa-list-alt me-2"></i>Order Summary</h5>
                    <span class="badge bg-light text-dark border">Items: <span id="itemCount">0</span></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="orderTable">
                            <thead class="table">
                                <tr>
                                    <th width="50" class="ps-4">#</th>
                                    <th>Product</th>
                                    <th>Distributor</th>
                                    <th width="120">Qty</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                    <th width="80" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                <tr id="emptyRow">
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fa fa-shopping-basket fa-3x mb-3 text-light"></i><br>
                                        Your order cart is empty. Start by adding products above.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="border-top">
                                <tr>
                                    <td colspan="5" class="text-end py-3"><strong class="fs-5">Grand Total:</strong></td>
                                    <td colspan="2" class="py-3"><strong id="grandTotal"
                                            class="fs-5 text-primary">₹0.00</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>


                </div>
                <div class="card-footer bg-white p-3 text-end">
                    <button type="submit" class="btn btn-success btn-lg px-5 fw-bold" id="btnSubmitOrder" disabled>
                        <i class="fa fa-check-circle me-2"></i>Place Order
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .select2-container .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-left: 12px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        /* Row Animation */
        @keyframes fadeInHighlight {
            0% {
                background-color: #d1e7dd;
                opacity: 0;
                transform: translateY(-10px);
            }

            100% {
                background-color: transparent;
                opacity: 1;
                transform: translateY(0);
            }
        }

        .new-row {
            animation: fadeInHighlight 0.5s ease-out forwards;
        }

        @keyframes fadeOutRed {
            0% {
                background-color: transparent;
                opacity: 1;
                transform: translateX(0);
            }

            30% {
                background-color: #f8d7da;
                transform: translateX(-5px);
            }

            100% {
                background-color: #f8d7da;
                opacity: 0;
                transform: translateX(100%);
            }
        }

        .remove-row {
            animation: fadeOutRed 0.5s ease-in forwards;
        }
    </style>

    <script>
        $(document).ready(function () {
            // Init Select2 for products
            $('#productSelect').select2({
                placeholder: "Search for a product...",
                allowClear: true
            });

            // Init Select2 for distributors
            $('#distributorSelect').select2({
                placeholder: "Select a distributor...",
                templateResult: formatDistributor,
                templateSelection: formatDistributor,
                escapeMarkup: function (m) {
                    return m;
                }
            });

            function formatDistributor(opt) {
                if (!opt.id) return opt.text;

                let el = $(opt.element);
                let stock = el.data('stock-raw');
                let distance = el.data('distance');

                if (stock !== undefined) {
                    let stockBadgeClass = stock > 0 ? 'bg-success' : 'bg-danger';
                    let distBadge = (distance && distance !== 'null') ? `<span class="badge bg-light text-dark border me-1"><i class="fa fa-map-marker-alt text-primary me-1"></i>${distance} km</span>` : '';

                    return $(`
                                                                                                            <div class="d-flex justify-content-between align-items-center">
                                                                                                                <span>${opt.text}</span>
                                                                                                                <div class="d-flex align-items-center">
                                                                                                                    ${distBadge}
                                                                                                                    <span class="badge ${stockBadgeClass} rounded-pill" style="min-width: 30px;">${stock}</span>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        `);
                }
                return opt.text;
            }

            var addedItems = {};
            var currentProductDetails = null;
            var lastAddedKey = null;

            // Product Logic
            $('#productSelect').on('select2:select', function (e) {
                let prodId = $(this).val();

                if (!prodId) return;

                // Show Details Card
                $('#productDetailsCard').show();
                $('#distributorSelect').empty().append('<option value="">Loading...</option>');

                // Reset Fields
                $('#previewImage').attr('src', 'https://placehold.co/400x300?text=Product+Image');
                $('#previewName').text('Loading...');
                $('#previewMrp').text('...');
                $('#previewGeneric').text('...');
                $('#previewPack').text('...');
                $('#previewHsn').text('...');
                $('#previewBox').text('...');

                // AJAX Fetch
                let retailerId = $('#retailer_id').val();
                if (!retailerId) {
                    showToast('error', 'Please select a retailer first');
                    $('#productSelect').val(null).trigger('change');
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.retailer-orders.product-details', ':id') }}".replace(':id', prodId),
                    type: 'GET',
                    data: {
                        retailer_id: retailerId
                    },
                    success: function (res) {
                        let details = res.product;
                        let distributors = res.distributors;
                        currentProductDetails = details;

                        $('#previewName').text(details.product_name);
                        // Show PTR as the primary price for Retailers
                        $('#previewMrp').text(parseFloat(details.ptr).toFixed(2));
                        $('#previewLabel').text('PTR');
                        $('#previewGeneric').text(details.generic_name || 'N/A');
                        $('#previewPack').text(details.pack || '-');
                        $('#previewHsn').text(details.hsn_code || '-');
                        $('#previewBox').text(details.box_size || '-');

                        // Auto-select unit if matches, else default
                        let unit = details.pack || 'Box';
                        let found = false;
                        $('#unitSelect option').each(function () {
                            if ($(this).val().toLowerCase() === unit.toLowerCase()) {
                                $('#unitSelect').val($(this).val());
                                found = true;
                                return false;
                            }
                        });
                        if (!found) $('#unitSelect').val('Box');

                        // Distributors
                        let distSelect = $('#distributorSelect');
                        distSelect.empty();
                        if (distributors && distributors.length > 0) {
                            distributors.forEach((d) => {
                                let name = d.user ? d.user.name : 'Distributor ' + d.id;
                                let distance = d.distance ? parseFloat(d.distance).toFixed(2) : null;
                                let stock = d.pivot ? d.pivot.stock : 0;
                                distSelect.append(`<option value="${d.id}" data-stock-raw="${stock}" data-distance="${distance}">${name}</option>`);
                            });
                            // Select first (closest)
                            let opts = distSelect.find('option');
                            if (opts.length > 0) {
                                opts.eq(0).prop('selected', true);
                            }
                        } else {
                            distSelect.append('<option value="">No Distributor Available</option>');
                        }
                    },
                    error: function () {
                        showToast('error', 'Failed to fetch product details');
                    },
                    complete: function () {
                        // $('#detailsLoader').hide(); // Removed
                    }
                });
            });

            // Clear details on clear
            $('#productSelect').on('select2:clear', function (e) {
                $('#productDetailsCard').slideUp();
                $('#distributorSelect').empty().append('<option value="">Select Product First</option>');
                currentProductDetails = null;
            });

            // Add Item Logic
            $('#btnAddItem').click(function () {
                let prodId = $('#productSelect').val();
                if (!prodId) {
                    $('#productSelect').select2('open');
                    return;
                }

                let distId = $('#distributorSelect').val();
                if (!distId) return showToast('error', 'No distributor selected');

                let qty = parseInt($('#qtyInput').val());
                let unit = $('#unitSelect').val(); // Capture Unit
                if (qty < 1) return showToast('error', 'Invalid quantity');

                if (!currentProductDetails) return showToast('error', 'Product details not loaded');

                let distName = $('#distributorSelect option:selected').text();

                let key = prodId + '-' + distId;

                if (addedItems[key]) {
                    // Update existing
                    addedItems[key].qty += qty;
                    addedItems[key].unit = unit; // Update unit
                    lastAddedKey = key; // Mark for highlight
                } else {
                    // Add new
                    addedItems[key] = {
                        key: key,
                        prodId: prodId,
                        prodName: currentProductDetails.product_name,
                        pack: currentProductDetails.pack || '-',
                        distId: distId,
                        distName: distName,
                        price: parseFloat(currentProductDetails.ptr), // Use PTR
                        qty: qty,
                        unit: unit // Store Unit
                    };
                    lastAddedKey = key; // Mark for highlight
                }

                renderTable();
                // Reset Input
                $('#productSelect').val(null).trigger('change');
                $('#qtyInput').val(1);
            });

            // Render Table
            function renderTable() {
                let tbody = $('#orderTable tbody');
                tbody.empty();
                let total = 0;
                let hasItems = false;

                let index = 1;
                $.each(addedItems, function (key, item) {
                    hasItems = true;
                    let lineTotal = item.qty * item.price;
                    total += lineTotal;

                    let rowClass = (key === lastAddedKey) ? 'new-row' : '';

                    tbody.append(`
                                                                                                            <tr class="${rowClass}">
                                                                                                                <td class="ps-4 text-muted">${index++}</td>
                                                                                                                <td>
                                                                                                                    <div class="fw-bold">${item.prodName}</div>
                                                                                                                    <div class="small text-muted">Unit: ${item.pack}</div>
                                                                                                                    <input type="hidden" name="items[${key}][product_id]" value="${item.prodId}">
                                                                                                                    <input type="hidden" name="items[${key}][distributor_id]" value="${item.distId}">
                                                                                                                </td>
                                                                                                                <td>${item.distName}</td>
                                                                                                                <td>
                                                                                                                    <div class="input-group input-group-sm" style="width: 150px;">
                                                                                                                        <input type="number" class="form-control qty-change" data-key="${key}" value="${item.qty}" name="items[${key}][quantity]" min="1">
                                                                                                                        <select class="form-select unit-change" data-key="${key}" name="items[${key}][unit]" style="max-width: 80px;">
                                                                                                                            <option value="Strips" ${item.unit === 'Strips' ? 'selected' : ''}>Strips</option>
                                                                                                                            <option value="Carton" ${item.unit === 'Carton' ? 'selected' : ''}>Carton</option>
                                                                                                                            <option value="Box" ${item.unit === 'Box' ? 'selected' : ''}>Box</option>
                                                                                                                        </select>
                                                                                                                    </div>
                                                                                                                </td>
                                                                                                                <td>₹${item.price.toFixed(2)}</td>
                                                                                                                <td>₹${lineTotal.toFixed(2)}</td>
                                                                                                                <td>
                                                                                                                    <button type="button" class="btn btn-danger btn-xs remove-btn" data-key="${key}"><i class="fa fa-times"></i></button>
                                                                                                                </td>
                                                                                                            </tr>
                                                                                                        `);
                });

                lastAddedKey = null; // Reset highlight

                if (!hasItems) {
                    tbody.append('<tr id="emptyRow"><td colspan="7" class="text-center text-muted">No items added yet</td></tr>');
                    $('#btnSubmitOrder').prop('disabled', true);
                } else {
                    $('#btnSubmitOrder').prop('disabled', false);
                }

                $('#grandTotal').text('₹' + total.toFixed(2));
                $('#itemCount').text(Object.keys(addedItems).length);
            }

            // Qty Change in List
            $(document).on('change', '.qty-change', function () {
                let key = $(this).data('key');
                let val = parseInt($(this).val());
                if (val < 1) val = 1;
                addedItems[key].qty = val;
                renderTable();
            });

            // Unit Change in List
            $(document).on('change', '.unit-change', function () {
                let key = $(this).data('key');
                let val = $(this).val();
                addedItems[key].unit = val;
                renderTable();
            });

            // Remove Item
            $(document).on('click', '.remove-btn', function () {
                let key = $(this).data('key');
                let row = $(this).closest('tr');

                // Add red fade out class
                row.addClass('remove-row');

                // Wait for animation to finish then remove
                setTimeout(function () {
                    delete addedItems[key];
                    renderTable();
                }, 500);
            });

            // Submit Form
            $('#createOrderForm').submit(function (e) {
                e.preventDefault();
                let form = $(this);
                let btn = $('#btnSubmitOrder');

                // Prevent double submission
                if (btn.prop('disabled')) return;

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i> Placing Order...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function (res) {
                        if (res.success) {
                            showToast('success', res.success);
                            setTimeout(() => {
                                window.location.href = "{{ route('admin.retailer-orders.index') }}";
                            }, 1000);
                        } else {
                            showToast('error', 'Failed to create order');
                            btn.prop('disabled', false).html('<i class="fa fa-check-circle me-2"></i>Place Order');
                        }
                    },
                    error: function (xhr) {
                        let err = 'Error occurred';
                        if (xhr.responseJSON && xhr.responseJSON.message) err = xhr.responseJSON.message;
                        if (xhr.responseJSON && xhr.responseJSON.error) err = xhr.responseJSON.error;
                        showToast('error', err);
                        btn.prop('disabled', false).html('<i class="fa fa-check-circle me-2"></i>Place Order');
                    }
                });
            });
        });
    </script>
@endpush