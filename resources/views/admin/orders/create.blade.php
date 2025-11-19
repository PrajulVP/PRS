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
                            @if(Auth::user()->distributor)
                            <input type="hidden" name="distributor_id" value="{{ Auth::user()->distributor->id }}">
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
                                                data-stock="{{ $product->pivot->stock }}"
                                            @else
                                                data-stock="{{ $product->stock }}"
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
        var orderType = "{{ $orderType }}";

        $('#product_id').change(function() {
            var selectedOption = $(this).find('option:selected');
            var unitPrice = selectedOption.data('unit-price');
            var stock = selectedOption.data('stock');
            var productName = selectedOption.text().split(' (')[0];

            $('#display_unit_price').val(unitPrice || 0);
            $('#hidden_unit_price').val(unitPrice || 0);
            $('#available_stock').text(stock || 0);
            $('#hidden_product_name').val(productName || '');
            $('#quantity').attr('max', stock || 0);
        }).change();

        $('#quantity').on('input', function() {
            var max = parseInt($(this).attr('max'));
            var current = parseInt($(this).val());
            if (current > max) {
                $(this).val(max);
            }
        });
    });
</script>
@endpush
