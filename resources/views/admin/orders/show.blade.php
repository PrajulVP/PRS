@extends('layouts.admin')
@section('page-body')
<div class="container p-4">
    <h2>Order #{{ $retailerOrder->id }}</h2>

    <div class="card p-3 mb-3">
        <p><strong>Retailer:</strong> {{ $retailerOrder->retailer ? ($retailerOrder->retailer->user->name ?? $retailerOrder->retailer->user->email) : 'N/A' }}</p>
        <p><strong>Product:</strong> {{ $retailerOrder->product_name }}</p>
        <p><strong>SKU:</strong> {{ $retailerOrder->sku }}</p>
        <p><strong>Quantity:</strong> {{ $retailerOrder->quantity }}</p>
        <p><strong>Unit price:</strong> {{ number_format($retailerOrder->unit_price,2) }}</p>
        <p><strong>Total:</strong> {{ number_format($retailerOrder->total_amount,2) }}</p>
        <p><strong>Status:</strong> {{ ucfirst($retailerOrder->status) }}</p>
        @if($retailerOrder->fieldStaff)
        <p><strong>Assigned Field Staff:</strong> {{ $retailerOrder->fieldStaff->user->name ?? 'N/A' }}</p>
        @endif
        <p><strong>Placed at:</strong> {{ $retailerOrder->placed_at ? \Carbon\Carbon::parse($retailerOrder->placed_at)->format('Y-m-d') : '-' }}</p>
        <p><strong>Notes:</strong><br>{{ $retailerOrder->notes }}</p>
    </div>

    <a href="{{ route('retailer-orders-management.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
