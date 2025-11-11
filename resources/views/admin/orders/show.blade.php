@extends('layouts.admin')
@section('page-body')
<div class="container p-4">
    <h2>Order #{{ $order->id }}</h2>

    <div class="card p-3 mb-3">
        <p><strong>Retailer:</strong> {{ $order->retailer->name ?? $order->retailer->email }}</p>
        <p><strong>Product:</strong> {{ $order->product_name }}</p>
        <p><strong>SKU:</strong> {{ $order->sku }}</p>
        <p><strong>Quantity:</strong> {{ $order->quantity }}</p>
        <p><strong>Unit price:</strong> {{ number_format($order->unit_price,2) }}</p>
        <p><strong>Total:</strong> {{ number_format($order->total_amount,2) }}</p>
        <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
        <p><strong>Placed at:</strong> {{ $order->placed_at ? \Carbon\Carbon::parse($order->placed_at)->format('Y-m-d') : '-' }}</p>
        <p><strong>Notes:</strong><br>{{ $order->notes }}</p>
    </div>

    <a href="{{ route('distributor-orders.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
