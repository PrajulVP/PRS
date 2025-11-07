@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Distributor Dashboard - Assigned Orders</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">Distributor</li>
                    <li class="breadcrumb-item active">Assigned Orders</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
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
                            <th>Assigned To Field Staff</th>
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
                            <td>{{ $order->fieldStaff->user->name ?? 'Not Assigned' }}</td>
                            <td>
                                @if($order->status === 'assigned_to_distributor')
                                <form class="assign-fieldstaff-form">
                                    @csrf
                                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                                    <div class="input-group">
                                        <select class="form-select" name="fieldstaff_id" required>
                                            <option value="">-- Select Field Staff --</option>
                                            @foreach(App\Models\FieldStaff::all() as $fieldstaff)
                                                <option value="{{ $fieldstaff->id }}">{{ $fieldstaff->user->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-primary">Assign</button>
                                    </div>
                                </form>
                                @else
                                    <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No assigned orders found.</td>
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
        const forms = document.querySelectorAll('.assign-fieldstaff-form');
        forms.forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const orderId = this.querySelector('input[name="order_id"]').value;
                const fieldstaffId = this.querySelector('select[name="fieldstaff_id"]').value;
                const token = this.querySelector('input[name="_token"]').value;

                if (!fieldstaffId) {
                    alert('Please select a field staff.');
                    return;
                }

                fetch(`/distributor/orders/${orderId}/assign-fieldstaff`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ fieldstaff_id: fieldstaffId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.success);
                        // Optionally, update the row
                        this.closest('tr').querySelector('td:nth-child(7)').textContent = data.fieldstaff_name;
                        this.closest('td').innerHTML = `<span class="badge bg-info">Assigned To Fieldstaff</span>`;
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

