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

    #distributor-orders-table tbody td {
        font-size: 0.85em; /* Decrease font size to view more records */
    }

    /* Force action column to never wrap */
    #distributor-orders-table td:last-child {
        white-space: nowrap !important;
    }

    /* Flex wrapper for actions */
    .action-buttons {
        display: inline-flex !important;
        align-items: center;
        gap: 4px;
        flex-wrap: nowrap;
    }

    /* Make every child inline-flex (buttons + forms) */
    .action-buttons > * {
        display: inline-flex !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Normalize button sizes */
    .action-buttons .btn {
        padding: 6px 10px !important;
        font-size: 0.75rem !important;
        line-height: 1 !important;
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
                <table class="display table table-striped table-hover" id="distributor-orders-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Order Code</th>
                            <th>Distributor Name</th>
                            <th>Sales Manager</th>
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
            var table = $('#distributor-orders-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('distributor-orders.index') }}",
                    type: 'GET'
                },
                columns: [
                    { 
                        data: null,
                        name: 'no',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1; // Serial number
                        }
                    },
                    { data: 'order_code', name: 'order_code' },
                    { data: 'name', name: 'distributor.user.name' }, // Distributor Name
                    { data: 'sales_manager_name', name: 'salesManager.user.name', orderable: true, searchable: false }, // Sales Manager Name
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

                            var showUrl = "{{ route('distributor-orders.show', ':id') }}".replace(':id', row.id);

                            output += `<div class="action-buttons">`;

                            // View button
                            output += `<a href="${showUrl}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>`;

                            // Distributor
                            @if (Auth::user()->hasRole('distributor'))
                                if (status === 'pending') {
                                    output += `<button class="btn btn-danger btn-sm cancel-order-btn" data-id="${row.id}">Cancel</button>`;
                                }
                                if (status === 'accepted_by_sales_manager') {
                                    output += `<button class="btn btn-warning btn-sm request-cancellation-btn" data-id="${row.id}">Cancel</button>`;
                                }
                            @endif

                            // Sales Manager
                            @if (Auth::user()->hasRole('salesmanager'))
                                if (status === 'pending') {
                                    output += `<button class="btn btn-primary btn-sm accept-order-btn" data-id="${row.id}" data-action="sales_manager_accept">Accept</button>`;
                                }
                                if (status === 'cancellation_requested') {
                                    output += `<button class="btn btn-success btn-sm approve-cancellation-btn" data-id="${row.id}">Approve</button>`;
                                }
                            @endif

                            // Admin
                            @if (Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('admin'))
                                
                                if (status === 'cancellation_requested') {
                                    output += `<button class="btn btn-success btn-sm approve-cancellation-btn" data-id="${row.id}">Approve</button>`;
                                }

                                var editUrl = "{{ route('distributor-orders.edit', ':id') }}".replace(':id', row.id);
                                var deleteUrl = "{{ route('distributor-orders.destroy', ':id') }}".replace(':id', row.id);
                                var csrfToken = "{{ csrf_token() }}";

                                output += `<a href="${editUrl}" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a>`;

                                output += `<form action="${deleteUrl}" method="POST" onsubmit="return confirm('Are you sure?');">
                                                <input type="hidden" name="_token" value="${csrfToken}">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                        </form>`;

                                if (status === 'accepted_by_sales_manager') {
                                    output += `<button class="btn btn-success btn-sm accept-order-btn" data-id="${row.id}" data-action="admin_accept">Accept</button>`;
                                }
                            @endif

                            output += `</div>`;

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
                    { extend: 'print', className: 'btn btn-sm' }              
                ]
            });

            // Handle Accept Order button click
            $('#distributor-orders-table').on('click', '.accept-order-btn', function() {
                var orderId = $(this).data('id');
                var actionType = $(this).data('action'); // 'sales_manager_accept' or 'admin_accept'
                var url = '';

                if (actionType === 'sales_manager_accept') {
                    url = `/distributor-orders/${orderId}/accept-by-sales-manager`;
                } else if (actionType === 'admin_accept') {
                    url = `/distributor-orders/${orderId}/accept-by-admin`;
                } else {
                    alert('Invalid accept action.');
                    return;
                }

                if (confirm('Are you sure you want to accept this order?')) {
                    $.ajax({
                        url: url,
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

            // Handle Request Cancellation button click (for Distributor)
            $('#distributor-orders-table').on('click', '.request-cancellation-btn', function() {
                var orderId = $(this).data('id');
                var reason = prompt('Please enter a reason for cancellation:');
                if (reason) {
                    $.ajax({
                        url: `/distributor-orders/${orderId}/request-cancellation`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            cancellation_reason: reason
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
                            alert('An error occurred while requesting cancellation.');
                        }
                    });
                } else if (reason === '') {
                    alert('Cancellation reason cannot be empty.');
                }
            });

            // Handle Approve Cancellation button click (for Sales Manager/Admin)
            $('#distributor-orders-table').on('click', '.approve-cancellation-btn', function() {
                var orderId = $(this).data('id');
                if (confirm('Are you sure you want to approve this cancellation request and restore stock?')) {
                    $.ajax({
                        url: `/distributor-orders/${orderId}/approve-cancellation`,
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
                            alert('An error occurred while approving cancellation.');
                        }
                    });
                }
            });

            // Handle Cancel Order button click
            $('#distributor-orders-table').on('click', '.cancel-order-btn', function() {
                var orderId = $(this).data('id');
                if (confirm('Are you sure you want to cancel this order?')) {
                    $.ajax({
                        url: `/distributor-orders/${orderId}/cancel-order`,
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
        });
    </script>
@endpush
