@extends('layouts.admin')

@section('page-body')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header text-white">
                    <h5 class="mb-0">Create Product</h5>
                </div>
                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('products.store') }}" method="POST" class="p-3 border rounded">
                        @csrf
                        <div class="row g-4"> {{-- g-4 adds uniform spacing between fields --}}
                            {{-- Left Column --}}
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_code" class="form-label">Product Code</label>
                                    <input type="text" name="product_code" id="product_code" class="form-control" value="{{ old('product_code') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="product_name" class="form-label">Product Name</label>
                                    <input type="text" name="product_name" id="product_name" class="form-control" value="{{ old('product_name') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="generic_name" class="form-label">Generic Name</label>
                                    <input type="text" name="generic_name" id="generic_name" class="form-control" value="{{ old('generic_name') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="pack_quantity" class="form-label">Pack Quantity</label>
                                    <input type="number" name="pack_quantity" id="pack_quantity" class="form-control" value="{{ old('pack_quantity') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="stock" class="form-label">Stock</label>
                                    <input type="number" name="stock" id="stock" class="form-control" value="{{ old('stock', 0) }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="expiry" class="form-label">Expiry Date</label>
                                    <input type="date" name="expiry" id="expiry" class="form-control" value="{{ old('expiry') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="strip_size" class="form-label">Strip Size</label>
                                    <input type="number" name="strip_size" id="strip_size" class="form-control" value="{{ old('strip_size') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="box_size" class="form-label">Box Size</label>
                                    <input type="number" name="box_size" id="box_size" class="form-control" value="{{ old('box_size') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="carton_size" class="form-label">Carton Size</label>
                                    <input type="number" name="carton_size" id="carton_size" class="form-control" value="{{ old('carton_size') }}">
                                </div>
                            </div>

                            {{-- Right Column --}}
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hsn_code" class="form-label">HSN Code</label>
                                    <input type="text" name="hsn_code" id="hsn_code" class="form-control" value="{{ old('hsn_code') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="batch_no" class="form-label">Batch No.</label>
                                    <input type="text" name="batch_no" id="batch_no" class="form-control" value="{{ old('batch_no') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="mrp" class="form-label">MRP</label>
                                    <input type="number" step="0.01" name="mrp" id="mrp" class="form-control" value="{{ old('mrp') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="ptr" class="form-label">PTR</label>
                                    <input type="number" step="0.01" name="ptr" id="ptr" class="form-control" value="{{ old('ptr') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="taxable_value" class="form-label">Taxable Value</label>
                                    <input type="number" step="0.01" name="taxable_value" id="taxable_value" class="form-control" value="{{ old('taxable_value') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="gst" class="form-label">GST</label>
                                    <input type="number" step="0.01" name="gst" id="gst" class="form-control" value="{{ old('gst') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="offer" class="form-label">Offer</label>
                                    <input type="number" step="0.01" name="offer" id="offer" class="form-control" value="{{ old('offer') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="discount" class="form-label">Discount</label>
                                    <input type="number" step="0.01" name="discount" id="discount" class="form-control" value="{{ old('discount') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="net_amount" class="form-label">Net Amount</label>
                                    <input type="number" step="0.01" name="net_amount" id="net_amount" class="form-control" value="{{ old('net_amount') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary px-4">Create Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
