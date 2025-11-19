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

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fa fa-shopping-bag me-2"></i>Retailer Orders (Managed)</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="display table table-striped table-hover" id="orders-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Retailer</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
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
    </div>
</div>
@endsection

<!-- Assign Field Staff Modal -->
<div class="modal fade" id="assignFieldStaffModal" tabindex="-1" aria-labelledby="assignFieldStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignFieldStaffModalLabel">Assign Field Staff to Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="modalAssignFieldStaffForm">
                    @csrf
                    <input type="hidden" name="order_id" id="modalOrderId">
                    <div class="mb-3">
                        <label for="modalFieldStaffSelect" class="form-label">Select Field Staff</label>
                        <select class="form-select" id="modalFieldStaffSelect" name="fieldstaff_id" required>
                            <option value="">-- Select Field Staff --</option>
                            {{-- Options will be populated by JavaScript --}}
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAssignFieldStaffBtn">Assign</button>
            </div>
        </div>
    </div>
</div>

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
    var table; // Declare table in a higher scope

    $(document).ready(function() {
        table = $('#orders-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('retailer-orders-management.index') }}",
                type: 'GET'
            },
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'retailer_name',
                    name: 'retailer.user.name'
                },
                {
                    data: 'product_name',
                    name: 'product_name'
                },
                {
                    data: 'quantity',
                    name: 'quantity'
                },
                {
                    data: 'unit_price',
                    name: 'unit_price'
                },
                {
                    data: 'total_amount',
                    name: 'total_amount'
                },
                                    {
                                        data: 'status',
                                        name: 'status',
                                        render: function(data, type, row) {
                                            var status = data; // Keep it as is (e.g., 'Pending', 'Accepted')
                                            var badgeClass = 'badge-primary';
                                            switch (status) {
                                                case 'Pending':
                                                    badgeClass = 'badge-warning';
                                                    break;
                                                case 'Dispatched':
                                                    // Check if field staff is assigned
                                                    if (row.fieldstaff_name && row.fieldstaff_name !== 'Not Assigned') {
                                                        return `<span class="badge badge-info">Assigned to Field Staff / Dispatched</span>`;
                                                    } else {
                                                        // This case should ideally not happen if dispatched implies assigned
                                                        return `<span class="badge badge-secondary">Dispatched</span>`;
                                                    }
                                                    break;
                                                case 'Delivered':
                                                    badgeClass = 'badge-success';
                                                    break;
                                                case 'Cancelled':
                                                    badgeClass = 'badge-danger';
                                                    break;
                                                default:
                                                    badgeClass = 'badge-primary'; // Fallback
                                                    break;
                                            }
                                            return `<span class="badge ${badgeClass}">${data}</span>`;
                                        }
                                    }, {
                    data: 'placed_at',
                    name: 'placed_at'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        var viewUrl = "{{ route('retailer-orders-management.show', ':id') }}".replace(':id', row.id);
                        var editUrl = "{{ route('retailer-orders-management.edit', ':id') }}".replace(':id', row.id);
                        var deleteUrl = "{{ route('retailer-orders-management.destroy', ':id') }}".replace(':id', row.id);
                        var csrfToken = "{{ csrf_token() }}";
                        var output = '';

                        if (row.status === 'Pending') {
                            output += `<button class="btn btn-success btn-sm accept-order-btn" data-id="${row.id}">Accept Order</button>`;
                        } else if (row.status === 'Accepted By Distributor') {
                            output += `<button class="btn btn-primary btn-sm open-assign-modal-btn" data-id="${row.id}" data-bs-toggle="modal" data-bs-target="#assignFieldStaffModal">Assign Field Staff</button>`;
                        }
                                            

                                                                        output += `

                                                                            <a href="${viewUrl}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>

                                                                            <a href="${editUrl}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>

                                                                            <form action="${deleteUrl}" method="POST" style="display:inline-block;">

                                                                                <input type="hidden" name="_token" value="${csrfToken}">

                                                                                <input type="hidden" name="_method" value="DELETE">

                                                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>

                                                                            </form>

                                                                        `;

                                                                        return output;

                                                                    }

                                                                }

                                                            ],

                                                            dom: "<'row mb-3'<'col-sm-12'B>>" +

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
            $('#orders-table').on('click', '.accept-order-btn', function() {
                var orderId = $(this).data('id');
                if (confirm('Are you sure you want to accept this order?')) {
                    $.ajax({
                        url: "{{ route('retailer-orders-management.acceptOrder', ['retailerOrder' => ':id']) }}".replace(':id', orderId),
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
                                 
                                                        // Populate field staff dropdown when modal opens
                                                        $('#assignFieldStaffModal').on('show.bs.modal', function (event) {
                                                            var button = $(event.relatedTarget); // Button that triggered the modal
                                                            var orderId = button.data('id'); // Extract info from data-* attributes
                                                            var modal = $(this);
                                                            modal.find('#modalOrderId').val(orderId);

                                                            var fieldstaffs = {!! json_encode($fieldstaffs) !!}; // Get fieldstaffs data
                                                            var optionsHtml = '<option value="">-- Select Field Staff --</option>';
                                                            fieldstaffs.forEach(function(fieldstaff) {
                                                                optionsHtml += `<option value="${fieldstaff.id}">${fieldstaff.name}</option>`;
                                                            });
                                                            modal.find('#modalFieldStaffSelect').html(optionsHtml);
                                                        });

                                                        // Handle Assign button click in the modal
                                                        $('#confirmAssignFieldStaffBtn').on('click', function() {
                                                            var orderId = $('#modalOrderId').val();
                                                            var fieldstaffId = $('#modalFieldStaffSelect').val();
                                                            var csrfToken = $('#modalAssignFieldStaffForm input[name="_token"]').val();

                                                            if (!fieldstaffId) {
                                                                alert('Please select a field staff.');
                                                                return;
                                                            }

                                                            var url = "{{ route('retailer-orders-management.assignFieldStaff', ['retailerOrder' => ':id']) }}".replace(':id', orderId);

                                                            $.ajax({
                                                                url: url,
                                                                method: 'POST',
                                                                data: {
                                                                    _token: csrfToken,
                                                                    fieldstaff_id: fieldstaffId
                                                                },
                                                                success: function(response) {
                                                                    if (response.success) {
                                                                        alert(response.success);
                                                                        $('#assignFieldStaffModal').modal('hide'); // Hide the modal
                                                                        table.draw(); // Redraw the table
                                                                    } else {
                                                                        alert(response.error || 'Something went wrong.');
                                                                    }
                                                                },
                                                                error: function(xhr, status, error) {
                                                                    console.error('Error:', error);
                                                                    alert('An error occurred while accepting and assigning the order.');
                                                                    $('#assignFieldStaffModal').modal('hide'); // Hide the modal on error
                                                                }
                                                            });
                                                        });
                                                    });    </script>
@endpush