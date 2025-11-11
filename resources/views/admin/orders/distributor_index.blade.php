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
                    url: "{{ route('distributor.orders.index') }}",
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
                                case 'assigned_to_distributor':
                                    badgeClass = 'badge-info';
                                    break;
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
                    { data: 'fieldstaff_name', name: 'fieldstaff_name' },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            if (row.status.toLowerCase().replace(/ /g, '_') === 'assigned_to_distributor') {
                                var fieldstaffs = {!! json_encode(App\Models\FieldStaff::with('user')->get()->map(function($fieldstaff) {
                                    return ['id' => $fieldstaff->id, 'name' => $fieldstaff->user->name];
                                })) !!};
                                var options = '<option value="">-- Select Field Staff --</option>';
                                fieldstaffs.forEach(function(fieldstaff) {
                                    options += `<option value="${fieldstaff.id}">${fieldstaff.name}</option>`;
                                });

                                return `
                                    <form class="assign-fieldstaff-form">
                                        @csrf
                                        <input type="hidden" name="order_id" value="${row.id}">
                                        <div class="input-group">
                                            <select class="form-select" name="fieldstaff_id" required>
                                                ${options}
                                            </select>
                                            <button type="submit" class="btn btn-primary">Assign</button>
                                        </div>
                                    </form>
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

            $('#retailer-orders-table').on('submit', '.assign-fieldstaff-form', function(e) {
                e.preventDefault();

                var form = $(this);
                var orderId = form.find('input[name="order_id"]').val();
                var fieldstaffId = form.find('select[name="fieldstaff_id"]').val();
                var token = form.find('input[name="_token"]').val();

                if (!fieldstaffId) {
                    alert('Please select a field staff.');
                    return;
                }

                $.ajax({
                    url: "{{ route('distributor.orders.assignFieldStaff', ['order' => ':orderId']) }}".replace(':orderId', orderId),
                    method: 'POST',
                    data: {
                        _token: token,
                        fieldstaff_id: fieldstaffId
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
