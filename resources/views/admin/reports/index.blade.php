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
    <div class="row">
      <!-- Total Sales Card -->
      <div class="col-xl-3 col-md-6 box-col-6">
        <div class="card report-stats-card gradient-primary">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <h6 class="text-white opacity-80 mb-2">Monthly Sales</h6>
                <h3 class="text-white mb-0">₹{{ number_format($stats['total_sales_value'], 2) }}</h3>
              </div>
              <div class="stats-icon">
                <i class="fa fa-money text-white"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Retailer Orders Card -->
      <div class="col-xl-3 col-md-6 box-col-6">
        <div class="card report-stats-card gradient-info">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <h6 class="text-white opacity-80 mb-2">Retailer Orders</h6>
                <h3 class="text-white mb-0">{{ number_format($stats['total_retailer_orders']) }}</h3>
              </div>
              <div class="stats-icon">
                <i class="fa fa-shopping-basket text-white"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Distributor Orders Card -->
      <div class="col-xl-3 col-md-6 box-col-6">
        <div class="card report-stats-card gradient-secondary">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <h6 class="text-white opacity-80 mb-2">Distributor Orders</h6>
                <h3 class="text-white mb-0">{{ number_format($stats['total_distributor_orders']) }}</h3>
              </div>
              <div class="stats-icon">
                <i class="fa fa-truck text-white"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Active Retailers Card -->
      <div class="col-xl-3 col-md-6 box-col-12">
        <div class="card report-stats-card gradient-success">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <h6 class="text-white opacity-80 mb-2">Active Retailers</h6>
                <h3 class="text-white mb-0">{{ number_format($stats['active_retailers']) }}</h3>
              </div>
              <div class="stats-icon">
                <i class="fa fa-users text-white"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Report Categories -->
    <div class="row mt-2 g-4">
        <!-- Master Order Report (New) -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden report-card animate-fade-in">
                <div class="card-body p-5 text-center">
                    <div class="mb-4 d-inline-block p-4 rounded-circle bg-soft-primary">
                        <i class="fa fa-list-alt fs-1 text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Master Order Report</h5>
                    <p class="text-muted mb-4 small">Detailed minute-by-minute order tracking with full product breakdowns and staff attribution.</p>
                    <a href="{{ route('admin.reports.orders') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">Explore Master Data</a>
                </div>
            </div>
        </div>

        <!-- Product Performance -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden report-card">
                <div class="card-body p-5 text-center">
                    <div class="mb-4 d-inline-block p-4 rounded-circle bg-soft-info">
                        <i class="fa fa-cubes fs-1 text-info"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Product Performance</h5>
                    <p class="text-muted mb-4 small">Analyze SKU-level mobility, popularity trends, and inventory sales velocity across all regions.</p>
                    <a href="{{ route('admin.reports.products') }}" class="btn btn-outline-info rounded-pill px-4">Analyze Products</a>
                </div>
            </div>
        </div>

        <!-- Distributor Reports -->
        @if(Auth::user()->hasPermissionToCategory('distributor_reports', 'view'))
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden report-card">
                <div class="card-body p-5 text-center">
                    <div class="mb-4 d-inline-block p-4 rounded-circle bg-soft-secondary">
                        <i class="fa fa-building fs-1 text-secondary"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Distributor Reports</h5>
                    <p class="text-muted mb-4 small">Analyse distributor performance, total orders, and dispatch statistics across different regions.</p>
                    <a href="{{ route('admin.reports.distributors') }}" class="btn btn-outline-secondary rounded-pill px-4">View Distributors</a>
                </div>
            </div>
        </div>
        @endif

        <!-- Retailer Reports -->
        @if(Auth::user()->hasPermissionToCategory('retailer_reports', 'view'))
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden report-card">
                <div class="card-body p-5 text-center">
                    <div class="mb-4 d-inline-block p-4 rounded-circle bg-soft-success">
                        <i class="fa fa-hospital-o fs-1 text-success"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Retailer Reports</h5>
                    <p class="text-muted mb-4 small">Track retailer order history, payment status, and order value distribution for all active shops.</p>
                    <a href="{{ route('admin.reports.retailers') }}" class="btn btn-outline-success rounded-pill px-4">Retailer Analytics</a>
                </div>
            </div>
        </div>
        @endif

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
