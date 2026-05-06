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
        <li class="sidebar-main-title mt-4">
          <div>
            <h6 class="lan-1">General</h6>
          </div>
        </li>

        @php
        $iconSprite = asset('admin/assets/svg/icon-sprite.svg');
        @endphp

        <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="{{ route('dashboard') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-home"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-home"></use>
            </svg><span>Dashboard</span>
          </a>
        </li>

        @if (Auth::user()->hasPermissionToCategory('loyalty_points', 'view') || Auth::user()->hasRole('retailer'))
        <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.loyalty-points.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-bookmark"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-bookmark"></use>
            </svg><span>Loyalty Points</span></a>
        </li>
        @endif

        {{-- Reports module --}}
        @if (Auth::user()->hasPermissionToCategory('executive_reports', 'view') || 
             Auth::user()->hasPermissionToCategory('distributor_reports', 'view') || 
             Auth::user()->hasPermissionToCategory('retailer_reports', 'view') || 
             Auth::user()->hasPermissionToCategory('performance_reports', 'view') ||
             Auth::user()->hasPermissionToCategory('product_reports', 'view') ||
             Auth::user()->hasPermissionToCategory('master_order_reports', 'view'))
        @php $isReportRoute = request()->routeIs('admin.reports.*'); @endphp
        <li class="sidebar-main-title">
          <div>
            <h6>Reports</h6>
          </div>
        </li>
        <li class="sidebar-list">
          <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('admin.reports.index') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-charts"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-charts"></use>
            </svg><span>Executive Reports</span></a>
        </li>
        @if (Auth::user()->hasPermissionToCategory('field_staff_reports', 'view') || Auth::user()->hasAnyRole(['admin', 'superadmin']))
        <li class="sidebar-list">
          <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('admin.reports.fieldstaffs') ? 'active' : '' }}" href="{{ route('admin.reports.fieldstaffs') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-user"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-user"></use>
            </svg><span>Field Staff Reports</span></a>
        </li>
        @endif
        @endif

        @php
        $hasOrderPerms = Auth::user()->hasPermissionToCategory('retailer_orders', 'view') ||
        Auth::user()->hasPermissionToCategory('distributor_orders', 'view') ||
        Auth::user()->hasRole('retailer') ||
        Auth::user()->hasRole('distributor');

        $hasApprovalRoles = Auth::user()->hasRole('superadmin') ||
        Auth::user()->hasRole('admin') ||
        Auth::user()->hasRole('salesmanager');

        $actionCounts = Auth::user()->getActionCounts();
        $totalOrdersAction = $actionCounts['retailer_orders'] + $actionCounts['distributor_orders'];
        @endphp

        @if ($hasOrderPerms || $hasApprovalRoles)
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-13">Orders</h6>
          </div>
        </li>
        @endif

        {{-- Retailer Orders (Standalone Link) --}}
        @if (Auth::user()->hasRole('retailer'))
        <li class="sidebar-list">
          <a class="sidebar-link sidebar-title link-nav" href="{{ route('retailer.orders.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg><span>My Orders</span>
          </a>
        </li>
        @endif

        {{-- Distributor Orders (Standalone Links) --}}
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
          </a>
        </li>
        @endif
        @if (Auth::user()->hasPermissionToCategory('staff_ratings', 'view'))
        <li class="sidebar-list">
          <a class="sidebar-link sidebar-title link-nav" href="{{ route('distributor.staff-ratings.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-user"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-user"></use>
            </svg><span>Staff Ratings</span>
          </a>
        </li>
        @endif
        @endif

        {{-- Common Orders Dropdown for Admin/Staff --}}
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
            {{-- Field Staff Role --}}
            @if (Auth::user()->hasRole('fieldstaff'))
            <li style="position: relative;"><a href="{{ route('fieldstaff.orders.index') }}">Order</a></li>
            @endif

            {{-- Admin / Sales Manager / SuperAdmin --}}
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

        {{-- Returns & Credits --}}
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
            <span style="display: inline-flex; align-items: center; gap: 8px;">Returns & Credits
                <span id="badge-pending-returns" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; {{ ($actionCounts['pending_returns'] ?? 0) > 0 ? '' : 'display: none !important;' }}">{{ $actionCounts['pending_returns'] ?? 0 }}</span>
            </span>
          </a>
        </li>
        @endif

        {{-- Order Approvals Dropdown --}}
        @if (Auth::user()->hasPermissionToCategory('retailer_approvals', 'view') || 
             Auth::user()->hasPermissionToCategory('distributor_approvals', 'view') || 
             Auth::user()->hasRole('fieldstaff') ||
             $hasApprovalRoles)
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-15">Approvals</h6>
          </div>
        </li>
        <li class="sidebar-list" style="position: relative;">
          @php $totalApprovals = $actionCounts['retailer_approvals'] + $actionCounts['distributor_approvals']; @endphp
          <a class="sidebar-link sidebar-title" id="approvals" href="javascript:void(0)">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-user"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-user"></use>
            </svg>
            <span style="display: inline-flex; align-items: center; gap: 8px;">Approvals
                <span id="badge-total-approvals" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; {{ $totalApprovals > 0 ? '' : 'display: none !important;' }}">{{ $totalApprovals }}</span>
            </span>
          </a>
          <ul class="sidebar-submenu">
             @if(Auth::user()->hasPermissionToCategory('retailer_approvals', 'view') || Auth::user()->hasRole('fieldstaff') || (Auth::user()->hasAnyRole(['admin', 'superadmin']) && !Auth::user()->hasRole('salesmanager')))
             <li style="position: relative;"><a href="{{ route('admin.approvals.retailer') }}">
               <span style="display: inline-flex; align-items: center; gap: 8px;">Retailers
                   <span id="badge-retailer-approvals" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; {{ $actionCounts['retailer_approvals'] > 0 ? '' : 'display: none !important;' }}">{{ $actionCounts['retailer_approvals'] }}</span>
               </span>
             </a></li>
            @endif
             @if(Auth::user()->hasPermissionToCategory('distributor_approvals', 'view') || $hasApprovalRoles)
             <li style="position: relative;"><a href="{{ route('admin.approvals.distributor') }}">
               <span style="display: inline-flex; align-items: center; gap: 8px;">Distributors
                   <span id="badge-distributor-approvals" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; {{ $actionCounts['distributor_approvals'] > 0 ? '' : 'display: none !important;' }}">{{ $actionCounts['distributor_approvals'] }}</span>
               </span>
             </a></li>
            @endif
          </ul>
        </li>

        {{-- Staff Approvals Dropdown --}}
        @if ($hasApprovalRoles)
        <li class="sidebar-list" style="position: relative;">
          @php $staffActionCounts = $actionCounts['staff_expenses'] + $actionCounts['staff_leaves']; @endphp
          <a class="sidebar-link sidebar-title" id="staff-approvals" href="javascript:void(0)">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-user"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-user"></use>
            </svg>
            <span style="display: inline-flex; align-items: center; gap: 8px;">Staff Approval
                <span id="badge-staff-approvals" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; {{ $staffActionCounts > 0 ? '' : 'display: none !important;' }}">{{ $staffActionCounts }}</span>
            </span>
          </a>
          <ul class="sidebar-submenu">
              <li><a href="{{ route('admin.field-staff.expenses') }}">
                <span style="display: inline-flex; align-items: center; gap: 8px;">Staff Expenses
                    <span id="badge-staff-expenses" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; {{ $actionCounts['staff_expenses'] > 0 ? '' : 'display: none !important;' }}">{{ $actionCounts['staff_expenses'] }}</span>
                </span>
              </a></li>
              <li><a href="{{ route('admin.field-staff.leaves') }}">
                <span style="display: inline-flex; align-items: center; gap: 8px;">Staff Leaves
                    <span id="badge-staff-leaves" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; {{ $actionCounts['staff_leaves'] > 0 ? '' : 'display: none !important;' }}">{{ $actionCounts['staff_leaves'] }}</span>
                </span>
              </a></li>
          </ul>
        </li>
        @endif


        @endif



        @if (Auth::user()->hasAnyRole(['admin', 'superadmin']) || Auth::user()->hasPermissionToCategory('products', 'view'))
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-12">Products</h6>
          </div>
        </li>
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
        @if(!Auth::user()->hasAnyRole(['admin', 'superadmin']))
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-15">Stock</h6>
          </div>
        </li>
        @endif
        <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="{{ route('inventories.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg><span>Stock</span></a>
        </li>
        @endif

        @if (
        Auth::user()->hasPermissionToCategory('districts', 'view') ||
        Auth::user()->hasPermissionToCategory('districts', 'add') ||
        Auth::user()->hasPermissionToCategory('areas', 'view') ||
        Auth::user()->hasPermissionToCategory('areas', 'add')
        )
        <!-- <li class="sidebar-main-title">
          <div>
            <h6 class="lan-14">Locations</h6>
          </div>
        </li>
        @endif


        @if (Auth::user()->hasPermissionToCategory('districts', 'view') || Auth::user()->hasPermissionToCategory('districts', 'add'))
        <li class="sidebar-list">
          
          <a class="sidebar-link sidebar-title link-nav" href="{{ route('districts.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg>
            <span>Districts</span>
          </a>
        </li>
        @endif

        @if (Auth::user()->hasPermissionToCategory('areas', 'view') || Auth::user()->hasPermissionToCategory('areas', 'add'))
        <li class="sidebar-list">
          
          <a class="sidebar-link sidebar-title link-nav" href="{{ route('areas.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg>
            <span>Areas</span>
          </a>
        </li>
        @endif -->

        @if (Auth::user()->hasPermissionToCategory('permissions', 'view') || Auth::user()->hasPermissionToCategory('sales_managers', 'view') || Auth::user()->hasPermissionToCategory('distributors', 'view') || Auth::user()->hasPermissionToCategory('field_staff', 'view') || Auth::user()->hasPermissionToCategory('retailers', 'view'))
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-11">User Management</h6>
          </div>
        </li>
        @endif

        @if (Auth::user()->hasPermissionToCategory('sales_managers', 'view'))
        <li class="sidebar-list">
          <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.sales-managers.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg>
            <span style="display: inline-flex; align-items: center; gap: 8px;">Sales Managers
                <span id="badge-inactive-sales-managers" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; {{ $actionCounts['inactive_sales_managers'] > 0 ? '' : 'display: none !important;' }}">{{ $actionCounts['inactive_sales_managers'] }}</span>
            </span>
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
            <span style="display: inline-flex; align-items: center; gap: 8px;">Distributors
                <span id="badge-inactive-distributors" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; {{ $actionCounts['inactive_distributors'] > 0 ? '' : 'display: none !important;' }}">{{ $actionCounts['inactive_distributors'] }}</span>
            </span>
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
            <span style="display: inline-flex; align-items: center; gap: 8px;">Field Staff
                <span id="badge-inactive-field-staff" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; {{ $actionCounts['inactive_field_staff'] > 0 ? '' : 'display: none !important;' }}">{{ $actionCounts['inactive_field_staff'] }}</span>
            </span>
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
            <span style="display: inline-flex; align-items: center; gap: 8px;">Retailers
                <span id="badge-inactive-retailers" class="sidebar-badge" style="padding: 2px 6px !important; font-size: 10px !important; font-weight: bold !important; color: #1e3a5f !important; background-color: rgba(255, 255, 255, 0.9) !important; border-radius: 12px !important; line-height: 1 !important; box-shadow: none !important; {{ $actionCounts['inactive_retailers'] > 0 ? '' : 'display: none !important;' }}">{{ $actionCounts['inactive_retailers'] }}</span>
            </span>
          </a>
        </li>
        @endif

        @if (Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('admin'))
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-10">Settings</h6>
          </div>
        </li>
        <li class="sidebar-list"><a class="sidebar-link sidebar-title -link-nav" href="javascript:void(0)">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-user"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-user"></use>
            </svg><span>Master Settings</span></a>
          <ul class="sidebar-submenu">
            @if (Auth::user()->hasPermissionToCategory('districts', 'view') || Auth::user()->hasPermissionToCategory('districts', 'add'))
            <li class="sidebar-list">
              
              <a class="sidebar-link sidebar-title link-nav" href="{{ route('districts.index') }}">
                <svg class="stroke-icon">
                  <use href="{{ $iconSprite }}#stroke-form"></use>
                </svg>
                <svg class="fill-icon">
                  <use href="{{ $iconSprite }}#fill-form"></use>
                </svg>
                <span>Districts</span>
              </a>
            </li>
            @endif

            @if (Auth::user()->hasPermissionToCategory('areas', 'view') || Auth::user()->hasPermissionToCategory('areas', 'add'))
            <li class="sidebar-list">
              
              <a class="sidebar-link sidebar-title link-nav" href="{{ route('areas.index') }}">
                <svg class="stroke-icon">
                  <use href="{{ $iconSprite }}#stroke-form"></use>
                </svg>
                <svg class="fill-icon">
                  <use href="{{ $iconSprite }}#fill-form"></use>
                </svg>
                <span>Areas</span>
              </a>
            </li>
            @endif


            <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.permissions.index') }}">
                <svg class="stroke-icon">
                  <use href="{{ $iconSprite }}#stroke-user"></use>
                </svg>
                <svg class="fill-icon">
                  <use href="{{ $iconSprite }}#fill-user"></use>
                </svg><span>Roles</span></a>
            </li>


            {{-- General settings page --}}
            <li class="sidebar-list">
              <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.settings.general') }}">
                <svg class="stroke-icon">
                  <use href="{{ $iconSprite }}#stroke-form"></use>
                </svg>
                <span>System Config</span>
              </a>
            </li>
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