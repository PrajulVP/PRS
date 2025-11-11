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
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home"></i></a></li>
                    <li class="breadcrumb-item">Manager</li>
                    <li class="breadcrumb-item active">Pending Orders</li>
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
                <table class="display table table-striped table-hover" id="manager-orders-table">
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
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
    <!-- DataTables with Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
    <!-- jQuery (required) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap 5 JS (ensure already included in your layout) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables Bootstrap 5 -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- DataTables Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#manager-orders-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('manager.orders.index') }}",
                    type: 'GET'
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'retailer_name', name: 'retailer_name' },
                    { data: 'product_name', name: 'product_name' },
                    { data: 'quantity', name: 'quantity' },
                    { data: 'total_amount', name: 'total_amount' },
                    { data: 'status', name: 'status',
                        render: function(data, type, row) {
                            return `<span class="badge badge-warning">${data}</span>`;
                        }
                    },
                    { data: 'placed_at', name: 'placed_at' },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            var distributors = {!! json_encode(App\Models\Distributor::with('user')->get()->map(function($distributor) {
                                return ['id' => $distributor->id, 'name' => $distributor->user->name];
                            })) !!};
                            var options = '<option value="">-- Select Distributor --</option>';
                            distributors.forEach(function(distributor) {
                                options += `<option value="${distributor->id}">${distributor->name}</option>`;
                            });

                            return `
                                <form class="assign-distributor-form">
                                    @csrf
                                    <input type="hidden" name="order_id" value="${row.id}">
                                    <div class="input-group">
                                        <select class="form-select" name="distributor_id" required>
                                            ${options}
                                        </select>
                                        <button type="submit" class="btn btn-primary">Assign</button>
                                    </div>
                                </form>
                            `;
                        }
                    }
                ],
                dom: 'Blfrtip',
                buttons: [
                    { extend: 'copy', className: 'btn btn-outline-secondary btn-sm' },
                    { extend: 'csv', className: 'btn btn-outline-secondary btn-sm' },
                    { extend: 'excel', className: 'btn btn-outline-secondary btn-sm' },
                    { extend: 'pdf', className: 'btn btn-outline-secondary btn-sm' },
                    { extend: 'print', className: 'btn btn-outline-secondary btn-sm' },
                ]
            });

            $('#manager-orders-table').on('submit', '.assign-distributor-form', function(e) {
                e.preventDefault();

                var form = $(this);
                var orderId = form.find('input[name="order_id"]').val();
                var distributorId = form.find('select[name="distributor_id"]').val();
                var token = form.find('input[name="_token"]').val();

                if (!distributorId) {
                    alert('Please select a distributor.');
                    return;
                }

                $.ajax({
                    url: `/admin/orders/${orderId}/assign-distributor`,
                    method: 'POST',
                    data: {
                        _token: token,
                        distributor_id: distributorId
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.success);
                            table.draw(); // Redraw the table
                        } else {
                            alert('Something went wrong.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('Something went wrong.');
                    }
                });
            });
        });
    </script>
@endpush

