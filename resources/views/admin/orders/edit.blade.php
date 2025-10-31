@extends('layouts.admin')
@section('page-body')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 p-4">
            <div class="card">
                <div class="card-header">
                    <h5>Edit Order</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                    @endif

                    <form action="{{ route('orders.update', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label>Retailer</label>
                            <select name="retailer_id" class="form-select" required>
                                <option value="">Select Retailer</option>
                                @foreach($retailers as $r)
                                    <option value="{{ $r->id }}" {{ $order->retailer_id == $r->id ? 'selected' : '' }}>
                                        {{ $r->name }} — {{ $r->email }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Product name</label>
                            <input type="text" name="product_name" class="form-control" value="{{ old('product_name', $order->product_name) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>SKU (optional)</label>
                                <input type="text" name="sku" class="form-control" value="{{ old('sku', $order->sku) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Quantity</label>
                                <input type="number" name="quantity" class="form-control" value="{{ old('quantity', $order->quantity) }}" min="1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Unit price</label>
                                <input type="number" step="0.01" name="unit_price" class="form-control" value="{{ old('unit_price', $order->unit_price) }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Status</label>
                            <select name="status" class="form-select" required>
                                <option value="pending" {{ $order->status=='pending' ? 'selected':'' }}>Pending</option>
                                <option value="accepted" {{ $order->status=='accepted' ? 'selected':'' }}>Accepted</option>
                                <option value="dispatched" {{ $order->status=='dispatched' ? 'selected':'' }}>Dispatched</option>
                                <option value="delivered" {{ $order->status=='delivered' ? 'selected':'' }}>Delivered</option>
                                <option value="cancelled" {{ $order->status=='cancelled' ? 'selected':'' }}>Cancelled</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control">{{ old('notes', $order->notes) }}</textarea>
                        </div>

                        <button class="btn btn-success">Update Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
