@extends('layouts.admin')
@section('page-body')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 p-4">
            <div class="card">
                <div class="card-header">
                    <h5>Edit Retailer Order</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                    @endif

                    <form action="{{ route('retailer-orders-management.update', ['retailerOrder' => $retailerOrder->id]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label>Retailer</label>
                            <select name="retailer_id" class="form-select" required>
                                @foreach($retailers as $retailer)
                                <option value="{{ $retailer->id }}" {{ $retailerOrder->retailer_id == $retailer->id ? 'selected' : '' }}>
                                    {{ $retailer->user->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Product name</label>
                            <input type="text" name="product_name" class="form-control" value="{{ old('product_name', $retailerOrder->product_name) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>SKU (optional)</label>
                                <input type="text" name="sku" class="form-control" value="{{ old('sku', $retailerOrder->sku) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Quantity</label>
                                <input type="number" name="quantity" class="form-control" value="{{ old('quantity', $retailerOrder->quantity) }}" min="1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Unit price</label>
                                <input type="number" step="0.01" name="unit_price" class="form-control" value="{{ old('unit_price', $retailerOrder->unit_price) }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Status</label>
                            <select name="status" class="form-select" required @if(Auth::user()->hasRole('distributor')) disabled @endif>
                                <option value="pending" {{ $retailerOrder->status=='pending' ? 'selected':'' }}>Pending</option>
                                <option value="accepted" {{ $retailerOrder->status=='accepted' ? 'selected':'' }}>Accepted</option>
                                <option value="dispatched" {{ $retailerOrder->status=='dispatched' ? 'selected':'' }}>Dispatched</option>
                                <option value="delivered" {{ $retailerOrder->status=='delivered' ? 'selected':'' }}>Delivered</option>
                                <option value="cancelled" {{ $retailerOrder->status=='cancelled' ? 'selected':'' }}>Cancelled</option>
                            </select>
                            @if(Auth::user()->hasRole('distributor'))
                            <small class="form-text text-muted">Order status must be managed from the order list page.</small>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control">{{ old('notes', $retailerOrder->notes) }}</textarea>
                        </div>

                        <button class="btn btn-success">Update Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
