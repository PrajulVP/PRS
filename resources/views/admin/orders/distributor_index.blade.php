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
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Distributor Dashboard - Assigned Orders</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home"></i></a></li>
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
                <table class="display table table-striped table-hover" id="retailer-orders-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Distributor Name</th>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Amount</th>
                            <th>Placed At</th>
                            <th>Notes</th>
                            <th>Prescription Photo</th>
                            <th>Delivery Notes</th>
                            <th>Distributor ID</th>
                            <th>Field Staff ID</th>
                            <th>Status / Actions</th>
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
                    { data: 'name', name: 'name' }, // Distributor Name
                    { data: 'product_name', name: 'product_name' },
                    { data: 'sku', name: 'sku' },
                    { data: 'quantity', name: 'quantity' },
                    { data: 'unit_price', name: 'unit_price' },
                    { data: 'total_amount', name: 'total_amount' },
                    { data: 'placed_at', name: 'placed_at' },
                    { data: 'notes', name: 'notes' },
                    { data: 'prescription_photo', name: 'prescription_photo' },
                    { data: 'delivery_notes', name: 'delivery_notes' },
                    { data: 'distributor_id', name: 'distributor_id' },
                    { data: 'fieldstaff_id', name: 'fieldstaff_id' },
                    {
                        data: null, // Use null to indicate that data will be generated by the render function
                        name: 'status_actions',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            var status = row.status.toLowerCase().replace(/ /g, '_');
                            var output = '';

                            // Logic for displaying status or action buttons
                            if (status === 'pending') {
                                if ({{ Auth::user()->hasRole('distributor') ? 'true' : 'false' }}) { // Distributor
                                    output = `<button class="btn btn-danger btn-sm cancel-order-btn" data-id="${row.id}">Cancel</button>`;
                                } else if ({{ Auth::user()->hasPermissionToCategory('distributor_orders', 'edit') ? 'true' : 'false' }}) { // Manager/Admin
                                    output = `<button class="btn btn-primary btn-sm accept-order-btn" data-id="${row.id}">Accept</button>`;
                                } else {
                                    output = `<span class="badge badge-warning">${row.status}</span>`;
                                }
                            } else if (status === 'accepted') {
                                if ({{ Auth::user()->hasRole('distributor') ? 'true' : 'false' }}) {
                                    output = `<button class="btn btn-success btn-sm confirm-delivery-btn" data-id="${row.id}">Confirm</button>`;
                                } else {
                                    output = `<span class="badge badge-info">${row.status}</span>`;
                                }
                            } else {
                                // Default status display for other states
                                var badgeClass = 'badge-primary';
                                switch (status) {
                                    case 'accepted':
                                        badgeClass = 'badge-success';
                                        break;
                                    case 'delivered':
                                        badgeClass = 'badge-success';
                                        break;
                                    case 'cancelled':
                                        badgeClass = 'badge-danger';
                                        break;
                                }
                                output = `<span class="badge ${badgeClass}">${row.status}</span>`;
                            }
                            return output;
                        }
                    }
                ],
                dom:  "<'row mb-3'<'col-sm-12'B>>" + 
                        "<'row mb-3 d-flex align-items-center'<'col-md-6'f><'col-md-6 text-end'l>>" +
                        "rtip",
                buttons: [
                    { extend: 'copy', className: 'btn btn-primary btn-sm' },
                    { extend: 'csv', className: 'btn btn-primary btn-sm' },
                    { extend: 'excel', className: 'btn btn-primary btn-sm' },
                    { extend: 'pdf', className: 'btn btn-primary btn-sm' },
                    { extend: 'print', className: 'btn btn-primary btn-sm' },
                ]
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
