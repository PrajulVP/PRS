<!-- Page Sidebar Start-->
<div class="sidebar-wrapper" data-layout="stroke-svg">
  <div class="logo-wrapper"><a href="{{ route('dashboard') }}"><img class="img-fluid" src="{{ asset('admin/assets/images/logo/logo.png') }}" alt=""></a>
    <div class="back-btn"><i class="fa fa-angle-left"> </i></div>
    <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i></div>
  </div>
  <div class="logo-icon-wrapper"><a href="{{ route('dashboard') }}"><img class="img-fluid" src="{{ asset('admin/assets/images/logo/logo-icon.png') }}" alt=""></a></div>
  <nav class="sidebar-main">
    <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
    <div id="sidebar-menu">
      <ul class="sidebar-links" id="simple-bar">
        <li class="back-btn"><a href="index.html"><img class="img-fluid" src="../../admin/assets/images/logo/logo-icon.png" alt=""></a>
          <div class="mobile-back text-end"> <span>Back </span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
        </li>
        <li class="pin-title sidebar-main-title">
          <div>
            <h6>Pinned</h6>
          </div>
        </li>
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-1">General</h6>
          </div>
        </li>
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="{{ route('dashboard') }}">
            <svg class="stroke-icon">
              <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-home') }}"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-home') }}"></use>
            </svg><span>Dashboard</span></a>
        </li>

        @if (Auth::user()->hasPermissionToCategory('permissions', 'view') || Auth::user()->hasPermissionToCategory('sales_managers', 'view') || Auth::user()->hasPermissionToCategory('distributors', 'view') || Auth::user()->hasPermissionToCategory('field_staff', 'view') || Auth::user()->hasPermissionToCategory('retailers', 'view'))
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-10">User Management</h6>
          </div>
          @endif

          @if (Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('admin'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.permissions.index') }}">
            <svg class="stroke-icon">
              <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-home') }}"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-home') }}"></use>
            </svg><span>Manage Permissions</span></a>
        </li>
        @endif

        @if (Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('admin') || Auth::user()->hasRole('salesmanager'))
        <li class="sidebar-list">

          <a class="sidebar-link sidebar-title link-nav d-flex justify-content-between align-items-center 
                  {{ request()->routeIs('*pending*') ? 'active' : '' }}"
            href="{{ route('pending-approvals') }}" style="color:#333;">

            <div class="d-flex align-items-center">
              <svg class="stroke-icon me-2">
                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-user') }}"></use>
              </svg>

              <svg class="fill-icon me-2">
                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-user') }}"></use>
              </svg>

              <span class="text-truncate" style="max-width: calc(100% - 40px);">
                Pending Approval
              </span>
            </div>
          </a>
        </li>
        @endif


        @if (Auth::user()->hasPermissionToCategory('sales_managers', 'view'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg><span>Sales Managers</span></a>
          <ul class="sidebar-submenu">
            <li><a href="{{ route('admin.salesmanagers.index') }}">Sales Managers List</a></li>
            @if (Auth::user()->hasPermissionToCategory('sales_managers', 'add'))
            <li><a href="{{ route('admin.salesmanagers.create') }}">Create Sales Managers</a></li>
            @endif
          </ul>
        </li>
        @endif

        @if (Auth::user()->hasPermissionToCategory('distributors', 'view'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg><span>Distributors</span></a>
          <ul class="sidebar-submenu">
            <li><a href="{{ route('admin.distributors.index') }}">Distributors List</a></li>
            @if (Auth::user()->hasPermissionToCategory('distributors', 'add'))
            <li><a href="{{ route('admin.distributors.create') }}">Create Distributors</a></li>
            @endif
          </ul>
        </li>
        @endif

        @if (Auth::user()->hasPermissionToCategory('field_staff', 'view'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg><span>Field Staff</span></a>
          <ul class="sidebar-submenu">
            <li><a href="{{ route('admin.fieldstaffs.index') }}">Field Staff List</a></li>
            @if (Auth::user()->hasPermissionToCategory('field_staff', 'add'))
            <li><a href="{{ route('admin.fieldstaffs.create') }}">Create Field Staff</a></li>
            @endif
          </ul>
        </li>
        @endif

        @if (Auth::user()->hasPermissionToCategory('retailers', 'view'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg><span>Retailer (Chemist)</span></a>
          <ul class="sidebar-submenu">
            <li><a href="{{ route('admin.retailers.index') }}">Retailers List</a></li>
            @if (Auth::user()->hasPermissionToCategory('retailers', 'add'))
            <li><a href="{{ route('admin.retailers.create') }}">Create Retailers</a></li>
            @endif
          </ul>
        </li>
        @endif

        @if (
        Auth::user()->hasPermissionToCategory('districts', 'view') ||
        Auth::user()->hasPermissionToCategory('districts', 'add') ||
        Auth::user()->hasPermissionToCategory('areas', 'view') ||
        Auth::user()->hasPermissionToCategory('areas', 'add')
        )
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-10">Locations</h6>
          </div>
        </li>
        @endif


        @if (
        Auth::user()->hasPermissionToCategory('districts', 'view') ||
        Auth::user()->hasPermissionToCategory('districts', 'add')
        )
        <li class="sidebar-list">
          <i class="fa fa-thumb-tack"></i>
          <a class="sidebar-link sidebar-title link-nav"
            href="{{ route('districts.index') }}">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg>
            <span>Districts</span>
          </a>
        </li>
        @endif


        @if (
        Auth::user()->hasPermissionToCategory('areas', 'view') ||
        Auth::user()->hasPermissionToCategory('areas', 'add')
        )
        <li class="sidebar-list">
          <i class="fa fa-thumb-tack"></i>
          <a class="sidebar-link sidebar-title link-nav"
            href="{{ route('areas.index') }}">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg>
            <span>Areas</span>
          </a>
        </li>
        @endif


        @if (Auth::user()->hasPermissionToCategory('products', 'view'))
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-10">Products</h6>
          </div>
        </li>
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg><span>Products</span></a>
          <ul class="sidebar-submenu">
            <li><a href="{{ route('products.index') }}">Product List</a></li>
            @if (Auth::user()->hasPermissionToCategory('products', 'add'))
            <li><a href="{{ route('products.create') }}">Create Product</a></li>
            @endif
          </ul>
        </li>
        @endif

        @if (Auth::user()->hasPermissionToCategory('retailer_orders', 'view') || Auth::user()->hasPermissionToCategory('distributor_orders', 'view'))
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-10">Orders</h6>
          </div>
        </li>

        @if (Auth::user()->hasRole('retailer'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg><span>Orders</span></a>
          <ul class="sidebar-submenu">
            <li><a href="{{ route('retailer.orders.index') }}">Order List</a></li>
            @if (Auth::user()->hasPermissionToCategory('retailer_orders', 'add'))
            <li><a href="{{ route('retailer.orders.create') }}">Create Order</a></li>
            @endif
          </ul>
        </li>

        @if (!Auth::user()->hasRole('retailer') && Auth::user()->hasPermissionToCategory('retailer_orders', 'view'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg><span>Retailer Orders</span></a>
          <ul class="sidebar-submenu">
            <li><a href="{{ route('retailer-orders-management.index') }}">Order List</a></li>

          </ul>
        </li>
        @endif
        @elseif (Auth::user()->hasRole('distributor'))
        @if (Auth::user()->hasPermissionToCategory('retailer_orders', 'view'))
        <li class="sidebar-list">
          <a class="sidebar-link" href="{{ route('distributor.orders.index') }}">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <span>Received Orders</span>
          </a>
        </li>
        @endif

        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg><span>My Orders</span></a>
          <ul class="sidebar-submenu">
            @if (Auth::user()->hasPermissionToCategory('distributor_orders', 'view'))
            <li><a href="{{ route('distributor-orders.index') }}">My Orders</a></li>
            @endif
            @if (Auth::user()->hasPermissionToCategory('distributor_orders', 'add'))
            <li><a href="{{ route('distributor-orders.create') }}">Create Order</a></li>
            @endif
          </ul>
        </li>
        @else {{-- Superadmin or Admin or Sales Manager --}}
        @if (Auth::user()->hasPermissionToCategory('retailer_orders', 'view'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg><span>Retailer Orders</span></a>
          <ul class="sidebar-submenu">
            <li><a href="{{ route('retailer-orders-management.index') }}">Order List</a></li>

          </ul>
        </li>
        @endif

        @if (Auth::user()->hasPermissionToCategory('distributor_orders', 'view'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg><span>Distributor Orders</span></a>
          <ul class="sidebar-submenu">
            <li><a href="{{ route('distributor-orders.index') }}">Order List</a></li>
          </ul>
        </li>
        @endif
        @endif
        @endif

      </ul>
      <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
    </div>
  </nav>
</div>
<!-- Page Sidebar Ends-->