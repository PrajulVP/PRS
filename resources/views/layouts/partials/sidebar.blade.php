<!-- Page Sidebar Start-->
<div class="sidebar-wrapper bg-gradient-pb-2" data-layout="stroke-svg">
  <div class="logo-wrapper"><a href="{{ route('dashboard') }}"><img class="img-fluid" src="{{ asset('admin/assets/images/logo/favicon.ico') }}" width="120" alt=""></a>
    <div class="back-btn"><i class="fa fa-angle-left"> </i></div>
    <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i></div>
  </div>
  <div class="logo-icon-wrapper"><a href="{{ route('dashboard') }}"><img class="img-fluid" src="{{ asset('admin/assets/images/logo/atom-logo.webp') }}" alt=""></a></div>
  <nav class="sidebar-main">
    <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
    <div id="sidebar-menu">
      <ul class="sidebar-links" id="simple-bar">
        <li class="back-btn"><a href="index.html"><img class="img-fluid" src="{{ asset('admin/assets/images/logo/atom-logo.webp') }}" alt=""></a>
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

        @php
        $iconSprite = asset('admin/assets/svg/icon-sprite.svg');
        @endphp

        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="{{ route('dashboard') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-home"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-home"></use>
            </svg><span>Dashboard</span></a>
        </li>

        @php
        $hasOrderPerms = Auth::user()->hasPermissionToCategory('retailer_orders', 'view') ||
        Auth::user()->hasPermissionToCategory('distributor_orders', 'view') ||
        Auth::user()->hasRole('retailer') ||
        Auth::user()->hasRole('distributor');

        $hasApprovalRoles = Auth::user()->hasRole('superadmin') ||
        Auth::user()->hasRole('admin') ||
        Auth::user()->hasRole('salesmanager');
        @endphp

        @if ($hasOrderPerms || $hasApprovalRoles)
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-13">Orders</h6>
          </div>
        </li>
        @endif

        @if ($hasOrderPerms)
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
          <a class="sidebar-link sidebar-title" id="orders" href="#orders">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg><span>Orders</span>
          </a>
          <ul class="sidebar-submenu">
            {{-- Retailer Role: My Orders --}}
            @if (Auth::user()->hasRole('retailer'))
            <li><a href="{{ route('retailer.orders.index') }}">My Orders</a></li>
            @endif

            <!-- {{-- Distributor Role --}}
            @if (Auth::user()->hasRole('distributor'))
            @if (Auth::user()->hasPermissionToCategory('retailer_orders', 'view'))
            <li><a href="{{ route('distributor.orders.index') }}">Received Orders</a></li>
            @endif
            @if (Auth::user()->hasPermissionToCategory('distributor_orders', 'view'))
            <li><a href="{{ route('admin.distributor-orders.index') }}">My Orders</a></li>
            @endif
            @endif -->

            @if (!Auth::user()->hasRole('retailer') || !Auth::user()->hasRole('distributor'))
            @if (Auth::user()->hasPermissionToCategory('retailer_orders', 'view'))
            <li><a href="{{ route('admin.retailer-orders.index') }}">Retailer Orders</a></li>
            @endif
            @if (Auth::user()->hasPermissionToCategory('distributor_orders', 'view'))
            <li><a href="{{ route('admin.distributor-orders.index') }}">Distributor Orders</a></li>
            @endif
            @endif
          </ul>
        </li>
        @endif

        @if ($hasApprovalRoles)
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
          <a class="sidebar-link sidebar-title" id="approvals" href="#approvals">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-user"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-user"></use>
            </svg><span>Approvals</span>
          </a>
          <ul class="sidebar-submenu">
            <li><a href="{{ route('approvals.retailer') }}">Retailers</a></li>
            <li><a href="{{ route('approvals.distributor') }}">Distributors</a></li>
          </ul>
        </li>
        @endif

        @if (Auth::user()->hasPermissionToCategory('products', 'view'))
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-12">Products</h6>
          </div>
        </li>
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="{{ route('products.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg><span>Products</span></a>
        </li>
        <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="{{ route('inventories.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg><span>Inventory</span></a>
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
          <i class="fa fa-thumb-tack"></i>
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
          <i class="fa fa-thumb-tack"></i>
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
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
          <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.sales-managers.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg><span>Sales Managers</span></a>
        </li>
        @endif

        @if (Auth::user()->hasPermissionToCategory('distributors', 'view'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
          <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.distributors.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg><span>Distributors</span></a>
        </li>
        @endif

        @if (Auth::user()->hasPermissionToCategory('field_staff', 'view'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
          <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.field-staffs.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg><span>Field Staff</span></a>
        </li>
        @endif

        @if (Auth::user()->hasPermissionToCategory('retailers', 'view'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
          <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.retailers.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg><span>Retailers</span></a>
        </li>
        @endif

        @if (Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('admin'))
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-10">Settings</h6>
          </div>
        </li>
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title -link-nav" href="#">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-user"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-user"></use>
            </svg><span>Master Settings</span></a>
          <ul class="sidebar-submenu">
            @if (Auth::user()->hasPermissionToCategory('districts', 'view') || Auth::user()->hasPermissionToCategory('districts', 'add'))
            <li class="sidebar-list">
              <i class="fa fa-thumb-tack"></i>
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
              <i class="fa fa-thumb-tack"></i>
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


            <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.permissions.index') }}">
                <svg class="stroke-icon">
                  <use href="{{ $iconSprite }}#stroke-user"></use>
                </svg>
                <svg class="fill-icon">
                  <use href="{{ $iconSprite }}#fill-user"></use>
                </svg><span>Roles</span></a>
            </li>


            {{-- General settings page --}}
            <li class="sidebar-list">
              <!-- <i class="fa fa-cog"></i> -->
              <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.settings.general') }}">
                <svg class="stroke-icon">
                  <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
                </svg>
                <!-- <svg class="fill-icon">
                  <use href="../../admin/assets/svg/icon-sprite.svg#fill-settings"></use>
                </svg> -->
                <span>General</span>
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