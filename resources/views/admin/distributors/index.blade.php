@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 p-4">

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Distributors</h5>
                    <a href="{{ route('admin.distributors.create') }}" class="btn btn-primary fw-bold">
                        <i class="fa fa-plus me-1"></i> Add Distributor
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="distributors-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>GST</th>
                                    <th>Drug License No</th>
                                    <th>Contact No</th>
                                    <th>Address</th>
                                    <th>District</th>
                                    <th>Area</th>
                                    <th>Pincode</th>
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
        $('#distributors-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.distributors.index') }}",
            type: 'GET'
        },
        columns: [
            { data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return meta.row + 1; // Generates serial number based on row index
                }
            },
            { data: 'name', name: 'name' },
            { data: 'user.email', name: 'user.email' },
            { data: 'gst', name: 'gst' },
            { data: 'drug_license_no', name: 'drug_license_no' },
            { data: 'contact_no', name: 'contact_no' },
            { data: 'address', name: 'address' },
            { data: 'district.name', name: 'district.name' },
            { data: 'area.name', name: 'area.name' },
            { data: 'pincode', name: 'pincode' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        dom: "<'row mb-3'<'col-sm-12'B>>" + 
                            "<'row mb-3 d-flex align-items-center'<'col-md-6 d-flex justify-content-start'f><'col-md-6 d-flex justify-content-end text-end'l>>" +
                            "rtip",
        buttons: [
            { extend: 'copy', className: 'btn btn-primary btn-sm' },
            { extend: 'csv', className: 'btn btn-sm' },
            { extend: 'excel', className: 'btn btn-sm' },
            { extend: 'pdf', className: 'btn btn-sm' },
            { extend: 'print', className: 'btn btn-sm' },
        ]
    });

    </script>

@endpush