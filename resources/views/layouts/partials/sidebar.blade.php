<!-- Page Sidebar Start-->
        <div class="sidebar-wrapper" data-layout="stroke-svg">
          <div class="logo-wrapper"><a href="index.html"><img class="img-fluid" src="../../admin/assets/images/logo/logo.png" alt=""></a>
            <div class="back-btn"><i class="fa fa-angle-left"> </i></div>
            <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i></div>
          </div>
          <div class="logo-icon-wrapper"><a href="index.html"><img class="img-fluid" src="../../admin/assets/images/logo/logo-icon.png" alt=""></a></div>
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
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon"> 
                      <use href="../../admin/assets/svg/icon-sprite.svg#stroke-widget"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#fill-widget"></use>
                    </svg><span class="lan-6">Widgets</span></a>
                  <ul class="sidebar-submenu">
                    <li><a href="general-widget.html">General</a></li>
                    <li><a href="chart-widget.html">Chart</a></li>
                  </ul>
                </li>
                <li class="sidebar-main-title">
                  <div>
                    <h6 class="lan-10">User Management</h6>
                  </div>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
                    </svg><span>Distributors</span></a>
                  <ul class="sidebar-submenu">
                    <li><a href="{{ route('distributors.index') }}">Distributors List</a></li>
                    <li><a href="{{ route('distributors.create') }}">Create Distributors</a></li>
                  </ul>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
                    </svg><span>Chemists</span></a>
                  <ul class="sidebar-submenu">
                    <li><a href="{{ route('chemists.index') }}">Chemists List</a></li>
                    <li><a href="{{ route('chemists.create') }}">Create chemists</a></li>
                  </ul>
                </li>
                <li class="sidebar-main-title">
                  <div>
                    <h6 class="lan-10">Regions & Areas</h6>
                  </div>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
                    </svg><span>Districts</span></a>
                  <ul class="sidebar-submenu">
                    <li><a href="{{ route('districts.index') }}">District List</a></li>
                    <li><a href="{{ route('districts.create') }}">Create District</a></li>
                  </ul>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"></use>
                    </svg><span>Areas</span></a>
                  <ul class="sidebar-submenu">
                    <li><a href="{{ route('areas.index') }}">Area List</a></li>
                    <li><a href="{{ route('areas.create') }}">Create Area</a></li>
                  </ul>
                </li>
                <li class="sidebar-main-title">
                  <div>
                    <h6 class="lan-8">Applications</h6>
                  </div>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="file-manager.html">
                    <svg class="stroke-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#stroke-file"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#fill-file"></use>
                    </svg><span>File manager</span></a></li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#stroke-ecommerce"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#fill-ecommerce"></use>
                    </svg><span>Ecommerce</span></a>
                  <ul class="sidebar-submenu">
                    <li> <a href="add-products.html">Add Products</a></li>
                    <li><a href="product.html">Product</a></li>
                    <li><a href="category.html">Category page</a></li>
                    <li><a href="product-page.html">Product page</a></li>
                    <li><a href="list-products.html">Product list</a></li>
                    <li><a href="payment-details.html">Payment Details</a></li>
                    <li><a href="order-history.html">Order History</a></li>
                    <li><a class="submenu-title" href="#">Invoices<span class="sub-arrow"><i class="fa fa-angle-right"></i></span></a>
                      <ul class="nav-sub-childmenu submenu-content">
                        <li><a href="invoice-1.html">Invoice-1</a></li>
                        <li><a href="invoice-2.html">Invoice-2</a></li>
                        <li><a href="invoice-3.html">Invoice-3</a></li>
                        <li><a href="invoice-4.html">Invoice-4</a></li>
                        <li><a href="invoice-5.html">Invoice-5</a></li>
                        <li><a href="invoice-template.html">Invoice-6</a></li>
                      </ul>
                    </li>
                    <li><a href="cart.html">Cart</a></li>
                    <li><a href="list-wish.html">Wishlist</a></li>
                    <li><a href="checkout.html">Checkout</a></li>
                    <li><a href="pricing.html">Pricing      </a></li>
                  </ul>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="letter-box.html">
                    <svg class="stroke-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#stroke-email"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#fill-email"></use>
                    </svg><span>Letter Box   </span></a>
                  <ul class="sidebar-submenu">
                    <li><a href="email-application.html">Email App</a></li>
                    <li><a href="email-compose.html">Email Compose</a></li>
                  </ul>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#stroke-chat"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#fill-chat"></use>
                    </svg><span>Chat</span></a>
                  <ul class="sidebar-submenu">
                    <li><a href="private-chat.html">Private Chat</a></li>
                    <li> <a href="group-chat.html">Group Chat</a></li>
                  </ul>
                </li>
                <li class="sidebar-list"> <i class="fa fa-thumb-tack"> </i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon"> 
                      <use href="../../admin/assets/svg/icon-sprite.svg#stroke-user"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#fill-user"></use>
                    </svg><span>Users</span></a>
                  <ul class="sidebar-submenu">
                    <li><a href="user-profile.html">Users Profile</a></li>
                    <li><a href="edit-profile.html">Users Edit</a></li>
                    <li><a href="user-cards.html">Users Cards</a></li>
                  </ul>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="contacts.html">
                    <svg class="stroke-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#stroke-contact"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#fill-contact"> </use>
                    </svg><span>Contacts</span></a></li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="calendar-basic.html">
                    <svg class="stroke-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#stroke-calendar"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#fill-calender"></use>
                    </svg><span>Calendar</span></a></li>
                <!-- <li class="sidebar-main-title"> 
                  <div>
                    <h6>Forms & Table</h6>
                  </div>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon"> 
                      <use href="../../admin/assets/svg/icon-sprite.svg#stroke-form"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#fill-form"> </use>
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
                </li> -->
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#stroke-table"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#fill-table"></use>
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
                <li class="sidebar-main-title">
                  <div>
                    <h6>Components</h6>
                  </div>
                </li>
                <li class="mega-menu sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#stroke-others"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#fill-others"></use>
                    </svg><span>Others</span></a>
                  <div class="mega-menu-container menu-content">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col mega-box">
                          <div class="link-section">
                            <div class="submenu-title">
                              <h5>Error Page</h5>
                            </div>
                            <ul class="submenu-content opensubmegamenu">
                              <li><a href="error-400.html">Error 400</a></li>
                              <li><a href="error-401.html">Error 401</a></li>
                              <li><a href="error-403.html">Error 403</a></li>
                              <li><a href="error-404.html">Error 404</a></li>
                              <li><a href="error-500.html">Error 500</a></li>
                              <li><a href="error-503.html">Error 503</a></li>
                            </ul>
                          </div>
                        </div>
                        <div class="col mega-box">
                          <div class="link-section">
                            <div class="submenu-title">
                              <h5> Authentication</h5>
                            </div>
                            <ul class="submenu-content opensubmegamenu">
                              <li><a href="login.html" target="_blank">Login Simple</a></li>
                              <li><a href="login_one.html" target="_blank">Login bg image</a></li>
                              <li><a href="login_two.html" target="_blank">Login image two                      </a></li>
                              <li><a href="login-bs-validation.html" target="_blank">Login validation</a></li>
                              <li><a href="login-bs-tt-validation.html" target="_blank">Login tooltip</a></li>
                              <li><a href="login-sa-validation.html" target="_blank">Login sweetalert</a></li>
                              <li><a href="sign-up.html" target="_blank">Register Simple</a></li>
                              <li><a href="sign-up-one.html" target="_blank">Register Bg-Image</a></li>
                              <li><a href="sign-up-two.html" target="_blank">Register two-image </a></li>
                              <li><a href="sign-up-wizard.html" target="_blank">Register wizard</a></li>
                              <li><a href="unlock.html">Unlock User</a></li>
                              <li><a href="forget-password.html">Forget Password</a></li>
                              <li><a href="reset-password.html">Reset Password</a></li>
                              <li><a href="maintenance.html">Maintenance</a></li>
                            </ul>
                          </div>
                        </div>
                        <div class="col mega-box">
                          <div class="link-section">
                            <div class="submenu-title">
                              <h5>Coming Soon</h5>
                            </div>
                            <ul class="submenu-content opensubmegamenu">
                              <li><a href="comingsoon.html">Coming Simple</a></li>
                              <li><a href="comingsoon-bg-video.html">Coming with Bg video</a></li>
                              <li><a href="comingsoon-bg-img.html">Coming with Bg Image</a></li>
                            </ul>
                          </div>
                        </div>
                        <div class="col mega-box">
                          <div class="link-section">
                            <div class="submenu-title">
                              <h5>Email templates</h5>
                            </div>
                            <ul class="submenu-content opensubmegamenu">
                              <li><a href="basic-template.html">Basic Email</a></li>
                              <li><a href="email-header.html">Basic With Header</a></li>
                              <li><a href="template-email.html">Ecomerce Tem...</a></li>
                              <li><a href="template-email-2.html">Email Template 2</a></li>
                              <li><a href="ecommerce-templates.html">Ecommerce Email</a></li>
                              <li><a href="email-order-success.html">Order Success</a></li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
                <li class="sidebar-main-title">
                  <div>
                    <h6>Miscellaneous</h6>
                  </div>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="faq.html">
                    <svg class="stroke-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#stroke-faq"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#fill-faq"></use>
                    </svg><span>FAQ</span></a></li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#stroke-maps"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#fill-maps"></use>
                    </svg><span>Maps</span></a>
                  <ul class="sidebar-submenu">
                    <li><a href="map-js.html">Maps JS</a></li>
                    <li><a href="vector-map.html">Vector Maps</a></li>
                  </ul>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="support-ticket.html">
                    <svg class="stroke-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#stroke-support-tickets"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../admin/assets/svg/icon-sprite.svg#fill-support-tickets"></use>
                    </svg><span>Support Ticket</span></a></li>
              </ul>
              <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
            </div>
          </nav>
        </div>
        <!-- Page Sidebar Ends-->