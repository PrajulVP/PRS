@extends('layouts.admin')

@section('page-body')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Field Staff Configuration</h5>
                        <span>Manage global parameters for trackng, geo-fencing and reimbursements.</span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Geo-fencing -->
                            <div class="col-md-6 mb-4">
                                <div class="card border-primary shadow-sm h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary"><i class="fa fa-map-marker-alt me-2"></i>Geo-fencing Radius</h6>
                                        <p class="text-muted small">Max allowed distance (in meters) from customer for punching and visit validation.</p>
                                        <form class="setting-form">
                                            @csrf
                                            <input type="hidden" name="slug" value="geofence_radius">
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="value" value="{{ $geofence_radius }}" min="1">
                                                <span class="input-group-text">Meters</span>
                                                <button type="button" class="btn btn-primary save-setting-btn">Save</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- TA Rate -->
                            <div class="col-md-6 mb-4">
                                <div class="card border-success shadow-sm h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-success"><i class="fa fa-car me-2"></i>Travel Allowance (TA) Rate</h6>
                                        <p class="text-muted small">Reimbursement rate per kilometer travelled.</p>
                                        <form class="setting-form">
                                            @csrf
                                            <input type="hidden" name="slug" value="ta_rate_per_km">
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" step="0.01" class="form-control" name="value" value="{{ $ta_rate_per_km }}" min="0">
                                                <span class="input-group-text">per KM</span>
                                                <button type="button" class="btn btn-success save-setting-btn">Save</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- DA HQ Rate -->
                            <div class="col-md-6 mb-4">
                                <div class="card border-info shadow-sm h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-info"><i class="fa fa-building me-2"></i>DA HQ Rate</h6>
                                        <p class="text-muted small">Daily Allowance rate for regular Headquarter visits.</p>
                                        <form class="setting-form">
                                            @csrf
                                            <input type="hidden" name="slug" value="da_hq_rate">
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" step="0.01" class="form-control" name="value" value="{{ $da_hq_rate }}" min="0">
                                                <span class="input-group-text">per Day</span>
                                                <button type="button" class="btn btn-info text-white save-setting-btn">Save</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- DA Outstation Rate -->
                            <div class="col-md-6 mb-4">
                                <div class="card border-warning shadow-sm h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-warning"><i class="fa fa-plane-departure me-2"></i>DA Outstation Rate</h6>
                                        <p class="text-muted small">Daily Allowance rate for visits outside specified headquarters.</p>
                                        <form class="setting-form">
                                            @csrf
                                            <input type="hidden" name="slug" value="da_outstation_rate">
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" step="0.01" class="form-control" name="value" value="{{ $da_outstation_rate }}" min="0">
                                                <span class="input-group-text">per Day</span>
                                                <button type="button" class="btn btn-warning text-white save-setting-btn">Save</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                             <!-- HQ Radius Threshold -->
                            <div class="col-md-6 mb-4">
                                <div class="card border-danger shadow-sm h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-danger"><i class="fa fa-compass me-2"></i>HQ Radius Threshold</h6>
                                        <p class="text-muted small">Maximum distance (in KM) considered as local HQ area.</p>
                                        <form class="setting-form">
                                            @csrf
                                            <input type="hidden" name="slug" value="hq_radius_km">
                                            <div class="input-group">
                                                <input type="number" step="0.1" class="form-control" name="value" value="{{ $hq_radius_km }}" min="0">
                                                <span class="input-group-text">KM</span>
                                                <button type="button" class="btn btn-danger save-setting-btn">Save</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Brands -->
                            <div class="col-md-12 mb-4">
                                <div class="card border-dark shadow-sm h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-dark"><i class="fa fa-tags me-2"></i>Product Brands Master</h6>
                                        <p class="text-muted small">Enter available brands separated by commas (e.g. Atomets, Atomshield, etc.)</p>
                                        <form class="setting-form" id="brands_form">
                                            @csrf
                                            <input type="hidden" name="slug" value="product_brands">
                                            <input type="hidden" name="value" id="brands_final_value" value="{{ $product_brands }}">
                                            
                                            <div class="mb-4">
                                                <label class="form-label fw-bold">Add New Brand</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control border-dark" id="new_brand_name" placeholder="Enter brand name (e.g. Atomed)">
                                                    <button type="button" class="btn btn-dark" id="add_brand_btn"><i class="fa fa-plus me-2"></i>Add Brand</button>
                                                </div>
                                            </div>

                                            <label class="form-label fw-bold">Current Active Brands</label>
                                            <div id="brands_tag_container" class="d-flex flex-wrap gap-3 p-3 bg-light rounded border shadow-inner" style="min-height: 80px;">
                                                <!-- Tags filled by JS -->
                                            </div>
                                            
                                            <div class="mt-4 text-end">
                                                <button type="button" class="btn btn-primary px-4 save-setting-btn" id="save_brands_btn" style="display: none;">
                                                    <i class="fa fa-save me-2"></i>Apply Changes
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-light border mt-2">
                            <h5><i class="fa fa-info-circle me-2"></i>Legacy Settings</h5>
                            <p class="mb-0 small text-muted">Loyalty Points and GST settings have been moved to individual Product settings. Please edit products to manage those values.</p>
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
            function showError(msg) {
                $('#settings-errors').text(msg).show();
            }

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

            // Brand Management Logic
            let currentBrands = $('#brands_final_value').val().split(',').map(s => s.trim()).filter(s => s.length > 0);

            function renderBrands() {
                let html = '';
                if (currentBrands.length === 0) {
                    html = '<div class="text-muted small w-100 text-center py-2">No brands added yet.</div>';
                } else {
                    currentBrands.forEach((brand, index) => {
                        html += `
                            <div class="brand-tag-wrapper d-inline-flex align-items-center bg-white border border-primary rounded p-2 shadow-sm">
                                <span class="fw-bold pe-2 text-primary border-end me-2"><i class="fa fa-tag me-1 small"></i>${brand}</span>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-outline-info p-0 d-flex align-items-center justify-content-center edit-brand-btn" data-index="${index}" style="width: 25px; height: 25px;" title="Edit"><i class="fa fa-edit small"></i></button>
                                    <button type="button" class="btn btn-outline-danger p-0 d-flex align-items-center justify-content-center delete-brand-btn" data-index="${index}" style="width: 25px; height: 25px;" title="Delete"><i class="fa fa-trash small"></i></button>
                                </div>
                            </div>
                        `;
                    });
                }
                $('#brands_tag_container').html(html);
                $('#brands_final_value').val(currentBrands.join(','));
            }

            // Initial render
            renderBrands();

            $('#add_brand_btn').on('click', function() {
                let name = $('#new_brand_name').val().trim();
                if (name && !currentBrands.includes(name)) {
                    currentBrands.push(name);
                    $('#new_brand_name').val('');
                    $('#save_brands_btn').show();
                    renderBrands();
                } else if (currentBrands.includes(name)) {
                    showToast('warning', 'Duplicate', 'This brand already exists');
                }
            });

            $(document).on('click', '.delete-brand-btn', function() {
                let index = $(this).data('index');
                let name = currentBrands[index];
                Swal.fire({
                    title: 'Remove Brand?',
                    text: `Are you sure you want to remove "${name}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, remove it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        currentBrands.splice(index, 1);
                        $('#save_brands_btn').show();
                        renderBrands();
                    }
                });
            });

            $(document).on('click', '.edit-brand-btn', function() {
                let index = $(this).data('index');
                let oldName = currentBrands[index];
                Swal.fire({
                    title: 'Edit Brand Name',
                    input: 'text',
                    inputValue: oldName,
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                }).then((result) => {
                    if (result.value && result.value.trim().length > 0) {
                        currentBrands[index] = result.value.trim();
                        $('#save_brands_btn').show();
                        renderBrands();
                    }
                });
            });

            $('.save-setting-btn').on('click', function () {
                $('#settings-errors').hide();
                var $btn = $(this).prop('disabled', true).text('Saving...');
                var $form = $btn.closest('form');
                var data = $form.serialize();

                // Show persistent saving toast
                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: 'info',
                    title: 'Saving...',
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route('admin.settings.save') }}',
                    method: 'POST',
                    data: data,
                    success: function (res) {
                        Swal.close();
                        showToast('success', 'Saved', 'Setting saved successfully');
                        if (data.includes('slug=product_brands')) {
                            $('#save_brands_btn').hide();
                        }
                    },
                    // ... error handler ...
                    error: function (xhr) {
                        Swal.close();
                        var message = 'An unexpected error occurred';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            var all = [];
                            $.each(xhr.responseJSON.errors, function (k, v) {
                                all.push(v.join(', '));
                            });
                            message = all.join('\n');
                        }
                        showToast('error', 'Error', message, 5000);
                    },
                    complete: function () {
                        $btn.prop('disabled', false).text('Save');
                    }
                });
            });
        });
    </script>
@endpush