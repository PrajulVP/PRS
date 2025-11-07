@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6 p-4">
                <h3>Manager Dashboard - Pending Orders</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">Manager</li>
                    <li class="breadcrumb-item active">Pending Orders</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card p-4">
        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Retailer</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Placed At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->retailer->name ?? 'N/A' }}</td>
                            <td>{{ $order->product_name }}</td>
                            <td>{{ $order->quantity }}</td>
                            <td>{{ number_format($order->total_amount, 2) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td>
                            <td>{{ $order->placed_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <form class="assign-distributor-form">
                                    @csrf
                                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                                    <div class="input-group">
                                        <select class="form-select" name="distributor_id" required>
                                            <option value="">-- Select Distributor --</option>
                                            @foreach(App\Models\Distributor::all() as $distributor)
                                                <option value="{{ $distributor->id }}">{{ $distributor->user->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-primary">Assign</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No pending orders found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $orders->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const forms = document.querySelectorAll('.assign-distributor-form');
        forms.forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const orderId = this.querySelector('input[name="order_id"]').value;
                const distributorId = this.querySelector('select[name="distributor_id"]').value;
                const token = this.querySelector('input[name="_token"]').value;

                if (!distributorId) {
                    alert('Please select a distributor.');
                    return;
                }

                fetch(`/admin/orders/${orderId}/assign-distributor`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ distributor_id: distributorId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.success);
                        // Optionally, remove the row from the table
                        this.closest('tr').remove();
                    } else {
                        alert('Something went wrong.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Something went wrong.');
                });
            });
        });
    });
</script>
@endpush

