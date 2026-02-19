@extends('layouts.admin')

@section('page-body')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Loyalty Points Dashboard</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            @if(!Auth::user()->hasRole('retailer'))
                <div class="col-sm-12 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="card-title mb-0"><i class="fa fa-users me-2"></i>Select Retailer</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Select Retailer to View Points</label>
                                    <select id="retailer_selector" class="form-select select2">
                                        <option value="">-- Choose Retailer --</option>
                                        @foreach($retailers as $r)
                                            <option value="{{ $r->id }}">{{ $r->shop_name }} ({{ $r->user->name }}) -
                                                {{ $r->loyalty_points }} pts</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Points Summary Card -->
            <div class="col-sm-12" id="points-container"
                style="{{ Auth::user()->hasRole('retailer') ? '' : 'display:none;' }}">
                <div class="row">
                    <div class="col-sm-6 col-xl-3">
                        <div class="card bg-warning text-white widget-visitor-card">
                            <div class="card-body text-center">
                                <h3 id="display_total_points">0</h3>
                                <h6 class="text-uppercase font-secondary">Total Loyalty Points</h6>
                                <i class="fa fa-coins font-warning"
                                    style="font-size: 50px; opacity: 0.3; position: absolute; right: 20px; bottom: 20px;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction History -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h5>Points History</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="display" id="points-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Order Code</th>
                                            <th>Points Earned</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Populated via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            // Initialize Select2
            $('.select2').select2();

            let table;

            // If user selects a retailer
            $('#retailer_selector').on('change', function () {
                let retailerId = $(this).val();
                if (retailerId) {
                    $('#points-container').show();
                    fetchRetailerData(retailerId);
                } else {
                    $('#points-container').hide();
                }
            });

            function fetchRetailerData(retailerId) {
                // 1. Fetch Summary
                $.get("{{ route('admin.loyalty-points.summary', ':id') }}".replace(':id', retailerId), function (data) {
                    $('#display_total_points').text(Math.round(data.total_points));
                });

                // 2. Load DataTable
                if ($.fn.DataTable.isDataTable('#points-table')) {
                    $('#points-table').DataTable().destroy();
                }

                $('#points-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('admin.loyalty-points.index') }}",
                        data: function (d) {
                            d.retailer_id = retailerId;
                        }
                    },
                    columns: [
                        { data: 'updated_at', name: 'updated_at' },
                        { data: 'order_code', name: 'order_code' },
                        {
                            data: 'loyalty_points_earned',
                            name: 'loyalty_points_earned',
                            render: function (data) {
                                return `<span class="badge bg-warning text-dark"><i class="fa fa-coins me-1"></i> ${data}</span>`;
                            }
                        },
                        {
                            data: 'status',
                            name: 'status',
                            render: function (data) {
                                return `<span class="badge bg-success">${data}</span>`;
                            }
                        }
                    ]
                });
            }
        });
    </script>
@endsection