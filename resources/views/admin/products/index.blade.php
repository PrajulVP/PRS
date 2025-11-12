@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fa fa-package me-2"></i>Products</h5>
                    <a href="{{ route('products.create') }}" class="btn btn-primary"><i class="fa fa-plus me-1"></i>Add Product</a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="display table table-striped table-hover" id="products-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Generic Name</th>
                                    <th>Pack Quantity</th>
                                    <th>Stock</th>
                                    <th>Batch No</th>
                                    <th>Expiry</th>
                                    <th>MRP</th>
                                    <th>Net Amount</th>
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
        $('#products-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: "{{ route('products.index') }}",
        type: 'GET'
    },
    columns: [
        { data: 'id', name: 'id' },
        { data: 'product_code', name: 'product_code' },
        { data: 'product_name', name: 'product_name' },
        { data: 'generic_name', name: 'generic_name' },
        { data: 'pack_quantity', name: 'pack_quantity' }, // New column for pack_quantity
        { data: 'stock', name: 'stock',
            render: function(data, type, row) {
                var stockValue = parseInt(data); // Total individual units
                var badgeClass = 'badge-danger';
                var statusText = 'Out of Stock';

                if (stockValue > 0) { // Check if total individual units are greater than 0
                    badgeClass = 'badge-success';
                    statusText = 'In Stock (' + stockValue + ' units)'; // Simplified stock display
                }

                return `<span class="badge ${badgeClass}">${statusText}</span>`;
            }
        },
        { data: 'batch_no', name: 'batch_no' },
        { data: 'expiry', name: 'expiry' },
        { data: 'mrp', name: 'mrp' },
        { data: 'net_amount', name: 'net_amount' },
        {
            data: 'actions',
            name: 'actions',
            orderable: false,
            searchable: false,
            render: function(data, type, row) {
                var viewUrl = "{{ route('products.show', ':id') }}".replace(':id', row.id);
                var editUrl = "{{ route('products.edit', ':id') }}".replace(':id', row.id);
                var deleteUrl = "{{ route('products.destroy', ':id') }}".replace(':id', row.id);
                var csrfToken = "{{ csrf_token() }}";
                return `
                    <a href="${viewUrl}" class="btn btn-outline-info btn-sm"><i class="fa fa-eye"></i></a>
                    <a href="${editUrl}" class="btn btn-outline-primary btn-sm"><i class="fa fa-edit"></i></a>
                    <form action="${deleteUrl}" method="POST" style="display:inline-block;">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                    </form>
                `;
            }
        }
    ],
    dom: 'Blfrtip',
    buttons: [
        { extend: 'copy', className: 'btn btn-primary btn-sm' },
        { extend: 'csv', className: 'btn btn-primary btn-sm' },
        { extend: 'excel', className: 'btn btn-primary btn-sm' },
        { extend: 'pdf', className: 'btn btn-primary btn-sm' },
        { extend: 'print', className: 'btn btn-primary btn-sm' },
    ]
});

    </script>
@endpush