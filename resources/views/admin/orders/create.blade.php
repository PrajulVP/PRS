@extends('layouts.admin')
@section('page-body')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 p-4">
            <div class="card">
                <div class="card-header">
                    <h5>Create Retailer Order</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                    @endif

                    <form action="{{ route('retailer-orders-management.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label>Product name</label>
                            <input type="text" name="product_name" class="form-control" value="{{ old('product_name') }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Quantity</label>
                                <input type="number" name="quantity" class="form-control" value="{{ old('quantity',1) }}" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Unit price</label>
                                <input type="number" step="0.01" name="unit_price" class="form-control" value="{{ old('unit_price',0) }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="prescription_photo" class="form-label">Doctor's Prescription Photo (Optional)</label>
                            <input class="form-control" type="file" id="prescription_photo" name="prescription_photo">
                        </div>

                        <div class="mb-3">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
                        </div>

                        <button class="btn btn-success">Create Retailer Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
