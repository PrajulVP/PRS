
@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Products</h5>
                    <a href="{{ route('products.create') }}" class="btn btn-primary">Add Product</a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="row">
                        @forelse($products as $product)
                        <div class="col-md-4 mb-4">
                            <div class="card p-4">
                                <h5 class="card-title">{{ $product->product_name }} ({{ $product->product_code }})</h5>
                                <p class="card-text"><strong>Generic Name:</strong> {{ $product->generic_name ?? 'N/A' }}</p>
                                <p class="card-text"><strong>Batch No:</strong> {{ $product->batch_no }}</p>
                                <p class="card-text"><strong>Expiry:</strong> {{ \Carbon\Carbon::parse($product->expiry)->format('Y-m-d') }}</p>
                                <p class="card-text"><strong>MRP:</strong> {{ number_format($product->mrp, 2) }}</p>
                                <p class="card-text"><strong>Net Amount:</strong> {{ number_format($product->net_amount, 2) }}</p>
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <p>No products found.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
