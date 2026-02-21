<!-- Page Sidebar Start-->
<div class="sidebar-wrapper medical-theme-sidebar" data-layout="stroke-svg">
  <div class="logo-wrapper"><a href="{{ route('dashboard') }}"><img class="img-fluid" src="{{ asset('admin/assets/images/logo/atom-logo-main-white.png') }}" width="150" alt=""></a>
    <div class="back-btn"><i class="fa fa-angle-left"> </i></div>
    <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i></div>
  </div>
  <div class="logo-icon-wrapper"><a href="{{ route('dashboard') }}"><img class="img-fluid" src="{{ asset('admin/assets/images/logo/atom-logo-main-white.png') }}" width="150" alt=""></a></div>
  <nav class="sidebar-main">
    <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
    <div id="sidebar-menu">
      <ul class="sidebar-links" id="simple-bar">
        <li class="back-btn"><a href="{{ route('dashboard') }}"><img class="img-fluid" src="{{ asset('admin/assets/images/logo/atom-logo-main-white.png') }}" width="150" alt=""></a>
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

        <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="{{ route('dashboard') }}">
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

        @if ($hasOrderPerms)
        <li class="sidebar-list" style="position: relative;">
          <a class="sidebar-link sidebar-title" id="orders" href="#orders">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-form"></use>
            </svg><span>Orders</span>
          </a>
          <ul class="sidebar-submenu">
            {{-- Retailer Role --}}
            @if (Auth::user()->hasRole('retailer'))
              <li style="position: relative;"><a href="{{ route('retailer.orders.index') }}">My Orders</a></li>
            @endif

            {{-- Distributor Role --}}
            @if (Auth::user()->hasRole('distributor'))
            <li style="position: relative;"><a href="{{ route('distributor.orders.index') }}">Received Orders</a></li>

            @if (Auth::user()->hasPermissionToCategory('distributor_orders', 'view'))
            <li><a href="{{ route('admin.distributor-orders.index') }}">My Orders</a></li>
            @endif
            @endif

            {{-- Field Staff Role --}}
            @if (Auth::user()->hasRole('fieldstaff'))
            <li style="position: relative;"><a href="{{ route('fieldstaff.orders.index') }}">Orders</a></li>
            @endif

            {{-- Admin / Sales Manager / SuperAdmin --}}
            @if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('salesmanager'))
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

        @if (Auth::user()->hasPermissionToCategory('retailer_approvals', 'view') || 
             Auth::user()->hasPermissionToCategory('distributor_approvals', 'view') || 
             $hasApprovalRoles)
        <li class="sidebar-list" style="position: relative;">
          @php $totalApprovals = $actionCounts['retailer_approvals'] + $actionCounts['distributor_approvals']; @endphp
          <a class="sidebar-link sidebar-title" id="approvals" href="#approvals">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-user"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-user"></use>
            </svg><span>Approvals</span>
          </a>
          <ul class="sidebar-submenu">
             @if(Auth::user()->hasPermissionToCategory('retailer_approvals', 'view') || $hasApprovalRoles)
             <li style="position: relative;"><a href="{{ route('approvals.retailer') }}">Retailers</a></li>
            @endif
             @if(Auth::user()->hasPermissionToCategory('distributor_approvals', 'view') || $hasApprovalRoles)
             <li style="position: relative;"><a href="{{ route('approvals.distributor') }}">Distributors</a></li>
            @endif
          </ul>
        </li>
        @endif
        
        @if (Auth::user()->hasPermissionToCategory('loyalty_points', 'view') || Auth::user()->hasRole('retailer'))
        <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.loyalty-points.index') }}">
            <svg class="stroke-icon">
              <use href="{{ $iconSprite }}#stroke-task"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ $iconSprite }}#fill-task"></use>
            </svg><span>Loyalty Points</span></a>
        </li>
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
        
        @if (Auth::user()->hasPermissionToCategory('inventory', 'view'))
        @if(!Auth::user()->hasAnyRole(['admin', 'superadmin']))
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-15">Inventory</h6>
          </div>
        </li>
        @endif
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
            </svg><span>Sales Managers</span></a>
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
            </svg><span>Distributors</span></a>
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
            </svg><span>Field Staff</span></a>
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
            </svg><span>Retailers</span></a>
        </li>
        @endif

        @if (Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('admin'))
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-10">Settings</h6>
          </div>
        </li>
        <li class="sidebar-list"><a class="sidebar-link sidebar-title -link-nav" href="#">
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
            {{-- <li class="sidebar-list">
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
            </li> --}}
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
    /* Light Sea Green to Dark Emerald/Teal */
  }

  /* Ensure logo wrapper allows gradient to show through */
  .medical-theme-sidebar .logo-wrapper,
  .medical-theme-sidebar .logo-icon-wrapper {
    background: transparent !important;
  }

  /* Decrease the line height and spacing of modules inside the navbar */
  .sidebar-wrapper .sidebar-list {
    margin-bottom: 4px !important;
  }
  
  .sidebar-wrapper .sidebar-main .sidebar-links .sidebar-list .sidebar-link {
    padding-top: 8px !important;
    padding-bottom: 8px !important;
    display: flex !important;
    align-items: center !important;
  }

  .sidebar-wrapper .sidebar-main .sidebar-links .sidebar-list .sidebar-link svg {
    margin: 0 12px 0 0 !important;
    position: relative !important;
    top: 3px !important; /* Push icon firmly down to optical center */
  }

  .sidebar-wrapper .sidebar-main .sidebar-links .sidebar-list .sidebar-link span {
    margin: 0 !important;
    position: relative !important;
    top: 0px !important;
    line-height: normal !important;
  }

  .sidebar-wrapper .sidebar-main-title {
    padding-top: 8px !important;
    padding-bottom: 4px !important;
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