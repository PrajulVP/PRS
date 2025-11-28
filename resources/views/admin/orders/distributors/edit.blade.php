@extends('layouts.admin')
@section('page-body')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10 p-4">
            <div class="card">
                <div class="card-header">
                    <h5>Edit Order #{{ $distributorOrder->order_code }}</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                    @endif

                    <form action="{{ route('distributor-orders.update', $distributorOrder->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @if(!Auth::user()->distributor)
                        <div class="mb-3">
                            <label for="distributor_select" class="form-label">Distributor</label>
                            <select class="form-select" id="distributor_select" name="distributor_id" required>
                                <option value="">Select Distributor</option>
                                @foreach($distributors as $distributor)
                                    <option value="{{ $distributor->id }}" {{ (old('distributor_id', $distributorOrder->distributor_id) == $distributor->id) ? 'selected' : '' }}>
                                        {{ $distributor->user->name }} {{ $distributor->company_name }}
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
                                    <tbody>
                                        <!-- Order items will be added here by JavaScript -->
                                    </tbody>
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

                        <div class="mb-3">
                            <label>Delivery Notes</label>
                            <textarea name="delivery_notes" class="form-control">{{ old('delivery_notes', $distributorOrder->delivery_notes) }}</textarea>
                        </div>

                        <button class="btn btn-success">Update Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var products = @json($products->keyBy('id'));
        var orderItems = {}; // Stores product_id => {product, quantity, unitPrice, total, order_item_id (if existing)}

        // Populate orderItems with existing order items
        @foreach($distributorOrder->items as $item)
            if (products[{{ $item->product_id }}]) {
                orderItems[{{ $item->product_id }}] = {
                    product: products[{{ $item->product_id }}],
                    quantity: {{ $item->quantity }},
                    unitPrice: parseFloat({{ $item->unit_price }}),
                    total: parseFloat({{ $item->total_amount }}),
                    order_item_id: {{ $item->id }} // Keep track of existing item ID
                };
                // Adjust stock for products already in the order
                products[{{ $item->product_id }}].stock += {{ $item->quantity }};
            }
        @endforeach


        function calculateGrandTotal() {
            let grandTotal = 0;
            for (const productId in orderItems) {
                grandTotal += orderItems[productId].total;
            }
            $('#grand_total').text(grandTotal.toFixed(2));
        }

        function renderOrderItemsTable() {
            $('#order_items_table tbody').empty();
            if (Object.keys(orderItems).length === 0) {
                $('#order_items_table tbody').append('<tr><td colspan="6" class="text-center">No products added yet.</td></tr>');
            } else {
                for (const productId in orderItems) {
                    const item = orderItems[productId];
                    const row = `
                        <tr data-product-id="${productId}">
                            <td>${item.product.product_name} (${item.product.product_code})
                                <input type="hidden" name="items[${productId}][product_id]" value="${productId}">
                                <input type="hidden" name="items[${productId}][order_item_id]" value="${item.order_item_id || ''}">
                            </td>
                            <td>${item.product.stock}</td>
                            <td>
                                <input type="number" name="items[${productId}][quantity]" class="form-control form-control-sm item-quantity" value="${item.quantity}" min="1" max="${item.product.stock}" data-product-id="${productId}" required>
                            </td>
                            <td>${item.unitPrice.toFixed(2)}</td>
                            <td>${item.total.toFixed(2)}</td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove-item-btn" data-product-id="${productId}">Remove</button>
                            </td>
                        </tr>
                    `;
                    $('#order_items_table tbody').append(row);
                }
            }
            calculateGrandTotal();
        }

        $('#add_product_btn').click(function() {
            const productId = $('#product_select').val();
            if (productId && !orderItems[productId]) {
                const product = products[productId];
                if (product) {
                    orderItems[productId] = {
                        product: product,
                        quantity: 1,
                        unitPrice: parseFloat(product.mrp),
                        total: parseFloat(product.mrp)
                    };
                    renderOrderItemsTable();
                    $('#product_select').val(''); // Clear selection
                }
            } else if (productId && orderItems[productId]) {
                alert('Product already added to order. You can adjust the quantity.');
            } else {
                alert('Please select a product to add.');
            }
        });

        $('#order_items_table').on('input', '.item-quantity', function() {
            const productId = $(this).data('product-id');
            let quantity = parseInt($(this).val());
            const max = parseInt($(this).attr('max'));

            if (isNaN(quantity) || quantity < 1) {
                quantity = 1;
                $(this).val(1);
            }
            if (quantity > max) {
                quantity = max;
                $(this).val(max);
            }

            if (orderItems[productId]) {
                orderItems[productId].quantity = quantity;
                orderItems[productId].total = quantity * orderItems[productId].unitPrice;
                renderOrderItemsTable(); // Re-render to update totals
            }
        });

        $('#order_items_table').on('click', '.remove-item-btn', function() {
            const productId = $(this).data('product-id');
            delete orderItems[productId];
            renderOrderItemsTable();
        });

        renderOrderItemsTable(); // Initial render
    });
</script>
@endpush
