@extends('layouts.admin')

@section('page-body')
<div class="container-fluid pt-4">
    <div class="page-title text-start mb-4">
        <div class="row m-0">
          <div class="col-sm-6 p-0">
            <h4 class="mb-0 fw-bold">Master Order Performance Report</h4>
            <p class="text-muted mb-0 small"><i class="fa fa-info-circle me-1"></i> Comprehensive view of all retail orders and product distribution metrics.</p>
          </div>
          <div class="col-sm-6 p-0 text-end">
              <nav aria-label="breadcrumb">
                  <ol class="breadcrumb justify-content-end mb-0 bg-transparent">
                      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                      <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                      <li class="breadcrumb-item active">Master Orders</li>
                  </ol>
              </nav>
          </div>
        </div>
    </div>

    <!-- Filters Section -->
    @include('admin.reports.partials._filters', [
        'reportType' => 'orders',
        'salesManagers' => $salesManagers,
        'distributors' => $distributors,
        'retailers' => $retailers,
        'fieldStaffs' => $fieldStaffs
    ])

    <!-- Report Table -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header border-0 py-3">
                    <h5 class="mb-0 fw-bold text-primary" id="reportTableHeading">Detailed Sales Records</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive px-4 pb-4">
                        <table class="table table-hover w-100" id="ordersReportTable">
                            <thead>
                                <tr>
                                    <th>Order Details</th>
                                    <th>Invoice No</th>
                                    <th>Sales Context</th>
                                    <th>Volume</th>
                                    <th class="text-end">Value</th>
                                    <th class="text-center">Payment</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // DataTable Initialization
        const table = $('#ordersReportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.reports.orders') }}",
                data: function(d) {
                    d.order_type = $('input[name="order_type"]').val();
                    d.payment_status = $('#payment_status').val();
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                    d.sales_manager_id = $('#sales_manager_id').val();
                    d.distributor_id = $('#distributor_id').val();
                    d.retailer_id = $('#retailer_id').val();
                    d.fieldstaff_id = $('#fieldstaff_id').val();
                    d.status = $('#status').val();
                    d.brand = $('#brand').val();
                    d.period = $('.preset-btn.active').data('range');
                }
            },
            columns: [
                { 
                    data: 'order_code', 
                    name: 'order_code',
                    render: function(data, type, row) {
                        return `<div class="fw-bold text-dark">${data}</div><div class="small text-muted">${row.placed_at}</div>`;
                    }
                },
                {
                    data: 'invoice_no',
                    name: 'invoice_no',
                    render: function(data, type, row) {
                        return data ? `<div class="fw-bold text-primary" style="font-size: 0.85rem;">${data}</div>` : `<div class="small text-muted fst-italic">N/A</div>`;
                    }
                },
                { 
                    data: 'retailer_name', 
                    name: 'retailer_name',
                    searchable: false,
                    render: function(data, type, row) {
                        return `<div class="fw-bold" style="font-size: 0.85rem;">${data}</div><div class="small text-muted">via ${row.distributor_name}</div>`;
                    }
                },
                { 
                    data: 'total_quantity', 
                    name: 'total_quantity',
                    searchable: false,
                    render: function(data, type, row) {
                        return `<div class="fw-bold">${data} Units</div><div class="small text-muted text-nowrap">${row.total_items} SKUs</div>`;
                    }
                },
                { data: 'total_amount', name: 'total_amount', className: 'fw-bold text-primary text-end', searchable: false },
                { 
                    data: 'payment_status', 
                    name: 'payment_status',
                    className: 'text-center',
                    render: function(data) {
                        let status = (data || 'pending').toLowerCase();
                        let badgeClass = 'bg-light-secondary text-secondary';
                        if (status === 'paid') badgeClass = 'bg-light-success text-success';
                        else badgeClass = 'bg-light-warning text-warning';
                        return `<span class="badge ${badgeClass} text-uppercase px-2 py-1" style="font-size: 0.65rem;">${status}</span>`;
                    }
                },
                { 
                    data: 'status', 
                    name: 'status',
                    className: 'text-center',
                    render: function(data) {
                        let status = (data || 'pending').toLowerCase();
                        let badgeClass = 'bg-secondary text-white'; 
                        if (status === 'delivered') badgeClass = 'bg-success text-white';
                        else if (status === 'cancelled') badgeClass = 'bg-danger text-white';
                        else if (status === 'rejected') badgeClass = 'bg-dark-red text-white';
                        else if (status === 'pending') badgeClass = 'bg-secondary text-white';
                        else if (status === 'processing') badgeClass = 'bg-warning text-white';
                        else if (status === 'approved') badgeClass = 'bg-info text-white';
                        return `<span class="badge ${badgeClass} text-uppercase px-2 py-1" style="font-size: 0.65rem;">${status}</span>`;
                    }
                }
            ],
            dom: '<"row mb-3 align-items-center"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6 text-end"f>>t<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>', 
            buttons: [
                {
                    extend: 'print',
                    text: '<i class="fa fa-print me-1"></i> Print',
                    className: 'btn btn-sm btn-info',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: ':visible'
                    },
                    title: 'Orders Report - ' + new Date().toLocaleDateString(),
                    customize: function (win) {
                        $(win.document.body).addClass('landscape');
                        $(win.document.body).find('.dataTables_paginate, .pagination, .dataTables_info').hide();
                        $(win.document.body).find('table').addClass('compact').css('font-size', 'inherit');
                    }
                },
                {
                    text: '<i class="fa fa-file-excel-o me-1"></i> Excel',
                    className: 'btn btn-sm btn-success',
                    action: function (e, dt, button, config) {
                        exportProductWiseCSV(dt);
                    }
                },
                {
                    text: '<i class="fa fa-file-text-o me-1"></i> CSV',
                    className: 'btn btn-sm btn-secondary',
                    action: function (e, dt, button, config) {
                        exportProductWiseCSV(dt);
                    }
                }
            ],
            pageLength: 25,
            order: [[1, 'desc']],
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                search: "_INPUT_",
                searchPlaceholder: "Search report data..."
            }
        });

        function exportProductWiseCSV(dt) {
            let orderType = $('input[name="order_type"]').val() || 'retailer';
            let rows = dt.rows({ search: 'applied' }).data().toArray();
            let csvContent = "\uFEFF"; // UTF-8 BOM
            
            if (orderType === 'retailer') {
                csvContent += "No.,Order Code,Retailer,Shop Name,District,Area,Distributor,Sales Manager,Field Staff,Phone,GST,Drug License,Product Code,Product Name,Brand,Variant,Qty,Free Qty,Unit,Unit Price,Total Amount,Status,Placed At,Delivered At,Payment Status\n";
                let slNo = 1;
                rows.forEach(function(row) {
                    let baseData = [
                        slNo++,
                        row.order_code,
                        row.retailer_name || (row.retailer && row.retailer.user ? row.retailer.user.name : ''),
                        row.retailer && row.retailer.shop_name ? row.retailer.shop_name : '',
                        row.retailer && row.retailer.district ? (row.retailer.district.name || '') : '',
                        row.retailer && row.retailer.area ? (row.retailer.area.name || '') : '',
                        row.distributor_name || (row.distributor && row.distributor.user ? row.distributor.user.name : ''),
                        row.sales_manager_name || (row.sales_manager && row.sales_manager.user ? row.sales_manager.user.name : '') || (row.retailer && row.retailer.field_staff && row.retailer.field_staff.sales_manager && row.retailer.field_staff.sales_manager.user ? row.retailer.field_staff.sales_manager.user.name : ''),
                        row.fieldstaff_name || (row.field_staff && row.field_staff.user ? row.field_staff.user.name : ''),
                        row.retailer ? (row.retailer.contact_no || row.retailer.phone || '') : '',
                        row.retailer ? (row.retailer.gst || '') : '',
                        row.retailer ? (row.retailer.drug_license_no || '') : ''
                    ];
                    if (row.items && row.items.length > 0) {
                        row.items.forEach(function(item) {
                            let variantStr = (item.side ? item.side + ' ' : '') + (item.size || '');
                            let productCode = item.product_code || (item.product ? item.product.product_code : '');
                            let productName = item.product_name || item.name || (item.product ? item.product.product_name : '');
                            let brandName = item.brand || (item.product ? item.product.brand : '');
                            let qty = item.quantity || 0;
                            let freeQty = item.free_quantity || 0;
                            let unit = item.unit || 'Strips';
                            let unitPrice = item.unit_price || (item.product ? item.product.unit_price : 0);
                            let totalAmount = item.total_amount || (qty * unitPrice) || 0;
                            
                            let itemData = [
                                productCode,
                                productName,
                                brandName,
                                variantStr,
                                qty,
                                freeQty,
                                unit,
                                unitPrice,
                                totalAmount,
                                row.status || '',
                                row.placed_at || '',
                                row.delivered_at || '',
                                row.payment_status || 'Pending'
                            ];
                            csvContent += baseData.concat(itemData).map(val => `"${(val === null || val === undefined ? '' : val).toString().replace(/"/g, '""')}"`).join(",") + "\n";
                        });
                    } else {
                        let itemData = ['', '', '', '', '', '', '', '', '', '', row.status || '', row.placed_at || '', row.delivered_at || '', row.payment_status || 'Pending'];
                        csvContent += baseData.concat(itemData).map(val => `"${(val === null || val === undefined ? '' : val).toString().replace(/"/g, '""')}"`).join(",") + "\n";
                    }
                });
            } else {
                csvContent += "No.,Order Code,Distributor,Email,Phone,GST,Drug License,Sales Manager,Product Code,Product Name,Brand,Variant,Qty,Unit,Unit Price,Total Amount,Status,Placed At,Delivered At,Payment Status\n";
                let slNo = 1;
                rows.forEach(function(row) {
                    let baseData = [
                        slNo++,
                        row.order_code,
                        row.distributor_name || (row.distributor && row.distributor.user ? row.distributor.user.name : ''),
                        row.distributor ? (row.distributor.email || (row.distributor.user ? row.distributor.user.email : '')) : '',
                        row.distributor ? (row.distributor.contact_no || row.distributor.phone || '') : '',
                        row.distributor ? (row.distributor.gst || '') : '',
                        row.distributor ? (row.distributor.drug_license_no || '') : '',
                        row.sales_manager_name || (row.sales_manager && row.sales_manager.user ? row.sales_manager.user.name : '') || (row.distributor && row.distributor.sales_manager && row.distributor.sales_manager.user ? row.distributor.sales_manager.user.name : '')
                    ];
                    if (row.items && row.items.length > 0) {
                        row.items.forEach(function(item) {
                            let variantStr = (item.side ? item.side + ' ' : '') + (item.size || '');
                            let productCode = item.product_code || (item.product ? item.product.product_code : '');
                            let productName = item.product_name || item.name || (item.product ? item.product.product_name : '');
                            let brandName = item.brand || (item.product ? item.product.brand : '');
                            let qty = item.quantity || 0;
                            let unit = item.unit || 'Strips';
                            let unitPrice = item.unit_price || (item.product ? item.product.unit_price : 0);
                            let totalAmount = item.total_amount || (qty * unitPrice) || 0;
                            
                            let itemData = [
                                productCode,
                                productName,
                                brandName,
                                variantStr,
                                qty,
                                unit,
                                unitPrice,
                                totalAmount,
                                row.status || '',
                                row.placed_at || '',
                                row.delivered_at || '',
                                row.payment_status || 'Pending'
                            ];
                            csvContent += baseData.concat(itemData).map(val => `"${(val === null || val === undefined ? '' : val).toString().replace(/"/g, '""')}"`).join(",") + "\n";
                        });
                    } else {
                        let itemData = ['', '', '', '', '', '', '', '', row.status || '', row.placed_at || '', row.delivered_at || '', row.payment_status || 'Pending'];
                        csvContent += baseData.concat(itemData).map(val => `"${(val === null || val === undefined ? '' : val).toString().replace(/"/g, '""')}"`).join(",") + "\n";
                    }
                });
            }
            
            let blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            let url = URL.createObjectURL(blob);
            let link = document.createElement("a");
            link.setAttribute("href", url);
            let filename = orderType === 'retailer' ? 'Retailer_Orders_Report_' : 'Distributor_Orders_Report_';
            link.setAttribute("download", `${filename}${new Date().toISOString().slice(0,10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        window.reportsTable = table;

        // Apply Filters
        $('#applyFilters').on('click', function() {
            table.draw();
        });

        // Update Dynamic Heading based on Order Source
        function updateDynamicHeading() {
            const type = $('input[name="order_type"]').val();
            const sourceText = type === 'distributor' ? 'Distributor' : 'Retailer';
            $('#reportTableHeading').html(`<i class="fa fa-list-alt me-2"></i>Detailed <span class="text-info">${sourceText}</span> Orders Sales Records`);
        }

        // Initial update
        updateDynamicHeading();
        const initialType = $('input[name="order_type"]').val();
        table.column(2).visible(initialType === 'retailer'); // retailer_name is now at index 2
    });
</script>
@endpush

@push('styles')
<style>
    .bg-light-primary { background-color: rgba(var(--bs-primary-rgb), 0.1) !important; }
    .bg-light-success { background-color: rgba(var(--bs-success-rgb), 0.1) !important; }
    .bg-light-danger { background-color: rgba(var(--bs-danger-rgb), 0.1) !important; }
    .bg-light-warning { background-color: rgba(var(--bs-warning-rgb), 0.1) !important; }
    .bg-light-secondary { background-color: rgba(var(--bs-secondary-rgb), 0.1) !important; }
    
    #ordersReportTable thead th {
        font-size: 0.75rem !important;
        background-color: var(--med-bg-body) !important;
    }

    /* Search Filter Styling */
    .dataTables_filter {
        display: inline-block;
        margin-bottom: 0;
    }
    .dataTables_filter input {
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        padding: 6px 12px;
        width: 250px !important;
        font-size: 0.85rem;
        transition: all 0.2s;
    }
    .dataTables_filter input:focus {
        border-color: var(--med-primary);
        box-shadow: 0 0 0 0.2rem rgba(var(--med-primary-rgb), 0.15);
        outline: none;
    }
</style>
@endpush
@endsection
