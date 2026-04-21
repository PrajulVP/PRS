@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title text-start mb-4">
        <div class="row m-0">
          <div class="col-sm-6 p-0">
            <h4 class="mb-0">Executive Report Dashboard</h4>
            <p class="text-muted mb-0">Monthly Overview: {{ $fromDate->format('M d, Y') }} - {{ $toDate->format('M d, Y') }}</p>
          </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3">
      <!-- Total Sales Card -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card report-stats-card" style="background: linear-gradient(135deg, #00497a 0%, #0067ab 100%) !important; color: white; min-height: 110px;">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <h6 class="text-white opacity-80 mb-1 small uppercase fw-bold">Monthly Sales</h6>
                <h4 class="text-white mb-0">₹{{ number_format($stats['total_sales_value'], 0) }}</h4>
              </div>
              <div class="stats-icon opacity-50">
                <i class="fa fa-money text-white fs-4"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Retailer Orders Card -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card report-stats-card" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%) !important; color: white; min-height: 110px;">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <h6 class="text-white opacity-80 mb-1 small uppercase fw-bold">Orders</h6>
                <h4 class="text-white mb-0">{{ number_format($stats['total_retailer_orders']) }}</h4>
              </div>
              <div class="stats-icon opacity-50">
                <i class="fa fa-shopping-basket text-white fs-4"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Prescriptions Logged Card -->
      <div class="col-xl-3 col-md-4 col-sm-6">
        <div class="card report-stats-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; color: white; min-height: 110px;">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <h6 class="text-white opacity-80 mb-1 small uppercase fw-bold">Prescriptions Logged</h6>
                <h4 class="text-white mb-0">{{ number_format($stats['prescriptions_analyzed']) }}</h4>
              </div>
              <div class="stats-icon opacity-50">
                <i class="fa fa-flask text-white fs-4"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Monthly Visits Card -->
      <div class="col-xl-2 col-md-6 col-sm-6">
        <div class="card report-stats-card" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%) !important; color: white; min-height: 110px;">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <h6 class="text-white opacity-80 mb-1 small uppercase fw-bold">Visits</h6>
                <h4 class="text-white mb-0">{{ number_format($stats['total_visits']) }}</h4>
              </div>
              <div class="stats-icon opacity-50">
                <i class="fa fa-map-marker text-white fs-4"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pending Payments Card -->
      <div class="col-xl-3 col-md-6 col-sm-12">
        <div class="card report-stats-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%) !important; color: white; min-height: 110px;">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <h6 class="text-white opacity-80 mb-1 small uppercase fw-bold">Outstanding</h6>
                <h4 class="text-white mb-0">₹{{ number_format($stats['pending_payments'], 0) }}</h4>
              </div>
              <div class="stats-icon opacity-50">
                <i class="fa fa-clock-o text-white fs-4"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Report Categories -->
    <div class="row mt-2 g-4">
        @if(Auth::user()->hasPermissionToCategory('master_order_reports', 'view'))
        <!-- Master Order Report (New) -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden report-card animate-fade-in">
                <div class="card-body p-5 text-center">
                    <div class="mb-4 d-inline-block p-4 rounded-circle bg-soft-primary">
                        <i class="fa fa-list-alt fs-1 text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Master Order Report</h5>
                    <p class="text-muted mb-4 small">Detailed minute-by-minute distributor and retailer master order tracking with full product breakdowns and staff attribution.</p>
                    <a href="{{ route('admin.reports.orders') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">Explore Master Data</a>
                </div>
            </div>
        </div>
        @endif

        @if(Auth::user()->hasPermissionToCategory('performance_reports', 'view'))
        <!-- Target vs Achievement -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden report-card">
                <div class="card-body p-5 text-center">
                    <div class="mb-4 d-inline-block p-4 rounded-circle bg-soft-warning">
                        <i class="fa fa-bullseye fs-1 text-warning"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Target vs Achievement</h5>
                    <p class="text-muted mb-4 small">Compare sales targets against real-time delivered performance for personnel and outlets.</p>
                    <a href="{{ route('admin.reports.targets') }}" class="btn btn-outline-warning rounded-pill px-4">Performance Gap</a>
                </div>
            </div>
        </div>

        <!-- Field Staff Performance -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden report-card">
                <div class="card-body p-5 text-center">
                    <div class="mb-4 d-inline-block p-4 rounded-circle bg-soft-primary">
                        <i class="fa fa-line-chart fs-1 text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Personnel Analytics</h5>
                    <p class="text-muted mb-4 small">Ranking and sales contribution analytics for all field representatives and sales managers.</p>
                    <a href="{{ route('admin.reports.fieldstaffs') }}" class="btn btn-outline-primary rounded-pill px-4">Staff Performance</a>
                </div>
            </div>
        </div>

        <!-- Visit Reports -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden report-card">
                <div class="card-body p-5 text-center">
                    <div class="mb-4 d-inline-block p-4 rounded-circle bg-soft-info">
                        <i class="fa fa-car fs-1 text-info"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Visit & Coverage</h5>
                    <p class="text-muted mb-4 small">Analyze field visit frequency, shop coverage productivity, and route mapping logs.</p>
                    <a href="{{ route('admin.reports.visits') }}" class="btn btn-outline-info rounded-pill px-4">Coverage Analysis</a>
                </div>
            </div>
        </div>

        <!-- Live Monitoring Dashboard -->
        <div class="col-xl-4 col-md-6 box-col-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden report-card" style="border-top: 4px solid #7366ff !important;">
                <div class="card-body p-5 text-center">
                    <div class="mb-4 d-inline-block p-4 rounded-circle bg-soft-primary" style="position: relative;">
                        <span class="status-dot-pulse"></span>
                        <i class="fa fa-map-marked-alt fs-1 text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Live Fleet Monitoring</h5>
                    <p class="text-muted mb-4 small">Real-time live map tracking of all field personnel with movement alerts and visit tracking.</p>
                    <a href="{{ route('admin.reports.monitoring') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">Command Center</a>
                </div>
            </div>
        </div>

        <!-- Expense Reports -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden report-card">
                <div class="card-body p-5 text-center">
                    <div class="mb-4 d-inline-block p-4 rounded-circle bg-soft-danger">
                        <i class="fa fa-credit-card fs-1 text-danger"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Expense Management</h5>
                    <p class="text-muted mb-4 small">Detailed monitoring of field staff claims, approval statuses, and regional budget utilization.</p>
                    <a href="{{ route('admin.field-staff.expenses') }}" class="btn btn-outline-danger rounded-pill px-4">Audit Expenses</a>
                </div>
            </div>
        </div>
        @endif

        @if(Auth::user()->hasPermissionToCategory('retailer_reports', 'view') || Auth::user()->hasPermissionToCategory('distributor_reports', 'view'))
        <!-- Outstanding & Payments -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden report-card">
                <div class="card-body p-5 text-center">
                    <div class="mb-4 d-inline-block p-4 rounded-circle bg-soft-success" style="background-color: rgba(231, 76, 60, 0.1);">
                        <i class="fa fa-hourglass-half fs-1 text-danger"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Outstanding Balances</h5>
                    <p class="text-muted mb-4 small">Track unpaid retailer invoices and distributor credit cycles to manage regional cash flow.</p>
                    <a href="{{ route('admin.reports.outstanding') }}" class="btn btn-outline-danger rounded-pill px-4">Manage Debt</a>
                </div>
            </div>
        </div>
        @endif

        @if(Auth::user()->hasPermissionToCategory('reports', 'view') || Auth::user()->hasRole(['superadmin', 'admin']))
        <!-- Prescription Molecule Trends -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden report-card" style="border-top: 4px solid #10b981 !important;">
                <div class="card-body p-5 text-center">
                    <div class="mb-4 d-inline-block p-4 rounded-circle bg-soft-success">
                        <i class="fa fa-flask fs-1 text-success"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Prescription Molecule Trends</h5>
                    <p class="text-muted mb-4 small">AI-extracted analysis of the most prescribed salts and molecules across all uploaded medical documents.</p>
                    <a href="{{ route('admin.reports.molecule-analytics') }}?tab=demand" class="btn btn-success rounded-pill px-4 shadow-sm">View Trends</a>
                </div>
            </div>
        </div>

        <!-- Sales Molecule Analysis -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden report-card" style="border-top: 4px solid #b45309 !important;">
                <div class="card-body p-5 text-center">
                    <div class="mb-4 d-inline-block p-4 rounded-circle" style="background-color: rgba(180, 83, 9, 0.1);">
                        <i class="fa fa-bolt fs-1" style="color: #b45309;"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Fastest Moving Molecules</h5>
                    <p class="text-muted mb-4 small">Discover secondary sales demand by identifying the top-grossing generic names and molecule categories.</p>
                    <a href="{{ route('admin.reports.molecule-analytics') }}?tab=sales" class="btn rounded-pill px-4 shadow-sm" style="background-color: #b45309; color: white;">Sales Trends</a>
                </div>
            </div>
        </div>
        @endif

        <!-- Entity Performance Card Group -->
        <div class="col-12 mt-4">
            <div class="card border-0 bg-transparent">
                <div class="card-header bg-transparent border-0 px-0">
                    <h5 class="fw-bold mb-0">Distribution & Entity Analysis</h5>
                    <p class="text-muted small">Deep-dive into performance by Distributor, Retailer, and Product SKU.</p>
                </div>
                <div class="card-body px-0">
                    <div class="row g-4">
                        @if(Auth::user()->hasPermissionToCategory('product_reports', 'view'))
                        <!-- Product Performance -->
                        <div class="col-xl-4 col-md-6">
                            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden report-card">
                                <div class="card-body p-4 text-center">
                                    <i class="fa fa-cubes text-info mb-2 fs-3"></i>
                                    <h6 class="fw-bold mb-2">Product Mobility</h6>
                                    <a href="{{ route('admin.reports.products') }}" class="btn btn-sm btn-link text-info p-0">Detailed Analysis</a>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(Auth::user()->hasPermissionToCategory('distributor_reports', 'view'))
                        <!-- Distributor Reports -->
                        <div class="col-xl-4 col-md-6">
                            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden report-card">
                                <div class="card-body p-4 text-center">
                                    <i class="fa fa-building text-secondary mb-2 fs-3"></i>
                                    <h6 class="fw-bold mb-2">Distributor Registry</h6>
                                    <a href="{{ route('admin.reports.distributors') }}" class="btn btn-sm btn-link text-secondary p-0">View Report</a>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(Auth::user()->hasPermissionToCategory('retailer_reports', 'view'))
                        <!-- Retailer Reports -->
                        <div class="col-xl-4 col-md-6">
                            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden report-card">
                                <div class="card-body p-4 text-center">
                                    <i class="fa fa-hospital-o text-success mb-2 fs-3"></i>
                                    <h6 class="fw-bold mb-2">Retailer Rankings</h6>
                                    <a href="{{ route('admin.reports.retailers') }}" class="btn btn-sm btn-link text-success p-0">Retailer Analytics</a>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .report-card {
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        border: 1px solid rgba(0,0,0,0.05) !important;
    }
    .report-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
        border-color: var(--med-primary) !important;
    }
    .bg-soft-primary { background-color: rgba(var(--bs-primary-rgb), 0.1); }
    .bg-soft-success { background-color: rgba(var(--bs-success-rgb), 0.1); }
    .bg-soft-info { background-color: rgba(var(--bs-info-rgb), 0.1); }
    .bg-soft-warning { background-color: rgba(var(--bs-warning-rgb), 0.1); }
    .bg-soft-secondary { background-color: rgba(var(--bs-secondary-rgb), 0.1); }
    
    .animate-fade-in {
        animation: fadeIn 0.5s ease-out forwards;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
