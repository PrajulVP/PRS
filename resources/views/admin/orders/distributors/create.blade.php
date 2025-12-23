@extends('layouts.admin')

@section('page-body')


<div class="container-fluid">
    <form id="createOrderForm" method="POST" action="{{ route('admin.distributor-orders.store') }}">
        @csrf
        <input type="hidden" name="status" value="pending">

        {{-- Distributor Context --}}
        @if(Auth::user()->distributor)
        <input type="hidden" name="distributor_id" value="{{ Auth::user()->distributor->id }}">
        @endif


        {{-- Top Section: Product Details Preview --}}
        <div class="card mb-4 shadow-sm border-0" id="productDetailsCard" style="display: none;">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 text-primary">Selected Product Details</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <img id="previewImage" src="https://placehold.co/400x300?text=Product+Image" class="img-fluid rounded shadow-sm" style="max-height: 120px; object-fit: contain;">
                    </div>
                    <div class="col-md-10">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h4 id="previewName" class="fw-bold mb-0">Product Name</h4>
                            <div>
                                <span class="badge bg-success fs-6 me-2">MRP: ₹<span id="previewMrp">0.00</span></span>
                            </div>
                        </div>
                        <p class="text-muted small mb-3" id="previewGeneric">Generic Name</p>
                        <p class="text-muted small mb-1"><strong>Unit/Pack:</strong> <span id="previewPack">-</span></p>

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
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Search Product</label>
                        <select id="productSelect" class="form-select select2">
                            <option value="">Search for a product...</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->product_name }} - ₹{{ $p->mrp }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Quantity</label>
                        <input type="number" id="qtyInput" class="form-control" value="1" min="1">
                    </div>
                    <div class="col-md-3">
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
                        <thead class="table-light">
                            <tr>
                                <th width="50" class="ps-4">#</th>
                                <th class="ps-4">Product</th>
                                <th width="120">Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th width="80" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <tr id="emptyRow">
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa fa-shopping-basket fa-3x mb-3 text-light"></i><br>
                                    Your order list is empty. Start by adding products above.
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="border-top">
                            <tr>
                                <td colspan="4" class="text-end py-3"><strong class="fs-5">Grand Total:</strong></td>
                                <td colspan="2" class="py-3"><strong id="grandTotal" class="fs-5 text-primary">₹0.00</strong></td>
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
    $(document).ready(function() {
        // Init Select2
        $('.select2').select2({
            placeholder: "Select...",
            allowClear: true
        });

        var addedItems = {};
        var currentProductDetails = null;
        var lastAddedKey = null;

        // Product Logic
        $('#productSelect').on('select2:select', function(e) {
            let prodId = $(this).val();

            if (!prodId) return;

            // Show Details Card
            $('#productDetailsCard').show();

            // Reset Fields
            $('#previewImage').attr('src', 'https://placehold.co/400x300?text=Product+Image');
            $('#previewName').text('Loading...');
            $('#previewMrp').text('...');
            $('#previewGeneric').text('...');
            $('#previewPack').text('...');
            $('#previewHsn').text('...');
            $('#previewBox').text('...');

            // AJAX Fetch
            $.ajax({
                url: "{{ route('admin.distributor-orders.product-details', ':id') }}".replace(':id', prodId),
                type: 'GET',
                success: function(res) {
                    let details = res.product;
                    currentProductDetails = details;

                    $('#previewName').text(details.product_name);
                    $('#previewMrp').text(parseFloat(details.mrp).toFixed(2));
                    $('#previewGeneric').text(details.generic_name || 'N/A');
                    $('#previewPack').text(details.pack || '-');
                    $('#previewHsn').text(details.hsn_code || '-');
                    $('#previewBox').text(details.box_size || '-');
                },
                error: function() {
                    showToast('error', 'Failed to fetch product details');
                },
                complete: function() {
                    // $('#detailsLoader').hide(); // Removed
                }
            });

        });

        // Clear details on clear
        $('#productSelect').on('select2:clear', function(e) {
            $('#productDetailsCard').slideUp();
            currentProductDetails = null;
        });

        // Add Item Logic
        $('#btnAddItem').click(function() {
            let prodId = $('#productSelect').val();
            if (!prodId) {
                $('#productSelect').select2('open');
                return;
            }

            let qty = parseInt($('#qtyInput').val());
            if (qty < 1) return showToast('error', 'Invalid quantity');

            if (!currentProductDetails) return showToast('error', 'Product details not loaded');

            let key = prodId;

            if (addedItems[key]) {
                // Update existing
                addedItems[key].qty += qty;
                lastAddedKey = key;
            } else {
                // Add new
                addedItems[key] = {
                    key: key,
                    prodId: prodId,
                    prodName: currentProductDetails.product_name,
                    pack: currentProductDetails.pack || '-',
                    price: parseFloat(currentProductDetails.mrp),
                    qty: qty
                };
                lastAddedKey = key;
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
            $.each(addedItems, function(key, item) {
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
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm qty-change" data-key="${key}" value="${item.qty}" name="items[${key}][quantity]" style="width: 80px;">
                        </td>
                        <td>₹${item.price.toFixed(2)}</td>
                        <td>₹${lineTotal.toFixed(2)}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-xs remove-btn" data-key="${key}"><i class="fa fa-times"></i></button>
                        </td>
                    </tr>
                `);
            });

            lastAddedKey = null;

            if (!hasItems) {
                tbody.append('<tr id="emptyRow"><td colspan="6" class="text-center text-muted">No items added yet</td></tr>');
                $('#btnSubmitOrder').prop('disabled', true);
            } else {
                $('#btnSubmitOrder').prop('disabled', false);
            }

            $('#grandTotal').text('₹' + total.toFixed(2));
            $('#itemCount').text(Object.keys(addedItems).length);
        }

        // Qty Change in List
        $(document).on('change', '.qty-change', function() {
            let key = $(this).data('key');
            let val = parseInt($(this).val());
            if (val < 1) val = 1;
            addedItems[key].qty = val;
            renderTable();
        });

        // Remove Item
        $(document).on('click', '.remove-btn', function() {
            let key = $(this).data('key');
            let row = $(this).closest('tr');

            // Add red fade out class
            row.addClass('remove-row');

            // Wait for animation to finish then remove
            setTimeout(function() {
                delete addedItems[key];
                renderTable();
            }, 500);
        });

        // Submit Form
        $('#createOrderForm').submit(function(e) {
            e.preventDefault();
            let form = $(this);
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(res) {
                    if (res.success) {
                        showToast('success', res.success);
                        setTimeout(() => {
                            window.location.href = "{{ route('admin.distributor-orders.index') }}";
                        }, 1000);
                    } else {
                        showToast('error', 'Failed to create order');
                    }
                },
                error: function(xhr) {
                    let err = 'Error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.message) err = xhr.responseJSON.message;
                    if (xhr.responseJSON && xhr.responseJSON.error) err = xhr.responseJSON.error;
                    showToast('error', err);
                }
            });
        });
    });
</script>
@endpush