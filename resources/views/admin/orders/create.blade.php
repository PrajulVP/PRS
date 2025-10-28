@extends('layouts.admin')
@section('page-body')
<div class="container p-4">
    <h2>Create Order (Admin)</h2>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('orders.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Retailer</label>
            <select name="retailer_id" class="form-select" required>
                <option value="">Select Retailer</option>
                @foreach($retailers as $r)
                    <option value="{{ $r->id }}" {{ old('retailer_id') == $r->id ? 'selected' : '' }}>
                        {{ $r->name }} — {{ $r->email }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Product name</label>
            <input type="text" name="product_name" class="form-control" value="{{ old('product_name') }}" required>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>SKU (optional)</label>
                <input type="text" name="sku" class="form-control" value="{{ old('sku') }}">
            </div>
            <div class="col-md-4 mb-3">
                <label>Quantity</label>
                <input type="number" name="quantity" class="form-control" value="{{ old('quantity',1) }}" min="1" required>
            </div>
            <div class="col-md-4 mb-3">
                <label>Unit price</label>
                <input type="number" step="0.01" name="unit_price" class="form-control" value="{{ old('unit_price',0) }}" required>
            </div>
        </div>

        <div class="mb-3">
            <label>Notes</label>
            <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
        </div>

        <button class="btn btn-success">Create Order</button>
    </form>
</div>
@endsection
