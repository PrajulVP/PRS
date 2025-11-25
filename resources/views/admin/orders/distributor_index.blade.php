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

    #retailer-orders-table tbody td {
        font-size: 0.85em; /* Decrease font size to view more records */
    }
</style>


@section('page-body')

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
                            <th>ID</th>
                            <th>Order Code</th>
                            <th>Distributor Name</th>
                            <th>Products</th>
                            <th>Total Items</th>
                            <th>Total Quantity</th>
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
            var table = $('#retailer-orders-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('distributor-bulk-orders.index') }}",
                    type: 'GET'
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'order_code', name: 'order_code' },
                    { data: 'name', name: 'distributor.user.name' }, // Distributor Name
                    { data: 'product_summary', name: 'product_summary', orderable: false, searchable: false },
                    { data: 'total_items', name: 'total_items' },
                    { data: 'total_quantity', name: 'total_quantity' },
                    { data: 'total_amount', name: 'total_amount' },
                    { 
                        data: 'status', 
                        name: 'status',
                        render: function(data, type, row) {
                            var status = data.toLowerCase().replace(/ /g, '_');
                            var badgeClass = 'badge-primary';
                            switch (status) {
                                case 'pending':
                                    badgeClass = 'badge-warning';
                                    break;
                                case 'accepted':
                                    badgeClass = 'badge-info';
                                    break;
                                case 'dispatched':
                                    badgeClass = 'badge-secondary';
                                    break;
                                case 'delivered':
                                    badgeClass = 'badge-success';
                                    break;
                                case 'cancelled':
                                    badgeClass = 'badge-danger';
                                    break;
                            }
                            return `<span class="badge ${badgeClass}">${row.status}</span>`;
                        }
                    },
                    { data: 'placed_at', name: 'placed_at' },
                    {
                        data: null, // Use null to indicate that data will be generated by the render function
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            var status = row.status.toLowerCase().replace(/ /g, '_');
                            var output = '';
                            var showUrl = "{{ route('distributor-bulk-orders.show', ':id') }}".replace(':id', row.id);

                            output += `<a href="${showUrl}" class="btn btn-sm btn-info me-1"><i class="fa fa-eye"></i></a>`;

                            // Logic for displaying status or action buttons
                            if (status === 'pending') {
                                @if (Auth::user()->hasRole('distributor'))
                                output += `<button class="btn btn-danger btn-sm cancel-order-btn me-1" data-id="${row.id}">Cancel</button>`;
                                @endif
                                @if (Auth::user()->hasPermissionToCategory('distributor_orders', 'edit')) // Manager/Admin
                                output += `<button class="btn btn-primary btn-sm accept-order-btn me-1" data-id="${row.id}">Accept</button>`;
                                @endif
                            } else if (status === 'accepted') {
                                @if (Auth::user()->hasRole('distributor'))
                                output += `<button class="btn btn-success btn-sm confirm-delivery-btn me-1" data-id="${row.id}">Confirm</button>`;
                                @endif
                            }
                            // Add Edit/Delete for Admin/Superadmin here
                            @if (Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('admin'))
                                var editUrl = "{{ route('distributor-bulk-orders.edit', ':id') }}".replace(':id', row.id);
                                var deleteUrl = "{{ route('distributor-bulk-orders.destroy', ':id') }}".replace(':id', row.id);
                                var csrfToken = "{{ csrf_token() }}";

                                output += `<a href="${editUrl}" class="btn btn-sm btn-primary me-1"><i class="fa fa-edit"></i></a>`;
                                output += `<form action="${deleteUrl}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this order?');">
                                                <input type="hidden" name="_token" value="${csrfToken}">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                            </form>`;
                            @endif
                            
                            return output;
                        }
                    }
                ],
                dom:  "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                      "<'row'<'col-sm-12'tr>>" +
                      "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [
                    { extend: 'copyHtml5', className: 'btn btn-primary btn-sm' },
                    { extend: 'excelHtml5', className: 'btn btn-sm' },
                    { extend: 'csvHtml5', className: 'btn btn-sm' },
                    { extend: 'pdfHtml5', className: 'btn btn-sm' },
                    { extend: 'print', className: 'btn btn-sm' },                ]
            });

            // Handle Accept Order button click
            $('#retailer-orders-table').on('click', '.accept-order-btn', function() {
                var orderId = $(this).data('id');
                if (confirm('Are you sure you want to accept this order?')) {
                    $.ajax({
                        url: `/distributor-bulk-orders/${orderId}/accept-order`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                alert(response.success);
                                table.draw(); // Redraw the table
                            } else {
                                alert(response.error || 'Something went wrong.');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            alert('An error occurred while accepting the order.');
                        }
                    });
                }
            });

            // Handle Cancel Order button click
            $('#retailer-orders-table').on('click', '.cancel-order-btn', function() {
                var orderId = $(this).data('id');
                if (confirm('Are you sure you want to cancel this order?')) {
                    $.ajax({
                        url: `/distributor-bulk-orders/${orderId}/cancel-order`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                alert(response.success);
                                table.draw(); // Redraw the table
                            } else {
                                alert(response.error || 'Something went wrong.');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            alert('An error occurred while cancelling the order.');
                        }
                    });
                }
            });

            // Handle Confirm Delivery button click
            $('#retailer-orders-table').on('click', '.confirm-delivery-btn', function() {
                var orderId = $(this).data('id');
                if (confirm('Are you sure you want to confirm this order as delivered?')) {
                    $.ajax({
                        url: `/distributor-bulk-orders/${orderId}/confirm-delivery`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                alert(response.success);
                                table.draw(); // Redraw the table
                            } else {
                                alert(response.error || 'Something went wrong.');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            alert('An error occurred while confirming delivery.');
                        }
                    });
                }
            });
        });
    </script>
@endpush
