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
                <h3>Distributor Dashboard - Assigned Retailer Orders</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home"></i></a></li>
                    <li class="breadcrumb-item">Distributor</li>
                    <li class="breadcrumb-item active">Assigned Retailer Orders</li>
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
                            <th>Retailer Name</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Total Amount</th>
                            <th>Status / Actions</th>
                            <th>Field Staff</th>
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
        var table; // Declare table in a higher scope

        $(document).ready(function() {
            table = $('#retailer-orders-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('distributor.orders.index') }}", // This should be the route for distributor's retailer orders
                    type: 'GET'
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'retailer_name', name: 'retailer_name' },
                    { data: 'product_name', name: 'product_name' },
                    { data: 'quantity', name: 'quantity' },
                    { data: 'total_amount', name: 'total_amount' },
                    {
                        data: null, // Use null to indicate that data will be generated by the render function
                        name: 'status_actions',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            if (!row.status) {
                                return '';
                            }
                            var status = row.status;
                            var output = '';

                            // Logic for displaying status or action buttons
                            if (status === 'Pending') {
                                output = `<button class="btn btn-primary btn-sm open-assign-modal-btn" data-id="${row.id}" data-bs-toggle="modal" data-bs-target="#assignFieldStaffModal">Accept & Assign</button>`;
                            } else if (status === 'Dispatched' && row.fieldstaff_name !== 'Not Assigned') {
                                output = `<span class="badge badge-info">Assigned to Field Staff / Dispatched</span>`;
                            } else {
                                // Default status display for other states
                                var badgeClass = 'badge-primary';
                                switch (status) {
                                    case 'Delivered':
                                        badgeClass = 'badge-success';
                                        break;
                                    case 'Cancelled':
                                        badgeClass = 'badge-danger';
                                        break;
                                    default:
                                        return 'Unknown Status: ' + row.status;
                                }
                                output = `<span class="badge ${badgeClass}">${row.status}</span>`;
                            }
                            return output;
                        }
                    },
                    { data: 'fieldstaff_name', name: 'fieldstaff_name' },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            var viewUrl = "{{ route('retailer-orders-management.show', ':id') }}".replace(':id', row.id);
                            return `<a href="${viewUrl}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>`;
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

            

                            var url = "{{ route('retailer-orders-management.acceptAndAssignFieldStaff', ['retailerOrder' => ':id']) }}".replace(':id', orderId);

            

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

                    });
    </script>
@endpush
