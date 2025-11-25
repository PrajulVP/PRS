@extends('layouts.admin')

<style>
    /* Align search bar properly */
    .dataTables_filter {
        text-align: left !important;
    }
    .dataTables_filter input {
        width: 230px !important;
        margin-left: 10px !important;
    }

    /* Align show entries to the right */
    .dataTables_length {
        text-align: right !important;
    }
    .dataTables_length select {
        margin: 0 5px !important;
        width: 70px !important;
        display: inline-block;
    }
</style>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="display table table-striped table-hover" id="retailer-orders-table">
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
            var table = $('#retailer-orders-table').DataTable({
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
                dom: "<'row mb-3'<'col-sm-12'B>>" + 
                        "<'row mb-3 d-flex align-items-center'<'col-md-6'f><'col-md-6 text-end'l>>" +
                        "rtip",
                buttons: [
                    { extend: 'copy', className: 'btn btn-primary btn-sm' },
                    { extend: 'csv', className: 'btn btn-sm' },
                    { extend: 'excel', className: 'btn btn-sm' },
                    { extend: 'pdf', className: 'btn btn-sm' },
                    { extend: 'print', className: 'btn btn-sm' },                ]
            });

            $('#retailer-orders-table').on('submit', '.assign-distributor-form', function(e) {
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
                    url: "{{ route('admin.orders.assign_distributor', ['order' => ':orderId']) }}".replace(':orderId', orderId),
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
