@extends('layouts.admin')
<style>
    .action-buttons {
        display: flex !important;
        gap: 4px;
        align-items: center;
    }

    .action-buttons form {
        margin-bottom: 0;
    }

    .action-buttons .btn {
        margin: 0 !important;
    }

    /* Modal sizing and table compacting */
    .modal-xl {
        max-width: 1140px;
    }

    #orders-table td:last-child {
        white-space: nowrap !important;
    }

    /* Preview / full content helper */
    .preview-content {
        display: inline-block;
    }

    .full-content {
        display: block;
    }

    .full-content.d-none {
        display: none;
    }

    /* Fix DataTable Length Select Arrow Issue */
    .dataTables_length select.form-select {
        padding-right: 2.5rem !important;
        background-position: right 0.75rem center;
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
                        <h5><i class="fa fa-boxes me-2"></i>Stock</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#createInventoryModal">
                            <i class="fa fa-plus me-1"></i>Add Product
                        </button>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <div id="distributor_filter_container" class="d-none">
                            @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
                                <select id="distributor_filter" class="form-select form-select-sm border-primary"
                                    style="width: 200px; display: inline-block;">
                                    <option value="">All Distributors</option>
                                    @foreach($distributors as $d)
                                        <option value="{{ $d->id }}">{{ $d->user->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div class="table-responsive">
                            <table class="display table table-hover" id="inventories-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>

                                        <th>Product Code</th>
                                        <th>Product Name</th>
                                        @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
                                            <th>Distributor</th>
                                        @endif
                                        <th>Batch No</th>
                                        <th>Expiry Date</th>
                                        <th>Stock (Total)</th>
                                        <th>Breakdown</th>
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

    {{-- Create Inventory Modal --}}
    <div class="modal fade" id="createInventoryModal" tabindex="-1" aria-labelledby="createInventoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createInventoryModalLabel">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createInventoryForm">
                    @csrf
                    <div class="modal-body">


                        <div class="mb-3">
                            <label for="create_product_id" class="form-label">Product</label>
                            <select name="product_id" id="create_product_id" class="form-select" required>
                                <option value="">-- Select product --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" data-code="{{ $p->product_code }}"
                                        data-strip-size="{{ $p->strip_size ?? 0 }}" data-box-size="{{ $p->box_size ?? 0 }}"
                                        data-carton-size="{{ $p->carton_size ?? 0 }}"
                                        data-pack="{{ strtolower($p->pack ?? '') }}">
                                        {{ $p->product_name }}{{ (!empty(trim($p->product_code)) && strtoupper(trim($p->product_code)) !== 'N/A') ? ' ('.$p->product_code.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
                            <div class="mb-3">
                                <label for="create_distributor_id" class="form-label">Distributor</label>
                                <select name="distributor_id" id="create_distributor_id" class="form-select" required>
                                    <option value="">-- Select Distributor --</option>
                                    @foreach($distributors as $d)
                                        <option value="{{ $d->id }}">{{ $d->user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                var select = document.getElementById('create_product_id');
                                var code = document.getElementById('create_distributor_product_code');
                                var packInfo = document.getElementById('create_pack_info');

                                var calcCtn = document.getElementById('create_calc_ctn');
                                var calcQty = document.getElementById('create_input_qty');
                                var calcUnit = document.getElementById('create_input_unit');
                                var totalInput = document.getElementById('create_stock');

                                function calculateCreateTotal() {
                                    var opt = select.options[select.selectedIndex];
                                    if (!opt || opt.value === "") return;

                                    var boxSize = parseInt(opt.getAttribute('data-box-size')) || 0;
                                    var cartonSize = parseInt(opt.getAttribute('data-carton-size')) || 0;

                                    var qty = parseInt(calcQty.value) || 0;
                                    var unit = calcUnit.value;

                                    var total = 0;
                                    if (unit === 'strip') {
                                        total = qty;
                                    } else if (unit === 'box') {
                                        total = qty * boxSize;
                                    } else if (unit === 'carton') {
                                        total = qty * boxSize * (cartonSize || 1);
                                    }

                                    totalInput.value = total;
                                }

                                if (select) {
                                    select.addEventListener('change', function () {
                                        var selected = select.options[select.selectedIndex];
                                        if (selected && selected.value !== "") {
                                            var prodCode = selected.getAttribute('data-code');
                                            var bSize = selected.getAttribute('data-box-size');
                                            var cSize = selected.getAttribute('data-carton-size');

                                            var pPack = selected.getAttribute('data-pack') || '';
                                            var pName = (selected.text || '').toLowerCase();
                                            var isCount = (parseInt(bSize) === 1 && parseInt(cSize) === 1) ||
                                                pPack.includes('nos') || pPack.includes('count') ||
                                                pPack.includes('pair') || pPack.includes('bottle') ||
                                                pPack.includes('ml') || pPack.includes('gm') || pPack.includes('syp') ||
                                                pName.includes('syp') || pName.includes('syrup') || pName.includes('drop') || pName.includes('ointment');

                                            var unitSelect = calcUnit;
                                            var totalLabel = document.getElementById('create_stock_label');

                                            // Rebuild unit options based on pack type
                                            unitSelect.innerHTML = '';
                                            if (isCount) {
                                                unitSelect.innerHTML += '<option value="strip">Nos</option>';
                                                totalLabel.innerText = "Converted Total (Nos)";
                                                packInfo.innerHTML = `Packaging: <b>${bSize} Nos/Box</b> | <b>${cSize} Box/Ctn</b>`;
                                            } else {
                                                unitSelect.innerHTML += '<option value="strip">Strips</option>';
                                                totalLabel.innerText = "Converted Total (Strips)";
                                                packInfo.innerHTML = `Packaging: <b>${bSize} Str/Box</b> | <b>${cSize} Box/Ctn</b>`;
                                            }
                                            unitSelect.innerHTML += '<option value="box">Boxes</option>';
                                            unitSelect.innerHTML += '<option value="carton">Cartons</option>';

                                            if (code && (!code.value || code.value.trim() === '')) {
                                                code.value = prodCode || '';
                                            }

                                            calculateCreateTotal();
                                        } else {
                                            packInfo.innerText = "Select a product to see packaging rules";
                                        }
                                    });
                                }

                                $('#create_input_qty, #create_input_unit').on('input change', calculateCreateTotal);
                            });
                        </script>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Batch No</label>
                                <input type="text" name="batch_no" class="form-control" placeholder="Enter Batch No"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Expiry Date</label>
                                <input type="date" name="expiry_date" class="form-control" required>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Quantity</label>
                                <input type="number" id="create_input_qty" class="form-control" placeholder="0" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Unit</label>
                                <select id="create_input_unit" class="form-select">
                                    <option value="strip">Strips</option>
                                    <option value="box">Boxes</option>
                                    <option value="carton">Cartons</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="create_stock" id="create_stock_label"
                                class="form-label fw-bold text-muted small">Converted Total
                                (Strips)</label>
                            <input type="number" name="stock" id="create_stock" class="form-control bg-light" value="0"
                                readonly required>
                            <div id="create_pack_info" class="form-text small text-info opacity-75">Select a product to see
                                packaging rules</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Inventory Modal --}}
    <div class="modal fade" id="editInventoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editInventoryForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Distributor Product Code</label>
                            <input type="text" name="distributor_product_code" id="edit_distributor_product_code"
                                class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="product_name" id="edit_product_name" class="form-control" required>
                        </div>

                        <input type="hidden" name="product_id" id="edit_product_id">

                        @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
                            <div class="mb-3">
                                <label for="edit_distributor_id" class="form-label">Distributor</label>
                                <select name="distributor_id" id="edit_distributor_id" class="form-select" required>
                                    <option value="">-- Select Distributor --</option>
                                    @foreach($distributors as $d)
                                        <option value="{{ $d->id }}">{{ $d->user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="distributor_id" id="edit_distributor_id">
                        @endif

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Batch No</label>
                                <input type="text" name="batch_no" id="edit_batch_no" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Expiry Date</label>
                                <input type="date" name="expiry_date" id="edit_expiry_date" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" id="edit_stock" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Stock Adjustment Modal --}}
    <div class="modal fade" id="stockAdjustmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title d-flex align-items-center" id="stockAdjustmentModalLabel">
                        <i class="fa fa-boxes me-2"></i> <span>Adjust Stock</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="stockAdjustmentForm">
                    @csrf
                    <input type="hidden" name="id" id="stock_adj_id">
                    <input type="hidden" name="operation" id="stock_adj_op">
                    <div class="modal-body p-4">
                        <div class="text-center mb-3">
                            <h6 class="text-muted" id="stock_adj_product_name"></h6>
                        </div>
                        <div class="row g-2 mb-4">
                            <div class="col-md-8">
                                <label class="form-label fw-bold text-muted small uppercase">Quantity to <span
                                        id="op_text_label">Update</span></label>
                                <input type="number" id="adj_input_qty"
                                    class="form-control form-control-lg text-center fw-bold" placeholder="0" min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-muted small uppercase">Unit</label>
                                <select id="adj_input_unit" class="form-select form-select-lg">
                                    <option value="strip">Strips</option>
                                    <option value="box">Boxes</option>
                                    <option value="carton">Cartons</option>
                                </select>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded text-center">
                            <label class="form-label fw-bold text-muted small mb-1">EFFECTIVE STRIPS <span
                                    id="op_text_caps">UPDATED</span></label>
                            <input type="number" name="quantity" id="stock_adj_quantity"
                                class="form-control form-control-lg text-center bg-transparent border-0 fw-bold fs-3"
                                value="0" readonly required>
                            <div id="adj_pack_info" class="text-info small opacity-75 mt-1"></div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4" id="btn_save_stock">Update Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">Confirm Removal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3">Are you sure you want to remove this item from your stock?</p>
                    <div class="text-muted small">
                        <i class="fa fa-info-circle me-1"></i> The product definition will <strong>NOT</strong> be deleted.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn"><i class="fa fa-trash me-1"></i>
                        Remove Item</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Product Details Modal --}}
    <div class="modal fade" id="productDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="prodDetailName">Product Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- <div class="text-center mb-4">
                        <img id="prodDetailImage" src="" class="img-fluid rounded" style="max-height: 150px;"
                            alt="Product Image">
                    </div> --}}
                    <ul class="list-group list-group-flush" id="prodDetailList">
                        <!-- Details populated via JS -->
                    </ul>
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
        $(document).ready(function () {
            var table = $('#inventories-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('inventories.index') }}",
                    type: 'GET',
                    data: function (d) {
                        d.distributor_id = $('#distributor_filter').val();
                    },
                    dataSrc: 'data',
                    error: function (xhr, error, thrown) {
                        console.error('Inventories AJAX error:', xhr.responseText);
                        alert('Inventories AJAX Error: ' + (xhr.status ? xhr.status + ' - ' + xhr.statusText : error));
                    }
                },
                dom: "<'row mb-3'<'col-sm-12'B>>" +
                    "<'row mb-3'<'col-md-6'l><'col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row mt-3'<'col-sm-12 col-md-5 d-flex justify-content-center justify-content-md-start align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-center justify-content-md-end align-items-center'p>>",
                initComplete: function () {
                    var $filter = $('#distributor_filter_container');
                    $filter.removeClass('d-none').addClass('ms-4');
                    $('.dataTables_length').addClass('d-flex align-items-center').append($filter);
                },
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
                },
                order: [
                    [2, 'desc']
                ],
                columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'distributor_product_code',
                    name: 'distributor_product_code'
                },
                {
                    data: 'product_name',
                    name: 'product_name',
                    render: function (data, type, row) {
                        let detailJson = JSON.stringify(row.product_details).replace(/"/g, '&quot;');
                        return `
                                                                <div class="d-flex align-items-center">
                                                                    <a href="javascript:void(0)" class="text-primary fw-bold product-detail-link" 
                                                                       data-name="${data}" \
                                                                       data-details='${detailJson}'>
                                                                       ${data}
                                                                    </a>
                                                                </div>
                                                            `;
                    }
                },
                    @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
                            data: 'distributor_name',
                            name: 'distributor_name'
                        },
                    @endif{
                    data: 'batch_no',
                    name: 'batch_no'
                },
                {
                    data: 'expiry_date',
                    name: 'expiry_date'
                },
                {
                    data: 'stock',
                    name: 'stock',
                    render: function (data, type, row) {
                        return `<span class="badge ${data > 0 ? 'bg-success' : 'bg-danger'}">${data}</span>`;
                    }
                },
                {
                    data: 'stock',
                    name: 'breakdown',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        if (!row.product_details) return '-';

                        let boxSize = row.product_details.box_size || 0;
                        let cartonSize = row.product_details.carton_size || 0;

                        let pPack = row.product_details.pack ? row.product_details.pack.toLowerCase() : '';
                        let pName = row.product_name ? row.product_name.toLowerCase() : '';
                        let isCount = (parseInt(boxSize) === 1 && parseInt(cartonSize) === 1) ||
                            pPack.includes('nos') || pPack.includes('count') ||
                            pPack.includes('pair') || pPack.includes('bottle') ||
                            pPack.includes('ml') || pPack.includes('gm') || pPack.includes('syp') ||
                            pName.includes('syp') || pName.includes('syrup') || pName.includes('drop') || pName.includes('ointment');
                        let baseStr = isCount ? 'Nos' : 'Str';

                        if (isCount) {
                            // Requirement: Display just number as counts for "Nos"
                            let countHtml = `<span class="badge bg-secondary me-1">${data} ${baseStr}</span>`;
                            
                            // Optional: Show conversions if relevant (boxSize > 1)
                            if (boxSize > 1) {
                                let remaining = data;
                                let cartons = 0;
                                if (cartonSize > 0) {
                                    let stripsPerCarton = boxSize * cartonSize;
                                    cartons = Math.floor(data / stripsPerCarton);
                                    remaining = data % stripsPerCarton;
                                }
                                let boxes = Math.floor(remaining / boxSize);
                                if (boxes > 0) countHtml += `<span class="badge bg-info text-white me-1">${boxes} Box</span>`;
                                if (cartons > 0) countHtml += `<span class="badge bg-primary me-1">${cartons} Ctn</span>`;
                            }

                            countHtml += `<div class="mt-1 small text-muted" style="font-size: 0.7rem;">
                                        (${boxSize > 0 ? boxSize : 1} ${baseStr}/Box | ${cartonSize || 0} Box/Ctn)
                                     </div>`;
                            return countHtml;
                        }

                        if (boxSize <= 0) return '-';

                        let stripsPerCarton = boxSize * (cartonSize || 1);
                        let cartons = 0;
                        let remaining = data;

                        if (cartonSize > 0) {
                            cartons = Math.floor(data / stripsPerCarton);
                            remaining = data % stripsPerCarton;
                        }

                        let boxes = Math.floor(remaining / boxSize);
                        let strips = remaining % boxSize;

                        let html = '';
                        // Requirement: Show strips counts priorily, then Box and Carton
                        if (strips > 0 || (boxes === 0 && cartons === 0)) {
                            html += `<span class="badge bg-secondary me-1">${strips} ${baseStr}</span>`;
                        }
                        if (boxes > 0) {
                            html += `<span class="badge bg-info text-white me-1">${boxes} Box</span>`;
                        }
                        if (cartonSize > 0 && cartons > 0) {
                            html += `<span class="badge bg-primary me-1">${cartons} Ctn</span>`;
                        }

                        html += `<div class="mt-1 small text-muted" style="font-size: 0.7rem;">
                                    (${boxSize} ${baseStr}/Box | ${cartonSize || 0} Box/Ctn)
                                 </div>`;

                        return html || '0';
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function (id, type, row) {
                        let deleteUrl = "{{ route('inventories.destroy', ':id') }}".replace(':id', id);
                        let csrf = "{{ csrf_token() }}";
                        let rowData = JSON.stringify(row).replace(/"/g, '&quot;');

                        return `
                                                    <div class="action-buttons">
                                                        <button type="button" class="btn btn-sm btn-info edit-btn" data-inventory='${rowData}' title="Edit Stock"><i class="fa fa-edit"></i></button>
                                                        <button type="button" class="btn btn-sm btn-success stock-btn" data-id="${id}" data-op="add" data-name="${row.product_name}" data-box-size="${row.product_details?.box_size || 0}" data-carton-size="${row.product_details?.carton_size || 0}" title="Add Stock"><i class="fa fa-plus"></i></button>
                                                        <button type="button" class="btn btn-sm btn-warning stock-btn" data-id="${id}" data-op="subtract" data-name="${row.product_name}" data-box-size="${row.product_details?.box_size || 0}" data-carton-size="${row.product_details?.carton_size || 0}" title="Reduce Stock"><i class="fa fa-minus"></i></button>

                                                        <form id="delete-form-${id}" action="${deleteUrl}" method="POST" style="display:inline;">
                                                            <input type="hidden" name="_token" value="${csrf}">
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="${id}" title="Remove from Stock"><i class="fa fa-trash"></i></button>
                                                        </form>
                                                    </div>
                                                    `;
                    }
                }
                ]
            });

            // Filter Change Handler
            $('#distributor_filter').on('change', function () {
                table.ajax.reload();
            });

            // Edit Handler
            $('#inventories-table').on('click', '.edit-btn', function () {
                var data = $(this).data('inventory');
                if (typeof data === 'string') {
                    data = JSON.parse(data);
                }

                $('#edit_product_name').val(data.product_name);
                $('#edit_stock').val(data.stock);
                $('#edit_batch_no').val(data.batch_no);
                $('#edit_expiry_date').val(data.expiry_date ? data.expiry_date.split('-').reverse().join('-') : ''); // Convert d-m-Y back to Y-m-d if needed, but wait, data format is d-m-Y in JS

                // If expiry_date is d-m-Y, convert it to Y-m-d for date input
                if (data.expiry_date && data.expiry_date.includes('-')) {
                    let parts = data.expiry_date.split('-');
                    if (parts[0].length === 2) {
                        $('#edit_expiry_date').val(`${parts[2]}-${parts[1]}-${parts[0]}`);
                    } else {
                        $('#edit_expiry_date').val(data.expiry_date);
                    }
                }

                $('#edit_distributor_id').val(data.distributor_id);
                // Hide dist code field if it's auto-managed or read-only in edit too
                // But let's keep it in edit form if admin needs to fix it.
                $('#edit_distributor_product_code').val(data.distributor_product_code);

                var url = "{{ route('inventories.update', ':id') }}".replace(':id', data.id);
                $('#editInventoryForm').attr('action', url);

                $('#editInventoryModal').modal('show');
            });

            // Stock Adjustment Handler
            let currentBoxSize = 0;
            let currentCartonSize = 0;

            function calculateAdjTotal() {
                let qty = parseInt($('#adj_input_qty').val()) || 0;
                let unit = $('#adj_input_unit').val();

                let total = 0;
                if (unit === 'strip') {
                    total = qty;
                } else if (unit === 'box') {
                    total = qty * currentBoxSize;
                } else if (unit === 'carton') {
                    total = qty * currentBoxSize * (currentCartonSize || 1);
                }
                $('#stock_adj_quantity').val(total);
            }

            $('#inventories-table').on('click', '.stock-btn', function () {
                let id = $(this).data('id');
                let op = $(this).data('op'); // 'add' or 'subtract'
                let name = $(this).data('name');
                currentBoxSize = parseInt($(this).data('box-size')) || 0;
                currentCartonSize = parseInt($(this).data('carton-size')) || 0;

                $('#stock_adj_id').val(id);
                $('#stock_adj_op').val(op);
                $('#stock_adj_product_name').text(name);
                $('#adj_pack_info').html(`Packaging Pattern: <b>${currentBoxSize}</b> Strips/Box, <b>${currentCartonSize}</b> Boxes/Carton`);

                // Reset calc fields
                $('#adj_input_qty').val('');
                $('#adj_input_unit').val('strip');
                $('#stock_adj_quantity').val(0);

                let title, btnClass, btnText;
                if (op === 'add') {
                    title = 'Add Stock';
                    btnText = 'Add Stock';
                    btnClass = 'btn-success';
                    $('#op_text').text('Addition').addClass('text-success').removeClass('text-danger');
                    $('#op_text_label').text('Addition');
                    $('#op_text_caps').text('ADDED').addClass('text-success').removeClass('text-danger');
                } else {
                    title = 'Reduce Stock';
                    btnText = 'Reduce Stock';
                    btnClass = 'btn-warning';
                    $('#op_text').text('Reduction').addClass('text-danger').removeClass('text-success');
                    $('#op_text_label').text('Reduction');
                    $('#op_text_caps').text('REDUCED').addClass('text-danger').removeClass('text-success');
                }

                $('#stockAdjustmentModalLabel span').text(title);
                $('#btn_save_stock').text(btnText).removeClass('btn-success btn-warning').addClass(btnClass);

                $('#stockAdjustmentModal').modal('show');
            });

            $('#adj_input_qty, #adj_input_unit').on('input change', calculateAdjTotal);

            // Delete Confirmation Handler
            let deleteFormId = null;
            $('#inventories-table').on('click', '.delete-btn', function () {
                let id = $(this).data('id');
                deleteFormId = '#delete-form-' + id;
                $('#deleteConfirmModal').modal('show');
            });

            $('#confirmDeleteBtn').click(function () {
                if (deleteFormId) {
                    let form = $(deleteFormId);
                    let url = form.attr('action');
                    let formData = form.serialize();

                    $.post(url, formData, function (res) {
                        $('#deleteConfirmModal').modal('hide');
                        if (typeof showToast === 'function') showToast('success', res.success);
                        table.ajax.reload(null, false);
                    }).fail(function (xhr) {
                        if (typeof showToast === 'function') showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Error deleting item');
                    });
                }
            });

            // Add handler for Create form
            $('#createInventoryForm').submit(function (e) {
                e.preventDefault();
                let url = "{{ route('inventories.store') }}";
                let formData = $(this).serialize();

                $.post(url, formData, function (res) {
                    $('#createInventoryModal').modal('hide');
                    $('#createInventoryForm')[0].reset();
                    if (typeof showToast === 'function') showToast('success', res.success);
                    table.ajax.reload(null, false);
                }).fail(function (xhr) {
                    if (typeof showToast === 'function') showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Error creating item');
                });
            });

            // Add handler for Edit form
            $('#editInventoryForm').submit(function (e) {
                e.preventDefault();
                let url = $(this).attr('action');
                let formData = $(this).serialize();

                $.post(url, formData, function (res) {
                    $('#editInventoryModal').modal('hide');
                    if (typeof showToast === 'function') showToast('success', res.success);
                    table.ajax.reload(null, false);
                }).fail(function (xhr) {
                    if (typeof showToast === 'function') showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Error updating item');
                });
            });

            // Product Details Modal Handler
            $('#inventories-table').on('click', '.product-detail-link', function () {
                let name = $(this).data('name');
                let image = $(this).data('image');
                let details = $(this).data('details');

                $('#prodDetailName').text(name);
                $('#prodDetailImage').attr('src', image);

                let html = '';
                if (details) {
                    if (details.generic_name) html += `<li class="list-group-item d-flex justify-content-between"><span>Generic Name</span> <strong>${details.generic_name}</strong></li>`;
                    if (details.pack) html += `<li class="list-group-item d-flex justify-content-between"><span>Pack</span> <strong>${details.pack}</strong></li>`;
                    if (details.mrp) html += `<li class="list-group-item d-flex justify-content-between"><span>MRP</span> <strong>${details.mrp}</strong></li>`;
                    if (details.ptr) html += `<li class="list-group-item d-flex justify-content-between"><span>PTR</span> <strong>${details.ptr}</strong></li>`;
                    if (details.hsn_code) html += `<li class="list-group-item d-flex justify-content-between"><span>HSN</span> <strong>${details.hsn_code}</strong></li>`;
                    if (details.box_size) html += `<li class="list-group-item d-flex justify-content-between"><span>Strip / Box</span> <strong>${details.box_size}</strong></li>`;
                    if (details.carton_size) html += `<li class="list-group-item d-flex justify-content-between"><span>Box / Carton</span> <strong>${details.carton_size}</strong></li>`;
                } else {
                    html = '<li class="list-group-item text-center text-muted">No additional details available</li>';
                }
                $('#prodDetailList').html(html);

                $('#productDetailsModal').modal('show');
            });

            $('#stockAdjustmentForm').submit(function (e) {
                e.preventDefault();
                let id = $('#stock_adj_id').val();
                let formData = $(this).serialize();
                let url = "{{ route('inventories.adjust-stock', ':id') }}".replace(':id', id);

                $.post(url, formData, function (res) {
                    $('#stockAdjustmentModal').modal('hide');
                    if (res.success) {
                        // alert(res.success); 
                        // Use toast if available or simple alert
                        // Assuming global showToast exists from layout
                        if (typeof showToast === 'function') showToast('success', res.success);
                        table.ajax.reload(null, false);
                    }
                }).fail(function (xhr) {
                    if (typeof showToast === 'function') showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Error updating stock');
                    else alert('Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Server Error'));
                });
            });

        });
    </script>
@endpush