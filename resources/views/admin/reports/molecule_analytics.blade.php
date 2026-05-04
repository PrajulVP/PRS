@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-extrabold text-dark mb-1"><i class="fa fa-microscope me-2 text-primary"></i>Molecule & Prescription Analytics</h3>
            <p class="text-muted small mb-0">Combined view of Prescription Trends (Demand) vs Secondary Sales (Fulfillment).</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm rounded-3 px-3" onclick="window.print()">
                <i class="fa fa-print me-2"></i>Print
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
        <div class="card-body p-4 bg-light-soft">
            <form id="filterForm" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted small text-uppercase">Analysis Range</label>
                    <div class="input-group shadow-sm rounded-3 overflow-hidden">
                        <input type="date" name="from_date" value="{{ $fromDate->toDateString() }}" class="form-control border-0">
                        <span class="input-group-text bg-white border-0">to</span>
                        <input type="date" name="to_date" value="{{ $toDate->toDateString() }}" class="form-control border-0">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-dark w-100 fw-bold rounded-3 shadow-sm">
                        <i class="fa fa-sync-alt me-2"></i>Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <ul class="nav nav-pills mb-4 gap-2 p-1 bg-light rounded-pill d-inline-flex shadow-sm" id="analyticsTab" role="tablist" style="border: 1px solid #e2e8f0;">
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill active px-4 py-2 fw-bold" id="demand-tab" data-bs-toggle="pill" data-bs-target="#demand-content" type="button" role="tab">
                <i class="fa fa-file-medical me-2"></i>Prescription Demand
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-2 fw-bold" id="sales-tab" data-bs-toggle="pill" data-bs-target="#sales-content" type="button" role="tab">
                <i class="fa fa-shopping-cart me-2"></i>Sales Fulfillment
            </button>
        </li>
    </ul>

    <div class="tab-content" id="analyticsTabContent">
        {{-- Demand Tab --}}
        <div class="tab-pane fade show active" id="demand-content" role="tabpanel">
            <div class="row g-4">
                <div class="col-xl-7">
                    <div class="card shadow-sm border-0 h-100 rounded-4 overflow-hidden report-card">
                        <div class="card-header bg-white py-4 border-bottom border-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0 text-success">Prescription Molecule Trends</h5>
                                <span class="badge bg-soft-success text-success px-3 py-2 rounded-pill fw-bold">{{ $totalMedicines }} Molecules Found</span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div style="height: 400px;">
                                <canvas id="demandChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-5">
                    <div class="card shadow-sm border-0 h-100 rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-4 border-bottom border-light">
                            <h6 class="fw-bold mb-0 small text-uppercase text-muted">Top Prescribed Salts</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @foreach($topSalts as $name => $count)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 border-light">
                                    <span class="fw-bold text-dark">{{ $name }}</span>
                                    <span class="badge bg-soft-success text-success rounded-pill px-3">{{ $count }} Hits</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Detailed Extraction Log --}}
                <div class="col-12 mt-2">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-dark text-white py-4 border-0">
                            <h5 class="fw-bold mb-0"><i class="fa fa-clipboard-list me-2"></i>Detailed AI Transcription Log</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="moleculeTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 py-3">Molecule Name</th>
                                            <th class="py-3 text-center">Retailer Source</th>
                                            <th class="py-3 text-center">Capture Date</th>
                                            <th class="pe-4 py-3 text-end">AI Confidence</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($detailedMolecules as $detail)
                                        <tr>
                                            <td class="ps-4 fw-bold text-primary">{{ $detail['name'] }}</td>
                                            <td class="text-center text-muted">{{ $detail['retailer'] }}</td>
                                            <td class="text-center small">{{ $detail['date'] }}</td>
                                            <td class="pe-4 text-end">
                                                @if($detail['confidence'] != 'N/A')
                                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                                        <div class="progress" style="height: 6px; width: 60px;">
                                                            <div class="progress-bar bg-success" style="width: {{ $detail['confidence'] * 100 }}%"></div>
                                                        </div>
                                                        <span class="small fw-bold">{{ round($detail['confidence'] * 100) }}%</span>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sales Tab --}}
        <div class="tab-pane fade" id="sales-content" role="tabpanel">
            <div class="row g-4">
                <div class="col-xl-7">
                    <div class="card shadow-sm border-0 h-100 rounded-4 overflow-hidden report-card">
                        <div class="card-header bg-white py-4 border-bottom border-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0" style="color: #b45309;">Secondary Sales Trends</h5>
                                <span class="badge px-3 py-2 rounded-pill fw-bold" style="background-color: rgba(180, 83, 9, 0.1); color: #b45309;">Actual Sales Units</span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div style="height: 400px;">
                                <canvas id="salesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-5">
                    <div class="card shadow-sm border-0 h-100 rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-4 border-bottom border-light">
                            <h6 class="fw-bold mb-0 small text-uppercase text-muted">Top Moving Molecules</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @foreach($salesTrends as $trend)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 border-light">
                                    <span class="fw-bold text-dark">{{ $trend->generic_name }}</span>
                                    <span class="badge rounded-pill px-3" style="background-color: rgba(180, 83, 9, 0.1); color: #b45309;">{{ number_format($trend->total_sold) }} Units</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Demand Chart
        const demandCtx = document.getElementById('demandChart').getContext('2d');
        new Chart(demandCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($topSalts)) !!},
                datasets: [{
                    label: 'Occurrences',
                    data: {!! json_encode(array_values($topSalts)) !!},
                    backgroundColor: '#10b981',
                    borderRadius: 8
                }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($salesTrends->pluck('generic_name')) !!},
                datasets: [{
                    label: 'Units Sold',
                    data: {!! json_encode($salesTrends->pluck('total_sold')) !!},
                    backgroundColor: '#b45309',
                    borderRadius: 8
                }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // Handle URL parameter for tabs
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab === 'sales') {
            const salesTrigger = document.querySelector('#sales-tab');
            if (salesTrigger) {
                const tabInstance = new bootstrap.Tab(salesTrigger);
                tabInstance.show();
            }
        } else {
            const demandTrigger = document.querySelector('#demand-tab');
            if (demandTrigger) {
                const tabInstance = new bootstrap.Tab(demandTrigger);
                tabInstance.show();
            }
        }
    });
</script>
<style>
    .bg-light-soft { background-color: #f8fafc; }
    .bg-soft-success { background-color: rgba(16, 185, 129, 0.1); }
    .bg-soft-warning { background-color: rgba(245, 158, 11, 0.1); }
    .nav-pills .nav-link { color: #64748b; background: transparent; transition: all 0.2s ease; }
    .nav-pills .nav-link.active { color: #fff !important; background: #00497a !important; box-shadow: 0 4px 12px rgba(0, 73, 122, 0.2); }
    .list-group-item { border-bottom: 1px solid #f1f5f9; }
    #moleculeTable td { padding-top: 1rem; padding-bottom: 1rem; }
    .report-card:hover { transform: translateY(-2px); transition: all 0.2s ease-in-out; }
</style>
@endpush
