@extends('layouts.admin')
@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Orders</h5>
                    <a href="{{ route('orders.create') }}" class="btn btn-primary">Create Order</a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="row">
                        @forelse($orders as $order)
                        <div class="col-md-4 mb-4">
                            <div class="card p-4">
                                <h5 class="card-title">Order #{{ $order->id }}</h5>
                                <p class="card-text"><strong>Retailer:</strong> {{ $order->retailer->name ?? $order->retailer->email }}</p>
                                <p class="card-text"><strong>Product:</strong> {{ $order->product_name }}</p>
                                <p class="card-text"><strong>Quantity:</strong> {{ $order->quantity }}</p>
                                <p class="card-text"><strong>Unit Price:</strong> {{ number_format($order->unit_price,2) }}</p>
                                <p class="card-text"><strong>Total Amount:</strong> {{ number_format($order->total_amount,2) }}</p>
                                <p class="card-text"><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
                                <p class="card-text"><strong>Placed At:</strong> {{ $order->placed_at ? \Carbon\Carbon::parse($order->placed_at)->format('Y-m-d') : '-' }}</p>
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form action="{{ route('orders.destroy', $order->id) }}" method="POST" style="display:inline-block;">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete order?')">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <p>No orders yet.</p>
                        </div>
                        @endforelse
                    </div>
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
