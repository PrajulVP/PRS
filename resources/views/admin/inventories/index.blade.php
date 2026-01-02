@extends('layouts.admin')
<style>
    .dataTables_filter {
        text-align: left !important;
    }

    .dataTables_filter input {
        width: 230px !important;
        margin-left: 10px !important;
    }

    .dataTables_length {
        text-align: right !important;
    }

    .action-buttons {
        display: inline-flex !important;
        gap: 4px;
        align-items: center;
    }

    .action-buttons .btn {
        padding: 4px 8px !important;
        font-size: 0.75rem !important;
    }

    /* Modal sizing and table compacting */
    .modal-xl { max-width: 1140px; }
    #orders-table td:last-child { white-space: nowrap !important; }

    /* Preview / full content helper */
    .preview-content { display: inline-block; }
    .full-content { display: block; }
    .full-content.d-none { display: none; }
</style>

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fa fa-boxes me-2"></i>Inventory</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createInventoryModal">
                        <i class="fa fa-plus me-1"></i>Add Inventory
                    </button>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <div class="table-responsive">
                        <table class="display table table-striped table-hover" id="inventories-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Distributor Code</th>
                                    <th>Product Name</th>
                                    <th>Stock</th>
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
<div class="modal fade" id="createInventoryModal" tabindex="-1" aria-labelledby="createInventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createInventoryModalLabel">Create Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('inventories.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="create_distributor_product_code" class="form-label">Distributor Product Code</label>
                        <input type="text" name="distributor_product_code" id="create_distributor_product_code" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="create_product_id" class="form-label">Product</label>
                        <select name="product_id" id="create_product_id" class="form-select" required>
                            <option value="">-- Select product --</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}" data-code="{{ $p->product_code }}">{{ $p->product_name }} ({{ $p->product_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <script>
                    document.addEventListener('DOMContentLoaded', function(){
                        var select = document.getElementById('create_product_id');
                        var code = document.getElementById('create_distributor_product_code');
                        if(select){
                            select.addEventListener('change', function(){
                                var selected = select.options[select.selectedIndex];
                                var prodCode = selected ? selected.getAttribute('data-code') : '';
                                // Only set distributor code if it's empty
                                if(code && (!code.value || code.value.trim() === '')){
                                    code.value = prodCode || '';
                                }
                            });
                        }
                    });
                    </script>

                    <div class="mb-3">
                        <label for="create_stock" class="form-label">Stock</label>
                        <input type="number" name="stock" id="create_stock" class="form-control" value="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Inventory</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#inventories-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('inventories.index') }}",
            type: 'GET',
            dataSrc: 'data',
            error: function(xhr, error, thrown) {
                console.error('Inventories AJAX error:', xhr.responseText);
                alert('Inventories AJAX Error: ' + (xhr.status ? xhr.status + ' - ' + xhr.statusText : error));
            }
        },
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
            { data: 'distributor_product_code', name: 'distributor_product_code' },
            { data: 'product_name', name: 'product_name' },
            { data: 'stock', name: 'stock', render: function(data){ return `<span class="badge ${data>0? 'bg-success' : 'bg-danger'}">${data}</span>` } },
            { data: 'id', orderable: false, searchable: false, render: function(id, type, row){
                let deleteUrl = "{{ route('inventories.destroy', ':id') }}".replace(':id', id);
                let csrf = "{{ csrf_token() }}";
                let rowData = JSON.stringify(row).replace(/"/g, '&quot;');

                return `
                <div class="action-buttons">
                    <button type="button" class="btn btn-sm btn-primary view-btn" data-inventory='${rowData}'><i class="fa fa-eye"></i></button>
                    <button type="button" class="btn btn-sm btn-primary edit-btn" data-inventory='${rowData}'><i class="fa fa-edit"></i></button>
                    <form action="${deleteUrl}" method="POST" onsubmit="return confirm('Are you sure?')" class="mb-0">
                        <input type="hidden" name="_token" value="${csrf}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                    </form>
                </div>
                `;
            } }
        ]
    });

    // Edit handlers and view handlers can be added as needed
});
</script>
@endpush
