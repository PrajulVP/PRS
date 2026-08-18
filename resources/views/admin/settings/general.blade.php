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

        /* === SweetAlert2 Light & Dark Theme Adjustments === */
        .swal2-popup {
            background-color: #ffffff !important;
            color: #1e293b !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        }
        .swal2-title {
            color: #1e293b !important;
            font-weight: 700 !important;
        }
        .swal2-html-container {
            color: #334155 !important;
        }
        .swal2-html-container label.form-label {
            color: #475569 !important;
            font-weight: 600 !important;
        }
        .swal2-html-container .form-check-label {
            color: #475569 !important;
        }
        .swal2-html-container input.form-control,
        .swal2-html-container select.form-select {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border: 1px solid #cbd5e1 !important;
        }
        .swal2-html-container input.form-control::placeholder {
            color: #94a3b8 !important;
        }
        .swal2-html-container #swal_custom_fields_container {
            background-color: #f8fafc !important;
            border-color: #cbd5e1 !important;
        }
        .swal2-html-container #swal_custom_fields_container .text-muted {
            color: #64748b !important;
        }
        
        /* Dark mode overrides for SweetAlert2 */
        body.dark-only .swal2-popup {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
        body.dark-only .swal2-title {
            color: #f8fafc !important;
        }
        body.dark-only .swal2-html-container {
            color: #cbd5e1 !important;
        }
        body.dark-only .swal2-html-container label.form-label {
            color: #cbd5e1 !important;
        }
        body.dark-only .swal2-html-container .form-check-label {
            color: #cbd5e1 !important;
        }
        body.dark-only .swal2-html-container input.form-control,
        body.dark-only .swal2-html-container select.form-select {
            background-color: #1e293b !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
        }
        body.dark-only .swal2-html-container input.form-control::placeholder {
            color: #64748b !important;
        }
        body.dark-only .swal2-html-container #swal_custom_fields_container {
            background-color: #1e293b !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        body.dark-only .swal2-html-container #swal_custom_fields_container .text-muted {
            color: #94a3b8 !important;
        }
        body.dark-only .swal2-cancel {
            background-color: #334155 !important;
            color: #ffffff !important;
        }

        /* === Accordion Custom Plus/Minus Icons === */
        .accordion-button::after {
            content: "+" !important;
            font-family: inherit !important;
            font-size: 1.5rem !important;
            font-weight: 300 !important;
            background-image: none !important;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: none !important;
            color: #64748b;
            transition: all 0.2s ease-in-out;
        }
        .accordion-button:not(.collapsed)::after {
            content: "-" !important;
            font-size: 1.8rem !important;
            line-height: 0.8 !important;
            color: var(--med-primary, #00497a);
        }
        body.dark-only .accordion-button::after {
            color: #94a3b8;
        }
        body.dark-only .accordion-button:not(.collapsed)::after {
            color: #38bdf8;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom-0 pb-2 pt-4 px-4">
                        <h4 class="fw-bold mb-1">General Settings</h4>
                        <p class="text-muted small mb-0">Manage global parameters for products, tracking, geo-fencing, and reimbursements.</p>
                    </div>
                    <div class="card-body p-4 pt-2">
                        <div class="accordion accordion-flush" id="settingsAccordion">
                            
                            <style>
                                .custom-brand-toggle {
                                    cursor: pointer;
                                    transition: all 0.3s ease;
                                    border: 1px solid rgba(0,0,0,0.08);
                                }
                                .custom-brand-toggle:hover {
                                    background-color: rgba(0,0,0,0.015);
                                    border-color: rgba(13,110,253,0.3);
                                }
                                .custom-brand-toggle[aria-expanded="true"] {
                                    border-bottom: none;
                                    border-bottom-left-radius: 0 !important;
                                    border-bottom-right-radius: 0 !important;
                                    background-color: var(--bs-body-bg, #fff);
                                }
                                .custom-brand-toggle[aria-expanded="true"] .toggle-icon {
                                    transform: rotate(180deg);
                                }
                                .custom-brand-toggle[aria-expanded="true"] .toggle-icon-wrap {
                                    color: #0d6efd !important;
                                }
                            </style>
                            <!-- Product Brands Master (Priority #1) -->
                            <div class="accordion-item border-0 mb-4 rounded-4 shadow-sm" style="background: var(--bs-body-bg, #fff);">
                                <div class="custom-brand-toggle p-3 rounded-4" id="headingBrands" data-bs-toggle="collapse" data-bs-target="#collapseBrands" aria-expanded="false" aria-controls="collapseBrands">
                                    <div class="d-flex align-items-center justify-content-between w-100">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="fw-bold mb-1" style="color: var(--bs-body-color, #1e293b); letter-spacing: 0.3px; font-size: 1rem;">Product Brands Master</h6>
                                                <small class="text-muted fw-medium d-block" style="font-size: 0.85rem;">{{ count(array_filter(explode(',', trim($product_brands)))) }} Active Brands Configured</small>
                                            </div>
                                        </div>
                                        <div class="toggle-icon-wrap d-flex align-items-center justify-content-center me-2" style="color: #6c757d; transition: all 0.3s ease;">
                                            <i class="fa fa-chevron-down toggle-icon" style="transition: transform 0.3s ease; font-size: 0.9rem;"></i>
                                        </div>
                                    </div>
                                </div>
                                <div id="collapseBrands" class="accordion-collapse collapse" aria-labelledby="headingBrands" data-bs-parent="#settingsAccordion">
                                    <div class="accordion-body p-0 border border-top-0 rounded-bottom-4" style="border-color: rgba(0,0,0,0.08) !important;">
                                        <div class="p-4 p-md-5" style="background: rgba(0,0,0,0.015);">
                                            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 pb-3 border-bottom">
                                                <div>
                                                    <h6 class="mb-1 fw-bold" style="color: var(--bs-body-color, #1e293b); font-size: 1.1rem;">Active Brands List</h6>
                                                    <p class="text-muted small mb-0">Manage layout behavior (Medical, Orthopedic, etc.), Return Rules, and Loyalty settings for each brand.</p>
                                                </div>
                                                <button type="button" class="btn btn-dark fw-bold px-4 py-2 shadow-sm rounded-pill mt-3 mt-md-0 text-nowrap" id="add_brand_btn" style="letter-spacing: 0.5px;">
                                                    <i class="fa fa-plus me-2"></i>Add New Brand
                                                </button>
                                            </div>
                                         <form class="setting-form" id="brands_form">
                                             @csrf
                                             <input type="hidden" name="slug" value="product_brands">
                                             <input type="hidden" name="value" id="brands_final_value" value="{{ $product_brands }}">
                                             
                                             <div id="brands_tag_container" class="p-3 bg-white rounded-3 border shadow-inner" style="min-height: 80px;">
                                                 <!-- Tags filled by JS -->
                                             </div>
                                         </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Field Staff Configuration moved to dedicated page -->


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
            let brandsData = @json($brands);
            let returnableBrands = '{{ $returnable_brands }}'.split(',').map(s => s.trim()).filter(s => s.length > 0);
            let loyaltyBrands = '{{ $loyalty_brands }}'.split(',').map(s => s.trim()).filter(s => s.length > 0);
            let loyaltyRules = @json(json_decode($loyalty_rules, true) ?: (object)[]);

            function getBrandFieldsList(brandObj) {
                let layout = brandObj.layout_type;
                let fields = brandObj.custom_fields || [];
                let activeFields = [];
                if (layout === 'medical') {
                    activeFields = ['generic_name', 'hsn_code', 'strip_size', 'box_size'];
                } else if (layout === 'ortho') {
                    activeFields = ['product_code', 'hsn_code', 'variants'];
                } else if (layout === 'general') {
                    activeFields = ['product_code', 'hsn_code', 'pack'];
                } else {
                    activeFields = fields;
                }

                let fieldNamesMap = {
                    'product_code': 'Product Code',
                    'generic_name': 'Generic Name',
                    'hsn_code': 'HSN Code',
                    'pack': 'Pack Size',
                    'strip_size': 'Tablets/Strip',
                    'box_size': 'Strips/Box',
                    'variants': 'Variants'
                };

                return activeFields.map(f => fieldNamesMap[f] || f);
            }

            function renderBrands() {
                let html = '';
                if (brandsData.length === 0) {
                    html = '<div class="text-muted small w-100 text-center py-4">No brands added yet.</div>';
                } else {
                    html += '<div class="d-flex flex-column gap-4 w-100" id="brandConfigList">';
                    brandsData.forEach((brandObj, index) => {
                        let brand = brandObj.name;
                        let isReturnable = returnableBrands.includes(brand);
                        let isLoyalty = loyaltyBrands.includes(brand);
                        let fieldsList = getBrandFieldsList(brandObj);

                        html += `
                            <div class="card border rounded-4 shadow-sm w-100" style="background: var(--bs-body-bg, #fff);">
                                <div class="card-header bg-transparent border-bottom px-4 py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                    <div class="d-flex align-items-center flex-grow-1">
                                        <div class="d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                            <i class="fa ${brandObj.icon || 'fa-tag'} text-primary" style="font-size: 1.75rem;"></i>
                                        </div>
                                        <div class="text-start">
                                            <div class="fw-bold fs-5">${brand}</div>
                                            <div class="small text-muted mt-1">${brandObj.description || 'No description provided'}</div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-primary fw-bold px-4 shadow-sm text-nowrap edit-brand-btn" data-index="${index}"><i class="fa fa-edit me-2"></i>Edit Brand</button>
                                        <button type="button" class="btn btn-outline-danger fw-bold px-4 shadow-sm text-nowrap delete-brand-btn" data-index="${index}"><i class="fa fa-trash me-2"></i>Remove</button>
                                    </div>
                                </div>
                                <div class="card-body p-4" style="background: rgba(0,0,0,0.02);">
                                    <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
                                        <span class="small fw-bold text-uppercase text-muted me-2">Visible Fields:</span>
                                        ${fieldsList.length === 0 
                                            ? '<span class="text-muted small">None (Common only)</span>' 
                                            : fieldsList.map(f => `<span class="badge border px-3 py-2 rounded-pill shadow-sm" style="background: var(--bs-body-bg, #fff); color: var(--bs-body-color, #000);">${f}</span>`).join('')
                                        }
                                    </div>
                                    
                                    <div class="row g-4 align-items-stretch">
                                        
                                        <!-- Return Configuration -->
                                        <div class="col-md-6">
                                            <div class="p-4 rounded-4 border shadow-sm h-100 d-flex flex-column position-relative" style="border-top: 4px solid #f97316 !important; background: var(--bs-body-bg, #fff);">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div class="d-flex align-items-center gap-3 text-start">
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background: rgba(249,115,22,0.15); color: #f97316;">
                                                            <i class="fa fa-undo fs-5"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="fw-bold mb-1" style="color: inherit;">Return Configuration</h6>
                                                            <span class="small text-muted">Allow item returns for this brand</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-check form-switch mb-0 mt-1">
                                                        <input class="form-check-input returnable-toggle" style="width: 2.5em; height: 1.25em; cursor: pointer;" type="checkbox" data-brand="${brand}" ${isReturnable ? 'checked' : ''}>
                                                    </div>
                                                </div>
                                                
                                                <div class="mt-auto pt-3 border-top">
                                                    <button type="button" class="btn w-100 manage-products-btn shadow-sm py-2 fw-bold text-uppercase border-0" style="background-color: #f97316 !important; color: #ffffff !important; border-radius: 8px; letter-spacing: 0.5px; text-decoration: none !important;" data-brand="${brand}" ${!isReturnable ? 'disabled' : ''}>
                                                        <i class="fa fa-list-ul me-2" style="color: #ffffff !important;"></i>Manage Return Products
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Loyalty Configuration -->
                                        <div class="col-md-6">
                                            <div class="p-4 rounded-4 border shadow-sm h-100 d-flex flex-column position-relative" style="border-top: 4px solid #0ea5e9 !important; background: var(--bs-body-bg, #fff);">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div class="d-flex align-items-center gap-3 text-start">
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background: rgba(14,165,233,0.15); color: #0ea5e9;">
                                                            <i class="fa fa-gift fs-5"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="fw-bold mb-1" style="color: inherit;">Loyalty Program</h6>
                                                            <span class="small text-muted">Enable loyalty rewards</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-check form-switch mb-0 mt-1">
                                                        <input class="form-check-input loyalty-toggle" style="width: 2.5em; height: 1.25em; cursor: pointer;" type="checkbox" data-brand="${brand}" ${isLoyalty ? 'checked' : ''}>
                                                    </div>
                                                </div>
                                                
                                                <div class="mt-auto pt-3 border-top">
                                                    <button type="button" class="btn w-100 config-loyalty-btn shadow-sm py-2 fw-bold text-uppercase border-0" style="background-color: #0ea5e9 !important; color: #ffffff !important; border-radius: 8px; letter-spacing: 0.5px; text-decoration: none !important;" data-brand="${brand}" ${!isLoyalty ? 'disabled' : ''}>
                                                        <i class="fa fa-cog me-2" style="color: #ffffff !important;"></i>Configure Loyalty Rules
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                }
                $('#brands_tag_container').html(html);
                let brandNames = brandsData.map(b => b.name);
                $('#brands_final_value').val(brandNames.join(','));
                
                // Update hidden input for returnable_brands
                if ($('#returnable_brands_input').length === 0) {
                    $('#brands_form').append(`<input type="hidden" name="returnable_brands" id="returnable_brands_input" value="${returnableBrands.join(',')}">`);
                } else {
                    $('#returnable_brands_input').val(returnableBrands.join(','));
                }
                
                // Update hidden input for loyalty_brands
                if ($('#loyalty_brands_input').length === 0) {
                    $('#brands_form').append(`<input type="hidden" name="loyalty_brands" id="loyalty_brands_input" value="${loyaltyBrands.join(',')}">`);
                } else {
                    $('#loyalty_brands_input').val(loyaltyBrands.join(','));
                }
                
                if ($('#loyalty_rules_input').length === 0) {
                    $('#brands_form').append(`<input type="hidden" name="loyalty_rules" id="loyalty_rules_input" value='${JSON.stringify(loyaltyRules)}'>`);
                } else {
                    $('#loyalty_rules_input').val(JSON.stringify(loyaltyRules));
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
                                $manageBtn.prop('disabled', !isChecked).html('<i class="fa fa-list-ul me-1"></i>Manage Return Products');
                            },
                            error: function() {
                                showToast('error', 'Sync Failed', 'Could not sync products.');
                                $manageBtn.prop('disabled', !isChecked).html('<i class="fa fa-list-ul me-1"></i>Manage return Products');
                            }
                        });
                    },
                    error: function() {
                        showToast('error', 'Settings Failed', 'Could not save returnable brands setting.');
                        $manageBtn.prop('disabled', !isChecked).html('<i class="fa fa-list-ul me-1"></i>Manage Return Products');
                    }
                });
            });

            $(document).on('change', '.loyalty-toggle', function() {
                let brand = $(this).data('brand');
                let isChecked = $(this).is(':checked');
                let $configBtn = $(`.config-loyalty-btn[data-brand="${brand}"]`);
                
                $configBtn.prop('disabled', !isChecked);
                
                if (isChecked) {
                    if (!loyaltyBrands.includes(brand)) loyaltyBrands.push(brand);
                } else {
                    loyaltyBrands = loyaltyBrands.filter(b => b !== brand);
                }
                
                $('#loyalty_brands_input').val(loyaltyBrands.join(','));
                
                $.ajax({
                    url: '{{ route('admin.settings.save') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        slug: 'loyalty_brands',
                        value: loyaltyBrands.join(',')
                    },
                    success: function() {
                        showToast('success', 'Saved', 'Loyalty status updated for ' + brand);
                    },
                    error: function() {
                        showToast('error', 'Error', 'Could not save loyalty status.');
                    }
                });
            });

            $(document).on('click', '.config-loyalty-btn', function() {
                let brand = $(this).data('brand');
                let rules = loyaltyRules[brand] ? [...loyaltyRules[brand]] : [];

                rules.sort((a, b) => a.threshold - b.threshold);
                let editingIndex = null;

                let renderRulesList = (rulesList) => {
                    if (rulesList.length === 0) return '<div class="text-center text-muted small p-4 bg-light rounded border border-dashed">No loyalty rules configured for this brand yet.</div>';
                    let html = '<div class="row g-2 mb-3">';
                    rulesList.forEach((r, idx) => {
                        if (editingIndex === idx) {
                            let optionsHtml = (r.reward_options || []).map((opt, oIdx) => `
                                <div class="input-group input-group-sm mb-2 inline-option-row shadow-sm" style="border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;">
                                    <input type="text" class="form-control inline-reward-option border-0" value="${opt}" placeholder="Reward Option ${oIdx+1}" style="background-color: #f8fafc; padding: 0.5rem 0.75rem;">
                                    <button class="btn remove-inline-option-btn shadow-none px-2" type="button" ${oIdx===0 && r.reward_options.length===1 ? 'disabled' : ''} style="background-color: #007c89; border: none; ${oIdx===0 && r.reward_options.length===1 ? 'opacity: 0.5;' : ''}"><i class="fa fa-times text-white"></i></button>
                                </div>
                            `).join('');
                            if (optionsHtml === '') {
                                optionsHtml = `
                                <div class="input-group input-group-sm mb-2 inline-option-row shadow-sm" style="border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;">
                                    <input type="text" class="form-control inline-reward-option border-0" value="" placeholder="Reward Option 1" style="background-color: #f8fafc; padding: 0.5rem 0.75rem;">
                                    <button class="btn remove-inline-option-btn shadow-none px-2" type="button" disabled style="background-color: #007c89; border: none; opacity: 0.5;"><i class="fa fa-times text-white"></i></button>
                                </div>
                                `;
                            }

                            html += `
                            <div class="col-12 mb-3 d-flex align-items-stretch">
                                <div class="card w-100 border-primary shadow rounded-3 bg-white overflow-hidden">
                                    <div class="card-body p-3">
                                        <div class="row g-2">
                                            <div class="col-12 col-md-5">
                                                <input type="number" class="form-control form-control-sm inline-threshold shadow-sm" value="${r.threshold}" placeholder="Target ₹ (e.g. 2000)" min="1" style="background-color: #f8fafc; border-radius: 8px; padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0;">
                                            </div>
                                            <div class="col-12 col-md-7">
                                                <div class="inline-options-container">
                                                    ${optionsHtml}
                                                </div>
                                            </div>
                                            <div class="col-12 d-flex justify-content-end mt-0" style="padding-top: 0;">
                                                <div class="col-12 col-md-7 d-flex justify-content-end">
                                                    <button type="button" class="btn btn-sm text-white shadow-sm add-inline-option-btn" style="background-color: #007c89; border-radius: 6px; font-weight: 500;"><i class="fa fa-plus me-1"></i>Add Another Option</button>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-12 d-flex justify-content-end gap-2 mt-2">
                                                <button type="button" class="btn btn-light btn-sm btn-cancel-inline border shadow-sm px-3" style="border-radius: 8px;">Cancel</button>
                                                <button type="button" class="btn btn-primary btn-sm btn-save-inline shadow-sm px-4 fw-bold" data-index="${idx}" style="border-radius: 8px; background-color: #00497a;"><i class="fa fa-check me-1"></i> Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            `;
                            return;
                        }

                        let descHtml = r.description ? `<div class="text-muted mt-1 text-truncate" style="font-size: 0.85rem; max-width: 100%;" title="${r.description}">${r.description}</div>` : '';
                        let opacityStyle = r.is_active === false ? 'opacity: 0.6; filter: grayscale(1);' : '';
                        html += `
                            <div class="col-12 col-md-6 mb-2 d-flex align-items-stretch">
                                <div class="premium-loyalty-card h-100 w-100" style="${opacityStyle}">
                                    <div class="premium-loyalty-img-wrapper" style="width:48px; height:48px; background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                                        <i class="fa fa-gift" style="font-size: 1.25rem;"></i>
                                    </div>
                                    <div class="premium-loyalty-content text-start">
                                        <div class="premium-target-text"><i class="fa fa-bullseye me-1 text-primary" style="font-size: 1rem; vertical-align: middle;"></i>₹${r.threshold}</div>
                                        <div class="premium-reward-text"><i class="fa fa-gem"></i> ${(r.reward_options || []).join(', ')}</div>
                                        ${descHtml}
                                    </div>
                                    <div class="premium-action-btns d-flex flex-column align-items-center gap-2">
                                        <div class="form-check form-switch mb-0" style="margin-left: 0.5rem;" title="Toggle Status">
                                            <input class="form-check-input rule-active-toggle" type="checkbox" style="cursor: pointer;" data-index="${idx}" ${r.is_active !== false ? 'checked' : ''}>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="premium-btn-icon btn-edit-rule shadow-sm" data-index="${idx}" title="Edit">
                                                <i class="fa fa-edit" style="font-size: 0.85rem;"></i>
                                            </button>
                                            <button type="button" class="premium-btn-icon delete btn-remove-rule shadow-sm" data-index="${idx}" title="Remove">
                                                <i class="fa fa-trash" style="font-size: 0.85rem;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    return html;
                };

                let saveLoyaltyRulesDynamically = () => {
                    loyaltyRules[brand] = rules;
                    let formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('slug', 'loyalty_rules');
                    
                    let rulesPayload = JSON.parse(JSON.stringify(loyaltyRules, function(key, val) {
                        return val; 
                    }));
                    
                    Object.keys(loyaltyRules).forEach(b => {
                        loyaltyRules[b].forEach((r) => {
                            if (r.imageFile) {
                                formData.append(`images[${b}_${r.threshold}]`, r.imageFile);
                            }
                        });
                    });
                    
                    formData.append('value', JSON.stringify(rulesPayload));
                    
                    $.ajax({
                        url: '{{ route('admin.settings.save') }}',
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function() {
                            let $toast = $('<div class="text-white fw-bold shadow-lg position-fixed" style="bottom: 25px; right: 25px; font-size: 0.95rem; z-index: 10600; background: #10b981; padding: 12px 22px; border-radius: 8px; display: none;"><i class="fa fa-check-circle me-2"></i>Loyalty rules saved!</div>');
                            $('body').append($toast);
                            $toast.fadeIn(300);
                            setTimeout(() => {
                                $toast.fadeOut(400, function() { $(this).remove(); });
                            }, 3000);
                        },
                        error: function() {
                            let $toast = $('<div class="text-white fw-bold shadow-lg position-fixed" style="bottom: 25px; right: 25px; font-size: 0.95rem; z-index: 10600; background: #ef4444; padding: 12px 22px; border-radius: 8px; display: none;"><i class="fa fa-times-circle me-2"></i>Error saving rules!</div>');
                            $('body').append($toast);
                            $toast.fadeIn(300);
                            setTimeout(() => {
                                $toast.fadeOut(400, function() { $(this).remove(); });
                            }, 3000);
                        }
                    });
                };

                let updateModalContent = () => {
                    $('#loyalty_rules_list_container').html(renderRulesList(rules));
                    if (editingIndex !== null) {
                        $('#swal_add_rule_form').slideUp(200);
                    } else {
                        $('#swal_add_rule_form').slideDown(200);
                    }
                };

                Swal.fire({
                    html: `
                        <style>
                            .swal2-icon { display: none !important; }
                            .premium-loyalty-card {
                                background: linear-gradient(145deg, #ffffff, #f8fafc);
                                border: 1px solid rgba(0, 73, 122, 0.1);
                                border-radius: 16px;
                                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
                                transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
                                position: relative;
                                overflow: hidden;
                                display: flex;
                                align-items: center;
                                padding: 14px 16px;
                            }
                            .premium-loyalty-card:hover {
                                transform: translateY(-4px);
                                box-shadow: 0 12px 24px rgba(0, 73, 122, 0.1);
                                border-color: rgba(0, 73, 122, 0.3);
                            }
                            .premium-loyalty-card::before {
                                content: '';
                                position: absolute;
                                top: 0; left: 0; width: 5px; height: 100%;
                                background: linear-gradient(180deg, var(--med-primary, #00497a), #00a8ff);
                                border-radius: 16px 0 0 16px;
                            }
                            .premium-loyalty-img-wrapper {
                                border-radius: 50%;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                flex-shrink: 0;
                            }
                            .premium-loyalty-content {
                                flex-grow: 1;
                                padding-left: 14px;
                                padding-right: 14px;
                                overflow: hidden;
                            }
                            .premium-target-text {
                                font-size: 1.15rem;
                                font-weight: 700;
                                color: var(--med-primary, #00497a);
                                line-height: 1.2;
                                margin-bottom: 2px;
                            }
                            .premium-reward-text {
                                font-size: 0.95rem;
                                font-weight: 600;
                                color: #1e293b;
                                display: flex;
                                align-items: center;
                            }
                            .premium-reward-text i {
                                color: #f59e0b;
                                margin-right: 6px;
                                font-size: 0.9rem;
                            }
                            .premium-action-btns {
                                flex-shrink: 0;
                                display: flex;
                                flex-direction: column;
                                gap: 6px;
                            }
                            .premium-btn-icon {
                                background: #ffffff;
                                border: 1px solid #e2e8f0;
                                border-radius: 8px;
                                width: 32px;
                                height: 32px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                color: #64748b;
                                cursor: pointer;
                                transition: all 0.2s;
                            }
                            .premium-btn-icon:hover {
                                background: #f1f5f9;
                                color: var(--med-primary, #00497a);
                                border-color: #cbd5e1;
                            }
                            .premium-btn-icon.delete:hover {
                                color: #ef4444;
                                background: #fef2f2;
                                border-color: #fecaca;
                            }
                            body.dark-only .premium-loyalty-card {
                                background: #1e293b;
                                border-color: #334155;
                            }
                            body.dark-only .premium-target-text { color: #38bdf8; }
                            body.dark-only .premium-reward-text { color: #f8fafc; }
                            body.dark-only .premium-btn-icon {
                                background: #0f172a;
                                border-color: #334155;
                                color: #94a3b8;
                            }
                            body.dark-only .premium-btn-icon:hover {
                                background: #1e293b;
                                color: #38bdf8;
                            }
                            body.dark-only .premium-btn-icon.delete:hover {
                                color: #f87171;
                            }
                            .premium-add-form {
                                background: #f8fafc;
                                border: 1px dashed #cbd5e1;
                                border-radius: 12px;
                                padding: 16px;
                                margin-top: 10px;
                            }
                            body.dark-only .premium-add-form {
                                background: #0f172a;
                                border-color: #334155;
                            }
                            #loyalty_rules_list_container::-webkit-scrollbar {
                                width: 6px;
                            }
                            #loyalty_rules_list_container::-webkit-scrollbar-track {
                                background: transparent; 
                            }
                            #loyalty_rules_list_container::-webkit-scrollbar-thumb {
                                background: #cbd5e1; 
                                border-radius: 10px;
                            }
                            body.dark-only #loyalty_rules_list_container::-webkit-scrollbar-thumb {
                                background: #475569;
                            }
                        </style>
                        <div class="text-start mb-4 px-2">
                            <h4 class="fw-bold text-dark mb-1" style="font-size: 1.6rem; letter-spacing: -0.5px;">Configure Loyalty</h4>
                            <p class="text-muted mb-0" style="font-size: 0.95rem;">Set order targets and rewards for <b class="text-primary">${brand}</b>.</p>
                        </div>
                        <div id="loyalty_rules_list_container" class="text-start mb-3 px-2 py-1" style="max-height: 320px; overflow-y: auto; overflow-x: hidden;">
                            ${renderRulesList(rules)}
                        </div>
                        <div id="swal_add_rule_form" class="premium-add-form mx-2 text-start">
                            <h6 class="fw-bold mb-3" style="color: #3b82f6; font-size: 1.05rem;" id="swal_rule_form_title">Add New Reward Level</h6>
                            <div class="row g-3">
                                <div class="col-12 col-md-5">
                                    <input type="number" id="swal_rule_threshold" class="form-control shadow-sm" placeholder="Target ₹ (e.g. 2000)" min="1" style="background-color: #f8fafc; border-radius: 8px; padding: 0.6rem 1rem; border: 1px solid #e2e8f0;">
                                </div>
                                <div class="col-12 col-md-7" id="swal_options_container">
                                    <div class="input-group mb-2 option-row shadow-sm" style="border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;">
                                        <input type="text" class="form-control swal_rule_reward_option border-0" placeholder="Reward Option 1" style="background-color: #f8fafc; padding: 0.6rem 1rem;">
                                        <button class="btn remove-option-btn shadow-none px-2" type="button" disabled style="background-color: #007c89; border: none; opacity: 0.5;"><i class="fa fa-times text-white"></i></button>
                                    </div>
                                </div>
                                <div class="col-12 d-flex justify-content-end mt-0" style="padding-top: 0;">
                                    <div class="col-12 col-md-7 d-flex justify-content-end">
                                        <button type="button" class="btn btn-sm text-white shadow-sm" id="swal_add_option_btn" style="background-color: #007c89; border-radius: 6px; font-weight: 500;"><i class="fa fa-plus me-1"></i>Add Another Option</button>
                                    </div>
                                </div>
                                <div class="col-12 col-md-12 d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-primary shadow-sm px-4" id="swal_add_rule_btn" style="border-radius: 8px; font-weight: 600; background-color: #00497a;"><i class="fa fa-plus me-1"></i> Add</button>
                                </div>
                            </div>
                        </div>
                    `,
                    width: '750px',
                    showConfirmButton: false,
                    showCancelButton: true,
                    cancelButtonText: 'Close',
                    buttonsStyling: true,
                    customClass: {
                        cancelButton: 'btn btn-light px-4 shadow-sm ms-2'
                    },
                    didOpen: () => {
                        $(document).off('click', '#swal_add_option_btn').on('click', '#swal_add_option_btn', function() {
                            let count = $('#swal_options_container .option-row').length + 1;
                            $('#swal_options_container').append(`
                                <div class="input-group mb-2 option-row shadow-sm" style="border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;">
                                    <input type="text" class="form-control swal_rule_reward_option border-0" placeholder="Reward Option ${count}" style="background-color: #f8fafc; padding: 0.6rem 1rem;">
                                    <button class="btn remove-option-btn shadow-none px-2" type="button" style="background-color: #007c89; border: none;"><i class="fa fa-times text-white"></i></button>
                                </div>
                            `);
                            $('#swal_options_container .remove-option-btn').prop('disabled', false).css('opacity', '1');
                        });
                        
                        $(document).off('click', '.remove-option-btn').on('click', '.remove-option-btn', function() {
                            $(this).closest('.option-row').remove();
                            let count = $('#swal_options_container .option-row').length;
                            if (count <= 1) {
                                $('#swal_options_container .remove-option-btn').prop('disabled', true).css('opacity', '0.5');
                            }
                            $('#swal_options_container .swal_rule_reward_option').each(function(index) {
                                $(this).attr('placeholder', 'Reward Option ' + (index + 1));
                            });
                        });
                        
                        // Inline edit add/remove options
                        $(document).off('click', '.add-inline-option-btn').on('click', '.add-inline-option-btn', function() {
                            let $container = $(this).closest('.row').find('.inline-options-container');
                            let count = $container.find('.inline-option-row').length + 1;
                            $container.append(`
                                <div class="input-group input-group-sm mb-2 inline-option-row shadow-sm" style="border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;">
                                    <input type="text" class="form-control inline-reward-option border-0" placeholder="Reward Option ${count}" style="background-color: #f8fafc; padding: 0.5rem 0.75rem;">
                                    <button class="btn remove-inline-option-btn shadow-none px-2" type="button" style="background-color: #007c89; border: none;"><i class="fa fa-times text-white"></i></button>
                                </div>
                            `);
                            $container.find('.remove-inline-option-btn').prop('disabled', false).css('opacity', '1');
                        });

                        $(document).off('click', '.remove-inline-option-btn').on('click', '.remove-inline-option-btn', function() {
                            let $container = $(this).closest('.inline-options-container');
                            $(this).closest('.inline-option-row').remove();
                            let count = $container.find('.inline-option-row').length;
                            if (count <= 1) {
                                $container.find('.remove-inline-option-btn').prop('disabled', true).css('opacity', '0.5');
                            }
                            $container.find('.inline-reward-option').each(function(index) {
                                $(this).attr('placeholder', 'Reward Option ' + (index + 1));
                            });
                        });

                        $(document).off('click', '#swal_add_rule_btn').on('click', '#swal_add_rule_btn', function() {
                            let threshold = parseFloat($('#swal_rule_threshold').val());
                            let description = '';
                            
                            let options = [];
                            $('.swal_rule_reward_option').each(function() {
                                let val = $(this).val().trim();
                                if(val !== '') options.push(val);
                            });
                            
                            if (isNaN(threshold) || threshold <= 0) {
                                Swal.showValidationMessage('Please enter a valid order target amount.');
                                return;
                            }
                            if (options.length === 0) {
                                Swal.showValidationMessage('Please enter at least one reward option.');
                                return;
                            }
                            
                            Swal.resetValidationMessage();
                            
                            rules.push({ threshold: threshold, reward_options: options, description: description, is_active: true });
                            
                            rules.sort((a, b) => a.threshold - b.threshold);
                            updateModalContent();
                            saveLoyaltyRulesDynamically();
                            
                            $('#swal_rule_threshold').val('');
                            $('#swal_options_container').html(`
                                <div class="input-group mb-2 option-row shadow-sm" style="border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;">
                                    <input type="text" class="form-control swal_rule_reward_option border-0" placeholder="Reward Option 1" style="background-color: #f8fafc; padding: 0.6rem 1rem;">
                                    <button class="btn remove-option-btn shadow-none px-2" type="button" disabled style="background-color: #007c89; border: none; opacity: 0.5;"><i class="fa fa-times text-white"></i></button>
                                </div>
                            `);
                            $('#swal_rule_description').val('');
                        });

                        $(document).off('click', '.btn-remove-rule').on('click', '.btn-remove-rule', function() {
                            let idx = $(this).data('index');
                            rules.splice(idx, 1);
                            updateModalContent();
                            saveLoyaltyRulesDynamically();
                        });

                        $(document).off('change', '.rule-active-toggle').on('change', '.rule-active-toggle', function() {
                            let idx = $(this).data('index');
                            rules[idx].is_active = $(this).is(':checked');
                            updateModalContent();
                            saveLoyaltyRulesDynamically();
                        });

                        $(document).off('click', '.btn-edit-rule').on('click', '.btn-edit-rule', function() {
                            editingIndex = $(this).data('index');
                            updateModalContent();
                        });
                        
                        $(document).off('click', '.btn-cancel-inline').on('click', '.btn-cancel-inline', function() {
                            editingIndex = null;
                            updateModalContent();
                        });
                        
                        $(document).off('click', '.btn-save-inline').on('click', '.btn-save-inline', function() {
                            let idx = $(this).data('index');
                            let $card = $(this).closest('.card-body');
                            
                            let threshold = parseFloat($card.find('.inline-threshold').val());
                            let options = [];
                            $card.find('.inline-reward-option').each(function() {
                                let val = $(this).val().trim();
                                if(val !== '') options.push(val);
                            });
                            let description = '';
                            
                            if (isNaN(threshold) || threshold <= 0 || options.length === 0) {
                                Swal.showValidationMessage('Valid target and at least one reward are required to update.');
                                return;
                            }
                            Swal.resetValidationMessage();
                            
                            rules[idx].threshold = threshold;
                            rules[idx].reward_options = options;
                            rules[idx].description = description;
                            
                            editingIndex = null;
                            rules.sort((a, b) => a.threshold - b.threshold);
                            updateModalContent();
                            saveLoyaltyRulesDynamically();
                        });
                    }
                });
            });

            // Initial render
            renderBrands();

            $('#add_brand_btn').on('click', function() {
                Swal.fire({
                    title: 'Add New Brand',
                    html: `
                        <div class="text-start mb-3">
                            <label class="form-label fw-bold small">Brand Name</label>
                            <input type="text" id="swal_brand_name" class="form-control" placeholder="e.g. ATOMEDS">
                        </div>
                        <div class="text-start mb-3">
                            <label class="form-label fw-bold small">Description</label>
                            <input type="text" id="swal_brand_desc" class="form-control" placeholder="e.g. Medicines">
                        </div>
                        <div id="swal_custom_fields_container" class="mt-3 p-3 border rounded bg-light text-start">
                            <div class="fw-bold mb-2 small text-muted">Select Visible Fields:</div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input swal-custom-field-chk" type="checkbox" value="product_code" id="chk_product_code" checked>
                                        <label class="form-check-label small" for="chk_product_code">Product Code</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input swal-custom-field-chk" type="checkbox" value="generic_name" id="chk_generic_name">
                                        <label class="form-check-label small" for="chk_generic_name">Generic Name</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input swal-custom-field-chk" type="checkbox" value="hsn_code" id="chk_hsn_code" checked>
                                        <label class="form-check-label small" for="chk_hsn_code">HSN Code</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input swal-custom-field-chk" type="checkbox" value="pack" id="chk_pack" checked>
                                        <label class="form-check-label small" for="chk_pack">Pack Size</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input swal-custom-field-chk" type="checkbox" value="strip_size" id="chk_strip_size">
                                        <label class="form-check-label small" for="chk_strip_size">Tablets / Strip</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input swal-custom-field-chk" type="checkbox" value="box_size" id="chk_box_size">
                                        <label class="form-check-label small" for="chk_box_size">Strips / Box</label>
                                    </div>
                                </div>
                                <div class="col-12 mt-2 pt-2 border-top">
                                    <div class="form-check">
                                        <input class="form-check-input swal-custom-field-chk" type="checkbox" value="variants" id="chk_variants">
                                        <label class="form-check-label small" for="chk_variants">Variants (Sides/Sizes)</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 pt-2 border-top text-muted small" style="font-size: 0.72rem; line-height: 1.35;">
                                <i class="fa fa-info-circle text-info me-1"></i> Commercial/pricing fields (MRP, PTR, PTS, Loyalty %) are common across all brands and are always displayed.
                            </div>
                        </div>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Add Brand',
                    didOpen: (modal) => {
                        $(modal).find('.swal2-icon').remove();
                    },
                    preConfirm: () => {
                        let name = document.getElementById('swal_brand_name').value.trim();
                        let desc = document.getElementById('swal_brand_desc').value.trim();
                        if (!name) {
                            Swal.showValidationMessage('Brand Name is required');
                            return false;
                        }
                        let customFields = [];
                        $('.swal-custom-field-chk:checked').each(function() {
                            customFields.push($(this).val());
                        });
                        return { name, description: desc, icon: 'fa-tag', layout_type: 'custom', custom_fields: customFields };
                    }
                }).then((result) => {
                    if (result.value) {
                        let brandData = result.value;
                        $.ajax({
                            url: '{{ route('admin.settings.brands.save') }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                name: brandData.name,
                                description: brandData.description,
                                icon: brandData.icon,
                                layout_type: brandData.layout_type,
                                custom_fields: brandData.custom_fields
                            },
                            success: function(res) {
                                brandsData.push(res.brand);
                                renderBrands();
                                showToast('success', 'Added', 'Brand added successfully');
                            },
                            error: function(xhr) {
                                let msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Could not add brand.';
                                showToast('error', 'Error', msg);
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.delete-brand-btn', function() {
                let index = $(this).data('index');
                let brandObj = brandsData[index];
                Swal.fire({
                    title: 'Remove Brand?',
                    text: `Are you sure you want to remove "${brandObj.name}"? This will not delete its products, but they will revert to General layout.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, remove it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('admin.settings.brands.delete') }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                id: brandObj.id
                            },
                            success: function() {
                                brandsData.splice(index, 1);
                                renderBrands();
                                showToast('success', 'Removed', 'Brand removed successfully');
                            },
                            error: function(xhr) {
                                showToast('error', 'Error', 'Could not remove brand.');
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.edit-brand-btn', function() {
                let index = $(this).data('index');
                let brandObj = brandsData[index];
                
                // Determine which fields should be checked based on layout_type & custom_fields (for backward compatibility)
                let customFieldsList = [];
                if (brandObj.layout_type === 'medical') {
                    customFieldsList = ['generic_name', 'hsn_code', 'strip_size', 'box_size'];
                } else if (brandObj.layout_type === 'ortho') {
                    customFieldsList = ['product_code', 'hsn_code', 'variants'];
                } else if (brandObj.layout_type === 'general') {
                    customFieldsList = ['product_code', 'hsn_code', 'pack'];
                } else if (brandObj.layout_type === 'custom') {
                    customFieldsList = brandObj.custom_fields || [];
                }
                
                Swal.fire({
                    title: 'Edit Brand Layout',
                    html: `
                        <div class="text-start mb-3">
                            <label class="form-label fw-bold small">Brand Name</label>
                            <input type="text" id="swal_brand_name" class="form-control" value="${brandObj.name}" placeholder="e.g. ATOMEDS">
                        </div>
                        <div class="text-start mb-3">
                            <label class="form-label fw-bold small">Description</label>
                            <input type="text" id="swal_brand_desc" class="form-control" value="${brandObj.description || ''}" placeholder="e.g. Medicines">
                        </div>
                        <div id="swal_custom_fields_container" class="mt-3 p-3 border rounded bg-light text-start">
                            <div class="fw-bold mb-2 small text-muted">Select Visible Fields:</div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input swal-custom-field-chk" type="checkbox" value="product_code" id="chk_product_code" ${customFieldsList.includes('product_code') ? 'checked' : ''}>
                                        <label class="form-check-label small" for="chk_product_code">Product Code</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input swal-custom-field-chk" type="checkbox" value="generic_name" id="chk_generic_name" ${customFieldsList.includes('generic_name') ? 'checked' : ''}>
                                        <label class="form-check-label small" for="chk_generic_name">Generic Name</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input swal-custom-field-chk" type="checkbox" value="hsn_code" id="chk_hsn_code" ${customFieldsList.includes('hsn_code') ? 'checked' : ''}>
                                        <label class="form-check-label small" for="chk_hsn_code">HSN Code</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input swal-custom-field-chk" type="checkbox" value="pack" id="chk_pack" ${customFieldsList.includes('pack') ? 'checked' : ''}>
                                        <label class="form-check-label small" for="chk_pack">Pack Size</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input swal-custom-field-chk" type="checkbox" value="strip_size" id="chk_strip_size" ${customFieldsList.includes('strip_size') ? 'checked' : ''}>
                                        <label class="form-check-label small" for="chk_strip_size">Tablets / Strip</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input swal-custom-field-chk" type="checkbox" value="box_size" id="chk_box_size" ${customFieldsList.includes('box_size') ? 'checked' : ''}>
                                        <label class="form-check-label small" for="chk_box_size">Strips / Box</label>
                                    </div>
                                </div>
                                <div class="col-12 mt-2 pt-2 border-top">
                                    <div class="form-check">
                                        <input class="form-check-input swal-custom-field-chk" type="checkbox" value="variants" id="chk_variants" ${customFieldsList.includes('variants') ? 'checked' : ''}>
                                        <label class="form-check-label small" for="chk_variants">Variants (Sides/Sizes)</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 pt-2 border-top text-muted small" style="font-size: 0.72rem; line-height: 1.35;">
                                <i class="fa fa-info-circle text-info me-1"></i> Commercial/pricing fields (MRP, PTR, PTS, Loyalty %) are common across all brands and are always displayed.
                            </div>
                        </div>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Update Brand',
                    didOpen: (modal) => {
                        $(modal).find('.swal2-icon').remove();
                    },
                    preConfirm: () => {
                        let name = document.getElementById('swal_brand_name').value.trim();
                        let desc = document.getElementById('swal_brand_desc').value.trim();
                        if (!name) {
                            Swal.showValidationMessage('Brand Name is required');
                            return false;
                        }
                        let customFields = [];
                        $('.swal-custom-field-chk:checked').each(function() {
                            customFields.push($(this).val());
                        });
                        return { id: brandObj.id, name, description: desc, icon: brandObj.icon || 'fa-tag', layout_type: 'custom', custom_fields: customFields };
                    }
                }).then((result) => {
                    if (result.value) {
                        let brandData = result.value;
                        $.ajax({
                            url: '{{ route('admin.settings.brands.save') }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                id: brandData.id,
                                name: brandData.name,
                                description: brandData.description,
                                icon: brandData.icon,
                                layout_type: brandData.layout_type,
                                custom_fields: brandData.custom_fields
                            },
                            success: function(res) {
                                brandsData[index] = res.brand;
                                renderBrands();
                                showToast('success', 'Updated', 'Brand updated successfully');
                            },
                            error: function(xhr) {
                                let msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Could not update brand.';
                                showToast('error', 'Error', msg);
                            }
                        });
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
                    success: function() {
                        showToast('success', 'Saved', 'Product returnability updated.');
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