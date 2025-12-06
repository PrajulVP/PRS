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
    .action-buttons>* {
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
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProductModal">
                        <i class="fa fa-plus me-1"></i>Add Product
                    </button>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
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

{{-- Create Product Modal --}}
<div class="modal fade" id="createProductModal" tabindex="-1" aria-labelledby="createProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createProductModalLabel">Create Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('products.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Left Column --}}
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="create_product_code" class="form-label">Product Code</label>
                                <input type="text" name="product_code" id="create_product_code" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="create_product_name" class="form-label">Product Name</label>
                                <input type="text" name="product_name" id="create_product_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="create_generic_name" class="form-label">Generic Name</label>
                                <input type="text" name="generic_name" id="create_generic_name" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="create_pack_quantity" class="form-label">Pack Quantity</label>
                                <input type="number" name="pack_quantity" id="create_pack_quantity" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="create_stock" class="form-label">Stock</label>
                                <input type="number" name="stock" id="create_stock" class="form-control" value="0" required>
                            </div>
                            <div class="mb-3">
                                <label for="create_expiry" class="form-label">Expiry Date</label>
                                <input type="date" name="expiry" id="create_expiry" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="create_strip_size" class="form-label">Strip Size</label>
                                <input type="number" name="strip_size" id="create_strip_size" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="create_box_size" class="form-label">Box Size</label>
                                <input type="number" name="box_size" id="create_box_size" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="create_carton_size" class="form-label">Carton Size</label>
                                <input type="number" name="carton_size" id="create_carton_size" class="form-control">
                            </div>
                        </div>
                        {{-- Right Column --}}
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="create_hsn_code" class="form-label">HSN Code</label>
                                <input type="text" name="hsn_code" id="create_hsn_code" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="create_batch_no" class="form-label">Batch No.</label>
                                <input type="text" name="batch_no" id="create_batch_no" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="create_mrp" class="form-label">MRP</label>
                                <input type="number" step="0.01" name="mrp" id="create_mrp" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="create_ptr" class="form-label">PTR</label>
                                <input type="number" step="0.01" name="ptr" id="create_ptr" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="create_taxable_value" class="form-label">Taxable Value</label>
                                <input type="number" step="0.01" name="taxable_value" id="create_taxable_value" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="create_gst" class="form-label">GST</label>
                                <input type="number" step="0.01" name="gst" id="create_gst" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="create_offer" class="form-label">Offer</label>
                                <input type="number" step="0.01" name="offer" id="create_offer" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="create_discount" class="form-label">Discount</label>
                                <input type="number" step="0.01" name="discount" id="create_discount" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="create_net_amount" class="form-label">Net Amount</label>
                                <input type="number" step="0.01" name="net_amount" id="create_net_amount" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Product Modal --}}
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProductForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Left Column --}}
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_product_code" class="form-label">Product Code</label>
                                <input type="text" name="product_code" id="edit_product_code" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_product_name" class="form-label">Product Name</label>
                                <input type="text" name="product_name" id="edit_product_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_generic_name" class="form-label">Generic Name</label>
                                <input type="text" name="generic_name" id="edit_generic_name" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="edit_pack_quantity" class="form-label">Pack Quantity</label>
                                <input type="number" name="pack_quantity" id="edit_pack_quantity" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_stock" class="form-label">Stock</label>
                                <input type="number" name="stock" id="edit_stock" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_expiry" class="form-label">Expiry Date</label>
                                <input type="date" name="expiry" id="edit_expiry" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_strip_size" class="form-label">Strip Size</label>
                                <input type="number" name="strip_size" id="edit_strip_size" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="edit_box_size" class="form-label">Box Size</label>
                                <input type="number" name="box_size" id="edit_box_size" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="edit_carton_size" class="form-label">Carton Size</label>
                                <input type="number" name="carton_size" id="edit_carton_size" class="form-control">
                            </div>
                        </div>
                        {{-- Right Column --}}
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_hsn_code" class="form-label">HSN Code</label>
                                <input type="text" name="hsn_code" id="edit_hsn_code" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="edit_batch_no" class="form-label">Batch No.</label>
                                <input type="text" name="batch_no" id="edit_batch_no" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_mrp" class="form-label">MRP</label>
                                <input type="number" step="0.01" name="mrp" id="edit_mrp" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_ptr" class="form-label">PTR</label>
                                <input type="number" step="0.01" name="ptr" id="edit_ptr" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_taxable_value" class="form-label">Taxable Value</label>
                                <input type="number" step="0.01" name="taxable_value" id="edit_taxable_value" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_gst" class="form-label">GST</label>
                                <input type="number" step="0.01" name="gst" id="edit_gst" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_offer" class="form-label">Offer</label>
                                <input type="number" step="0.01" name="offer" id="edit_offer" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="edit_discount" class="form-label">Discount</label>
                                <input type="number" step="0.01" name="discount" id="edit_discount" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="edit_net_amount" class="form-label">Net Amount</label>
                                <input type="number" step="0.01" name="net_amount" id="edit_net_amount" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Show Product Modal --}}
<div class="modal fade" id="showProductModal" tabindex="-1" aria-labelledby="showProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showProductModalLabel">Product Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tbody id="showProductTableBody">
                        {{-- Content will be loaded via JS --}}
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        var table = $('#products-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('products.index') }}",
            order: [
                [1, 'desc']
            ],
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'product_code',
                    name: 'product_code'
                },
                {
                    data: 'product_name',
                    name: 'product_name'
                },
                {
                    data: 'generic_name',
                    name: 'generic_name'
                },
                {
                    data: 'pack_quantity',
                    name: 'pack_quantity'
                },
                {
                    data: 'stock',
                    name: 'stock',
                    render: function(data) {
                        if (data > 50) return `<span class="badge bg-success">${data} units</span>`;
                        if (data > 0) return `<span class="badge bg-warning">${data} units</span>`;
                        return `<span class="badge bg-danger">Out</span>`;
                    }
                },
                {
                    data: 'batch_no',
                    name: 'batch_no'
                },
                {
                    data: 'expiry',
                    name: 'expiry'
                },
                {
                    data: 'mrp',
                    name: 'mrp'
                },
                {
                    data: 'net_amount',
                    name: 'net_amount'
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(id, type, row) { // Pass row data
                        let deleteUrl = "{{ route('products.destroy', ':id') }}".replace(':id', id);
                        let csrf = "{{ csrf_token() }}";
                        // Store row data in a data attribute (JSON stringified) for easy access
                        let rowData = JSON.stringify(row).replace(/"/g, '&quot;');

                        return `
                        <div class="action-buttons">
                            <button type="button" class="btn btn-sm btn-primary view-btn" data-product="${rowData}"><i class="fa fa-eye"></i></button>
                            <button type="button" class="btn btn-sm btn-primary edit-btn" data-product="${rowData}"><i class="fa fa-edit"></i></button>
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
            buttons: [{
                    extend: 'copy',
                    className: 'btn btn-sm btn-primary'
                },
                {
                    extend: 'csv',
                    className: 'btn btn-sm btn-secondary'
                },
                {
                    extend: 'excel',
                    className: 'btn btn-sm btn-success'
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-sm btn-danger'
                },
                {
                    extend: 'print',
                    className: 'btn btn-sm btn-info'
                }
            ]
        });

        // Handle Edit Button Click
        $('#products-table').on('click', '.edit-btn', function() {
            var product = $(this).data('product'); // This might be an object if processed by jQuery, or string
            if (typeof product === 'string') {
                // In case it's still a string despite data() magic
                // It shouldn't happen usually with data() if proper JSON, but safe to check.
                // However, data-product="${rowData}" puts it as a string attribute.
                // jQuery's .data() automatically parses JSON if it looks like JSON.
            }

            // Populate fields
            $('#edit_product_code').val(product.product_code);
            $('#edit_product_name').val(product.product_name);
            $('#edit_generic_name').val(product.generic_name);
            $('#edit_pack_quantity').val(product.pack_quantity);
            $('#edit_stock').val(product.stock);
            // Expiry might need formatting depending on how it comes from DB (DateTime string likely)
            // Assuming YYYY-MM-DD HH:MM:SS, just separate date
            if (product.expiry) {
                $('#edit_expiry').val(product.expiry.split('T')[0].split(' ')[0]);
            }
            $('#edit_strip_size').val(product.strip_size);
            $('#edit_box_size').val(product.box_size);
            $('#edit_carton_size').val(product.carton_size);
            $('#edit_hsn_code').val(product.hsn_code);
            $('#edit_batch_no').val(product.batch_no);
            $('#edit_mrp').val(product.mrp);
            $('#edit_ptr').val(product.ptr);
            $('#edit_taxable_value').val(product.taxable_value);
            $('#edit_gst').val(product.gst);
            $('#edit_offer').val(product.offer);
            $('#edit_discount').val(product.discount);
            $('#edit_net_amount').val(product.net_amount);

            // Update form action
            let updateUrl = "{{ route('products.update', ':id') }}".replace(':id', product.id);
            $('#editProductForm').attr('action', updateUrl);

            // Show modal
            $('#editProductModal').modal('show');
        });

        // Handle View Button Click
        $('#products-table').on('click', '.view-btn', function() {
            var product = $(this).data('product');

            let html = `
            <tr><th>Product Code</th><td>${product.product_code}</td></tr>
            <tr><th>Product Name</th><td>${product.product_name}</td></tr>
            <tr><th>Generic Name</th><td>${product.generic_name || 'N/A'}</td></tr>
            <tr><th>Pack Quantity</th><td>${product.pack_quantity}</td></tr>
             <tr><th>Expiry Date</th><td>${product.expiry ? product.expiry.split('T')[0].split(' ')[0] : 'N/A'}</td></tr>
            <tr><th>Stock</th><td>${product.stock}</td></tr>
             <tr><th>Strip Size</th><td>${product.strip_size || 'N/A'}</td></tr>
            <tr><th>Box Size</th><td>${product.box_size || 'N/A'}</td></tr>
            <tr><th>Carton Size</th><td>${product.carton_size || 'N/A'}</td></tr>
            <tr><th>HSN Code</th><td>${product.hsn_code || 'N/A'}</td></tr>
            <tr><th>Batch No.</th><td>${product.batch_no}</td></tr>
            <tr><th>MRP</th><td>${parseFloat(product.mrp).toFixed(2)}</td></tr>
            <tr><th>KEY</th><td>${parseFloat(product.ptr).toFixed(2)}</td></tr>
            <tr><th>Taxable Value</th><td>${parseFloat(product.taxable_value).toFixed(2)}</td></tr>
            <tr><th>GST</th><td>${parseFloat(product.gst).toFixed(2)}</td></tr>
            <tr><th>Offer</th><td>${product.offer ? parseFloat(product.offer).toFixed(2) : 'N/A'}</td></tr>
            <tr><th>Discount</th><td>${product.discount ? parseFloat(product.discount).toFixed(2) : 'N/A'}</td></tr>
            <tr><th>Net Amount</th><td>${parseFloat(product.net_amount).toFixed(2)}</td></tr>
        `;
            $('#showProductTableBody').html(html);
            $('#showProductModal').modal('show');
        });
    });
</script>

@endpush