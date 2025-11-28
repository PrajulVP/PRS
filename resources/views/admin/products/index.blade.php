@extends('layouts.admin')

<style>
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
        padding: 6px 12px !important;
        font-size: 0.75rem !important;
        line-height: 1 !important;
    }
</style>

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
                                    <th>No.</th>
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
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    $('#products-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('products.index') }}",
        order: [[1, 'desc']],
        columns: [
            { 
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'product_code', name: 'product_code' },
            { data: 'product_name', name: 'product_name' },
            { data: 'generic_name', name: 'generic_name' },
            { data: 'pack_quantity', name: 'pack_quantity' },
            { 
                data: 'stock',
                name: 'stock',
                render: function(data) {
                    if (data > 50) return `<span class="badge bg-success">${data} units</span>`;
                    if (data > 0) return `<span class="badge bg-warning">${data} units</span>`;
                    return `<span class="badge bg-danger">Out</span>`;
                }
            },
            { data: 'batch_no', name: 'batch_no' },
            { data: 'expiry', name: 'expiry' },
            { data: 'mrp', name: 'mrp' },
            { data: 'net_amount', name: 'net_amount' },
            { 
                data: 'id',
                orderable: false,
                searchable: false,
                render: function(id) {
                    let viewUrl = "{{ route('products.show', ':id') }}".replace(':id', id);
                    let editUrl = "{{ route('products.edit', ':id') }}".replace(':id', id);
                    let deleteUrl = "{{ route('products.destroy', ':id') }}".replace(':id', id);
                    let csrf = "{{ csrf_token() }}";

                    return `
                        <div class="action-buttons">
                            <a href="${viewUrl}" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></a>
                            <a href="${editUrl}" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a>
                            <form action="${deleteUrl}" method="POST" onsubmit="return confirm('Are you sure?')">
                                <input type="hidden" name="_token" value="${csrf}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                            </form>
                        </div>
                    `;
                }
            }
        ],
        dom: "<'row mb-3'<'col-sm-12'B>>" +
             "<'row mb-3 d-flex align-items-center'<'col-md-6'f><'col-md-6'l>>" +
             "rtip",
        buttons: [
            { extend: 'copy', className: 'btn btn-sm btn-primary' },
            { extend: 'csv', className: 'btn btn-sm btn-secondary' },
            { extend: 'excel', className: 'btn btn-sm btn-success' },
            { extend: 'pdf', className: 'btn btn-sm btn-danger' },
            { extend: 'print', className: 'btn btn-sm btn-info' }
        ]
    });
});
</script>

@endpush
