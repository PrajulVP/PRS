@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fa fa-shopping-bag me-2"></i>Retailer Orders (Managed)</h5>
                    <a href="{{ route('retailer-orders-management.create') }}" class="btn btn-primary"><i class="fa fa-plus me-1"></i>Create Retailer Order</a>
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

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
@endpush

@push('scripts')
    <!-- DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#orders-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('retailer-orders-management.index') }}",
                    type: 'GET'
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'retailer_name', name: 'retailer.user.name' },
                    { data: 'product_name', name: 'product_name' },
                    { data: 'quantity', name: 'quantity' },
                    { data: 'unit_price', name: 'unit_price' },
                    { data: 'total_amount', name: 'total_amount' },
                    { data: 'status', name: 'status',
                        render: function(data, type, row) {
                            var status = data.toLowerCase();
                            var badgeClass = 'badge-primary';
                            switch (status) {
                                case 'pending':
                                    badgeClass = 'badge-warning';
                                    break;
                                case 'assigned_to_distributor':
                                    badgeClass = 'badge-info';
                                    break;
                                case 'assigned_to_fieldstaff':
                                    badgeClass = 'badge-info';
                                    break;
                                case 'out_for_delivery':
                                    badgeClass = 'badge-secondary';
                                    break;
                                case 'delivered':
                                    badgeClass = 'badge-success';
                                    break;
                                case 'cancelled':
                                    badgeClass = 'badge-danger';
                                    break;
                            }
                            return `<span class="badge ${badgeClass}">${data}</span>`;
                        }
                    },
                    { data: 'placed_at', name: 'placed_at' },
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

                            return `
                                <a href="${viewUrl}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>
                                <a href="${editUrl}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                                <form action="${deleteUrl}" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                                </form>
                            `;
                        }
                    }
                ],
                dom: 'Blfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
        });
    </script>
@endpush
