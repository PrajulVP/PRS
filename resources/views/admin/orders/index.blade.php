@extends('layouts.admin')
@section('page-body')
<div class="container p-4">
    <h2>Orders</h2>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('orders.create') }}" class="btn btn-primary mb-3">Create Order</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Retailer</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Total</th>
                <th>Status</th>
                <th>Placed</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($orders as $key => $order)
            <tr>
                <td>{{ $orders->firstItem() + $key }}</td>
                <td>{{ $order->retailer->name ?? $order->retailer->email }}</td>
                <td>{{ $order->product_name }}</td>
                <td>{{ $order->quantity }}</td>
                <td>{{ number_format($order->unit_price,2) }}</td>
                <td>{{ number_format($order->total_amount,2) }}</td>
                <td>{{ ucfirst($order->status) }}</td>
                <td>{{ $order->placed_at ? \Carbon\Carbon::parse($order->placed_at)->format('Y-m-d') : '-' }}</td>
                <td>
                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-info">View</a>
                    <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-sm btn-primary">Edit</a>
                    <form action="{{ route('orders.destroy', $order->id) }}" method="POST" style="display:inline-block;">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete order?')">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="9">No orders yet.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{ $orders->links() }}
</div>
@endsection
