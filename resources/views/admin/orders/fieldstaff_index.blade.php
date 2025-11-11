@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Field Staff Dashboard - Assigned Orders</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home"></i></a></li>
                    <li class="breadcrumb-item">Field Staff</li>
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
                            <th>Order ID</th>
                            <th>Retailer</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Total Amount</th>
                            <th>Status</th>
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
                    url: "{{ route('fieldstaff.orders.index') }}",
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
                            var status = data.toLowerCase().replace(/ /g, '_');
                            var badgeClass = 'badge-primary';
                            switch (status) {
                                case 'assigned_to_fieldstaff':
                                    badgeClass = 'badge-info';
                                    break;
                                case 'out_for_delivery':
                                    badgeClass = 'badge-secondary';
                                    break;
                            }
                            return `<span class="badge ${badgeClass}">${data}</span>`;
                        }
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            if (row.status.toLowerCase().replace(/ /g, '_') === 'assigned_to_fieldstaff' || row.status.toLowerCase().replace(/ /g, '_') === 'out_for_delivery') {
                                return `
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateStatusModal${row.id}">Update Status</button>
                                    <div class="modal fade" id="updateStatusModal${row.id}" tabindex="-1" aria-labelledby="updateStatusModalLabel${row.id}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('fieldstaff.orders.updateDeliveryStatus', ['order' => ':orderId']) }}".replace(':orderId', row.id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="updateStatusModalLabel${row.id}">Update Delivery Status for Order #${row.id}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="status" class="form-label">Select Status</label>
                                                            <select class="form-select" id="status" name="status" required>
                                                                <option value="out_for_delivery" ${row.status.toLowerCase().replace(/ /g, '_') === 'out_for_delivery' ? 'selected' : ''}>Out for Delivery</option>
                                                                <option value="delivered" ${row.status.toLowerCase().replace(/ /g, '_') === 'delivered' ? 'selected' : ''}>Delivered</option>
                                                                <option value="cancelled" ${row.status.toLowerCase().replace(/ /g, '_') === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="delivery_notes" class="form-label">Delivery Notes (Optional)</label>
                                                            <textarea class="form-control" id="delivery_notes" name="delivery_notes"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Update Status</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            } else {
                                return `<span class="badge badge-info">${row.status}</span>`;
                            }
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
        });
    </script>
@endpush
