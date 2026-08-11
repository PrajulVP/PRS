@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-bottom pt-4 pb-3 px-4">
                    <h4 class="mb-0 fw-bold text-dark"><i class="fa fa-map-marker-alt text-primary me-2"></i> Staff Visits Tracking</h4>
                    <p class="text-muted small mt-1 mb-0">Monitor and filter field staff visits, locations, and purposes.</p>
                </div>
                
                <div class="card-body p-0">
                    <!-- Filters Section -->
                    <div class="border-bottom">
                        <form method="GET" action="{{ route('admin.field-staff.visits') }}" class="px-4 py-4 mb-0 bg-white">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-secondary">Manager</label>
                                    <select name="manager_id" id="manager_id" class="form-select form-select-sm">
                                        <option value="">All Managers</option>
                                        @foreach($managers as $manager)
                                            <option value="{{ $manager->id }}" {{ request('manager_id') == $manager->id ? 'selected' : '' }}>{{ $manager->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-secondary">Staff Member</label>
                                    <select name="user_id" id="user_id" class="form-select form-select-sm">
                                        <option value="">All Staff</option>
                                        @foreach($staffUsers as $staff)
                                            <option value="{{ $staff->id }}" data-manager-id="{{ optional(optional($staff->fieldStaff)->salesManager)->user_id }}" {{ request('user_id') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-secondary">Party Type</label>
                                    <select name="party_type" class="form-select form-select-sm">
                                        <option value="">All Types</option>
                                        <option value="retailer" {{ request('party_type') == 'retailer' ? 'selected' : '' }}>Retailer</option>
                                        <option value="distributor" {{ request('party_type') == 'distributor' ? 'selected' : '' }}>Distributor</option>
                                        <option value="other" {{ request('party_type') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-secondary">Purpose</label>
                                    <select name="purpose_id" class="form-select form-select-sm">
                                        <option value="">All Purposes</option>
                                        @foreach($purposes as $purpose)
                                            <option value="{{ $purpose->id }}" {{ request('purpose_id') == $purpose->id ? 'selected' : '' }}>{{ $purpose->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-secondary">Status</label>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="">All Statuses</option>
                                        <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row g-3 mt-1 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">Date Range</label>
                                    <div class="input-group input-group-sm">
                                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                        <span class="input-group-text">to</span>
                                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                    </div>
                                </div>
                                <div class="col-md-8 d-flex justify-content-end align-items-center gap-2">
                                    <a href="{{ route('admin.field-staff.visits') }}" class="btn btn-sm btn-reset-premium"><i class="fa fa-undo"></i> Reset</a>
                                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> Apply Filters</button>
                                    
                                    <div class="d-inline-flex gap-2 align-items-center export-buttons-wrapper">
                                        <button type="submit" name="export" value="csv" class="btn btn-sm btn-custom-export btn-csv-custom"><i class="fa fa-file-csv"></i> CSV</button>
                                        <button type="submit" name="export" value="excel" class="btn btn-sm btn-custom-export btn-excel-custom"><i class="fa fa-file-excel"></i> Excel</button>
                                        <button type="submit" name="export" value="pdf" class="btn btn-sm btn-custom-export btn-pdf-custom"><i class="fa fa-file-pdf"></i> PDF</button>
                                        <button type="submit" name="export" value="print" class="btn btn-sm btn-custom-export btn-print-custom"><i class="fa fa-print"></i> Print</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Results Table -->
                    <div class="table-responsive bg-white px-4 pb-4">
                        <table class="table table-hover table-borderless align-middle mb-0" id="visitsTable" style="width:100%">
                            <thead class="table-light border-bottom">
                                <tr>
                                    <th class="py-3 ps-4 text-secondary fw-bold">Date & Time</th>
                                    <th class="py-3 text-secondary fw-bold">Staff Member</th>
                                    <th class="py-3 text-secondary fw-bold">Manager</th>
                                    <th class="py-3 text-secondary fw-bold">Party Details</th>
                                    <th class="py-3 text-secondary fw-bold">Purpose</th>
                                    <th class="py-3 text-secondary fw-bold">Duration</th>
                                    <th class="py-3 text-secondary fw-bold">Distance</th>
                                    <th class="py-3 text-secondary fw-bold">Remarks</th>
                                    <th class="py-3 pe-4 text-secondary fw-bold text-center">Action</th>
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

<!-- Visit Details Modal -->
<div class="modal fade" id="visitDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0">
      <div class="modal-header px-4 py-3" style="background-color: var(--med-primary) !important;">
        <h6 class="modal-title fw-bold text-white m-0" style="color: #ffffff !important;"><i class="fa fa-user-circle me-2" style="opacity: 0.9;"></i> Visit Details</h6>
        <button type="button" class="bg-transparent border-0 text-white" data-bs-dismiss="modal" aria-label="Close">
            <i class="fa fa-times fa-lg" style="opacity: 0.8;"></i>
        </button>
      </div>
      
      <div class="modal-body p-4" style="background-color: var(--med-bg-body);">
        
        <h6 class="mb-3 fw-bold text-primary" style="font-size: 14px;"><i class="fa fa-info-circle me-1"></i> Basic Information</h6>
        
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="p-3 rounded border h-100" style="background-color: var(--med-bg-card);">
                    <p class="text-muted mb-1" style="font-size: 12px;"><i class="fa fa-user text-primary me-1"></i> Staff Member</p>
                    <h6 class="mb-0 fw-bold" id="modal_staff_name"></h6>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 rounded border h-100" style="background-color: var(--med-bg-card);">
                    <p class="text-muted mb-1" style="font-size: 12px;"><i class="fa fa-user-shield text-success me-1"></i> Manager</p>
                    <h6 class="mb-0 fw-bold" id="modal_manager_name"></h6>
                </div>
            </div>
        </div>

        <h6 class="mb-3 fw-bold text-primary" style="font-size: 14px;"><i class="fa fa-calendar-alt me-1"></i> Visit Data</h6>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="p-3 rounded border h-100" style="background-color: var(--med-bg-card);">
                    <p class="text-muted mb-1" style="font-size: 12px;"><i class="far fa-clock text-info me-1"></i> Date & Time</p>
                    <h6 class="mb-1 fw-bold" id="modal_date"></h6>
                    <small class="text-muted d-block" style="font-size: 11px;"><span id="modal_start_time"></span> - <span id="modal_end_time"></span></small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded border h-100" style="background-color: var(--med-bg-card);">
                    <p class="text-muted mb-1" style="font-size: 12px;"><i class="fa fa-store text-secondary me-1"></i> Party Details</p>
                    <h6 class="mb-2 fw-bold" id="modal_party_name"></h6>
                    <span class="badge border px-2 py-1 fw-normal text-muted" id="modal_party_type" style="background-color: var(--med-bg-body);"></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded border h-100" style="background-color: var(--med-bg-card);">
                    <p class="text-muted mb-1" style="font-size: 12px;"><i class="fa fa-bullseye text-warning me-1"></i> Purpose & Status</p>
                    <h6 class="mb-2 fw-bold" id="modal_purpose"></h6>
                    <span class="badge border px-2 py-1 fw-normal text-muted" id="modal_status" style="background-color: var(--med-bg-body);"></span>
                </div>
            </div>
        </div>
        
        <h6 class="mb-3 fw-bold text-primary" style="font-size: 14px;"><i class="fa fa-comment-dots me-1"></i> Remarks</h6>

        <div class="p-3 rounded shadow-sm border mb-1" style="background-color: var(--med-bg-card);">
            <p class="m-0" id="modal_remarks" style="min-height: 20px; font-size: 13px;"></p>
        </div>

      </div>
      <div class="modal-footer border-top-0 px-4 py-3" style="background-color: var(--med-bg-body);">
          <button type="button" class="btn btn-secondary px-4 btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
    .dataTables_length {
        margin-top: 1rem !important;
    }
    .dataTables_length label {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        margin-bottom: 0 !important;
    }
    .dataTables_length {
        display: none !important;
    }
    .dataTables_filter {
        margin-top: 1rem !important;
    }
    .dataTables_filter label {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 8px !important;
        margin-bottom: 0 !important;
    }
    .dataTables_filter input {
        width: 200px !important;
        display: inline-block !important;
        padding: 0.375rem 0.75rem !important;
        border-radius: 0.375rem !important;
        border: 1px solid #dee2e6 !important;
    }
    .btn-reset-premium {
        background-color: #334155 !important; /* Bold Dark Slate */
        border: none !important;
        color: #ffffff !important;
        font-weight: bold !important;
        box-shadow: 0 2px 4px rgba(51, 65, 85, 0.3) !important;
        transition: all 0.2s ease;
    }
    .btn-reset-premium:hover {
        background-color: #1e293b !important; /* Even darker on hover */
        color: #ffffff !important;
        box-shadow: 0 4px 8px rgba(51, 65, 85, 0.4) !important;
        transform: translateY(-1px);
    }
    body.dark-only .btn-reset-premium {
        background-color: #475569 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
    }
    body.dark-only .btn-reset-premium:hover {
        background-color: #334155 !important;
    }

    /* Premium Export Buttons styling */
    .export-buttons-wrapper .btn-custom-export {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 100px !important;
        padding: 6px 16px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03) !important;
        transition: all 0.2s ease-in-out !important;
        color: #334155 !important;
    }

    .export-buttons-wrapper .btn-custom-export:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 15px rgba(0, 73, 122, 0.08) !important;
        background: #f8fafc !important;
    }

    .btn-csv-custom {
        color: #475569 !important;
        border-color: #e2e8f0 !important;
    }
    .btn-csv-custom i { color: #475569 !important; }
    .btn-csv-custom:hover { border-color: #94a3b8 !important; }

    .btn-excel-custom {
        color: #15803d !important;
        border-color: rgba(21, 128, 61, 0.15) !important;
    }
    .btn-excel-custom i { color: #15803d !important; }
    .btn-excel-custom:hover {
        background: #f0fdf4 !important;
        border-color: #15803d !important;
    }

    .btn-pdf-custom {
        color: #b91c1c !important;
        border-color: rgba(185, 28, 28, 0.15) !important;
    }
    .btn-pdf-custom i { color: #b91c1c !important; }
    .btn-pdf-custom:hover {
        background: #fef2f2 !important;
        border-color: #b91c1c !important;
    }

    .btn-print-custom {
        color: #1d4ed8 !important;
        border-color: rgba(29, 78, 216, 0.15) !important;
    }
    .btn-print-custom i { color: #1d4ed8 !important; }
    .btn-print-custom:hover {
        background: #eff6ff !important;
        border-color: #1d4ed8 !important;
    }

    body.dark-only .export-buttons-wrapper .btn-custom-export {
        background: #121b2a !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        color: #f8fafc !important;
    }
    body.dark-only .export-buttons-wrapper .btn-custom-export:hover {
        background: rgba(255, 255, 255, 0.03) !important;
    }
    body.dark-only .btn-csv-custom { color: #94a3b8 !important; }
    body.dark-only .btn-csv-custom i { color: #94a3b8 !important; }
    body.dark-only .btn-excel-custom {
        color: #4ade80 !important;
        border-color: rgba(74, 222, 128, 0.1) !important;
    }
    body.dark-only .btn-excel-custom i { color: #4ade80 !important; }
    body.dark-only .btn-excel-custom:hover { border-color: #4ade80 !important; }
    body.dark-only .btn-pdf-custom {
        color: #f87171 !important;
        border-color: rgba(248, 113, 113, 0.1) !important;
    }
    body.dark-only .btn-pdf-custom i { color: #f87171 !important; }
    body.dark-only .btn-pdf-custom:hover { border-color: #f87171 !important; }
    body.dark-only .btn-print-custom {
        color: #60a5fa !important;
        border-color: rgba(96, 165, 250, 0.1) !important;
    }
    body.dark-only .btn-print-custom i { color: #60a5fa !important; }
    body.dark-only .btn-print-custom:hover { border-color: #60a5fa !important; }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Store all original options for filtering
        const $staffSelect = $('#user_id');
        const allStaffOptions = $staffSelect.find('option').clone();

        // Dynamic Staff Filtering based on Manager
        $('#manager_id').on('change', function() {
            let managerId = $(this).val();
            let currentSelected = $staffSelect.val();
            
            // Clear current options
            $staffSelect.empty();
            
            // Append valid options
            allStaffOptions.each(function() {
                if ($(this).val() === '') {
                    // Always append "All Staff"
                    $staffSelect.append($(this).clone());
                    return;
                }
                
                let staffManagerId = $(this).data('manager-id');
                if (!managerId || staffManagerId == managerId) {
                    $staffSelect.append($(this).clone());
                }
            });
            
            // Restore selection if it still exists in the new list, otherwise reset
            if (managerId && currentSelected !== '') {
                if ($staffSelect.find('option[value="' + currentSelected + '"]').length > 0) {
                    $staffSelect.val(currentSelected);
                } else {
                    $staffSelect.val('');
                }
            } else if (currentSelected !== '') {
                $staffSelect.val(currentSelected);
            }
            
            // Trigger change event to update any UI plugins (like Select2) if they exist
            $staffSelect.trigger('change');
        });

        // Trigger on load in case a manager is already selected
        if ($('#manager_id').val()) {
            $('#manager_id').trigger('change');
        }

        // Initialize DataTable
        var table = $('#visitsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.field-staff.visits') }}",
                data: function (d) {
                    d.manager_id = $('select[name="manager_id"]').val();
                    d.user_id = $('select[name="user_id"]').val();
                    d.party_type = $('select[name="party_type"]').val();
                    d.purpose_id = $('select[name="purpose_id"]').val();
                    d.status = $('select[name="status"]').val();
                    d.start_date = $('input[name="start_date"]').val();
                    d.end_date = $('input[name="end_date"]').val();
                }
            },
            columns: [
                { data: 'date_time', name: 'start_at' },
                { data: 'staff_member', name: 'user.name', orderable: false },
                { data: 'manager', name: 'manager', orderable: false, searchable: false },
                { data: 'party_details', name: 'party_type', orderable: false, searchable: false },
                { data: 'purpose', name: 'purpose.name', orderable: false },
                { data: 'duration', name: 'duration', orderable: false, searchable: false },
                { data: 'distance', name: 'distance', orderable: false, searchable: false },
                { data: 'remarks', name: 'remarks', orderable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[0, 'desc']],
            pageLength: 20,
            lengthChange: false,
            dom: "<'row mb-3 align-items-center'<'col-sm-12 d-flex justify-content-end'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            language: {
                search: "",
                searchPlaceholder: "Search visits...",
                lengthMenu: "Show _MENU_ entries"
            }
        });

        // Trigger filter reload instead of form submit
        $('form').on('submit', function(e) {
            // Only prevent default if we are NOT clicking an export button
            if (!$(document.activeElement).hasClass('btn-custom-export')) {
                e.preventDefault();
                table.draw();
            }
        });

        // View Visit Details Modal (delegated since data is dynamically loaded)
        $(document).on('click', '.view-visit-btn', function() {
            try {
                let data = $(this).data('visit');
                if (typeof data === 'string') {
                    data = JSON.parse(data);
                }
                
                $('#modal_staff_name').text(data.staff_name);
                $('#modal_manager_name').text(data.manager_name);
                
                $('#modal_date').text(data.date);
                $('#modal_start_time').text(data.start_time);
                $('#modal_end_time').text(data.end_time);
                $('#modal_duration').text(data.duration);
                
                $('#modal_party_name').text(data.party_name);
                $('#modal_party_type').text(data.party_type);
                
                $('#modal_purpose').text(data.purpose);
                $('#modal_status').text(data.status);
                
                $('#modal_remarks').text(data.remarks);
                
                let modal = new bootstrap.Modal(document.getElementById('visitDetailsModal'));
                modal.show();
            } catch (e) {
                console.error("Error parsing visit data:", e);
                alert("Could not load visit details.");
            }
        });
    });
</script>
@endpush
