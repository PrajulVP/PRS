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
                            
                            <!-- Product Brands Master (Priority #1) -->
                            <div class="accordion-item border mb-3 rounded-4 overflow-hidden shadow-sm">
                                <h2 class="accordion-header" id="headingBrands">
                                    <button class="accordion-button collapsed fw-bold py-3 px-4 bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBrands" aria-expanded="false" aria-controls="collapseBrands" style="font-size: 1.1rem; color: #1e293b;">
                                        <div class="d-flex align-items-center w-100 me-3">
                                            <i class="fa fa-tags me-3 text-primary fs-4"></i>
                                            <div>
                                                <div>Product Brands Master</div>
                                                <small class="text-muted fw-normal d-block mt-1" style="font-size: 0.85rem;">{{ count(array_filter(explode(',', trim($product_brands)))) }} Active Brands Configured</small>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapseBrands" class="accordion-collapse collapse" aria-labelledby="headingBrands" data-bs-parent="#settingsAccordion">
                                    <div class="accordion-body p-4 bg-light">
                                         <div class="d-flex align-items-center justify-content-between mb-3">
                                             <h6 class="text-dark mb-0 fw-bold">Active Brands List</h6>
                                             <button type="button" class="btn btn-dark btn-sm rounded-pill px-3 shadow-sm" id="add_brand_btn">
                                                 <i class="fa fa-plus me-1"></i>Add New Brand
                                             </button>
                                         </div>
                                         <p class="text-muted small mb-4">Manage the list of active product brands. Each brand can have its own description, icon, and custom layout behavior (Medical, Orthopedic, or General).</p>
                                         
                                         <form class="setting-form" id="brands_form">
                                             @csrf
                                             <input type="hidden" name="slug" value="product_brands">
                                             <input type="hidden" name="value" id="brands_final_value" value="{{ $product_brands }}">
                                             
                                             <div id="brands_tag_container" class="d-flex flex-wrap align-items-stretch gap-3 p-3 bg-white rounded-3 border shadow-inner" style="min-height: 80px;">
                                                 <!-- Tags filled by JS -->
                                             </div>
                                         </form>
                                    </div>
                                </div>
                            </div>

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
                                <div id="collapseFieldStaff" class="accordion-collapse collapse" aria-labelledby="headingFieldStaff" data-bs-parent="#settingsAccordion">
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
                                                <small class="text-muted fw-normal d-block mt-1" style="font-size: 0.85rem;">Configure default annual quotas</small>
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
                    html = '<div class="text-muted small w-100 text-center py-2">No brands added yet.</div>';
                } else {
                    brandsData.forEach((brandObj, index) => {
                        let brand = brandObj.name;
                        let isReturnable = returnableBrands.includes(brand);
                        let isLoyalty = loyaltyBrands.includes(brand);
                        let fieldsList = getBrandFieldsList(brandObj);

                        html += `
                            <div class="brand-tag-wrapper d-flex flex-column rounded p-3" style="min-width: 260px; max-width: 320px; flex: 1 1 auto; height: auto;">
                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                    <span class="brand-name text-truncate me-2 fw-bold" title="${brand}">
                                        <i class="fa ${brandObj.icon || 'fa-tag'} me-1 text-primary"></i>${brand}
                                    </span>
                                    <div class="d-flex gap-1 flex-shrink-0">
                                        <button type="button" class="btn btn-outline-info p-0 d-flex align-items-center justify-content-center edit-brand-btn" data-index="${index}" style="width: 28px; height: 28px;" title="Edit"><i class="fa fa-edit small"></i></button>
                                        <button type="button" class="btn btn-outline-danger p-0 d-flex align-items-center justify-content-center delete-brand-btn" data-index="${index}" style="width: 28px; height: 28px;" title="Delete"><i class="fa fa-trash small"></i></button>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <span class="small text-muted d-block">Description:</span>
                                    <span class="small fw-semibold text-dark">${brandObj.description || 'No description'}</span>
                                </div>
                                <div class="mb-2 border-top pt-2">
                                    <span class="small text-muted d-block mb-1">Visible Fields:</span>
                                    <div class="d-flex flex-wrap gap-1">
                                        ${fieldsList.length === 0 
                                            ? '<span class="text-muted small">None (Common only)</span>' 
                                            : fieldsList.map(f => `<span class="badge bg-light text-dark border px-2 py-1 small" style="font-size: 0.68rem; font-weight: 500;">${f}</span>`).join('')
                                        }
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-2 border-top pt-2">
                                    <span class="small text-muted">Returnable</span>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input returnable-toggle" type="checkbox" data-brand="${brand}" ${isReturnable ? 'checked' : ''}>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-2 border-top pt-2">
                                    <span class="small text-muted">Loyalty Program</span>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input loyalty-toggle" type="checkbox" data-brand="${brand}" ${isLoyalty ? 'checked' : ''}>
                                    </div>
                                </div>
                                <div class="border-top pt-3 mt-auto text-center d-flex flex-column gap-2">
                                    <button type="button" class="btn btn-sm btn-primary w-100 config-loyalty-btn shadow-sm ${isLoyalty ? '' : 'd-none'}" data-brand="${brand}">
                                        <i class="fa fa-gift me-1"></i>Configure Loyalty
                                    </button>
                                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 manage-products-btn" data-brand="${brand}">
                                        <i class="fa fa-list-ul me-1"></i>Manage Products
                                    </button>
                                </div>
                            </div>
                        `;
                    });
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

            $(document).on('change', '.loyalty-toggle', function() {
                let brand = $(this).data('brand');
                let isChecked = $(this).is(':checked');
                let $configBtn = $(`.config-loyalty-btn[data-brand="${brand}"]`);
                
                if (isChecked) {
                    if (!loyaltyBrands.includes(brand)) loyaltyBrands.push(brand);
                    $configBtn.removeClass('d-none');
                } else {
                    loyaltyBrands = loyaltyBrands.filter(b => b !== brand);
                    $configBtn.addClass('d-none');
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
                            html += `
                            <div class="col-12 col-sm-6">
                                <div class="card h-100 border-primary shadow rounded-3 bg-white overflow-hidden">
                                    <div class="card-body p-2 d-flex flex-column gap-2">
                                        <input type="number" class="form-control form-control-sm inline-threshold" value="${r.threshold}" placeholder="Target ₹" min="1">
                                        <input type="text" class="form-control form-control-sm inline-reward" value="${r.reward}" placeholder="Reward">
                                        <input type="text" class="form-control form-control-sm inline-description" value="${r.description || ''}" placeholder="Description">
                                        <div class="d-flex gap-1">
                                            <input type="file" class="form-control form-control-sm inline-image" accept="image/*" style="width: 100px; flex-grow:1;" title="Leave empty to keep image">
                                            <button type="button" class="btn btn-sm btn-success btn-save-inline shadow-sm px-2" data-index="${idx}" title="Save"><i class="fa fa-check"></i></button>
                                            <button type="button" class="btn btn-sm btn-light btn-cancel-inline border shadow-sm px-2" title="Cancel"><i class="fa fa-times"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            `;
                            return;
                        }

                        let imgHtml = r.image_url ? `<img src="${r.image_url}" class="rounded shadow-sm" style="width: 40px; height: 40px; object-fit: cover;">` : (r.imageFile ? `<img src="${URL.createObjectURL(r.imageFile)}" class="rounded shadow-sm" style="width: 40px; height: 40px; object-fit: cover;">` : `<div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white" style="width:40px; height:40px;"><i class="fa fa-gift"></i></div>`);
                        let descHtml = r.description ? `<div class="text-muted small mt-1 text-truncate" style="max-width: 100%;" title="${r.description}">${r.description}</div>` : '';
                        html += `
                            <div class="col-12 col-sm-6">
                                <div class="card h-100 border shadow-sm rounded-3 bg-white overflow-hidden">
                                    <div class="card-body p-3 d-flex gap-3 align-items-center position-relative">
                                        <!-- Action Buttons -->
                                        <div class="position-absolute top-50 translate-middle-y end-0 pe-2 d-flex flex-column gap-2 z-1">
                                            <button type="button" class="btn btn-sm btn-light border shadow-sm text-primary rounded-3 btn-edit-rule px-2 py-1" data-index="${idx}" title="Edit">
                                                <i class="fa fa-edit" style="font-size: 0.85rem;"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border shadow-sm text-danger rounded-3 btn-remove-rule px-2 py-1" data-index="${idx}" title="Remove">
                                                <i class="fa fa-trash" style="font-size: 0.85rem;"></i>
                                            </button>
                                        </div>
                                        
                                        <!-- Image -->
                                        <div class="flex-shrink-0">
                                            ${imgHtml}
                                        </div>
                                        
                                        <!-- Content -->
                                        <div class="flex-grow-1 overflow-hidden pe-5">
                                            <div class="text-primary fw-bold mb-1" style="font-size: 0.95rem;"><i class="fa fa-bullseye me-1"></i>Target: ₹${r.threshold}</div>
                                            <div class="text-success fw-bold small"><i class="fa fa-gift me-1"></i>${r.reward}</div>
                                            ${descHtml}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    return html;
                };

                let updateModalContent = () => {
                    $('#loyalty_rules_list_container').html(renderRulesList(rules));
                };

                Swal.fire({
                    html: `
                        <style>.swal2-icon { display: none !important; }</style>
                        <div class="text-start mb-4">
                            <h4 class="fw-bold text-dark mb-1">Configure Loyalty</h4>
                            <p class="text-muted small mb-0">Set order targets and rewards for <b>${brand}</b>.</p>
                        </div>
                        <div id="loyalty_rules_list_container" class="text-start mb-3" style="max-height: 280px; overflow-y: auto; overflow-x: hidden;">
                            ${renderRulesList(rules)}
                        </div>
                        <div class="p-3 border rounded-3 bg-white shadow-sm">
                            <h6 class="fw-bold small mb-2 text-start text-primary" id="swal_rule_form_title">Add New Reward Level</h6>
                            <div class="row g-2">
                                <div class="col-12 col-md-4">
                                    <input type="number" id="swal_rule_threshold" class="form-control form-control-sm" placeholder="Target (e.g. 2000)" min="1">
                                </div>
                                <div class="col-12 col-md-8">
                                    <input type="text" id="swal_rule_reward" class="form-control form-control-sm" placeholder="Reward (e.g. 1 Gold Coin)">
                                </div>
                                <div class="col-12 col-md-12">
                                    <input type="text" id="swal_rule_description" class="form-control form-control-sm" placeholder="Description (Optional)">
                                </div>
                                <div class="col-12 col-md-12 d-flex gap-2">
                                    <input type="file" id="swal_rule_image" class="form-control form-control-sm" accept="image/*">
                                    <button type="button" class="btn btn-sm btn-primary" id="swal_add_rule_btn" style="min-width:40px;"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    `,
                    width: '600px',
                    showCancelButton: true,
                    confirmButtonText: 'Save Rules',
                    cancelButtonText: 'Cancel',
                    buttonsStyling: true,
                    customClass: {
                        confirmButton: 'btn btn-primary px-4 shadow-sm',
                        cancelButton: 'btn btn-light px-4 shadow-sm ms-2'
                    },
                    didOpen: () => {
                        $(document).off('click', '#swal_add_rule_btn').on('click', '#swal_add_rule_btn', function() {
                            let threshold = parseFloat($('#swal_rule_threshold').val());
                            let reward = $('#swal_rule_reward').val().trim();
                            let description = $('#swal_rule_description').val().trim();
                            let imageFile = document.getElementById('swal_rule_image').files[0];
                            
                            if (isNaN(threshold) || threshold <= 0) {
                                Swal.showValidationMessage('Please enter a valid order target amount.');
                                return;
                            }
                            if (reward === '') {
                                Swal.showValidationMessage('Please enter a reward gift name.');
                                return;
                            }
                            
                            Swal.resetValidationMessage();
                            
                            rules.push({ threshold: threshold, reward: reward, description: description, imageFile: imageFile });
                            
                            rules.sort((a, b) => a.threshold - b.threshold);
                            updateModalContent();
                            
                            $('#swal_rule_threshold').val('');
                            $('#swal_rule_reward').val('');
                            $('#swal_rule_description').val('');
                            $('#swal_rule_image').val('');
                        });

                        $(document).off('click', '.btn-remove-rule').on('click', '.btn-remove-rule', function() {
                            let idx = $(this).data('index');
                            rules.splice(idx, 1);
                            if (editingIndex === idx) {
                                editingIndex = null;
                            }
                            updateModalContent();
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
                            let reward = $card.find('.inline-reward').val().trim();
                            let description = $card.find('.inline-description').val().trim();
                            let imageFile = $card.find('.inline-image')[0].files[0];
                            
                            if (isNaN(threshold) || threshold <= 0 || reward === '') {
                                Swal.showValidationMessage('Valid target and reward are required to update.');
                                return;
                            }
                            Swal.resetValidationMessage();
                            
                            rules[idx].threshold = threshold;
                            rules[idx].reward = reward;
                            rules[idx].description = description;
                            if (imageFile) {
                                rules[idx].imageFile = imageFile;
                                rules[idx].image_url = null;
                                rules[idx].image_path = null;
                            }
                            
                            editingIndex = null;
                            rules.sort((a, b) => a.threshold - b.threshold);
                            updateModalContent();
                        });
                    },
                    preConfirm: () => {
                        let threshold = parseFloat($('#swal_rule_threshold').val());
                        let reward = $('#swal_rule_reward').val() ? $('#swal_rule_reward').val().trim() : '';
                        let description = $('#swal_rule_description').val() ? $('#swal_rule_description').val().trim() : '';
                        let imageFile = document.getElementById('swal_rule_image') ? document.getElementById('swal_rule_image').files[0] : null;
                        
                        if (!isNaN(threshold) && threshold > 0 && reward !== '') {
                            rules.push({ threshold: threshold, reward: reward, description: description, imageFile: imageFile });
                            rules.sort((a, b) => a.threshold - b.threshold);
                        }
                        
                        return rules;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        loyaltyRules[brand] = result.value;
                        
                        // We do NOT stringify loyaltyRules back to a hidden input containing File objects,
                        // we just trigger the save logic via FormData.
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
                                showToast('success', 'Saved', 'Loyalty rules updated for ' + brand);
                            },
                            error: function() {
                                showToast('error', 'Error', 'Could not save loyalty rules.');
                            }
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

            // Leave Types Management Logic
            let leaveTypesData = @json($leaveTypes);

            function renderLeaveTypes() {
                let html = '';
                if (leaveTypesData.length === 0) {
                    html = '<div class="text-muted small w-100 text-center py-2">No leave types added yet.</div>';
                } else {
                    leaveTypesData.forEach((leaveType, index) => {
                        html += `
                            <div class="brand-tag-wrapper d-inline-flex flex-column rounded p-3" style="min-width: 220px; max-width: 280px; border-color: var(--med-info) !important;">
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

            // Allocate logic has been moved to backend automatically on save

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