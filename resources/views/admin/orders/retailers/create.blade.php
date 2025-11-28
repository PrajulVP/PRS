@extends('layouts.admin')
@section('page-body')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10 p-4">
            <div class="card">
                <div class="card-header">
                    <h5>Create {{ ucfirst($orderType) }} Order</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                    @endif

                    <form action="{{ $orderType == 'retailer' ? route('retailer.orders.store') : route('distributor-orders.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        @if($orderType == 'distributor')
                            @if(!Auth::user()->distributor)
                            <div class="mb-3">
                                <label for="distributor_select" class="form-label">Select Distributor</label>
                                <select class="form-select" id="distributor_select" name="distributor_id" required>
                                    <option value="">Select Distributor</option>
                                    @foreach($distributors as $distributor)
                                        <option value="{{ $distributor->id }}" {{ (old('distributor_id') == $distributor->id) ? 'selected' : '' }}>
                                            {{ $distributor->user->name }} ({{ $distributor->company_name }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('distributor_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            @else
                            <input type="hidden" name="distributor_id" value="{{ Auth::user()->distributor->id }}">
                            @endif
                        @endif

                        <div class="mb-3">
                            <label for="product_select">Add Product to Order</label>
                            <div class="input-group">
                                <select id="product_select" class="form-control">
                                    <option value="">Select a product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}"
                                                data-unit-price="{{ $product->mrp }}"
                                                data-stock="{{ $product->stock }}">
                                            {{ $product->product_name }} ({{ $product->product_code }}) - Stock: {{ $product->stock }} units
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-primary" id="add_product_btn">Add Product</button>
                            </div>
                            @error('items')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="order_items_table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Available Stock</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-end">Grand Total:</th>
                                            <th id="grand_total">0.00</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        @if($orderType == 'retailer')
                        <div class="mb-3">
                            <label for="prescription_photo" class="form-label">Doctor's Prescription Photo (Optional)</label>
                            <input class="form-control" type="file" id="prescription_photo" name="prescription_photo">
                        </div>
                        @endif

                        <div class="mb-3">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label>Delivery Notes</label>
                            <textarea name="delivery_notes" class="form-control">{{ old('delivery_notes') }}</textarea>
                        </div>

                        <button class="btn btn-success">Create {{ ucfirst($orderType) }} Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@push('scripts')
<script>
$(document).ready(function() {

    var products = @json($products->keyBy('id'));
    var orderItems = {};

    // Restore old items
    @if(old('items'))
        @foreach(old('items') as $productId => $itemData)
            if (products[{{ $productId }}]) {
                orderItems[{{ $productId }}] = {
                    product: products[{{ $productId }}],
                    quantity: {{ $itemData['quantity'] }},
                    unitPrice: parseFloat(products[{{ $productId }}].mrp),
                    total: {{ $itemData['quantity'] }} * parseFloat(products[{{ $productId }}].mrp)
                };
            }
        @endforeach
    @endif

    function calculateGrandTotal() {
        let grandTotal = 0;
        for (const productId in orderItems) {
            grandTotal += orderItems[productId].total;
        }
        $('#grand_total').text(grandTotal.toFixed(2));
    }

    function renderOrderItemsTable() {
        const tbody = $('#order_items_table tbody');
        tbody.empty();

        if (Object.keys(orderItems).length === 0) {
            tbody.append('<tr><td colspan="6" class="text-center">No products added yet.</td></tr>');
            return;
        }

        for (const productId in orderItems) {
            const item = orderItems[productId];

            const row = `
                <tr data-product-id="${productId}">
                    <td>${item.product.product_name} (${item.product.product_code})
                        <input type="hidden" name="items[${productId}][product_id]" value="${productId}">
                    </td>
                    <td>${item.product.stock}</td>

                    <td>
                        <div class="input-group input-group-sm" style="width:130px;">
                            <button type="button" class="btn btn-outline-secondary qty-minus" data-product-id="${productId}">-</button>

                            <input type="number"
                                name="items[${productId}][quantity]"
                                class="form-control text-center item-quantity"
                                value="${item.quantity}"
                                min="1"
                                max="${item.product.stock}"
                                data-product-id="${productId}"
                                step="1"
                                required>

                            <button type="button" class="btn btn-outline-secondary qty-plus" data-product-id="${productId}">+</button>
                        </div>
                    </td>

                    <td>${item.unitPrice.toFixed(2)}</td>
                    <td class="item-total">${item.total.toFixed(2)}</td>

                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-item-btn" data-product-id="${productId}">
                            Remove
                        </button>
                    </td>
                </tr>
            `;

            tbody.append(row);
        }

        calculateGrandTotal();
    }

    // Add product
    $('#add_product_btn').click(function() {
        const productId = $('#product_select').val();
        if (!productId) return alert('Please select a product.');
        if (orderItems[productId]) return alert('Product already added.');

        const product = products[productId];
        orderItems[productId] = {
            product: product,
            quantity: 1,
            unitPrice: parseFloat(product.mrp),
            total: parseFloat(product.mrp)
        };

        renderOrderItemsTable();
        $('#product_select').val('');
    });

    // + Button
    $('#order_items_table').on('click', '.qty-plus', function () {
        const productId = $(this).data('product-id');
        let input = $(`input[data-product-id="${productId}"]`);
        let qty = parseInt(input.val()) || 1;
        let max = parseInt(input.attr('max'));

        if (qty < max) {
            qty++;
            input.val(qty);
            updateQty(productId, qty);
        }
    });

    // - Button
    $('#order_items_table').on('click', '.qty-minus', function () {
        const productId = $(this).data('product-id');
        let input = $(`input[data-product-id="${productId}"]`);
        let qty = parseInt(input.val()) || 1;

        if (qty > 1) {
            qty--;
            input.val(qty);
            updateQty(productId, qty);
        }
    });

    // On typing (input event allows backspace)
    $('#order_items_table').on('input', '.item-quantity', function () {
        const productId = $(this).data('product-id');
        let value = $(this).val();

        // Allow empty while typing
        if (value === "") return;

        let qty = parseInt(value);
        let max = parseInt($(this).attr('max'));

        if (qty > max) qty = max;
        if (qty < 1) qty = 1;

        $(this).val(qty);
        updateQty(productId, qty);
    });

    // Update item qty & totals **without re-rendering full table**
    function updateQty(productId, qty) {
        orderItems[productId].quantity = qty;
        orderItems[productId].total = qty * orderItems[productId].unitPrice;

        // update only total column (no full rerender)
        $(`tr[data-product-id="${productId}"] .item-total`).text(orderItems[productId].total.toFixed(2));

        calculateGrandTotal();
    }

    // Remove item
    $('#order_items_table').on('click', '.remove-item-btn', function() {
        const productId = $(this).data('product-id');
        delete orderItems[productId];
        renderOrderItemsTable();
    });

    renderOrderItemsTable();
});
</script>
@endpush

@endpush
