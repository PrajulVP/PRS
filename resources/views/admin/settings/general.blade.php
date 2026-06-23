@extends('layouts.admin')

@section('page-body')
    <style>
        /* === Premium Dynamic Theme Adjustments === */
        #brands_tag_container {
            background-color: var(--med-bg-body) !important;
            border: 1px solid var(--med-border) !important;
            box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.05) !important;
            transition: all 0.3s ease;
        }
        body.dark-only #brands_tag_container {
            box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.3) !important;
        }
        .brand-tag-wrapper {
            background-color: var(--med-bg-card) !important;
            border: 1px solid var(--med-border) !important;
            box-shadow: var(--med-shadow-soft) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .brand-tag-wrapper:hover {
            transform: translateY(-3px);
            border-color: var(--med-primary) !important;
            box-shadow: var(--med-shadow-glow), var(--med-shadow-soft) !important;
        }
        .brand-name {
            color: var(--med-primary) !important;
            font-weight: 700 !important;
        }
        .manage-products-btn {
            color: var(--med-primary, #00497a) !important;
            font-weight: 700 !important;
            font-size: 0.85rem !important;
            transition: all 0.2s ease !important;
        }
        .manage-products-btn:hover {
            color: var(--med-secondary, #0067ab) !important;
            text-decoration: underline !important;
        }
        body.dark-only .manage-products-btn {
            color: #38bdf8 !important;
        }
        body.dark-only .manage-products-btn:hover {
            color: #7dd3fc !important;
        }
        body.dark-only .form-check-input {
            background-color: rgba(255, 255, 255, 0.1) !important;
            border-color: rgba(255, 255, 255, 0.2) !important;
        }
        body.dark-only .form-check-input:checked {
            background-color: #38bdf8 !important;
            border-color: #38bdf8 !important;
        }
        .alert.alert-light.border {
            background-color: var(--med-bg-card) !important;
            border-color: var(--med-border) !important;
            color: var(--med-text-main) !important;
            border-radius: 16px !important;
            box-shadow: var(--med-shadow-soft) !important;
        }
        .alert.alert-light.border h5 {
            color: var(--med-primary) !important;
            font-weight: 700 !important;
        }
        .alert.alert-light.border p {
            color: var(--med-text-muted) !important;
        }
        .edit-brand-btn {
            border-color: var(--med-primary, #00497a) !important;
            color: var(--med-primary, #00497a) !important;
            background: transparent !important;
            transition: all 0.2s ease !important;
        }
        .edit-brand-btn:hover {
            background-color: var(--med-primary, #00497a) !important;
            color: #ffffff !important;
        }
        body.dark-only .edit-brand-btn {
            border-color: #38bdf8 !important;
            color: #38bdf8 !important;
        }
        body.dark-only .edit-brand-btn:hover {
            background-color: #38bdf8 !important;
            color: #000000 !important;
        }
    </style>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Manage Products Modal -->
    <div class="modal fade" id="manageBrandProductsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 bg-light rounded-top-4 pb-3">
                    <div class="d-flex align-items-center">
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="modalBrandTitle">Brand Products</h5>
                            <p class="text-muted small mb-0">Toggle returnability for individual products.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="p-3 bg-light border-bottom">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                            <input type="text" id="productSearchInput" class="form-control border-start-0" placeholder="Search products in this brand...">
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 450px;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th class="ps-4 py-3 text-muted small text-uppercase fw-bold">Product Details</th>
                                    <th class="py-3 text-muted small text-uppercase fw-bold text-center">Returnable</th>
                                </tr>
                            </thead>
                            <tbody id="brandProductsTableBody">
                                <!-- Products loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Close</button>
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
            let returnableBrands = '{{ $returnable_brands }}'.split(',').map(s => s.trim()).filter(s => s.length > 0);

            function renderBrands() {
                let html = '';
                if (currentBrands.length === 0) {
                    html = '<div class="text-muted small w-100 text-center py-2">No brands added yet.</div>';
                } else {
                    currentBrands.forEach((brand, index) => {
                        let isReturnable = returnableBrands.includes(brand);
                        html += `
                            <div class="brand-tag-wrapper d-inline-flex flex-column rounded p-3" style="min-width: 220px;">
                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                    <span class="brand-name text-truncate me-2" title="${brand}"><i class="fa fa-tag me-1 small"></i>${brand}</span>
                                    <div class="d-flex gap-1 flex-shrink-0">
                                        <button type="button" class="btn btn-outline-info p-0 d-flex align-items-center justify-content-center edit-brand-btn" data-index="${index}" style="width: 28px; height: 28px;" title="Edit"><i class="fa fa-edit small"></i></button>
                                        <button type="button" class="btn btn-outline-danger p-0 d-flex align-items-center justify-content-center delete-brand-btn" data-index="${index}" style="width: 28px; height: 28px;" title="Delete"><i class="fa fa-trash small"></i></button>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="small text-muted">Returnable</span>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input returnable-toggle" type="checkbox" data-brand="${brand}" ${isReturnable ? 'checked' : ''}>
                                    </div>
                                </div>
                                <div class="border-top pt-2 mt-1 text-center">
                                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 manage-products-btn" data-brand="${brand}">
                                        <i class="fa fa-list-ul me-1"></i>Manage Products
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                }
                $('#brands_tag_container').html(html);
                $('#brands_final_value').val(currentBrands.join(','));
                
                // Update hidden input for returnable_brands
                if ($('#returnable_brands_input').length === 0) {
                    $('#brands_form').append(`<input type="hidden" name="returnable_brands" id="returnable_brands_input" value="${returnableBrands.join(',')}">`);
                } else {
                    $('#returnable_brands_input').val(returnableBrands.join(','));
                }
            }

            $(document).on('change', '.returnable-toggle', function() {
                let brand = $(this).data('brand');
                let isChecked = $(this).is(':checked');
                let $manageBtn = $(`.manage-products-btn[data-brand="${brand}"]`);
                
                $manageBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i>Syncing...');
                
                if (isChecked) {
                    if (!returnableBrands.includes(brand)) returnableBrands.push(brand);
                } else {
                    returnableBrands = returnableBrands.filter(b => b !== brand);
                }
                
                $('#returnable_brands_input').val(returnableBrands.join(','));
                
                // Save this specific setting immediately via AJAX to sync products
                $.ajax({
                    url: '{{ route('admin.settings.save') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        slug: 'returnable_brands',
                        value: returnableBrands.join(',')
                    },
                    success: function() {
                        // Now sync actual products
                        $.ajax({
                            url: '{{ route('products.bulk-brand-returnable') }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                brand: brand,
                                is_returnable: isChecked ? 1 : 0
                            },
                            success: function(res) {
                                showToast('success', 'Sync Successful', `Products for ${brand} are now ${isChecked ? 'returnable' : 'non-returnable'}.`);
                                $manageBtn.prop('disabled', false).html('<i class="fa fa-list-ul me-1"></i>Manage Products');
                            },
                            error: function() {
                                showToast('error', 'Sync Failed', 'Could not sync products.');
                                $manageBtn.prop('disabled', false).html('<i class="fa fa-list-ul me-1"></i>Manage Products');
                            }
                        });
                    },
                    error: function() {
                        showToast('error', 'Settings Failed', 'Could not save returnable brands setting.');
                        $manageBtn.prop('disabled', false).html('<i class="fa fa-list-ul me-1"></i>Manage Products');
                    }
                });
            });

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
                        let newName = result.value.trim();
                        if (newName !== oldName) {
                            currentBrands[index] = newName;
                            $('#brands_form').append(`<input type="hidden" name="renamed_brands[${oldName}]" value="${newName}">`);
                            $('#save_brands_btn').show();
                            renderBrands();
                        }
                    }
                });
            });

            // Manage Products Individual Logic
            $(document).on('click', '.manage-products-btn', function() {
                let brand = $(this).data('brand');
                $('#modalBrandTitle').text(`${brand} Products`);
                $('#brandProductsTableBody').html('<tr><td colspan="2" class="text-center py-5"><i class="fa fa-spinner fa-spin fa-2x mb-2 d-block"></i>Loading products...</td></tr>');
                $('#manageBrandProductsModal').modal('show');
                
                $.ajax({
                    url: `/products/get-by-brand/${encodeURIComponent(brand)}`,
                    method: 'GET',
                    cache: false,
                    success: function(products) {
                        renderBrandProducts(products);
                    }
                });
            });

            function renderBrandProducts(products) {
                let html = '';
                if (products.length === 0) {
                    html = '<tr><td colspan="2" class="text-center py-4 text-muted">No products found for this brand.</td></tr>';
                } else {
                    products.forEach(p => {
                        html += `
                            <tr class="product-row">
                                <td class="ps-4 py-3">
                                    <div class="fw-bold text-main-theme">${p.product_name}</div>
                                    <div class="text-muted small">${p.product_code || 'No Code'}</div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block mb-0">
                                        <input class="form-check-input individual-product-toggle" type="checkbox" data-id="${p.id}" ${p.is_returnable ? 'checked' : ''}>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                }
                $('#brandProductsTableBody').html(html);
            }

            // Search in Modal
            $('#productSearchInput').on('keyup', function() {
                let val = $(this).val().toLowerCase();
                $('.product-row').each(function() {
                    let text = $(this).text().toLowerCase();
                    $(this).toggle(text.indexOf(val) > -1);
                });
            });

            // Toggle Individual Product
            $(document).on('change', '.individual-product-toggle', function() {
                let id = $(this).data('id');
                let isChecked = $(this).is(':checked');
                let toggleUrl = "{{ route('products.toggle-returnable', ':id') }}".replace(':id', id);
                
                $.ajax({
                    url: toggleUrl,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    error: function() {
                        showToast('error', 'Error', 'Failed to update product setting.');
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