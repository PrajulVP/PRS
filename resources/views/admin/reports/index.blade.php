@extends('layouts.admin')

@push('css')
<style>
    .btn-pill-compact {
        border-radius: 50px !important;
        padding: 8px 18px !important;
        font-weight: 800 !important;
        font-size: 10px !important;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-width: 1.5px !important;
    }
    .btn-pill-compact:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .hover-elevate:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
</style>
@endpush

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

    <!-- Summary Stats Row -->
    <div class="row g-3">
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card report-stats-card" style="background: linear-gradient(135deg, #00497a 0%, #0067ab 100%) !important; color: white;">
          <div class="card-body p-3">
            <h6 class="text-white opacity-80 mb-1 small uppercase fw-bold">Monthly Sales</h6>
            <h4 class="text-white mb-0">₹{{ number_format($stats['total_sales_value'], 0) }}</h4>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card report-stats-card" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%) !important; color: white;">
          <div class="card-body p-3">
            <h6 class="text-white opacity-80 mb-1 small uppercase fw-bold">Orders</h6>
            <h4 class="text-white mb-0">{{ number_format($stats['total_retailer_orders']) }}</h4>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-4 col-sm-6">
        <div class="card report-stats-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; color: white;">
          <div class="card-body p-3">
            <h6 class="text-white opacity-80 mb-1 small uppercase fw-bold">Prescriptions</h6>
            <h4 class="text-white mb-0">{{ number_format($stats['prescriptions_analyzed']) }}</h4>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-md-6 col-sm-6">
        <div class="card report-stats-card" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%) !important; color: white;">
          <div class="card-body p-3">
            <h6 class="text-white opacity-80 mb-1 small uppercase fw-bold">Visits</h6>
            <h4 class="text-white mb-0">{{ number_format($stats['total_visits']) }}</h4>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6 col-sm-12">
        <div class="card report-stats-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%) !important; color: white;">
          <div class="card-body p-3">
            <h6 class="text-white opacity-80 mb-1 small uppercase fw-bold">Outstanding</h6>
            <h4 class="text-white mb-0">₹{{ number_format($stats['pending_payments'], 0) }}</h4>
          </div>
        </div>
      </div>
    </div>

    <!-- Intelligence Categories -->
    <div class="row mt-4 g-4">
        
        <div class="col-12 mb-2">
            <div class="section-header-modern mb-1">
                <div class="dash" style="width: 4px; height: 18px; background: var(--med-primary); border-radius: 10px; display: inline-block; margin-right: 10px; vertical-align: middle;"></div>
                <h6 class="fw-800 text-uppercase d-inline-block mb-0" style="font-size: 0.75rem; letter-spacing: 1px; color: var(--med-primary);">Executive Intelligence Hub</h6>
            </div>
        </div>

        <!-- Master Order Watch Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 report-card-premium overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="icon-circle shadow-sm bg-soft-primary" style="width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-eye text-primary fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-800 text-dark mb-1">Master Order Watch</h6>
                            <p class="text-muted small mb-0">Minute-by-minute order tracking</p>
                        </div>
                    </div>
                    <p class="text-muted small mb-4" style="font-size: 0.85rem; line-height: 1.5;">Monitor staff attribution and fulfillment velocity across the entire supply chain.</p>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.reports.orders', ['order_type' => 'retailer']) }}" class="btn btn-primary border-0 text-start px-4 py-2 rounded-3 d-flex align-items-center justify-content-between shadow-sm hover-elevate" style="background: linear-gradient(135deg, #00497a 0%, #0067ab 100%) !important;">
                            <span class="fw-bold text-white" style="font-size: 13px;"><i class="fa fa-shopping-bag me-3"></i>Retailer Master Log</span>
                            <i class="fa fa-arrow-right text-white opacity-75 small"></i>
                        </a>
                        <a href="{{ route('admin.reports.orders', ['order_type' => 'distributor']) }}" class="btn btn-primary border-0 text-start px-4 py-2 rounded-3 d-flex align-items-center justify-content-between shadow-sm hover-elevate" style="background: linear-gradient(135deg, #00497a 0%, #0067ab 100%) !important;">
                            <span class="fw-bold text-white" style="font-size: 13px;"><i class="fa fa-truck me-3"></i>Distributor Master Log</span>
                            <i class="fa fa-arrow-right text-white opacity-75 small"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Network Growth Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 report-card-premium overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="icon-circle shadow-sm bg-soft-warning" style="width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-line-chart text-warning fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-800 text-dark mb-1">Network Growth</h6>
                            <p class="text-muted small mb-0">Targets & Visit Velocity</p>
                        </div>
                    </div>
                    <p class="text-muted small mb-4" style="font-size: 0.85rem; line-height: 1.5;">Monitor target achievements and field force productivity metrics in real-time.</p>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.reports.targets') }}" class="btn btn-primary border-0 text-white text-start px-4 py-2 rounded-3 d-flex align-items-center justify-content-between shadow-sm hover-elevate" style="background: linear-gradient(135deg, #00497a 0%, #0067ab 100%) !important;">
                            <span class="fw-bold text-white" style="font-size: 13px;"><i class="fa fa-bullseye me-3"></i>Target vs Achievement</span>
                            <i class="fa fa-arrow-right text-white opacity-75 small"></i>
                        </a>
                        <a href="{{ route('admin.reports.visits') }}" class="btn btn-primary border-0 text-white text-start px-4 py-2 rounded-3 d-flex align-items-center justify-content-between shadow-sm hover-elevate" style="background: linear-gradient(135deg, #00497a 0%, #0067ab 100%) !important;">
                            <span class="fw-bold text-white" style="font-size: 13px;"><i class="fa fa-map-marker me-3"></i>Field Visit Reports</span>
                            <i class="fa fa-arrow-right text-white opacity-75 small"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Market Dynamics Card -->
        <div class="col-xl-4 col-md-12">
            <div class="card h-100 border-0 shadow-sm rounded-4 report-card-premium overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="icon-circle shadow-sm bg-soft-primary" style="width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-bookmark text-primary fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-800 text-dark mb-1">Market Dynamics</h6>
                            <p class="text-muted small mb-0">Brand & Category Share</p>
                        </div>
                    </div>
                    <p class="text-muted small mb-4" style="font-size: 0.85rem; line-height: 1.5;">Deep dive into brand-wise revenue share and SKU mobility analytics across all divisions.</p>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.reports.brands') }}" class="btn btn-primary border-0 text-white text-start px-4 py-2 rounded-3 d-flex align-items-center justify-content-between shadow-sm hover-elevate" style="background: linear-gradient(135deg, #00497a 0%, #0067ab 100%) !important;">
                            <span class="fw-bold text-white" style="font-size: 13px;"><i class="fa fa-tags me-3"></i>Brand Share Analytics</span>
                            <i class="fa fa-arrow-right text-white opacity-75 small"></i>
                        </a>
                        <a href="{{ route('admin.reports.products') }}" class="btn btn-primary border-0 text-white text-start px-4 py-2 rounded-3 d-flex align-items-center justify-content-between shadow-sm hover-elevate" style="background: linear-gradient(135deg, #00497a 0%, #0067ab 100%) !important;">
                            <span class="fw-bold text-white" style="font-size: 13px;"><i class="fa fa-cubes me-3"></i>Product SKU Mobility</span>
                            <i class="fa fa-arrow-right text-white opacity-75 small"></i>
                        </a>
                        <a href="{{ route('admin.reports.areas') }}" class="btn btn-primary border-0 text-white text-start px-4 py-2 rounded-3 d-flex align-items-center justify-content-between shadow-sm hover-elevate" style="background: linear-gradient(135deg, #00497a 0%, #0067ab 100%) !important;">
                            <span class="fw-bold text-white" style="font-size: 13px;"><i class="fa fa-map-pin me-3"></i>Area Share Analytics</span>
                            <i class="fa fa-arrow-right text-white opacity-75 small"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Row 2 -->
        <div class="col-12 mt-2">
            <div class="row g-4">
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm rounded-4 report-card-premium">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-soft-success p-3 rounded-3">
                                    <i class="fa fa-flask text-success fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-800 text-dark mb-1">Molecules & Demand</h6>
                                    <p class="text-muted small mb-0">AI-extracted prescription trends</p>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.reports.molecule-analytics') }}?tab=demand" class="btn btn-success btn-pill-compact shadow-sm hover-elevate" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; border: 0 !important; color: white !important;">Prescriptions</a>
                                <a href="{{ route('admin.reports.molecule-analytics') }}?tab=sales" class="btn btn-outline-dark btn-pill-compact">Sales Analytics</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm rounded-4 report-card-premium">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-soft-danger p-3 rounded-3">
                                    <i class="fa fa-credit-card text-danger fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-800 text-dark mb-1">Regional Cash Flow</h6>
                                    <p class="text-muted small mb-0">Monitor outstandings & expenses</p>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.reports.outstanding') }}" class="btn btn-danger rounded-pill fw-bold px-4 shadow-sm hover-elevate" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%) !important; border: 0 !important; color: white !important;">Balances</a>
                                <a href="{{ route('admin.field-staff.expenses') }}" class="btn btn-outline-dark rounded-pill fw-bold px-4">Expenses</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .report-card-premium {
        transition: all 0.3s ease;
        background: #fff;
    }
    .report-card-premium:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.08) !important;
    }
    .hover-elevate:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
    }
    .bg-soft-primary { background-color: rgba(var(--bs-primary-rgb), 0.1); }
    .bg-soft-success { background-color: rgba(var(--bs-success-rgb), 0.1); }
    .bg-soft-info { background-color: rgba(var(--bs-info-rgb), 0.1); }
    .bg-soft-warning { background-color: rgba(var(--bs-warning-rgb), 0.1); }
    .bg-soft-danger { background-color: rgba(var(--bs-danger-rgb), 0.1); }

    /* Dark Mode Support */
    .dark-only .report-card-premium {
        background: #1e1e2d !important;
        border: 1px solid rgba(255,255,255,0.05) !important;
    }
    .dark-only .text-dark { color: #fff !important; }
</style>
@endsection
