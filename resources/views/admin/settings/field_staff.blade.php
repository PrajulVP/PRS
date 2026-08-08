@extends('layouts.admin')

@section('page-body')
    <style>
        .setting-form .input-group-text { font-size: 0.85rem; }
        .accordion-button::after { content: "+" !important; font-family: inherit !important; font-size: 1.5rem !important; background-image: none !important; display: flex; align-items: center; justify-content: center; transform: none !important; color: #64748b; }
        .accordion-button:not(.collapsed)::after { content: "-" !important; font-size: 1.8rem !important; color: var(--med-primary, #00497a); }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom-0 pb-2 pt-4 px-4">
                        <h4 class="fw-bold mb-1">Field Staff Settings</h4>
                        <p class="text-muted small mb-0">Manage field staff configuration, leave types, and visit purposes.</p>
                    </div>
                    <div class="card-body p-4 pt-2">
                        <div class="accordion accordion-flush" id="settingsAccordion">
                            
                            <!-- Field Staff Configuration -->
                            <div class="accordion-item border mb-3 rounded-4 overflow-hidden shadow-sm">
                                <h2 class="accordion-header" id="headingFieldStaff">
                                    <button class="accordion-button collapsed fw-bold py-3 px-4 bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFieldStaff" aria-expanded="false" aria-controls="collapseFieldStaff" style="font-size: 1.1rem; color: #1e293b;">
                                        <div class="d-flex align-items-center w-100 me-3">
                                            <i class="fa fa-users-cog me-3 text-primary fs-4"></i>
                                            <div>
                                                <div>Field Staff Configuration</div>
                                                <small class="text-muted fw-normal d-block mt-1" style="font-size: 0.85rem;">Geo-fencing, TA, DA, and Radius rules</small>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapseFieldStaff" class="accordion-collapse collapse show" aria-labelledby="headingFieldStaff" data-bs-parent="#settingsAccordion">
                                    <div class="accordion-body p-4 bg-light">
                                        <div class="row g-4">
                                            <!-- Geo-fencing -->
                                            <div class="col-md-6">
                                                <div class="card border-0 shadow-sm h-100 rounded-3">
                                                    <div class="card-body p-4">
                                                        <h6 class="fw-bold text-primary mb-1"><i class="fa fa-map-marker-alt me-2"></i>Geo-fencing Radius</h6>
                                                        <p class="text-muted small mb-3">Max allowed distance (in meters) from customer for punching and visit validation.</p>
                                                        <form class="setting-form">
                                                            @csrf
                                                            <input type="hidden" name="slug" value="geofence_radius">
                                                            <div class="input-group input-group-sm">
                                                                <input type="number" class="form-control" name="value" value="{{ $geofence_radius }}" min="1">
                                                                <span class="input-group-text bg-light text-muted">Meters</span>
                                                                <button type="button" class="btn btn-primary px-3 save-setting-btn">Save</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- TA Rate -->
                                            <div class="col-md-6">
                                                <div class="card border-0 shadow-sm h-100 rounded-3">
                                                    <div class="card-body p-4">
                                                        <h6 class="fw-bold text-success mb-1"><i class="fa fa-car me-2"></i>Travel Allowance (TA) Rate</h6>
                                                        <p class="text-muted small mb-3">Reimbursement rate per kilometer travelled.</p>
                                                        <form class="setting-form">
                                                            @csrf
                                                            <input type="hidden" name="slug" value="ta_rate_per_km">
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text bg-light text-muted">₹</span>
                                                                <input type="number" step="0.01" class="form-control" name="value" value="{{ $ta_rate_per_km }}" min="0">
                                                                <span class="input-group-text bg-light text-muted">per KM</span>
                                                                <button type="button" class="btn btn-success px-3 save-setting-btn">Save</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- DA HQ Rate -->
                                            <div class="col-md-6">
                                                <div class="card border-0 shadow-sm h-100 rounded-3">
                                                    <div class="card-body p-4">
                                                        <h6 class="fw-bold text-info mb-1"><i class="fa fa-building me-2"></i>DA HQ Rate</h6>
                                                        <p class="text-muted small mb-3">Daily Allowance rate for regular Headquarter visits.</p>
                                                        <form class="setting-form">
                                                            @csrf
                                                            <input type="hidden" name="slug" value="da_hq_rate">
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text bg-light text-muted">₹</span>
                                                                <input type="number" step="0.01" class="form-control" name="value" value="{{ $da_hq_rate }}" min="0">
                                                                <span class="input-group-text bg-light text-muted">per Day</span>
                                                                <button type="button" class="btn btn-info text-white px-3 save-setting-btn">Save</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- DA Outstation Rate -->
                                            <div class="col-md-6">
                                                <div class="card border-0 shadow-sm h-100 rounded-3">
                                                    <div class="card-body p-4">
                                                        <h6 class="fw-bold text-warning mb-1"><i class="fa fa-plane-departure me-2"></i>DA Outstation Rate</h6>
                                                        <p class="text-muted small mb-3">Daily Allowance rate for visits outside specified headquarters.</p>
                                                        <form class="setting-form">
                                                            @csrf
                                                            <input type="hidden" name="slug" value="da_outstation_rate">
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text bg-light text-muted">₹</span>
                                                                <input type="number" step="0.01" class="form-control" name="value" value="{{ $da_outstation_rate }}" min="0">
                                                                <span class="input-group-text bg-light text-muted">per Day</span>
                                                                <button type="button" class="btn btn-warning text-white px-3 save-setting-btn">Save</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- HQ Radius Threshold -->
                                            <div class="col-md-6">
                                                <div class="card border-0 shadow-sm h-100 rounded-3">
                                                    <div class="card-body p-4">
                                                        <h6 class="fw-bold text-danger mb-1"><i class="fa fa-compass me-2"></i>HQ Radius Threshold</h6>
                                                        <p class="text-muted small mb-3">Maximum distance (in KM) considered as local HQ area.</p>
                                                        <form class="setting-form">
                                                            @csrf
                                                            <input type="hidden" name="slug" value="hq_radius_km">
                                                            <div class="input-group input-group-sm">
                                                                <input type="number" step="0.1" class="form-control" name="value" value="{{ $hq_radius_km }}" min="0">
                                                                <span class="input-group-text bg-light text-muted">KM</span>
                                                                <button type="button" class="btn btn-danger px-3 save-setting-btn">Save</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Leave Types Master -->
                            <div class="accordion-item border mb-3 rounded-4 overflow-hidden shadow-sm">
                                <h2 class="accordion-header" id="headingLeave">
                                    <button class="accordion-button collapsed fw-bold py-3 px-4 bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLeave" aria-expanded="false" aria-controls="collapseLeave" style="font-size: 1.1rem; color: #1e293b;">
                                        <div class="d-flex align-items-center w-100 me-3">
                                            <i class="fa fa-calendar-alt me-3 text-primary fs-4"></i>
                                            <div>
                                                <div>Leave Types Master</div>
                                                <small class="text-muted fw-normal d-block mt-1" style="font-size: 0.85rem;">Configure default annual quotas for field staff</small>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapseLeave" class="accordion-collapse collapse" aria-labelledby="headingLeave" data-bs-parent="#settingsAccordion">
                                    <div class="accordion-body p-4 bg-light">
                                         <div class="d-flex align-items-center justify-content-between mb-3">
                                             <h6 class="fw-bold mb-0 text-dark">Leave Type Configurations</h6>
                                             <div>
                                                 <button type="button" class="btn btn-info btn-sm rounded-pill px-3 text-white shadow-sm" id="add_leave_type_btn">
                                                     <i class="fa fa-plus me-1"></i>Add New Leave Type
                                                 </button>
                                             </div>
                                         </div>
                                         <p class="text-muted small mb-4">Manage the list of active leave types and their default annual quotas.</p>
                                         
                                         <div id="leave_types_container" class="d-flex flex-wrap gap-3 p-3 bg-white rounded-3 border shadow-inner" style="min-height: 80px;">
                                             <!-- Tags filled by JS -->
                                         </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Visit Purposes Master -->
                            <div class="accordion-item border mb-3 rounded-4 overflow-hidden shadow-sm">
                                <h2 class="accordion-header" id="headingPurposes">
                                    <button class="accordion-button collapsed fw-bold py-3 px-4 bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePurposes" aria-expanded="false" aria-controls="collapsePurposes" style="font-size: 1.1rem; color: #1e293b;">
                                        <div class="d-flex align-items-center w-100 me-3">
                                            <i class="fa fa-list-check me-3 text-primary fs-4"></i>
                                            <div>
                                                <div>Visit Purposes Master</div>
                                                <small class="text-muted fw-normal d-block mt-1" style="font-size: 0.85rem;">Configure purposes for field staff visits</small>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapsePurposes" class="accordion-collapse collapse" aria-labelledby="headingPurposes" data-bs-parent="#settingsAccordion">
                                    <div class="accordion-body p-4 bg-light">
                                         <div class="d-flex align-items-center justify-content-between mb-3">
                                             <h6 class="fw-bold mb-0 text-dark">Visit Purposes</h6>
                                             <div>
                                                 <button type="button" class="btn btn-info btn-sm rounded-pill px-3 text-white shadow-sm" id="add_purpose_btn">
                                                     <i class="fa fa-plus me-1"></i>Add New Purpose
                                                 </button>
                                             </div>
                                         </div>
                                         
                                         <div id="purposes_container" class="d-flex flex-wrap gap-3 p-3 bg-white rounded-3 border shadow-inner" style="min-height: 80px;">
                                             @foreach($visitPurposes as $purpose)
                                                 <div class="d-inline-flex align-items-center justify-content-between rounded-pill py-2 px-3 shadow-sm border" style="background-color: #f8fafc; border-color: #e2e8f0; font-size: 1.05rem;">
                                                     <div class="d-flex align-items-center me-4">
                                                         <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 32px; height: 32px;">
                                                             <i class="fa fa-tag small"></i>
                                                         </div>
                                                         <span class="fw-semibold text-dark">{{ $purpose->name }}</span>
                                                     </div>
                                                     <div class="d-flex gap-2">
                                                         <button type="button" class="btn btn-sm btn-light border p-0 d-flex align-items-center justify-content-center edit-purpose shadow-sm" data-id="{{ $purpose->id }}" data-name="{{ $purpose->name }}" style="width: 30px; height: 30px; border-radius: 50%;">
                                                             <i class="fa fa-edit text-primary" style="font-size: 0.8rem;"></i>
                                                         </button>
                                                         <button type="button" class="btn btn-sm btn-light border p-0 d-flex align-items-center justify-content-center delete-purpose shadow-sm" data-id="{{ $purpose->id }}" style="width: 30px; height: 30px; border-radius: 50%;">
                                                             <i class="fa fa-trash text-danger" style="font-size: 0.8rem;"></i>
                                                         </button>
                                                     </div>
                                                 </div>
                                             @endforeach
                                             @if($visitPurposes->isEmpty())
                                                 <span class="text-muted small">No purposes configured yet.</span>
                                             @endif
                                         </div>
                                    </div>
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
    <script>
        $(function () {
            function showToast(icon, title, text, timer = 3000) {
                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: icon,
                    title: title,
                    text: text,
                    showConfirmButton: false,
                    timer: timer,
                    timerProgressBar: true
                });
            }

            $('.save-setting-btn').on('click', function (e) {
                e.preventDefault();
                let form = $(this).closest('form');
                let btn = $(this);
                let originalText = btn.html();
                
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
                
                $.ajax({
                    url: '{{ route('admin.settings.save') }}',
                    method: 'POST',
                    data: form.serialize(),
                    success: function (res) {
                        showToast('success', 'Saved', res.message);
                        btn.prop('disabled', false).html(originalText);
                    },
                    error: function (xhr) {
                        showToast('error', 'Error', xhr.responseJSON?.message || 'Could not save setting.');
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            });
            // Leave Types Management Logic
            let leaveTypesData = @json($leaveTypes);

            function renderLeaveTypes() {
                let html = '';
                if (leaveTypesData.length === 0) {
                    html = '<div class="text-muted small w-100 text-center py-2">No leave types added yet.</div>';
                } else {
                    leaveTypesData.forEach((leaveType, index) => {
                        html += `
                            <div class="brand-tag-wrapper d-inline-flex flex-column rounded p-3 bg-white shadow-sm" style="min-width: 220px; max-width: 280px; border: 1px solid var(--med-info) !important;">
                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                    <span class="brand-name text-truncate me-2 fw-bold text-info" title="${leaveType.name}">
                                        <i class="fa fa-calendar-day me-1"></i>${leaveType.name}
                                    </span>
                                    <div class="d-flex gap-1 flex-shrink-0">
                                        <button type="button" class="btn btn-outline-info p-0 d-flex align-items-center justify-content-center edit-leavetype-btn" data-index="${index}" style="width: 28px; height: 28px;" title="Edit"><i class="fa fa-edit small"></i></button>
                                        <button type="button" class="btn btn-outline-danger p-0 d-flex align-items-center justify-content-center delete-leavetype-btn" data-index="${index}" style="width: 28px; height: 28px;" title="Delete"><i class="fa fa-trash small"></i></button>
                                    </div>
                                </div>
                                <div class="mb-2 text-center">
                                    <span class="small text-muted d-block mb-1">Default Quota:</span>
                                    <span class="badge bg-info text-white border px-3 py-2" style="font-size: 1rem;">${leaveType.default_quota} Days</span>
                                </div>
                            </div>
                        `;
                    });
                }
                $('#leave_types_container').html(html);
            }

            renderLeaveTypes();

            $('#add_leave_type_btn').on('click', function() {
                Swal.fire({
                    title: 'Add New Leave Type',
                    html: `
                        <div class="text-start mb-3">
                            <label class="form-label fw-bold small">Leave Type Name</label>
                            <input type="text" id="swal_leave_name" class="form-control" placeholder="e.g. Casual Leave">
                        </div>
                        <div class="text-start mb-3">
                            <label class="form-label fw-bold small">Default Annual Quota (Days)</label>
                            <input type="number" id="swal_leave_quota" class="form-control" value="0" min="0">
                        </div>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Add Leave Type',
                    preConfirm: () => {
                        let name = document.getElementById('swal_leave_name').value.trim();
                        let quota = document.getElementById('swal_leave_quota').value;
                        if (!name) {
                            Swal.showValidationMessage('Leave Type Name is required');
                            return false;
                        }
                        if (quota === '' || quota < 0) {
                            Swal.showValidationMessage('Valid Quota is required');
                            return false;
                        }
                        return { name, default_quota: quota };
                    }
                }).then((result) => {
                    if (result.value) {
                        $.ajax({
                            url: '{{ route('admin.settings.leave-types.save') }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                name: result.value.name,
                                default_quota: result.value.default_quota
                            },
                            success: function(res) {
                                leaveTypesData.push(res.leaveType);
                                renderLeaveTypes();
                                showToast('success', 'Added', 'Leave Type added successfully');
                            },
                            error: function(xhr) {
                                let msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Could not add leave type.';
                                showToast('error', 'Error', msg);
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.edit-leavetype-btn', function() {
                let index = $(this).data('index');
                let leaveObj = leaveTypesData[index];
                
                Swal.fire({
                    title: 'Edit Leave Type',
                    html: `
                        <div class="text-start mb-3">
                            <label class="form-label fw-bold small">Leave Type Name</label>
                            <input type="text" id="swal_leave_name" class="form-control" value="${leaveObj.name}" placeholder="e.g. Casual Leave">
                        </div>
                        <div class="text-start mb-3">
                            <label class="form-label fw-bold small">Default Annual Quota (Days)</label>
                            <input type="number" id="swal_leave_quota" class="form-control" value="${leaveObj.default_quota}" min="0">
                        </div>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Update Leave Type',
                    preConfirm: () => {
                        let name = document.getElementById('swal_leave_name').value.trim();
                        let quota = document.getElementById('swal_leave_quota').value;
                        if (!name) {
                            Swal.showValidationMessage('Leave Type Name is required');
                            return false;
                        }
                        return { id: leaveObj.id, name, default_quota: quota };
                    }
                }).then((result) => {
                    if (result.value) {
                        $.ajax({
                            url: '{{ route('admin.settings.leave-types.save') }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                id: result.value.id,
                                name: result.value.name,
                                default_quota: result.value.default_quota
                            },
                            success: function(res) {
                                leaveTypesData[index] = res.leaveType;
                                renderLeaveTypes();
                                showToast('success', 'Updated', 'Leave Type updated successfully');
                            },
                            error: function(xhr) {
                                let msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Could not update leave type.';
                                showToast('error', 'Error', msg);
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.delete-leavetype-btn', function() {
                let index = $(this).data('index');
                let leaveObj = leaveTypesData[index];
                Swal.fire({
                    title: 'Delete Leave Type?',
                    text: `Are you sure you want to delete "${leaveObj.name}"? This will also remove any existing user balances for this type.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('admin.settings.leave-types.delete') }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                id: leaveObj.id
                            },
                            success: function() {
                                leaveTypesData.splice(index, 1);
                                renderLeaveTypes();
                                showToast('success', 'Deleted', 'Leave Type deleted successfully');
                            },
                            error: function() {
                                showToast('error', 'Error', 'Could not delete leave type.');
                            }
                        });
                    }
                });
            });
            // Visit Purpose Management
            $('#add_purpose_btn').on('click', function() {
                Swal.fire({
                    title: 'Add New Visit Purpose',
                    input: 'text',
                    inputPlaceholder: 'Enter purpose name',
                    showCancelButton: true,
                    confirmButtonText: 'Save',
                    preConfirm: (name) => {
                        if (!name) Swal.showValidationMessage('Name is required');
                        return name;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        savePurpose(null, result.value);
                    }
                });
            });

            $(document).on('click', '.edit-purpose', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                Swal.fire({
                    title: 'Edit Visit Purpose',
                    input: 'text',
                    inputValue: name,
                    showCancelButton: true,
                    confirmButtonText: 'Save',
                    preConfirm: (newName) => {
                        if (!newName) Swal.showValidationMessage('Name is required');
                        return newName;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        savePurpose(id, result.value);
                    }
                });
            });

            $(document).on('click', '.delete-purpose', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('admin.settings.field-staff.delete-purpose') }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                id: id
                            },
                            success: function(res) {
                                showToast('success', 'Deleted', res.message);
                                setTimeout(() => location.reload(), 1000);
                            }
                        });
                    }
                });
            });

            function savePurpose(id, name) {
                $.ajax({
                    url: '{{ route('admin.settings.field-staff.save-purpose') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        name: name
                    },
                    success: function(res) {
                        showToast('success', 'Saved', res.message);
                        setTimeout(() => location.reload(), 1000);
                    },
                    error: function(xhr) {
                        showToast('error', 'Error', xhr.responseJSON?.message || 'Could not save purpose.');
                    }
                });
            }
        });
    </script>
@endpush
