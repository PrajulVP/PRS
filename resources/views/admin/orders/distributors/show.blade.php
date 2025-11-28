@extends('layouts.admin')
@section('page-body')
<div class="container p-4">
    <h2>Order #{{ $order->order_code }}</h2>

    <div class="card p-3 mb-3">
        <div class="row">
            <div class="col-md-6">
                @if ($order instanceof \App\Models\distributorOrder)
                    <p><strong>Distributor:</strong> {{ $order->distributor->user->name ?? 'N/A' }}</p>
                @elseif ($order instanceof \App\Models\RetailerOrder)
                    <p><strong>Retailer:</strong> {{ $order->retailer->user->name ?? 'N/A' }}</p>
                    <p><strong>Distributor:</strong> {{ $order->distributor->user->name ?? 'N/A' }}</p>
                @endif
                <p><strong>Total Items:</strong> {{ $order->total_items }}</p>
                <p><strong>Total Quantity:</strong> {{ $order->total_quantity }}</p>
                <p><strong>Total Amount:</strong> {{ number_format($order->total_amount, 2) }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Placed at:</strong> {{ $order->placed_at ? \Carbon\Carbon::parse($order->placed_at)->format('Y-m-d H:i') : '-' }}</p>
                @if ($order instanceof \App\Models\distributorOrder)
                    <p><strong>Delivery Notes:</strong><br>{{ $order->delivery_notes ?? '-' }}</p>
                @endif
                <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
            </div>
        </div>
    </div>

    <h3 class="mt-4">Order Items</h3>
    <div class="card p-3 mb-3">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->items as $item)
                    <tr>
                        <td>{{ $item->product->product_name ?? 'N/A' }}</td>
                        <td>{{ $item->product->product_code ?? 'N/A' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No items in this order.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($order instanceof \App\Models\distributorOrder)
        <a href="{{ route('distributor-orders.index') }}" class="btn btn-secondary">Back</a>
    @endif
</div>
@endsection

