<!-- Page Sidebar Start-->
<div class="sidebar-wrapper medical-theme-sidebar" data-layout="stroke-svg">
  <div class="logo-wrapper"><a href="{{ route('dashboard') }}"><img class="img-fluid" src="{{ asset('admin/assets/images/logo/atom-logo-main-white.png') }}" width="170" alt=""></a>
    <div class="back-btn"><i class="fa fa-angle-left"> </i></div>
    <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i></div>
  </div>
  <style>
    .sidebar-wrapper .sidebar-main .sidebar-links li {
        position: relative;
    }
    .sidebar-link {
        display: flex !important;
        align-items: center !important;
        white-space: nowrap !important;
        justify-content: flex-start !important;
    }
    .sidebar-link span {
        white-space: nowrap !important;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sidebar-badge {
        margin-left: auto !important;
        flex-shrink: 0 !important;
    }
    .sidebar-link .according-menu {
        top: 50% !important;
        transform: translateY(-50%) !important;
        right: 20px !important;
    }

    /* Custom Whitish Sidebar Scrollbar Visible Only on Hover & Drag */
    .sidebar-wrapper .simplebar-scrollbar:before {
        background-color: rgba(255, 255, 255, 0.45) !important;
        opacity: 0 !important;
        width: 8px !important;
        border-radius: 4px !important;
        transition: opacity 0.3s ease, background-color 0.3s ease !important;
        right: 1px !important;
        left: auto !important;
    }
    
    .sidebar-wrapper:hover .simplebar-scrollbar:before,
    .sidebar-wrapper .simplebar-scrollbar.simplebar-visible:before,
    .simplebar-dragging .simplebar-scrollbar:before {
        opacity: 0.65 !important;
    }
    
    .sidebar-wrapper .simplebar-scrollbar:hover:before,
    .sidebar-wrapper .simplebar-scrollbar.simplebar-visible:hover:before {
        background-color: rgba(255, 255, 255, 0.85) !important;
    }
    
    .sidebar-wrapper .simplebar-track.simplebar-vertical {
        width: 10px !important;
        right: 3px !important;
        background: transparent !important;
        z-index: 1000 !important;
    }
  </style>
  {{-- <div class="logo-icon-wrapper"><a href="{{ route('dashboard') }}"><img class="img-fluid" src="{{ asset('admin/assets/images/logo/atom-logo-main-white.png') }}" width="170" alt=""></a></div> --}}
  <nav class="sidebar-main">
    <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
    <div id="sidebar-menu">
      <ul class="sidebar-links" id="simple-bar">
        {{-- <li class="back-btn"><a href="{{ route('dashboard') }}"><img class="img-fluid" src="{{ asset('admin/assets/images/logo/atom-logo-main-white.png') }}" width="170" alt=""></a>
          <div class="mobile-back text-end"> <span>Back </span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
        </li> --}}
        <li class="pin-title sidebar-main-title">
          <div>
            <h6>Pinned</h6>
          </div>
        </li>

        @php
        $iconSprite = asset('admin/assets/svg/icon-sprite.svg');
        $hasApprovalRoles = Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('admin') || Auth::user()->hasRole('salesmanager');
        $actionCounts = Auth::user()->getActionCounts();
        @endphp

        {{-- 1. General Section --}}
        <li class="sidebar-main-title mt-4">
          <div>
            <h6 class="lan-1">General</h6>
          </div>
        </li>
        <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="{{ route('dashboard') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-home"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-home"></use>
            </svg><span>Dashboard</span>
          </a>
        </li>
        @if (Auth::user()->hasPermissionToCategory('loyalty_points', 'view') || Auth::user()->hasAnyRole(['retailer', 'distributor']))
          @if (Auth::user()->hasRole('retailer'))
            <li class="sidebar-list">
              <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.loyalty-points.index') }}">
                <svg class="stroke-icon">
                  <use href="{{ $iconSprite }}#stroke-bookmark"></use>
                </svg>
                <svg class="fill-icon">
                  <use href="{{ $iconSprite }}#fill-bookmark"></use>
                </svg><span>Loyalty & Credits</span>
              </a>
            </li>
          @elseif (Auth::user()->hasRole('distributor'))
            <li class="sidebar-list">
              <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.distributor-wallet.index') }}">
                <svg class="stroke-icon">
                  <use href="{{ $iconSprite }}#stroke-bookmark"></use>
                </svg>
                <svg class="fill-icon">
                  <use href="{{ $iconSprite }}#fill-bookmark"></use>
                </svg><span>Credits</span>
              </a>
            </li>
          @else
            <li class="sidebar-list" style="position: relative;">
              <a class="sidebar-link sidebar-title" href="javascript:void(0)">
                <svg class="stroke-icon">
                  <use href="{{ $iconSprite }}#stroke-bookmark"></use>
                </svg>
                <svg class="fill-icon">
                  <use href="{{ $iconSprite }}#fill-bookmark"></use>
                </svg>
                <span>Loyalty & Credits</span>
              </a>
              <ul class="sidebar-submenu">
                <li><a href="{{ route('admin.loyalty-points.index') }}">Retailer</a></li>
                <li><a href="{{ route('admin.distributor-wallet.index') }}">Distributor</a></li>
              </ul>
            </li>
          @endif
        @endif

        @if (Auth::user()->hasPermissionToCategory('staff_ratings', 'view') && !Auth::user()->hasAnyRole(['admin', 'superadmin']))
        <li class="sidebar-list">
          <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('distributor.staff-ratings.*') ? 'active' : '' }}" href="{{ route('distributor.staff-ratings.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-user"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-user"></use>
            </svg><span>Staff Ratings</span>
          </a>
        </li>
        @endif

        {{-- 6. Reports Section --}}
        @if (Auth::user()->hasPermissionToCategory('executive_reports', 'view') || 
             Auth::user()->hasPermissionToCategory('distributor_reports', 'view') || 
             Auth::user()->hasPermissionToCategory('retailer_reports', 'view') || 
             Auth::user()->hasPermissionToCategory('performance_reports', 'view') ||
             Auth::user()->hasPermissionToCategory('product_reports', 'view') ||
             Auth::user()->hasPermissionToCategory('master_order_reports', 'view'))
        <li class="sidebar-main-title">
          <div>
            <h6>Reports</h6>
          </div>
        </li>
        <li class="sidebar-list">
          <a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName() == 'admin.reports.index' ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-charts"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-charts"></use>
            </svg><span>Executive Reports</span></a>
        </li>
        @endif

        {{-- 2. Approvals Section --}}
        @if (Auth::user()->hasPermissionToCategory('retailer_approvals', 'view') || 
             Auth::user()->hasPermissionToCategory('distributor_approvals', 'view') || 
             Auth::user()->hasRole('fieldstaff') ||
             $hasApprovalRoles)
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-15">Order Approvals</h6>
          </div>
        </li>

        @if(Auth::user()->hasPermissionToCategory('retailer_approvals', 'view') || Auth::user()->hasRole('fieldstaff') || (Auth::user()->hasAnyRole(['admin', 'superadmin']) && !Auth::user()->hasRole('salesmanager')))
        <li class="sidebar-list">
          <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('admin.approvals.retailer') ? 'active' : '' }}" href="{{ route('admin.approvals.retailer') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-user"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-user"></use>
            </svg>
            <span>Retailers</span>
            @if($actionCounts['retailer_approvals'] > 0)
                <span id="badge-retailer-approvals" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; margin-left: 8px;">{{ $actionCounts['retailer_approvals'] }}</span>
            @endif
          </a>
        </li>
        @endif

        @if(Auth::user()->hasPermissionToCategory('distributor_approvals', 'view') || $hasApprovalRoles)
        <li class="sidebar-list">
          <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('admin.approvals.distributor') ? 'active' : '' }}" href="{{ route('admin.approvals.distributor') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-user"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-user"></use>
            </svg>
            <span>Distributors</span>
            @if($actionCounts['distributor_approvals'] > 0)
                <span id="badge-distributor-approvals" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; margin-left: 8px;">{{ $actionCounts['distributor_approvals'] }}</span>
            @endif
          </a>
        </li>
        @endif
        @endif

        {{-- 3. Orders Section --}}
        @php
        $hasOrderPerms = Auth::user()->hasPermissionToCategory('retailer_orders', 'view') ||
        Auth::user()->hasPermissionToCategory('distributor_orders', 'view') ||
        Auth::user()->hasRole('retailer') ||
        Auth::user()->hasRole('distributor');
        @endphp

        @if ($hasOrderPerms || $hasApprovalRoles)
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-13">Orders</h6>
          </div>
        </li>
        
        {{-- Standalone Role-Based Order Links --}}
        @if (Auth::user()->hasRole('retailer'))
        <li class="sidebar-list">
          <a class="sidebar-link sidebar-title link-nav" href="{{ route('retailer.orders.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg><span>My Orders</span>
            @if(isset($actionCounts['retailer_orders']) && $actionCounts['retailer_orders'] > 0)
                <span id="badge-retailer-orders" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; margin-left: 8px;">{{ $actionCounts['retailer_orders'] }}</span>
            @endif
          </a>
        </li>
        @endif

        @if (Auth::user()->hasRole('distributor'))
        @if (Auth::user()->hasPermissionToCategory('retailer_orders', 'view'))
        <li class="sidebar-list">
          <a class="sidebar-link sidebar-title link-nav" href="{{ route('distributor.orders.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-ecommerce"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-ecommerce"></use>
            </svg><span>Retailer Orders</span>
          </a>
        </li>
        @endif
        @if (Auth::user()->hasPermissionToCategory('distributor_orders', 'view'))
        <li class="sidebar-list">
          <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.distributor-orders.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg><span>My Orders</span>
            @if(isset($actionCounts['distributor_orders']) && $actionCounts['distributor_orders'] > 0)
                <span id="badge-distributor-orders" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; margin-left: 8px;">{{ $actionCounts['distributor_orders'] }}</span>
            @endif
          </a>
        </li>
        @endif

        @endif

        {{-- Admin/Staff Order Dropdown --}}
        @if(Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('admin') || Auth::user()->hasRole('salesmanager') || Auth::user()->hasRole('fieldstaff'))
        <li class="sidebar-list" style="position: relative;">
          <a class="sidebar-link sidebar-title" id="orders" href="javascript:void(0)">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg><span>View Orders</span>
          </a>
          <ul class="sidebar-submenu">
            @if (Auth::user()->hasRole('fieldstaff'))
            <li style="position: relative;"><a href="{{ route('fieldstaff.orders.index') }}">Order</a></li>
            @endif
            @if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('salesmanager'))
            @if (Auth::user()->hasPermissionToCategory('retailer_orders', 'view'))
            <li><a href="{{ route('admin.retailer.index') }}">Retailer Orders</a></li>
            @endif
            @if (Auth::user()->hasPermissionToCategory('distributor_orders', 'view'))
            <li><a href="{{ route('admin.distributor-orders.index') }}">Distributor Orders</a></li>
            @endif
            @endif
          </ul>
        </li>
        @endif
        @endif

        {{-- 4. Returns Section --}}
        @if (Auth::user()->hasPermissionToCategory('retailer_orders', 'view') || 
             Auth::user()->hasPermissionToCategory('distributor_orders', 'view') || 
             Auth::user()->hasRole('retailer') || 
             Auth::user()->hasRole('distributor') ||
             Auth::user()->hasRole('salesmanager') ||
             Auth::user()->hasRole('fieldstaff'))
        <li class="sidebar-list">
          <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('admin.returns.*') ? 'active' : '' }}" href="{{ route('admin.returns.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg>
            <span>Return Products</span>
          </a>
        </li>
        @endif

        {{-- 5. Staff Monitoring Section --}}
        @if (Auth::user()->hasPermissionToCategory('staff_monitoring', 'view') || $hasApprovalRoles)
        <li class="sidebar-main-title">
          <div>
            <h6>Staff Monitoring</h6>
          </div>
        </li>
        <li class="sidebar-list" style="position: relative;">
          @php $staffActionCounts = $actionCounts['staff_expenses'] + $actionCounts['staff_leaves']; @endphp
          <a class="sidebar-link sidebar-title" id="staff-monitoring" href="javascript:void(0)">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-user"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-user"></use>
            </svg>
            <span>Staff Management</span>
            @if($staffActionCounts > 0)
                <span id="badge-staff-approvals" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; margin-left: 8px;">{{ $staffActionCounts }}</span>
            @endif
          </a>
          <ul class="sidebar-submenu">
              @if (Auth::user()->hasPermissionToCategory('field_staff_reports', 'view') || Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
              <li>
                <a class="{{ (request()->routeIs('admin.field-staff.tracking') || request()->routeIs('admin.field-staff.tracking-map')) ? 'active' : '' }}" href="{{ route('admin.field-staff.tracking') }}">
                   <span>Staff Tracking</span>
                </a>
              </li>
              @endif
              <li><a href="{{ route('admin.field-staff.expenses') }}">
                <span>Staff Expenses</span>
                @if($actionCounts['staff_expenses'] > 0)
                    <span id="badge-staff-expenses" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; margin-left: 8px;">{{ $actionCounts['staff_expenses'] }}</span>
                @endif
              </a></li>
              <li><a href="{{ route('admin.field-staff.leaves') }}">
                <span>Staff Leaves</span>
                @if($actionCounts['staff_leaves'] > 0)
                    <span id="badge-staff-leaves" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; margin-left: 8px;">{{ $actionCounts['staff_leaves'] }}</span>
                @endif
              </a></li>
              @if (Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
              <li><a class="{{ request()->routeIs('admin.staff-ratings.*') ? 'active' : '' }}" href="{{ route('admin.staff-ratings.index') }}">
                <span>Staff Ratings</span>
              </a></li>
              @endif
          </ul>
        </li>
        @endif



        {{-- 7. Inventory Section (Products & Stock) --}}
        @if (Auth::user()->hasAnyRole(['admin', 'superadmin']) || Auth::user()->hasPermissionToCategory('products', 'view') || Auth::user()->hasPermissionToCategory('inventories', 'view'))
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-12">Inventory</h6>
          </div>
        </li>
        @if (Auth::user()->hasAnyRole(['admin', 'superadmin']) || Auth::user()->hasPermissionToCategory('products', 'view'))
        <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="{{ route('products.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg><span>Products</span></a>
        </li>
        @endif
        @if (Auth::user()->hasAnyRole(['admin', 'superadmin']) || Auth::user()->hasPermissionToCategory('inventories', 'view'))
        <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="{{ route('inventories.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg><span>Current Stock</span></a>
        </li>
        @endif
        @endif

        {{-- 8. User Management Section --}}
        @if (Auth::user()->hasPermissionToCategory('permissions', 'view') || Auth::user()->hasPermissionToCategory('sales_managers', 'view') || Auth::user()->hasPermissionToCategory('distributors', 'view') || Auth::user()->hasPermissionToCategory('field_staff', 'view') || Auth::user()->hasPermissionToCategory('retailers', 'view'))
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-11">User Management</h6>
          </div>
        </li>
        @if (Auth::user()->hasPermissionToCategory('sales_managers', 'view'))
        <li class="sidebar-list">
          <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.sales-managers.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg>
            <span>Sales Managers</span>
            @if($actionCounts['inactive_sales_managers'] > 0)
                <span id="badge-inactive-sales-managers" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; margin-left: 8px;">{{ $actionCounts['inactive_sales_managers'] }}</span>
            @endif
          </a>
        </li>
        @endif
        @if (Auth::user()->hasPermissionToCategory('distributors', 'view'))
        <li class="sidebar-list">
          <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.distributors.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg>
            <span>Distributors</span>
            @if($actionCounts['inactive_distributors'] > 0)
                <span id="badge-inactive-distributors" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; margin-left: 8px;">{{ $actionCounts['inactive_distributors'] }}</span>
            @endif
          </a>
        </li>
        @endif
        @if (Auth::user()->hasPermissionToCategory('field_staff', 'view'))
        <li class="sidebar-list">
          <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.field-staffs.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg>
            <span>Field Staff</span>
            @if($actionCounts['inactive_field_staff'] > 0)
                <span id="badge-inactive-field-staff" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; margin-left: 8px;">{{ $actionCounts['inactive_field_staff'] }}</span>
            @endif
          </a>
        </li>
        @endif
        @if (Auth::user()->hasPermissionToCategory('retailers', 'view'))
        <li class="sidebar-list">
          <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.retailers.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg>
            <span>Retailers</span>
            @if($actionCounts['inactive_retailers'] > 0)
                <span id="badge-inactive-retailers" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; margin-left: 8px;">{{ $actionCounts['inactive_retailers'] }}</span>
            @endif
          </a>
        </li>
        @endif
        @endif

        {{-- 9. Settings Section --}}
        @if (Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('admin'))
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-10">Settings</h6>
          </div>
        </li>
        <li class="sidebar-list" style="position: relative;">
          <a class="sidebar-link sidebar-title" href="javascript:void(0)">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-user"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-user"></use>
            </svg>
            <span>Master Settings</span>
          </a>
          <ul class="sidebar-submenu">
            @if (Auth::user()->hasPermissionToCategory('districts', 'view') || Auth::user()->hasPermissionToCategory('districts', 'add'))
            <li><a href="{{ route('districts.index') }}"><span>Districts</span></a></li>
            @endif
            @if (Auth::user()->hasPermissionToCategory('areas', 'view') || Auth::user()->hasPermissionToCategory('areas', 'add'))
            <li><a href="{{ route('areas.index') }}"><span>Areas</span></a></li>
            @endif
            <li><a href="{{ route('admin.permissions.index') }}"><span>Roles & Permissions</span></a></li>
            <li><a href="{{ route('admin.settings.general') }}"><span>System Config</span></a></li>
          </ul>
        </li>
        @endif

      </ul>
      <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
    </div>
  </nav>
</div>
<!-- Page Sidebar Ends-->

@push('styles')
<style>
  @media (max-width: 991.98px) {
    .sidebar-wrapper .logo-wrapper img {
      max-width: 100px !important;
      /* Constrain width on mobile */
      width: auto !important;
      height: auto;
    }
  }

  /* Medical Theme Sidebar - Reversed & Changed Color (Light top, Dark bottom) */
  .medical-theme-sidebar {
    /* background: linear-gradient(180deg, #20B2AA 0%, #004D40 100%) !important; */
  }

  /* Ensure logo wrapper allows gradient to show through */
  .medical-theme-sidebar .logo-wrapper,
  .medical-theme-sidebar .logo-icon-wrapper {
    background: transparent !important;
  }

  /* Decrease the line height and spacing of modules inside the navbar */
  .sidebar-wrapper .sidebar-list {
    /* margin-bottom: 2px !important; */
  }
  
  .sidebar-wrapper .sidebar-main .sidebar-links .sidebar-list .sidebar-link {
    /* padding-top: 6px !important; */
    /* padding-bottom: 6px !important; */
  }

  .sidebar-wrapper .sidebar-main-title {
    /* padding-top: 10px !important; */
    /* padding-bottom: 4px !important; */
  }

  /* Pulse Animation for Notification Badges */
  .pulse-badge {
    background-color: #ffffff !important;
    color: var(--theme-default, #7366ff) !important;
    border: 2px solid var(--theme-default, #7366ff) !important;
    animation: pulse-white 2s infinite;
  }

  @keyframes pulse-white {
    0% {
      box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7);
    }
    70% {
      box-shadow: 0 0 0 8px rgba(255, 255, 255, 0);
    }
    100% {
      box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
    }
  }

  /* Robust Action Required Dot */
  .action-required-dot {
    position: absolute !important;
    top: 50% !important;
    right: 40px !important;
    transform: translateY(-50%) !important;
    width: 8px !important;
    height: 8px !important;
    border-radius: 50% !important;
    background-color: #ff3333 !important; /* Bright Red */
    border: 1.5px solid #fff !important;
    z-index: 9999 !important;
    display: block !important;
    pointer-events: none !important;
  }

  .inline-dot {
    position: relative !important;
    display: inline-block !important;
    vertical-align: middle !important;
    margin-left: 8px !important;
    top: -1px !important;
    right: auto !important;
    transform: none !important;
    width: 10px !important;
    height: 10px !important;
  }

  /* Subtle Action Required Dot */
  .subtle-action-dot {
    display: inline-block !important;
    width: 8px !important;
    height: 8px !important;
    border-radius: 50% !important;
    background-color: #f1c40f !important; /* Soft gold/yellow that blends well */
    box-shadow: 0 0 4px rgba(241, 196, 15, 0.6) !important;
    animation: pulse-subtle 2s infinite !important;
    margin-left: 8px !important;
    vertical-align: middle !important;
    position: relative !important;
    top: -1px !important;
  }

  @keyframes pulse-subtle {
    0% {
      box-shadow: 0 0 0 0 rgba(241, 196, 15, 0.7) !important;
    }
    70% {
      box-shadow: 0 0 0 5px rgba(241, 196, 15, 0) !important;
    }
    100% {
      box-shadow: 0 0 0 0 rgba(241, 196, 15, 0) !important;
    }
  }
</style>
@endpush

@push('scripts')
<script>
  $(document).ready(function() {
    // Explicitly handle mobile back button to ensure sidebar closes
    $(document).on('click', '.mobile-back', function(e) {
      e.preventDefault();
      e.stopPropagation(); // Prevent bubbling to parent li.back-btn

      // Force close sidebar (add close_icon class handles hiding)
      $(".page-header").addClass("close_icon");
      $(".sidebar-wrapper").addClass("close_icon");

      // Trigger overlay update to remove backdrop
      $(window).trigger("overlay");
    });
  });
</script>
@endpush