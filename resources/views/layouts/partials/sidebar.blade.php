<div class="sidebar-wrapper bg-gradient-pb" data-layout="stroke-svg">
    <div class="logo-wrapper">
        <a href="{{ route('dashboard') }}"><img class="img-fluid" src="{{ asset('admin/assets/images/logo/logo.png') }}" alt=""></a>
        <div class="back-btn"><i class="fa fa-angle-left"></i></div>
        <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"></i></div>
    </div>
    <div class="logo-icon-wrapper">
        <a href="{{ route('dashboard') }}"><img class="img-fluid" src="{{ asset('admin/assets/images/logo/logo-icon.png') }}" alt=""></a>
    </div>
    <nav class="sidebar-main">
            <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
            <div id="sidebar-menu">
              <ul class="sidebar-links" id="simple-bar">
                <li class="back-btn"><a href="index.html"><img class="img-fluid" src="admin/assets/images/logo/logo-icon.png" alt=""></a>
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
                <li class="sidebar-list"><i class="fa fa-thumb-tack"> </i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon">
                      <use href="admin/assets/svg/icon-sprite.svg#stroke-home"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="admin/assets/svg/icon-sprite.svg#fill-home"></use>
                    </svg><span class="lan-3">Dashboard</span></a>
                  <ul class="sidebar-submenu">
                    <li><a href="index.html">Default</a></li>
                    <li><a href="dashboard-02.html">Ecommerce</a></li>
                    <li><a href="dashboard-03.html">Project</a></li>
                  </ul>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon"> 
                      <use href="admin/assets/svg/icon-sprite.svg#stroke-widget"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="admin/assets/svg/icon-sprite.svg#fill-widget"></use>
                    </svg><span class="lan-6">Widgets</span></a>
                  <ul class="sidebar-submenu">
                    <li><a href="general-widget.html">General</a></li>
                    <li><a href="chart-widget.html">Chart</a></li>
                  </ul>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon">
                      <use href="admin/assets/svg/icon-sprite.svg#stroke-layout"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="admin/assets/svg/icon-sprite.svg#fill-layout"></use>
                    </svg><span class="lan-7">Page layout</span></a>
                  <ul class="sidebar-submenu">
                    <li><a href="box-layout.html">Boxed</a></li>
                    <li><a href="layout-rtl.html">RTL</a></li>
                    <li><a href="layout-dark.html">Dark Layout</a></li>
                    <li> <a href="hide-on-scroll.html">Hide Nav Scroll</a></li>
                  </ul>
                </li>
                <li class="sidebar-main-title">
                  <div>
                    <h6 class="lan-8">Applications</h6>
                  </div>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon">
                      <use href="admin/assets/svg/icon-sprite.svg#stroke-project"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="admin/assets/svg/icon-sprite.svg#fill-project"></use>
                    </svg><span>Project           </span></a>
                  <ul class="sidebar-submenu">
                    <li><a href="projects.html">Project List</a></li>
                    <li><a href="projectcreate.html">Create new</a></li>
                  </ul>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon">
                      <use href="admin/assets/svg/icon-sprite.svg#stroke-chat"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="admin/assets/svg/icon-sprite.svg#fill-chat"></use>
                    </svg><span>Chat</span></a>
                  <ul class="sidebar-submenu">
                    <li><a href="private-chat.html">Private Chat</a></li>
                    <li> <a href="group-chat.html">Group Chat</a></li>
                  </ul>
                </li>
                <li class="sidebar-list"> <i class="fa fa-thumb-tack"> </i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon"> 
                      <use href="admin/assets/svg/icon-sprite.svg#stroke-user"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="admin/assets/svg/icon-sprite.svg#fill-user"></use>
                    </svg><span>Users</span></a>
                  <ul class="sidebar-submenu">
                    <li><a href="user-profile.html">Users Profile</a></li>
                    <li><a href="edit-profile.html">Users Edit</a></li>
                    <li><a href="user-cards.html">Users Cards</a></li>
                  </ul>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="bookmark.html">
                    <svg class="stroke-icon">
                      <use href="admin/assets/svg/icon-sprite.svg#stroke-bookmark"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="admin/assets/svg/icon-sprite.svg#fill-bookmark"> </use>
                    </svg><span>Bookmarks</span></a></li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="contacts.html">
                    <svg class="stroke-icon">
                      <use href="admin/assets/svg/icon-sprite.svg#stroke-contact"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="admin/assets/svg/icon-sprite.svg#fill-contact"> </use>
                    </svg><span>Contacts</span></a></li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="search.html">
                    <svg class="stroke-icon">
                      <use href="admin/assets/svg/icon-sprite.svg#stroke-search"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="admin/assets/svg/icon-sprite.svg#fill-search"> </use>
                    </svg><span>Search Result</span></a></li>
                <li class="sidebar-main-title"> 
                  <div>
                    <h6>Forms & Table</h6>
                  </div>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon"> 
                      <use href="admin/assets/svg/icon-sprite.svg#stroke-form"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="admin/assets/svg/icon-sprite.svg#fill-form"> </use>
                    </svg><span>Forms</span></a>
                  <ul class="sidebar-submenu">
                    <li> <a class="submenu-title" href="#">Form Controls <span class="sub-arrow"> <i class="fa fa-angle-right"></i></span></a>
                      <ul class="nav-sub-childmenu submenu-content">
                        <li><a href="form-validation.html">Form Validation</a></li>
                        <li><a href="base-input.html">Base Inputs</a></li>
                        <li><a href="radio-checkbox-control.html">Checkbox & Radio</a></li>
                        <li><a href="input-group.html">Input Groups</a></li>
                        <li> <a href="input-mask.html">Input Mask</a></li>
                        <li><a href="megaoptions.html">Mega Options</a></li>
                      </ul>
                    </li>
                    <li><a class="submenu-title" href="#">
                         Form Widgets<span class="sub-arrow"><i class="fa fa-angle-right"></i></span></a>
                      <ul class="nav-sub-childmenu submenu-content">
                        <li><a href="datepicker.html">Datepicker</a></li>
                        <li><a href="touchspin.html">Touchspin</a></li>
                        <li><a href="select2.html">Select2</a></li>
                        <li><a href="switch.html">Switch</a></li>
                        <li><a href="typeahead.html">Typeahead</a></li>
                        <li><a href="clipboard.html">Clipboard</a></li>
                      </ul>
                    </li>
                    <li><a class="submenu-title" href="#">Form layout<span class="sub-arrow"><i class="fa fa-angle-right"></i></span></a>
                      <ul class="nav-sub-childmenu submenu-content">
                        <li><a href="form-wizard.html">Form Wizard 1</a></li>
                        <li><a href="form-wizard-two.html">Form Wizard 2</a></li>
                        <li><a href="two-factor.html">Two Factor</a></li>
                      </ul>
                    </li>
                  </ul>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon">
                      <use href="admin/assets/svg/icon-sprite.svg#stroke-table"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="admin/assets/svg/icon-sprite.svg#fill-table"></use>
                    </svg><span>Tables</span></a>
                  <ul class="sidebar-submenu">
                    <li><a class="submenu-title" href="#">Bootstrap Tables<span class="sub-arrow"><i class="fa fa-angle-right"></i></span></a>
                      <ul class="nav-sub-childmenu submenu-content">
                        <li><a href="bootstrap-basic-table.html">Basic Tables</a></li>
                        <li><a href="table-components.html">Table components</a></li>
                      </ul>
                    </li>
                    <li><a class="submenu-title" href="#">Data Tables<span class="sub-arrow"><i class="fa fa-angle-right"></i></span></a>
                      <ul class="nav-sub-childmenu submenu-content">
                        <li><a href="datatable-basic-init.html">Basic Init</a></li>
                        <li> <a href="datatable-advance.html">Advance Init </a></li>
                        <li> <a href="datatable-API.html">API </a></li>
                        <li><a href="datatable-data-source.html">Data Sources</a></li>
                      </ul>
                    </li>
                    <li><a href="datatable-ext-autofill.html">Ex. Data Tables</a></li>
                    <li><a href="jsgrid-table.html">Js Grid Table        </a></li>
                  </ul>
                </li>
               
              <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
            </div>
          </nav>
</div>
