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

    /* Fix DataTable Length Select Arrow Issue */
    .dataTables_length select.form-select {
        padding-right: 2.5rem !important;
        /* Ensure space for arrow */
        background-position: right 0.75rem center;
        /* correct arrow position */
        width: auto !important;
        display: inline-block !important;
    }
</style>

@section('page-body')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fa fa-package me-2"></i>Products</h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                                data-bs-target="#importProductModal">
                                <i class="fa fa-upload me-1"></i>Import Products
                            </button>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#createProductModal">
                                <i class="fa fa-plus me-1"></i>Add Product
                            </button>
                        </div>
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
                                        <th>Pack</th>
                                        <th>MRP</th>
                                        <th>PTR</th>
                                        <th>PTS</th>
                                        <th>Loyalty %</th>
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
    <div class="modal fade" id="createProductModal" tabindex="-1" aria-labelledby="createProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createProductModalLabel">Create Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <!-- General Info -->
                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">General Information</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="create_product_code" class="form-label fw-medium">Product Code</label>
                                <input type="text" name="product_code" id="create_product_code" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="create_product_name" class="form-label fw-medium">Product Name</label>
                                <input type="text" name="product_name" id="create_product_name" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-3">
                                <label for="create_generic_name" class="form-label fw-medium">Generic Name</label>
                                <input type="text" name="generic_name" id="create_generic_name" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="create_hsn_code" class="form-label fw-medium">HSN Code</label>
                                <input type="text" name="hsn_code" id="create_hsn_code" class="form-control">
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <!-- Left Column: Stock & Packaging -->
                            <div class="col-md-6 border-end">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Stock & Packaging</h6>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="create_pack" class="form-label fw-medium">Pack</label>
                                        <input type="text" name="pack" id="create_pack" class="form-control"
                                            placeholder="e.g. 10x10, 30ml">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="create_strip_size" class="form-label fw-medium">Tablet / Strip</label>
                                        <input type="text" name="strip_size" id="create_strip_size" class="form-control"
                                            placeholder="Tablets per strips">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="create_box_size" class="form-label fw-medium">Strip / Box</label>
                                        <input type="number" name="box_size" id="create_box_size" class="form-control"
                                            placeholder="Strips per box">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="create_carton_size" class="form-label fw-medium">Box / Carton</label>
                                        <input type="number" name="carton_size" id="create_carton_size" class="form-control"
                                            placeholder="Boxes per carton">
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Pricing -->
                            <div class="col-md-6">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Pricing Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="create_mrp" class="form-label fw-medium">MRP</label>
                                        <input type="number" step="0.01" name="mrp" id="create_mrp" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="create_ptr" class="form-label fw-medium">PTR</label>
                                        <input type="number" step="0.01" name="ptr" id="create_ptr" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="create_pts" class="form-label fw-medium">PTS</label>
                                        <input type="number" step="0.01" name="pts" id="create_pts" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="create_loyalty_point_percentage" class="form-label fw-medium">Loyalty %</label>
                                        <input type="number" step="0.01" name="loyalty_point_percentage"
                                            id="create_loyalty_point_percentage" class="form-control" placeholder="0.00">
                                    </div>
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
                        <!-- General Info -->
                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">General Information</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="edit_product_code" class="form-label fw-medium">Product Code</label>
                                <input type="text" name="product_code" id="edit_product_code" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="edit_product_name" class="form-label fw-medium">Product Name</label>
                                <input type="text" name="product_name" id="edit_product_name" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label for="edit_generic_name" class="form-label fw-medium">Generic Name</label>
                                <input type="text" name="generic_name" id="edit_generic_name" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="edit_hsn_code" class="form-label fw-medium">HSN Code</label>
                                <input type="text" name="hsn_code" id="edit_hsn_code" class="form-control">
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <!-- Left Column: Stock & Packaging -->
                            <div class="col-md-6 border-end">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Stock & Packaging</h6>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="edit_pack" class="form-label fw-medium">Pack</label>
                                        <input type="text" name="pack" id="edit_pack" class="form-control"
                                            placeholder="e.g. 10x10, 30ml">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit_strip_size" class="form-label fw-medium">Tablet / Strip</label>
                                        <input type="text" name="strip_size" id="edit_strip_size" class="form-control"
                                            placeholder="Tablets per strips">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit_box_size" class="form-label fw-medium">Strip / Box</label>
                                        <input type="number" name="box_size" id="edit_box_size" class="form-control"
                                            placeholder="Strips per box">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit_carton_size" class="form-label fw-medium">Box / Carton</label>
                                        <input type="number" name="carton_size" id="edit_carton_size" class="form-control"
                                            placeholder="Boxes per carton">
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Pricing -->
                            <div class="col-md-6">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Pricing Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="edit_mrp" class="form-label fw-medium">MRP</label>
                                        <input type="number" step="0.01" name="mrp" id="edit_mrp" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit_ptr" class="form-label fw-medium">PTR</label>
                                        <input type="number" step="0.01" name="ptr" id="edit_ptr" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit_pts" class="form-label fw-medium">PTS</label>
                                        <input type="number" step="0.01" name="pts" id="edit_pts" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="edit_loyalty_point_percentage" class="form-label fw-medium">Loyalty
                                            %</label>
                                        <input type="number" step="0.01" name="loyalty_point_percentage"
                                            id="edit_loyalty_point_percentage" class="form-control" placeholder="0.00">
                                    </div>
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
                <div class="modal-body p-4" id="showProductTableBody">
                    {{-- Content will be loaded via JS --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Import Product Modal --}}
    <div class="modal fade" id="importProductModal" tabindex="-1" aria-labelledby="importProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importProductModalLabel">Import Products (CSV)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-info small mb-4">
                            <strong>Note:</strong> Download the template to ensure your CSV is formatted correctly.
                            <ul class="mb-0 mt-2 text-start">
                                <li><strong>Required fields:</strong> Product Name, MRP</li>
                                <li>If a Product Code is provided and already exists, it will be automatically <strong>updated</strong>.
                                </li>
                            </ul>
                        </div>
                        <div class="text-center mb-4">
                            <a href="{{ route('products.download-template') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fa fa-download me-1"></i> Download CSV Template
                            </a>
                        </div>
                        <div class="mb-3">
                            <label for="import_file" class="form-label fw-bold">Upload CSV File <span
                                    class="text-danger">*</span></label>
                            <input class="form-control" type="file" id="import_file" name="import_file" accept=".csv"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-upload me-1"></i> Import Data</button>
                    </div>
                </form>
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
        $(document).ready(function () {
            var table = $('#products-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('products.index') }}",
                order: [],
                columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
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
                    data: 'pack',
                    name: 'pack'
                },
                {
                    data: 'mrp',
                    name: 'mrp',
                    render: function(data) {
                        return '₹' + parseFloat(data).toFixed(2);
                    }
                },
                {
                    data: 'ptr',
                    name: 'ptr',
                    render: function(data) {
                        return '₹' + parseFloat(data).toFixed(2);
                    }
                },
                {
                    data: 'pts',
                    name: 'pts',
                    render: function(data) {
                        return '₹' + parseFloat(data).toFixed(2);
                    }
                },
                {
                    data: 'loyalty_point_percentage',
                    name: 'loyalty_point_percentage'
                },

                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function (id, type, row) { // Pass row data
                        let deleteUrl = "{{ route('products.destroy', ':id') }}".replace(':id', id);
                        let csrf = "{{ csrf_token() }}";
                        // Store row data in a data attribute (JSON stringified) for easy access
                        let rowData = JSON.stringify(row).replace(/"/g, '&quot;');

                        return `
                                                                                    <div class="action-buttons">
                                                                                        <button type="button" class="btn btn-sm btn-primary view-btn" data-product='${rowData}'><i class="fa fa-eye"></i></button>
                                                                                        <button type="button" class="btn btn-sm btn-primary edit-btn" data-product='${rowData}'><i class="fa fa-edit"></i></button>
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
                    "<'row mb-3'<'col-md-6'l><'col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: {
                    dom: {
                        button: {
                            className: 'btn btn-sm btn-icon'
                        }
                    },
                    buttons: [{
                        extend: 'copy',
                        className: 'btn btn-secondary btn-sm',
                        text: '<i class="fa fa-copy"></i> Copy'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-info btn-sm text-white',
                        text: '<i class="fa fa-file-csv"></i> CSV'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-success btn-sm',
                        text: '<i class="fa fa-file-excel"></i> Excel'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-danger btn-sm',
                        text: '<i class="fa fa-file-pdf"></i> PDF'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-dark btn-sm',
                        text: '<i class="fa fa-print"></i> Print'
                    }
                    ]
                }
            });

            // Handle Edit Button Click
            $('#products-table').on('click', '.edit-btn', function () {
                var product = $(this).data('product'); // This might be an object if processed by jQuery, or string
                console.log(product);
                if (typeof product === 'string') {
                    product = JSON.parse(product);
                }

                function removeCommas(value) {
                    return value ? value.toString().replace(/,/g, '') : '';
                }

                // Populate fields
                $('#edit_product_code').val(product.product_code);
                $('#edit_product_name').val(product.product_name);
                $('#edit_generic_name').val(product.generic_name);
                $('#edit_pack').val(product.pack);

                $('#edit_strip_size').val(product.strip_size);
                $('#edit_box_size').val(product.box_size);
                $('#edit_carton_size').val(product.carton_size);
                $('#edit_hsn_code').val(product.hsn_code);


                $('#edit_mrp').val(removeCommas(product.mrp));
                $('#edit_ptr').val(removeCommas(product.ptr));
                $('#edit_pts').val(removeCommas(product.pts));
                // Offer and discount fields removed
                $('#edit_loyalty_point_percentage').val(product.loyalty_point_percentage);

                // Update form action
                let updateUrl = "{{ route('products.update', ':id') }}".replace(':id', product.id);
                $('#editProductForm').attr('action', updateUrl);

                // Show modal
                $('#editProductModal').modal('show');
            });

            // Handle View Button Click
            $('#products-table').on('click', '.view-btn', function () {
                var product = $(this).data('product');

                let html = `
                                                                            <div class="row g-3">
                                                                                <div class="col-md-12 mb-3">
                                                                                    <div class="p-4 bg-light rounded text-center">
                                                                                        <h3 class="fw-bold text-primary mb-2">${product.product_name}</h3>
                                                                                        <div class="d-flex flex-wrap justify-content-center gap-3 text-secondary">
                                                                                            <span class="badge bg-white text-dark border shadow-sm px-3 py-2"><i class="fa fa-tag me-1 text-primary"></i>${product.product_code}</span>
                                                                                            <span class="badge bg-white text-dark border shadow-sm px-3 py-2"><i class="fa fa-dna me-1 text-info"></i>${product.generic_name || 'Generic N/A'}</span>
                                                                                            <span class="badge bg-white text-dark border shadow-sm px-3 py-2"><i class="fa fa-box me-1 text-warning"></i>${product.pack || 'Pack N/A'}</span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="row g-4">
                                                                                <!-- Left Column: Stock & Packaging -->
                                                                                <div class="col-md-6 border-end">
                                                                                    <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Stock & Packaging</h6>
                                                                                    <dl class="row mb-0">
                                                                                        <dt class="col-sm-4 text-muted small text-uppercase">Pack</dt>
                                                                                        <dd class="col-sm-8 fw-medium">${product.pack || '-'}</dd>

                                                                                        <dt class="col-sm-4 text-muted small text-uppercase">Tablet/Strip</dt>
                                                                                        <dd class="col-sm-8 fw-medium">${product.strip_size || '-'}</dd>

                                                                                        <dt class="col-sm-4 text-muted small text-uppercase">Strip / Box</dt>
                                                                                        <dd class="col-sm-8 fw-medium">${product.box_size || '-'}</dd>

                                                                                        <dt class="col-sm-4 text-muted small text-uppercase">Box / Carton</dt>
                                                                                        <dd class="col-sm-8 fw-medium">${product.carton_size || '-'}</dd>

                                                                                        <dt class="col-sm-4 text-muted small text-uppercase">HSN</dt>
                                                                                        <dd class="col-sm-8 fw-medium">${product.hsn_code || '-'}</dd>
                                                                                    </dl>
                                                                                </div>

                                                                                <!-- Right Column: Pricing -->
                                                                                <div class="col-md-6">
                                                                                    <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Pricing Details</h6>
                                                                                    <dl class="row mb-0">
                                                                                        <dt class="col-sm-4 text-muted small text-uppercase">MRP</dt>
                                                                                        <dd class="col-sm-8 fw-medium">₹${parseFloat(product.mrp).toFixed(2)}</dd>

                                                                                        <dt class="col-sm-4 text-muted small text-uppercase">PTR</dt>
                                                                                        <dd class="col-sm-8 fw-medium">₹${parseFloat(product.ptr).toFixed(2)}</dd>

                                                                                        <dt class="col-sm-4 text-muted small text-uppercase">PTS</dt>
                                                                                        <dd class="col-sm-8 fw-medium">₹${parseFloat(product.pts).toFixed(2)}</dd>                                      
                                                                                        <!-- Offer and discount removed -->
                                                                                        <dt class="col-sm-4 text-muted small text-uppercase">Loyalty %</dt>
                                                                                        <dd class="col-sm-8 fw-medium text-info">
                                                                                            ${parseFloat(product.loyalty_point_percentage || 0).toFixed(2)}%
                                                                                        </dd>
                                                                                    </dl>
                                                                                </div>
                                                                            </div>
                                                                        `;
                // Note: We need to change the modal body structure slightly in the blade file to accommodate this if it expects a table structure.
                // The current blade has <tbody id="showProductTableBody"> inside a <table>. We should check if we need to replace the table with a div.
                $('#showProductTableBody').html(html);
                $('#showProductModal').modal('show');
            });

            // Auto-calculation logic removed since tax info isn't on product level anymore.
        });
    </script>

@endpush