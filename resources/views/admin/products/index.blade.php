@extends('layouts.admin')

<style>
    /* Flex wrapper for actions */
    .action-buttons {
        display: inline-flex !important;
        align-items: center;
        gap: 4px;
        flex-wrap: nowrap;
    }

    /* Make every child inline-flex (buttons + forms) */
    .action-buttons>* {
        display: inline-flex !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Normalize button sizes */
    .action-buttons .btn {
        padding: 6px 12px !important;
        font-size: 0.75rem !important;
        line-height: 1 !important;
    }

    /* Fix DataTable Length Select Arrow Issue */
    .dataTables_length select.form-select {
        padding-right: 2.5rem !important;
        /* Ensure space for arrow */
        background-position: right 0.75rem center;
        /* correct arrow position */
        width: auto !important;
        display: inline-block !important;
    }

    /* Premium Detail UI Styles */
    .product-detail-premium {
        font-family: 'Inter', sans-serif;
    }
    
    .detail-card-panel {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 16px;
        transition: all 0.2s ease;
    }
    
    body.dark-only .detail-card-panel {
        background-color: rgba(255, 255, 255, 0.04) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }

    .detail-badge-pill {
        font-size: 0.72rem;
        padding: 6px 14px;
        background: #f1f5f9;
        color: #475569;
        border-radius: 100px;
        border: 1px solid #e2e8f0;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
    }
    
    body.dark-only .detail-badge-pill {
        background: rgba(255, 255, 255, 0.08) !important;
        color: #cbd5e1 !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
    }

    .detail-meta-item {
        display: inline-flex;
        align-items: center;
        font-size: 0.8rem;
        font-weight: 600;
        color: #64748b;
    }
    
    .detail-meta-item .meta-icon {
        width: 26px;
        height: 26px;
        border-radius: 6px;
        background: rgba(var(--bs-primary-rgb), 0.1);
        color: var(--bs-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 8px;
    }
    
    .detail-meta-item .meta-val {
        color: #0f172a;
        margin-left: 4px;
        font-weight: 700;
    }
    
    body.dark-only .detail-meta-item { color: #94a3b8 !important; }
    body.dark-only .detail-meta-item .meta-val { color: #f1f5f9 !important; }

    .detail-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .detail-text-title {
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.65rem;
        letter-spacing: 0.5px;
    }
    
    body.dark-only .detail-text-title {
        color: #94a3b8 !important;
    }
    
    .detail-text-value {
        color: #0f172a;
        font-weight: 800;
    }
    
    body.dark-only .detail-text-value {
        color: #f1f5f9 !important;
    }
    
    .detail-section-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: #1e293b;
        border-left: 4px solid var(--bs-primary);
        padding-left: 10px;
    }
    
    body.dark-only .detail-section-title {
        color: #f1f5f9 !important;
    }

    .detail-generic-formulation {
        background: #f8fafc;
        border: 1px dashed #e2e8f0;
        border-radius: 10px;
    }
    
    body.dark-only .detail-generic-formulation {
        background: rgba(255, 255, 255, 0.03) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }

    .detail-variant-pill {
        font-size: 0.75rem;
        border-radius: 6px;
        background: #ffffff;
        color: #334155;
        border: 1px solid #e2e8f0;
        font-weight: 700;
    }

    body.dark-only .detail-variant-pill {
        background: rgba(255, 255, 255, 0.08) !important;
        color: #f1f5f9 !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
    }

    .icon-box-blue { background: #e0f2fe; color: #0284c7; }
    .icon-box-green { background: #dcfce7; color: #16a34a; }
    .icon-box-amber { background: #fef3c7; color: #d97706; }
    .icon-box-indigo { background: #e0e7ff; color: #4f46e5; }
    
    body.dark-only .icon-box-blue { background: rgba(2, 132, 199, 0.15) !important; color: #38bdf8 !important; }
    body.dark-only .icon-box-green { background: rgba(22, 163, 74, 0.15) !important; color: #4ade80 !important; }
    body.dark-only .icon-box-amber { background: rgba(217, 119, 6, 0.15) !important; color: #fbbf24 !important; }
    body.dark-only .icon-box-indigo { background: rgba(79, 70, 229, 0.15) !important; color: #818cf8 !important; }
    
    /* Support modal header styling */
    body.dark-only #showProductModal .modal-content {
        background-color: #1d2a3a !important;
        color: #f1f5f9 !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    
    body.dark-only #showProductModal .modal-header {
        border-bottom-color: rgba(255, 255, 255, 0.1) !important;
    }
    
    body.dark-only #showProductModal .modal-footer {
        border-top-color: rgba(255, 255, 255, 0.1) !important;
    }
    
    .bg-soft-primary {
        background-color: rgba(var(--bs-primary-rgb), 0.1);
    }
    .font-outfit { font-family: 'Outfit', sans-serif; }

    #createProductTypeTabs .nav-link, #editProductTypeTabs .nav-link {
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.85rem;
        transition: all 0.2s ease-in-out;
    }
    #createProductTypeTabs .nav-link.active, #editProductTypeTabs .nav-link.active {
        background: var(--bs-primary);
        color: #ffffff;
        border-color: var(--bs-primary);
        box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.2);
    }
</style>

@section('page-body')
    <div class="container-fluid">
        <!-- Stats Summary Bar -->
        <div class="row g-3 pt-4 mb-4">
            {{-- Total Products Card --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 mb-0 border-0 shadow-sm" style="border-radius: 15px; overflow: hidden; background: linear-gradient(135deg, #1e3a5f, #2e6da4);">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 p-3 rounded-3" style="background: rgba(255,255,255,0.15);">
                                <i class="fa fa-cubes text-white" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1 text-uppercase fw-800" style="font-size: 0.7rem; letter-spacing: 0.5px; color: #ffffff !important; opacity: 0.8;">Global Inventory</h6>
                                <div class="d-flex align-items-baseline">
                                    <h4 class="mb-0 fw-800" style="color: #ffffff !important;">{{ number_format($totalProducts) }}</h4>
                                    <span class="ms-2 small" style="color: #ffffff !important; opacity: 0.8;">Total Products</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @foreach($brandStats as $stat)
                <div class="col-xl-3 col-md-6">
                    <div class="card h-100 mb-0 border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                        @php
                            $colors = ['#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#0ea5e9'];
                            $color = $colors[$loop->index % count($colors)];
                        @endphp
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 p-3 rounded-3" style="background-color: {{ $color }}15;">
                                    <i class="fa fa-tag" style="color: {{ $color }}; font-size: 1.2rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1 text-uppercase fw-800" style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ $stat['brand'] }}</h6>
                                    <div class="d-flex align-items-baseline">
                                        <h4 class="mb-0 fw-800" style="color: var(--med-primary);">{{ number_format($stat['count']) }}</h4>
                                        <span class="ms-2 text-muted small">Products</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="height: 4px; background-color: {{ $color }}; width: 100%;"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-800 text-uppercase" style="font-size: 0.9rem; letter-spacing: 1px; color: var(--med-primary);">
                            <i class="fa fa-th-list me-2"></i>Product Inventory
                        </h5>
                        <div class="d-flex gap-2">
                            @if(Auth::user()->hasAnyRole(['admin', 'superadmin']) || Auth::user()->hasPermissionToCategory('products', 'add'))

                            <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                                data-bs-target="#importProductModal">
                                <i class="fa fa-upload me-1"></i>Import Products
                            </button>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#createProductModal">
                                <i class="fa fa-plus me-1"></i>Add Product
                            </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="table-responsive">
                            <table class="display table table-striped table-hover" id="products-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Generic Name</th>
                                        <th>Pack</th>
                                        <th>MRP</th>
                                        <th>PTR</th>
                                        <th>PTS</th>
                                        <th>Loyalty %</th>
                                        <th>Brand</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Product Modal --}}
    <div class="modal fade" id="createProductModal" tabindex="-1" aria-labelledby="createProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createProductModalLabel">Create Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <!-- Product Type Pill Navigation -->
                        <div class="mb-4 text-center">
                            <label class="form-label fw-bold text-muted mb-2 text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Select Product Type</label>
                            <div class="nav nav-pills justify-content-center gap-2" id="createProductTypeTabs" role="tablist">
                                <button class="nav-link active fw-bold px-4 py-2 border shadow-sm" id="tab-create-medical" data-bs-toggle="pill" type="button" data-type="medical">
                                    <i class="fa fa-flask me-2"></i>Medical Products
                                </button>
                                <button class="nav-link fw-bold px-4 py-2 border shadow-sm" id="tab-create-ortho" data-bs-toggle="pill" type="button" data-type="ortho">
                                    <i class="fa fa-universal-access me-2"></i>Orthopedic & Supports
                                </button>
                                <button class="nav-link fw-bold px-4 py-2 border shadow-sm" id="tab-create-general" data-bs-toggle="pill" type="button" data-type="general">
                                    <i class="fa fa-leaf me-2"></i>Herbal & OTC
                                </button>
                            </div>
                        </div>

                        <!-- General Info -->
                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">General Information</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3" id="create_product_code_container">
                                <label for="create_product_code" class="form-label fw-medium">Product Code</label>
                                <input type="text" name="product_code" id="create_product_code" class="form-control">
                            </div>
                            <div class="col-md-3" id="create_product_name_container">
                                <label for="create_product_name" class="form-label fw-medium">Product Name</label>
                                <input type="text" name="product_name" id="create_product_name" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-3" id="create_generic_name_container">
                                <label for="create_generic_name" class="form-label fw-medium">Generic Name</label>
                                <input type="text" name="generic_name" id="create_generic_name" class="form-control">
                            </div>
                            <div class="col-md-3" id="create_hsn_code_container">
                                <label for="create_hsn_code" class="form-label fw-medium">HSN Code</label>
                                <input type="text" name="hsn_code" id="create_hsn_code" class="form-control">
                            </div>
                            <div class="col-md-3 d-none" id="create_brand_container">
                                <label for="create_brand" class="form-label fw-medium">Brand</label>
                                <select name="brand" id="create_brand" class="form-select">
                                    <option value="">Auto-Identify</option>
                                    @foreach($availableBrands as $brand)
                                        <option value="{{ $brand }}">{{ $brand }}</option>
                                    @endforeach
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <!-- Left Column: Stock & Packaging -->
                            <div class="col-md-6 border-end" id="create_packaging_section">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Stock & Packaging</h6>
                                <div class="row g-3">
                                    <div class="col-md-12" id="create_pack_container">
                                        <label for="create_pack" class="form-label fw-medium">Pack</label>
                                        <input type="text" name="pack" id="create_pack" class="form-control"
                                            placeholder="e.g. 10x10, 30ml">
                                    </div>
                                    <div class="col-md-6" id="create_strip_size_container">
                                        <label for="create_strip_size" class="form-label fw-medium">Tablet / Strip</label>
                                        <input type="text" name="strip_size" id="create_strip_size" class="form-control"
                                            placeholder="Tablets per strips">
                                    </div>
                                    <div class="col-md-6" id="create_box_size_container">
                                        <label for="create_box_size" class="form-label fw-medium">Strip / Box</label>
                                        <input type="number" name="box_size" id="create_box_size" class="form-control"
                                            placeholder="Strips per box">
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Pricing -->
                            <div class="col-md-6" id="create_pricing_section">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Pricing Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="create_mrp" class="form-label fw-medium">MRP</label>
                                        <input type="number" step="0.01" name="mrp" id="create_mrp" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="create_ptr" class="form-label fw-medium">PTR</label>
                                        <input type="number" step="0.01" name="ptr" id="create_ptr" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="create_pts" class="form-label fw-medium">PTS</label>
                                        <input type="number" step="0.01" name="pts" id="create_pts" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="create_loyalty_point_percentage" class="form-label fw-medium">Loyalty %</label>
                                        <input type="number" step="0.01" name="loyalty_point_percentage"
                                            id="create_loyalty_point_percentage" class="form-control" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Variant Configuration -->
                        <div id="create_variant_section" class="d-none">
                            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Variant Configuration (Optional)</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium small">Sides (comma separated)</label>
                                    <input type="hidden" name="variant_name_1" value="Side">
                                    <input type="text" name="variant_values_1" id="create_variant_values_1" class="form-control" placeholder="e.g. LEFT, RIGHT">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium small">Sizes (comma separated)</label>
                                    <input type="hidden" name="variant_name_2" value="Size">
                                    <input type="text" name="variant_values_2" id="create_variant_values_2" class="form-control" placeholder="e.g. S, M, L, XL">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Product Modal --}}
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editProductForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <!-- Product Type Pill Navigation -->
                        <div class="mb-4 text-center">
                            <label class="form-label fw-bold text-muted mb-2 text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Product Type</label>
                            <div class="nav nav-pills justify-content-center gap-2" id="editProductTypeTabs" role="tablist">
                                <button class="nav-link active fw-bold px-4 py-2 border shadow-sm" id="tab-edit-medical" data-bs-toggle="pill" type="button" data-type="medical">
                                    <i class="fa fa-flask me-2"></i>Medical Products
                                </button>
                                <button class="nav-link fw-bold px-4 py-2 border shadow-sm" id="tab-edit-ortho" data-bs-toggle="pill" type="button" data-type="ortho">
                                    <i class="fa fa-universal-access me-2"></i>Orthopedic & Supports
                                </button>
                                <button class="nav-link fw-bold px-4 py-2 border shadow-sm" id="tab-edit-general" data-bs-toggle="pill" type="button" data-type="general">
                                    <i class="fa fa-leaf me-2"></i>Herbal & OTC
                                </button>
                            </div>
                        </div>

                        <!-- General Info -->
                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">General Information</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3" id="edit_product_code_container">
                                <label for="edit_product_code" class="form-label fw-medium">Product Code</label>
                                <input type="text" name="product_code" id="edit_product_code" class="form-control">
                            </div>
                            <div class="col-md-3" id="edit_product_name_container">
                                <label for="edit_product_name" class="form-label fw-medium">Product Name</label>
                                <input type="text" name="product_name" id="edit_product_name" class="form-control" required>
                            </div>
                            <div class="col-md-3" id="edit_generic_name_container">
                                <label for="edit_generic_name" class="form-label fw-medium">Generic Name</label>
                                <input type="text" name="generic_name" id="edit_generic_name" class="form-control">
                            </div>
                            <div class="col-md-3" id="edit_hsn_code_container">
                                <label for="edit_hsn_code" class="form-label fw-medium">HSN Code</label>
                                <input type="text" name="hsn_code" id="edit_hsn_code" class="form-control">
                            </div>
                            <div class="col-md-3 d-none" id="edit_brand_container">
                                <label for="edit_brand" class="form-label fw-medium">Brand</label>
                                <select name="brand" id="edit_brand" class="form-select">
                                    @foreach($availableBrands as $brand)
                                        <option value="{{ $brand }}">{{ $brand }}</option>
                                    @endforeach
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <!-- Left Column: Stock & Packaging -->
                            <div class="col-md-6 border-end" id="edit_packaging_section">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Stock & Packaging</h6>
                                <div class="row g-3">
                                    <div class="col-md-12" id="edit_pack_container">
                                        <label for="edit_pack" class="form-label fw-medium">Pack</label>
                                        <input type="text" name="pack" id="edit_pack" class="form-control"
                                            placeholder="e.g. 10x10, 30ml">
                                    </div>
                                    <div class="col-md-6" id="edit_strip_size_container">
                                        <label for="edit_strip_size" class="form-label fw-medium">Tablet / Strip</label>
                                        <input type="text" name="strip_size" id="edit_strip_size" class="form-control"
                                            placeholder="Tablets per strips">
                                    </div>
                                    <div class="col-md-6" id="edit_box_size_container">
                                        <label for="edit_box_size" class="form-label fw-medium">Strip / Box</label>
                                        <input type="number" name="box_size" id="edit_box_size" class="form-control"
                                            placeholder="Strips per box">
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Pricing -->
                            <div class="col-md-6" id="edit_pricing_section">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Pricing Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="edit_mrp" class="form-label fw-medium">MRP</label>
                                        <input type="number" step="0.01" name="mrp" id="edit_mrp" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit_ptr" class="form-label fw-medium">PTR</label>
                                        <input type="number" step="0.01" name="ptr" id="edit_ptr" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit_pts" class="form-label fw-medium">PTS</label>
                                        <input type="number" step="0.01" name="pts" id="edit_pts" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit_loyalty_point_percentage" class="form-label fw-medium">Loyalty %</label>
                                        <input type="number" step="0.01" name="loyalty_point_percentage"
                                            id="edit_loyalty_point_percentage" class="form-control" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Variant Configuration -->
                        <div id="edit_variant_section" class="d-none">
                            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Variant Configuration (Optional)</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium small">Sides (comma separated)</label>
                                    <input type="hidden" name="variant_name_1" value="Side">
                                    <input type="text" name="variant_values_1" id="edit_variant_values_1" class="form-control" placeholder="e.g. LEFT, RIGHT">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium small">Sizes (comma separated)</label>
                                    <input type="hidden" name="variant_name_2" value="Size">
                                    <input type="text" name="variant_values_2" id="edit_variant_values_2" class="form-control" placeholder="e.g. S, M, L, XL">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Show Product Modal --}}
    <div class="modal fade" id="showProductModal" tabindex="-1" aria-labelledby="showProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="showProductModalLabel">Product Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="showProductTableBody">
                    {{-- Content will be loaded via JS --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Import Product Modal --}}
    <div class="modal fade" id="importProductModal" tabindex="-1" aria-labelledby="importProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importProductModalLabel">Import Products (CSV)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-info small mb-4 text-center">
                            <strong>Note:</strong> Download the template to ensure your CSV is formatted correctly.
                        </div>
                        <div class="text-center mb-4">
                            <a href="{{ route('products.download-template') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fa fa-download me-1"></i> Download CSV Template
                            </a>
                        </div>
                        <div class="mb-3">
                            <label for="import_file" class="form-label fw-bold">Upload CSV File <span
                                    class="text-danger">*</span></label>
                            <input class="form-control" type="file" id="import_file" name="import_file" accept=".csv"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-upload me-1"></i> Import Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function () {
            const canEdit = @json(Auth::user()->hasAnyRole(['admin', 'superadmin']) || Auth::user()->hasPermissionToCategory('products', 'edit'));
            const canDelete = @json(Auth::user()->hasAnyRole(['admin', 'superadmin']) || Auth::user()->hasPermissionToCategory('products', 'delete'));

            var table = $('#products-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('products.index') }}",
                order: [],
                columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'product_code',
                    name: 'product_code'
                },
                {
                    data: 'product_name',
                    name: 'product_name'
                },
                {
                    data: 'generic_name',
                    name: 'generic_name'
                },
                {
                    data: 'pack',
                    name: 'pack'
                },
                {
                    data: 'mrp',
                    name: 'mrp',
                    render: function(data) {
                        return '₹' + parseFloat(data).toFixed(2);
                    }
                },
                {
                    data: 'ptr',
                    name: 'ptr',
                    render: function(data) {
                        return '₹' + parseFloat(data).toFixed(2);
                    }
                },
                {
                    data: 'pts',
                    name: 'pts',
                    render: function(data) {
                        return '₹' + parseFloat(data).toFixed(2);
                    }
                },
                {
                    data: 'loyalty_point_percentage',
                    name: 'loyalty_point_percentage'
                },
                {
                    data: 'brand',
                    name: 'brand',
                    render: function(data) {
                        return data ? data : '<span class="text-muted">N/A</span>';
                    }
                },


                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function (id, type, row) { // Pass row data
                        let deleteUrl = "{{ route('products.destroy', ':id') }}".replace(':id', id);
                        let csrf = "{{ csrf_token() }}";
                        // Store row data in a data attribute (JSON stringified) for easy access
                        let rowData = JSON.stringify(row).replace(/"/g, '&quot;').replace(/'/g, '&#39;');

                        return `
                                                                                    <div class="action-buttons">
                                                                                        <button type="button" class="btn btn-sm btn-primary view-btn" data-product='${rowData}'><i class="fa fa-eye"></i></button>
                                                                                        ${canEdit ? `<button type="button" class="btn btn-sm btn-primary edit-btn" data-product='${rowData}'><i class="fa fa-edit"></i></button>` : ''}
                                                                                        ${canDelete ? `
                                                                                        <form action="${deleteUrl}" method="POST" onsubmit="return confirm('Are you sure?')">
                                                                                            <input type="hidden" name="_token" value="${csrf}">
                                                                                            <input type="hidden" name="_method" value="DELETE">
                                                                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                                                                        </form>` : ''}
                                                                                    </div>
                                                                                `;
                    }
                }
                ],
                dom: "<'row mb-3'<'col-sm-12'B>>" +
                    "<'row mb-3'<'col-md-6'l><'col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: {
                    dom: {
                        button: {
                            className: 'btn btn-sm btn-icon'
                        }
                    },
                    buttons: [{
                        extend: 'copy',
                        className: 'btn btn-secondary btn-sm',
                        text: '<i class="fa fa-copy"></i> Copy'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-info btn-sm text-white',
                        text: '<i class="fa fa-file-csv"></i> CSV'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-success btn-sm',
                        text: '<i class="fa fa-file-excel"></i> Excel'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-danger btn-sm',
                        text: '<i class="fa fa-file-pdf"></i> PDF'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-dark btn-sm',
                        text: '<i class="fa fa-print"></i> Print'
                    }
                    ]
                }
            });

            // Helper function to switch product creation/edit types dynamically
            function switchProductType(modalPrefix, type) {
                let codeCol = $(`#${modalPrefix}_product_code_container`);
                let nameCol = $(`#${modalPrefix}_product_name_container`);
                let hsnCol = $(`#${modalPrefix}_hsn_code_container`);
                let brandCol = $(`#${modalPrefix}_brand_container`);
                let genericCol = $(`#${modalPrefix}_generic_name_container`);

                let brandVal = '';
                if (type === 'medical') {
                    brandVal = 'Atomets';
                    
                    // Column grid adjustments
                    codeCol.addClass('d-none');
                    nameCol.removeClass('col-md-3 col-md-4').addClass('col-md-3');
                    genericCol.removeClass('col-md-3 col-md-4 d-none').addClass('col-md-3');
                    hsnCol.removeClass('col-md-3 col-md-4').addClass('col-md-3');
                    brandCol.removeClass('d-none').removeClass('col-md-3 col-md-4').addClass('col-md-3');

                    // Visibility toggling
                    $(`#${modalPrefix}_packaging_section`).removeClass('d-none');
                    $(`#${modalPrefix}_pack_container`).addClass('d-none');
                    $(`#${modalPrefix}_strip_size_container`).removeClass('d-none');
                    $(`#${modalPrefix}_box_size_container`).removeClass('d-none');
                    $(`#${modalPrefix}_pricing_section`).removeClass('col-md-12').addClass('col-md-6');
                    $(`#${modalPrefix}_variant_section`).addClass('d-none');
                } else if (type === 'ortho') {
                    brandVal = 'Atomshield';

                    // Column grid adjustments
                    codeCol.removeClass('d-none').removeClass('col-md-3 col-md-4').addClass('col-md-3');
                    nameCol.removeClass('col-md-3 col-md-4').addClass('col-md-3');
                    genericCol.addClass('d-none');
                    hsnCol.removeClass('col-md-3 col-md-4').addClass('col-md-3');
                    brandCol.removeClass('d-none').removeClass('col-md-3 col-md-4').addClass('col-md-3');

                    // Visibility toggling
                    $(`#${modalPrefix}_packaging_section`).addClass('d-none');
                    $(`#${modalPrefix}_pricing_section`).removeClass('col-md-6').addClass('col-md-12');
                    $(`#${modalPrefix}_variant_section`).removeClass('d-none');
                } else { // general
                    brandVal = 'Sudhneelgiri';

                    // Column grid adjustments
                    codeCol.removeClass('d-none').removeClass('col-md-3 col-md-4').addClass('col-md-3');
                    nameCol.removeClass('col-md-3 col-md-4').addClass('col-md-3');
                    genericCol.addClass('d-none');
                    hsnCol.removeClass('col-md-3 col-md-4').addClass('col-md-3');
                    brandCol.removeClass('d-none').removeClass('col-md-3 col-md-4').addClass('col-md-3');

                    // Visibility toggling
                    $(`#${modalPrefix}_packaging_section`).removeClass('d-none');
                    $(`#${modalPrefix}_pack_container`).removeClass('d-none');
                    $(`#${modalPrefix}_strip_size_container`).addClass('d-none');
                    $(`#${modalPrefix}_box_size_container`).addClass('d-none');
                    $(`#${modalPrefix}_pricing_section`).removeClass('col-md-12').addClass('col-md-6');
                    $(`#${modalPrefix}_variant_section`).addClass('d-none');
                }

                // Set select dropdown value
                $(`#${modalPrefix}_brand`).val(brandVal);

                // Update active tab buttons
                $(`#${modalPrefix}ProductTypeTabs button`).removeClass('active');
                if (modalPrefix === 'create') {
                    $(`#tab-create-${type}`).addClass('active');
                } else {
                    $(`#tab-edit-${type}`).addClass('active');
                }
            }

            // Default initial state for product creation
            switchProductType('create', 'medical');

            // Reset create modal type to medical on open
            $('#createProductModal').on('show.bs.modal', function () {
                switchProductType('create', 'medical');
            });

            // Click handlers for switching tabs
            $('#createProductTypeTabs button').on('click', function() {
                let type = $(this).data('type');
                switchProductType('create', type);
            });

            $('#editProductTypeTabs button').on('click', function() {
                let type = $(this).data('type');
                switchProductType('edit', type);
            });

            // Handle Edit Button Click
            $('#products-table').on('click', '.edit-btn', function () {
                var product = $(this).data('product'); // This might be an object if processed by jQuery, or string
                console.log(product);
                if (typeof product === 'string') {
                    product = JSON.parse(product);
                }

                function removeCommas(value) {
                    return value ? value.toString().replace(/,/g, '') : '';
                }

                // Map brand to tab type first to adjust field visibility
                let productType = 'general';
                if (product.brand === 'Atomets') {
                    productType = 'medical';
                } else if (product.brand === 'Atomshield') {
                    productType = 'ortho';
                }
                switchProductType('edit', productType);

                // Populate fields
                $('#edit_product_code').val(product.product_code);
                $('#edit_product_name').val(product.product_name);
                $('#edit_generic_name').val(product.generic_name);
                $('#edit_pack').val(product.pack);

                $('#edit_strip_size').val(product.strip_size);
                $('#edit_box_size').val(product.box_size);
                $('#edit_hsn_code').val(product.hsn_code);


                $('#edit_mrp').val(removeCommas(product.mrp));
                $('#edit_ptr').val(removeCommas(product.ptr));
                $('#edit_pts').val(removeCommas(product.pts));
                // Offer and discount fields removed
                $('#edit_loyalty_point_percentage').val(product.loyalty_point_percentage);
                $('#edit_brand').val(product.brand || '');


                // Variant Options
                $('#edit_variant_values_1').val('');
                $('#edit_variant_values_2').val('');

                if (product.variant_options) {
                    // Explicit Mapping
                    if (product.variant_options.Side) {
                        $('#edit_variant_values_1').val(product.variant_options.Side.join(', '));
                    }
                    if (product.variant_options.Size) {
                        $('#edit_variant_values_2').val(product.variant_options.Size.join(', '));
                    }
                }

                // Update form action
                let updateUrl = "{{ route('products.update', ':id') }}".replace(':id', product.id);
                $('#editProductForm').attr('action', updateUrl);

                // Show modal
                $('#editProductModal').modal('show');
            });

            // Handle View Button Click
            $('#products-table').on('click', '.view-btn', function () {
                var product = $(this).data('product');
                if (typeof product === 'string') {
                    try {
                        product = JSON.parse(product);
                    } catch (e) {
                        console.error('Failed to parse product data JSON', e);
                    }
                }

                const isValid = (val, type) => {
                    if (!val || val === 'null') return false;
                    let s = val.toString().toLowerCase().trim();
                    if (s === '' || s === 'n/a') return false;
                    if (type === 'generic' && s === 'generic n/a') return false;
                    if (type === 'pack' && s === 'pack n/a') return false;
                    return true;
                };

                const getProductProp = (obj, props) => {
                    if (!obj) return null;
                    for (let prop of props) {
                        if (obj[prop] !== undefined && obj[prop] !== null) return obj[prop];
                    }
                    return null;
                };

                const stripVal = getProductProp(product, ['strip_size', 'units_per_strip']);
                const boxVal = getProductProp(product, ['box_size', 'strips_per_box']);
                const cartonVal = getProductProp(product, ['boxes_per_carton', 'carton_size']);

                const stripValInt = parseInt(String(stripVal || '').replace(/[^0-9]/g, '')) || 1;
                const boxValInt = parseInt(String(boxVal || '').replace(/[^0-9]/g, '')) || 1;
                const cartonValInt = parseInt(String(cartonVal || '').replace(/[^0-9]/g, '')) || 1;

                const isStripBased = isValid(stripVal) && 
                                     isValid(boxVal) && 
                                     stripVal.toString().toLowerCase().trim() !== 'n/a' && 
                                     boxVal.toString().toLowerCase().trim() !== 'n/a' &&
                                     !(stripValInt === 1 && boxValInt === 1 && cartonValInt === 1);

                let packagingHtml = '';
                if (isStripBased) {
                    packagingHtml = `
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3 detail-section-title d-flex align-items-center">
                                Stock & Packaging
                            </h5>
                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="detail-card-panel d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="detail-icon-box icon-box-blue me-3">
                                                <i class="fa fa-dot-circle-o" style="font-size: 0.95rem;"></i>
                                            </div>
                                            <div>
                                                <div class="detail-text-title">Tablets Per Strip</div>
                                                <div class="detail-text-value fs-6" style="line-height: 1.1;">${stripVal} <span class="text-muted small" style="font-weight: 500;">Tabs</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="detail-card-panel d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="detail-icon-box icon-box-green me-3">
                                                <i class="fa fa-cube" style="font-size: 0.95rem;"></i>
                                            </div>
                                            <div>
                                                <div class="detail-text-title">Strips Per Box</div>
                                                <div class="detail-text-value fs-6" style="line-height: 1.1;">${boxVal} <span class="text-muted small" style="font-weight: 500;">Strips</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="detail-card-panel d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="detail-icon-box icon-box-amber me-3">
                                                <i class="fa fa-archive" style="font-size: 0.95rem;"></i>
                                            </div>
                                            <div>
                                                <div class="detail-text-title">Boxes Per Carton</div>
                                                <div class="detail-text-value fs-6" style="line-height: 1.1;">${cartonVal || '1'} <span class="text-muted small" style="font-weight: 500;">Boxes</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                let genericNameHtml = '';
                if (isValid(product.generic_name)) {
                    genericNameHtml = `
                        <div class="col-12 mb-3">
                            <div class="p-2.5 detail-generic-formulation d-flex align-items-center">
                                <div class="detail-icon-box icon-box-blue me-3">
                                    <i class="fa fa-flask" style="font-size: 0.9rem;"></i>
                                </div>
                                <div>
                                    <div class="detail-text-title" style="line-height: 1;">Generic Formulation</div>
                                    <div class="detail-text-value fs-7" style="line-height: 1.1;">${product.generic_name}</div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                let commercialsHtml = `
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3 detail-section-title d-flex align-items-center">
                            Commercials
                        </h5>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="detail-card-panel">
                                    <div class="detail-text-title mb-1">MRP</div>
                                    <div class="fs-5 detail-text-value">₹${parseFloat(product.mrp || 0).toFixed(2)}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="detail-card-panel">
                                    <div class="detail-text-title mb-1">PTR</div>
                                    <div class="fs-5 detail-text-value">₹${parseFloat(product.ptr || 0).toFixed(2)}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="detail-card-panel">
                                    <div class="detail-text-title mb-1">PTS</div>
                                    <div class="fs-5 detail-text-value">₹${parseFloat(product.pts || 0).toFixed(2)}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="detail-card-panel">
                                    <div class="detail-text-title mb-1">Loyalty Points</div>
                                    <div class="fs-5 detail-text-value">${parseFloat(product.loyalty_point_percentage || 0).toFixed(2)}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                let commercialsHtmlFull = `
                    <div class="mb-3">
                        <h5 class="fw-bold mb-3 detail-section-title d-flex align-items-center">
                            Commercials
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <div class="detail-card-panel h-100">
                                    <div class="detail-text-title mb-1">MRP</div>
                                    <div class="fs-5 detail-text-value">₹${parseFloat(product.mrp || 0).toFixed(2)}</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="detail-card-panel h-100">
                                    <div class="detail-text-title mb-1">PTR</div>
                                    <div class="fs-5 detail-text-value">₹${parseFloat(product.ptr || 0).toFixed(2)}</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="detail-card-panel h-100">
                                    <div class="detail-text-title mb-1">PTS</div>
                                    <div class="fs-5 detail-text-value">₹${parseFloat(product.pts || 0).toFixed(2)}</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="detail-card-panel h-100">
                                    <div class="detail-text-title mb-1">Loyalty Points</div>
                                    <div class="fs-5 detail-text-value">${parseFloat(product.loyalty_point_percentage || 0).toFixed(2)}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                let bodyColumnsHtml = '';
                if (isStripBased) {
                    bodyColumnsHtml = `
                        <div class="row g-3">
                            <div class="col-md-6 border-end pe-md-3">
                                ${packagingHtml}
                            </div>
                            <div class="col-md-6 ps-md-3">
                                ${commercialsHtml}
                            </div>
                        </div>
                    `;
                } else {
                    bodyColumnsHtml = `
                        <div class="row g-2">
                            <div class="col-12">
                                ${commercialsHtmlFull}
                            </div>
                        </div>
                    `;
                }

                let variantsHtml = '';
                if (product.variant_options && typeof product.variant_options === 'object' && Object.keys(product.variant_options).length > 0) {
                    variantsHtml = `
                        <div class="mt-3 pt-3 border-top">
                            <h5 class="fw-bold mb-3 detail-section-title d-flex align-items-center">
                                Structured Product Variants
                            </h5>
                            <div class="row g-2">
                                ${Object.entries(product.variant_options).map(([key, vals]) => `
                                    <div class="col-md-12">
                                        <div class="detail-card-panel">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="detail-icon-box icon-box-indigo me-2" style="width: 28px; height: 28px;">
                                                        <i class="fa ${key.toLowerCase() === 'side' ? 'fa-arrows-h' : 'fa-arrows-v'}" style="font-size: 0.95rem;"></i>
                                                    </div>
                                                    <span class="detail-text-title">${key} Options</span>
                                                </div>
                                                <div class="d-flex flex-wrap gap-1.5">
                                                    ${vals.map(v => `
                                                        <span class="badge detail-variant-pill px-2.5 py-1.5 border text-dark">
                                                            ${v}
                                                        </span>
                                                    `).join('')}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }

                const pName = getProductProp(product, ['product_name', 'name']) || 'N/A';

                let html = `
                    <div class="product-detail-premium">
                        <div class="text-center mb-3 pb-3 border-bottom">
                            <h3 class="fw-bold text-primary text-uppercase mb-2 font-outfit" style="letter-spacing: 0.5px; font-size: 1.35rem;">${pName}</h3>
                            <div class="d-flex flex-wrap justify-content-center gap-4 mt-3 mb-2">
                                ${isValid(product.product_code) ? `<div class="detail-meta-item"><div class="meta-icon"><i class="fa fa-tag" style="font-size: 0.75rem;"></i></div>Code: <span class="meta-val">${product.product_code}</span></div>` : ''}
                                ${isValid(product.pack) ? `<div class="detail-meta-item"><div class="meta-icon"><i class="fa fa-archive" style="font-size: 0.75rem;"></i></div>Pack: <span class="meta-val">${product.pack}</span></div>` : ''}
                                ${isValid(product.hsn_code) ? `<div class="detail-meta-item"><div class="meta-icon"><i class="fa fa-barcode" style="font-size: 0.75rem;"></i></div>HSN: <span class="meta-val">${product.hsn_code}</span></div>` : ''}
                                ${isValid(product.brand) ? `<div class="detail-meta-item"><div class="meta-icon"><i class="fa fa-building" style="font-size: 0.75rem;"></i></div>Brand: <span class="meta-val">${product.brand}</span></div>` : ''}
                            </div>
                        </div>

                        ${genericNameHtml}

                        ${bodyColumnsHtml}

                        ${variantsHtml}
                    </div>
                `;
                
                // Maintain consistent modal-lg size exactly like the Create Product modal
                $('#showProductModal .modal-dialog').removeClass('modal-md').addClass('modal-lg');

                $('#showProductTableBody').html(html);
                $('#showProductModal').modal('show');
            });





            // Auto-calculation logic removed since tax info isn't on product level anymore.
        });
    </script>

@endpush