@extends('layouts.admin')

@push('styles')
<style>
    /* Premium UI Enhancements */
    .permissions-container {
        padding: 1.5rem 0;
        animation: fadeIn 0.5s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .permission-checkbox {
        width: 0.95rem; /* Reduced size */
        height: 0.95rem;
        cursor: pointer;
        border-radius: 3px;
        border: 1.5px solid var(--med-border);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        accent-color: var(--med-primary);
        background-color: var(--med-bg-card);
    }

    .permission-checkbox:hover:not(:disabled) {
        transform: scale(1.1);
        border-color: var(--med-primary);
    }

    /* Dark Mode specific tweaks for checkbox */
    body.dark-only .permission-checkbox {
        border-color: rgba(255,255,255,0.2);
    }

    .saving-indicator {
        position: fixed;
        top: 25px;
        right: 25px;
        z-index: 9999;
        display: none;
        padding: 10px 20px;
        border-radius: 100px;
        background: var(--med-primary);
        color: white;
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        font-weight: 600;
        backdrop-filter: blur(8px);
        animation: slideIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(50px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .role-header-card {
        background: linear-gradient(135deg, var(--med-primary), var(--med-secondary));
        border-radius: 16px !important;
        margin-bottom: 1.5rem;
        color: white;
        border: none !important;
        overflow: hidden;
    }

    /* Muted Dark Mode Header */
    body.dark-only .role-header-card {
        background: var(--med-bg-card-header) !important;
        border: 1px solid var(--med-border) !important;
    }

    .role-header-card .card-body {
        padding: 1.15rem 2rem;
    }

    .permissions-table-card {
        border-radius: 16px !important;
        background: var(--med-bg-card) !important;
        box-shadow: var(--med-shadow-soft) !important;
        border: 1px solid var(--med-border) !important;
    }

    .table thead th {
        background: var(--med-primary) !important;
        color: white !important;
        border-top: none !important;
        padding: 1rem 1.5rem !important;
        font-weight: 600 !important;
        border-bottom: none !important;
    }

    body.dark-only .table thead th {
        background: var(--med-bg-card-header) !important;
        border-bottom: 1px solid var(--med-border) !important;
    }

    .group-row td {
        background: rgba(var(--med-primary-rgb), 0.05) !important;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.7rem;
        color: var(--med-primary);
        padding: 0.6rem 2rem !important;
    }

    body.dark-only .group-row td {
        background: rgba(255,255,255,0.05) !important;
    }

    .cat-row {
        transition: all 0.2s ease;
    }

    .cat-row:hover {
        background-color: rgba(var(--med-primary-rgb), 0.02) !important;
    }

    .cat-row td {
        padding: 1.2rem 2rem !important;
        border-bottom: 1px solid var(--med-border);
        color: var(--med-text-main);
    }

    .cat-name {
        font-weight: 500; /* Less font weight for categories */
        color: var(--med-text-main);
        display: block;
    }

    .cat-description {
        font-size: 0.75rem;
        color: var(--med-text-muted);
        font-weight: 400;
    }

    .search-permissions {
        border-radius: 10px;
        padding: 0.6rem 1rem;
        border: 1px solid var(--med-border);
        background: var(--med-bg-card);
        color: var(--med-text-main);
        width: 250px;
        transition: all 0.3s;
    }

    .search-permissions:focus {
        border-color: var(--med-primary);
        box-shadow: 0 0 0 3px rgba(var(--med-primary-rgb), 0.1);
        outline: none;
    }

    .action-icon {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        margin-bottom: 4px;
        font-size: 0.85rem;
    }

    /* Action icon backgrounds with better contrast in themes */
    .bg-view { background: rgba(3, 105, 161, 0.1); color: #0369a1; }
    .bg-add { background: rgba(21, 128, 61, 0.1); color: #15803d; }
    .bg-edit { background: rgba(161, 98, 7, 0.1); color: #a16207; }
    .bg-delete { background: rgba(185, 28, 28, 0.1); color: #b91c1c; }
    
    body.dark-only .bg-view { color: #38bdf8; }
    body.dark-only .bg-add { color: #4ade80; }
    body.dark-only .bg-edit { color: #facc15; }
    body.dark-only .bg-delete { color: #f87171; }
    .btn-premium-close {
        background: rgba(255, 255, 255, 0.12) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: white !important;
        backdrop-filter: blur(8px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-weight: 600 !important;
        letter-spacing: 0.5px;
    }

    .btn-premium-close:hover {
        background: white !important;
        color: var(--med-primary) !important;
        transform: translateX(-4px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('page-body')
<div class="saving-indicator" id="saving-indicator">
    <div class="d-flex align-items-center">
        <div class="spinner-border spinner-border-sm me-3" role="status"></div>
        <span>Saving...</span>
    </div>
</div>

<div class="container-fluid permissions-container">
    <!-- Compact Role Header -->
    <div class="card role-header-card shadow-sm border-0">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div>
                    <h4 class="mb-0 fw-bold text-white">{{ ucfirst($role->name) }} Access Control</h4>
                    <p class="text-white-50 mb-0 small mt-1">Manage feature access controls for this role.</p>
                </div>
            </div>
            <a href="{{ route('admin.permissions.index') }}" class="btn btn-sm btn-premium-close rounded-pill px-4">
                <i class="fa fa-arrow-left me-1 small"></i> Go Back
            </a>
        </div>
    </div>

    <!-- Permissions Table -->
    <div class="card permissions-table-card border-0">
        <div class="card-header border-bottom bg-transparent py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                
            </h6>
            <div class="position-relative">
                <input type="text" id="permission-search" class="search-permissions ps-4 small" placeholder="Quick find...">
            </div>
        </div>
        <div class="card-body p-2">
            <div class="table-responsive">
                <table class="table p-2 mb-0 align-middle">
                    <thead>
                        <tr>
                            <th width="40%" class="ps-4">Feature</th>
                            <th class="text-center small">View</th>
                            <th class="text-center small">Add</th>
                            <th class="text-center small">Edit</th>
                            <th class="text-center small">Delete</th>
                        </tr>
                    </thead>
                    <tbody id="permissions-tbody">
                        @foreach($groupedPermissions as $groupName => $groupData)
                        <tr class="group-row">
                            <td colspan="5" class="ps-4">{{ $groupName }}</td>
                        </tr>
                        @foreach($groupData['categories'] as $catName => $cat)
                        <tr class="cat-row">
                            <td class="ps-4">
                                <span class="cat-name">{{ $catName }}</span>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" class="permission-checkbox" 
                                    data-id="{{ $cat['id'] }}" data-type="can_view"
                                    {{ $cat['can_view'] ? 'checked' : '' }}
                                    {{ $cat['is_disabled'] ? 'disabled' : '' }}>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" class="permission-checkbox" 
                                    data-id="{{ $cat['id'] }}" data-type="can_add"
                                    {{ $cat['can_add'] ? 'checked' : '' }}
                                    {{ $cat['is_disabled'] ? 'disabled' : '' }}>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" class="permission-checkbox" 
                                    data-id="{{ $cat['id'] }}" data-type="can_edit"
                                    {{ $cat['can_edit'] ? 'checked' : '' }}
                                    {{ $cat['is_disabled'] ? 'disabled' : '' }}>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" class="permission-checkbox" 
                                    data-id="{{ $cat['id'] }}" data-type="can_delete"
                                    {{ $cat['can_delete'] ? 'checked' : '' }}
                                    {{ $cat['is_disabled'] ? 'disabled' : '' }}>
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const categoryId = this.dataset.id;
            const type = this.dataset.type;
            const value = this.checked ? 1 : 0;
            const indicator = document.getElementById('saving-indicator');

            // Show indicator
            indicator.style.display = 'block';

            fetch("{{ route('admin.permissions.updateSingle', $role->id) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    category_id: categoryId,
                    permission_type: type,
                    value: value
                })
            })
            .then(response => response.json())
            .then(data => {
                setTimeout(() => {
                    indicator.style.display = 'none';
                }, 400);

                if (data.error) {
                    showToast('error', data.error);
                    this.checked = !this.checked;
                }
            })
            .catch(error => {
                indicator.style.display = 'none';
                console.error('Error:', error);
                showToast('error', 'Sync failed');
                this.checked = !this.checked;
            });
        });
    });

    // Search Functionality
    document.getElementById('permission-search')?.addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('.cat-row').forEach(row => {
            const catName = row.querySelector('.cat-name').textContent.toLowerCase();
            row.style.display = catName.includes(term) ? '' : 'none';
        });

        // Hide group headers if no children are visible
        document.querySelectorAll('.group-row').forEach(groupRow => {
            let next = groupRow.nextElementSibling;
            let hasVisibleChild = false;
            while (next && !next.classList.contains('group-row')) {
                if (next.style.display !== 'none') {
                    hasVisibleChild = true;
                    break;
                }
                next = next.nextElementSibling;
            }
            groupRow.style.display = hasVisibleChild ? '' : 'none';
        });
    });
</script>
@endpush