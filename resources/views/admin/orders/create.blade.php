@extends('layouts.admin')
@section('page-body')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 p-4">
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

                    <form action="{{ $orderType == 'retailer' ? route('retailer.orders.store') : route('distributor-bulk-orders.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        @if($orderType == 'distributor')
                            @if(isset($authenticatedDistributorId))
                                <input type="hidden" name="distributor_id" value="{{ $authenticatedDistributorId }}">
                            @else
                                <div class="mb-3">
                                    <label for="distributor_id">Distributor</label>
                                    <select name="distributor_id" id="distributor_id" class="form-control" required>
                                        <option value="">Select a distributor</option>
                                        @foreach($distributors as $distributor)
                                            <option value="{{ $distributor->id }}" {{ old('distributor_id') == $distributor->id ? 'selected' : '' }}>
                                                {{ $distributor->user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        @endif

                        <div class="mb-3">
                            <label for="product_id">Product</label>
                            <select name="product_id" id="product_id" class="form-control" required>
                                <option value="">Select a product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                            data-unit-price="{{ $product->mrp }}"
                                            @if($orderType == 'retailer')
                                                data-stock="{{ floor($product->pivot->stock / ($product->pack_quantity ?: 1)) }}"
                                            @else
                                                data-stock="{{ floor($product->stock / ($product->pack_quantity ?: 1)) }}"
                                            @endif
                                            {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->product_name }} ({{ $product->product_code }}) - Stock: 
                                        @if($orderType == 'retailer')
                                            {{ $product->pivot->stock }} units
                                        @else
                                            {{ $product->stock }} units
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="product_name" id="hidden_product_name">
                            <input type="hidden" name="unit_price" id="hidden_unit_price">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="quantity">Quantity (units)</label>
                                <input type="number" name="quantity" id="quantity" class="form-control" value="{{ old('quantity',1) }}" min="1" required>
                                <small class="form-text text-muted">Available Stock: <span id="available_stock">0</span> units</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="display_unit_price">Unit Price (MRP)</label>
                                <input type="text" id="display_unit_price" class="form-control" value="{{ old('unit_price',0) }}" readonly>
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

                        <button class="btn btn-success">Create {{ ucfirst($orderType) }} Order</button>
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
        var products = {!! json_encode($products->keyBy('id')) !!};
        var orderType = "{{ $orderType }}"; // Get order type from Blade

        $('#product_id').change(function() {
            var productId = $(this).val();
            var selectedProduct = products[productId];

            if (selectedProduct) {
                var stock = (orderType === 'retailer' && selectedProduct.pivot) ? selectedProduct.pivot.stock : selectedProduct.stock;
                
                $('#display_unit_price').val(selectedProduct.mrp);
                $('#hidden_unit_price').val(selectedProduct.mrp);
                $('#available_stock').text(stock);
                $('#hidden_product_name').val(selectedProduct.product_name);

                if (orderType === 'retailer') {
                    $('#quantity').attr('max', stock);
                } else {
                    $('#quantity').removeAttr('max');
                }

            } else {
                $('#display_unit_price').val('0');
                $('#hidden_unit_price').val('0');
                $('#available_stock').text('0');
                $('#hidden_product_name').val('');
                $('#quantity').removeAttr('max');
            }
        }).change();

        $('#quantity').on('input', function() {
            if (orderType === 'retailer') {
                var max = parseInt($(this).attr('max'));
                var current = parseInt($(this).val());
                if (current > max) {
                    $(this).val(max);
                }
            }
        });
    });
</script>
@endpush
