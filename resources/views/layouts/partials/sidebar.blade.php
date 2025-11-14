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

        @if(Auth::user()->hasPermissionTo('view permissions') || Auth::user()->hasPermissionTo('view managers') || Auth::user()->hasPermissionTo('view distributors') || Auth::user()->hasPermissionTo('view field_staff') || Auth::user()->hasPermissionTo('view retailers'))
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-10">Users</h6>
          </div>
        </li>
        @endif

        @if(Auth::user()->hasPermissionTo('view permissions'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.permissions.index') }}">
            <svg class="stroke-icon">
              <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-home') }}"></use>
            </svg>
            <svg class="fill-icon">
              <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-home') }}"></use>
            </svg><span>Manage Permissions</span></a>
        </li>
        @endif

        @if(Auth::user()->hasPermissionTo('view managers'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg><span>Managers</span></a>
          <ul class="sidebar-submenu">
            <li><a href="{{ route('managers.index') }}">Managers List</a></li>
            @if(Auth::user()->hasPermissionTo('add managers'))
            <li><a href="{{ route('managers.create') }}">Create Managers</a></li>
            @endif
          </ul>
        </li>
        @endif

        @if(Auth::user()->hasPermissionTo('view distributors'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg><span>Distributors</span></a>
          <ul class="sidebar-submenu">
            <li><a href="{{ route('distributors.index') }}">Distributors List</a></li>
            @if(Auth::user()->hasPermissionTo('create distributors'))
            <li><a href="{{ route('distributors.create') }}">Create Distributors</a></li>
            @endif
          </ul>
        </li>
        @endif

        @if(Auth::user()->hasPermissionTo('view field_staff'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg><span>Field Staff</span></a>
          <ul class="sidebar-submenu">           
            <li><a href="{{ route('fieldstaffs.index') }}">Field Staff List</a></li>
            @if(Auth::user()->hasPermissionTo('add field_staff'))
            <li><a href="{{ route('fieldstaffs.create') }}">Create Field Staff</a></li>
            @endif
          </ul>
        </li>
        @endif

        @if(Auth::user()->hasPermissionTo('view retailers'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg><span>Retailer (Chemist)</span></a>
          <ul class="sidebar-submenu">
            <li><a href="{{ route('retailers.index') }}">Retailers List</a></li>
            @if(Auth::user()->hasPermissionTo('create retailers'))
            <li><a href="{{ route('retailers.create') }}">Create Retailers</a></li>
            @endif
          </ul>
        </li>
        @endif

        @if(Auth::user()->hasPermissionTo('view districts') || Auth::user()->hasPermissionTo('view areas'))
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-10">Regions & Area</h6>
          </div>
        </li>
        @endif

        @if(Auth::user()->hasPermissionTo('view districts'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg><span>Districts</span></a>
          <ul class="sidebar-submenu">
            <li><a href="{{ route('districts.index') }}">District List</a></li>
            @if(Auth::user()->hasPermissionTo('add districts'))
            <li><a href="{{ route('districts.create') }}">Create District</a></li>
            @endif
          </ul>
        </li>
        @endif

        @if(Auth::user()->hasPermissionTo('view areas'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg><span>Areas</span></a>
          <ul class="sidebar-submenu">
            <li><a href="{{ route('areas.index') }}">Area List</a></li>
            @if(Auth::user()->hasPermissionTo('add areas'))
            <li><a href="{{ route('areas.create') }}">Create Area</a></li>
            @endif
          </ul>
        </li>
        @endif

        @if(Auth::user()->hasPermissionTo('view products'))
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
            @if(Auth::user()->hasPermissionTo('create products'))
            <li><a href="{{ route('products.create') }}">Create Product</a></li>
            @endif
          </ul>
        </li>
        @endif

        @if(Auth::user()->hasPermissionTo('view retailer_orders') || Auth::user()->hasPermissionTo('view distributor_orders'))
        <li class="sidebar-main-title">
          <div>
            <h6 class="lan-10">Orders</h6>
          </div>
        </li>
        @endif
        
        @if(Auth::user()->hasPermissionTo('view retailer_orders'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg><span>Retailer Orders</span></a>
          <ul class="sidebar-submenu">
            <li><a href="{{ route('retailer-orders-management.index') }}">Order List</a></li>
            @if(Auth::user()->hasPermissionTo('add retailer_orders'))
            <li><a href="{{ route('retailer-orders-management.create') }}">Create Order</a></li>
            @endif
          </ul>
        </li>
        @endif

        @if(Auth::user()->hasPermissionTo('view distributor_orders'))
        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
            <svg class="stroke-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
            </svg>
            <svg class="fill-icon">
              <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
            </svg><span>Distributor Orders</span></a>
          <ul class="sidebar-submenu">
            <li><a href="{{ route('distributor-bulk-orders.index') }}">Order List</a></li>
            @if(Auth::user()->hasPermissionTo('add distributor_bulk_orders'))
            <li><a href="{{ route('distributor-bulk-orders.create') }}">Create Order</a></li>
            @endif
          </ul>
        </li>
        @endif

      </ul>
      <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
    </div>
  </nav>
</div>
<!-- Page Sidebar Ends-->