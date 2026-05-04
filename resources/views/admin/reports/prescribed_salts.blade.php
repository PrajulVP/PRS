@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-extrabold text-dark mb-1"><i class="fa fa-notes-medical me-2 text-primary"></i>Most Prescribed Salts</h3>
            <p class="text-muted small mb-0">AI-powered trend analysis from all uploaded prescriptions.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm rounded-3 shadow-sm px-3" onclick="window.print()">
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
                    <div class="input-group">
                        <input type="date" name="from_date" value="{{ $fromDate->toDateString() }}" class="form-control rounded-start">
                        <span class="input-group-text bg-white border-start-0 border-end-0">to</span>
                        <input type="date" name="to_date" value="{{ $toDate->toDateString() }}" class="form-control rounded-end">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-dark w-100 fw-bold rounded-3">
                        <i class="fa fa-filter me-2"></i>Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        {{-- Chart Section --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4 rounded-4" style="min-height: 500px;">
                <div class="card-header bg-white py-4 border-0">
                    <h5 class="card-title mb-0 fw-bold"><i class="fa fa-chart-bar me-2 text-info"></i>Molecule Frequency Heatmap</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <canvas id="saltChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Top List Section --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-white py-4 border-0">
                    <h5 class="card-title mb-0 fw-bold"><i class="fa fa-award me-2"></i>Top Ranked Molecules</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($topSalts as $salt => $count)
                            <div class="list-group-item d-flex justify-content-between align-items-center py-3 px-4 border-light-dark">
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">{{ $salt }}</h6>
                                    <small class="text-muted">{{ number_format(($count / max(1, array_sum($topSalts))) * 100, 1) }}% of top trends</small>
                                </div>
                                <span class="badge bg-soft-primary text-primary rounded-pill px-3 py-2 fw-bold">
                                    {{ $count }} <span class="small opacity-75 ms-1">Presc.</span>
                                </span>
                            </div>
                        @endforeach

                        @if(empty($topSalts))
                            <div class="text-center py-5 opacity-50">
                                <i class="fa fa-folder-open fa-3x mb-3 text-muted"></i>
                                <h6 class="text-muted">No data found for this range</h6>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Detailed Log Table --}}
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-4 border-0">
                    <h5 class="card-title mb-0 fw-bold"><i class="fa fa-list me-2 text-success"></i>Detailed Extraction Log</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="detailedTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Molecule/Salt Name</th>
                                    <th>Retailer Source</th>
                                    <th>Scan Date/Time</th>
                                    <th>AI Confidence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detailedMolecules as $med)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-bold text-dark">{{ $med['name'] }}</span>
                                        </td>
                                        <td>{{ $med['retailer'] }}</td>
                                        <td><span class="text-muted small">{{ $med['date'] }}</span></td>
                                        <td>
                                            @if($med['confidence'] != 'N/A')
                                                <div class="progress" style="height: 6px; width: 60px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $med['confidence'] * 100 }}%"></div>
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('saltChart').getContext('2d');
        
        const labels = {!! json_encode(array_keys($topSalts)) !!};
        const data = {!! json_encode(array_values($topSalts)) !!};

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Prescription Occurrences',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    borderRadius: 10,
                    barThickness: 30,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        cornerRadius: 10,
                        titleFont: { weight: 'bold' }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { weight: 'bold' } }
                    },
                    y: {
                        grid: { borderDash: [5, 5] },
                        ticks: { 
                            font: { 
                                weight: 'bold',
                                size: 12 
                            }
                        }
                    }
                }
            }
        });
    });
</script>
<style>
    .bg-light-soft { background-color: #f8fafc; }
    .border-light-dark { border-color: rgba(226, 232, 240, 0.6) !important; }
    .bg-soft-primary { background-color: rgba(54, 162, 235, 0.1); }
</style>
@endpush
