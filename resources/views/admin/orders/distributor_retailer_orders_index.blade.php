@extends('layouts.admin')

@push('styles')
<!-- Bootstrap 5 + DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<style>
    /* Small & clean table rows */
    #retailer-orders-table tbody td {
        font-size: 0.85rem !important;
        padding: 6px 10px !important;
    }

    /* Search bar aligned left */
    .dataTables_filter {
        text-align: left !important;
    }

    .dataTables_filter input {
        width: 230px !important;
        margin-left: 8px !important;
    }

    /* Entries dropdown aligned right */
    .dataTables_length {
        text-align: right !important;
    }

    .dataTables_length select {
        width: 70px !important;
        margin-left: 5px !important;
    }

    /* Beautiful buttons */
    .dt-buttons .btn {
        border-radius: 6px !important;
        padding: 4px 10px !important;
        font-size: 0.75rem !important;
    }

    .btn-view {
        background: #0dcaf0;
        padding: 4px 8px;
        font-size: 0.75rem;
        border-radius: 5px;
        color: #fff;
    }

    .btn-view:hover {
        background: #0bb0d4;
    }
</style>
@endpush



@section('page-body')

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
                            <th>Retailer</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>
    </div>
</div>

@endsection



@push('scripts')
<script>
$(document).ready(function () {

    let table = $('#retailer-orders-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('distributor.orders.index') }}",

        columns: [
            { data: 'id', name: 'id' },
            { data: 'retailer_name', name: 'retailer_name' },
            { data: 'product_name', name: 'product_name' },
            { data: 'quantity', name: 'quantity' },
            { data: 'total_amount', name: 'total_amount' },

            // STATUS
            {
                data: 'status',
                name: 'status',
                render: function (status) {
                    if (!status) return '';

                    let colors = {
                        "Pending": "warning",
                        "Delivered": "success",
                        "Cancelled": "danger"
                    };

                    return `<span class="badge bg-${colors[status] ?? 'secondary'}">${status}</span>`;
                }
            },

            // ACTIONS
            {
                data: 'id',
                name: 'actions',
                orderable: false,
                searchable: false,
                render: function (id) {
                    let showUrl = "{{ route('retailer-orders-management.show', ':id') }}".replace(':id', id);

                    return `
                        <a href="${showUrl}" class="btn-view">
                            <i class="fa fa-eye"></i>
                        </a>`;
                }
            },
        ],

        dom:
            "<'row mb-3'<'col-sm-12'B>>" +
            "<'row mb-3 d-flex align-items-center'<'col-md-6'f><'col-md-6 text-end'l>>" +
            "rtip",

        buttons: [
            { extend: 'copy', className: 'btn btn-sm' },
            { extend: 'csv', className: 'btn btn-sm' },
            { extend: 'excel', className: 'btn btn-sm' },
            { extend: 'pdf', className: 'btn btn-sm' },
            { extend: 'print', className: 'btn btn-sm' },
        ]
    });

});
</script>
@endpush
