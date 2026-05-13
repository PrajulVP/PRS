<!-- Page Header Start-->
<style>
  .notification-box, .mode-toggle {
    position: relative;
    display: flex !important;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50% !important;
    background-color: rgba(255, 255, 255, 0.1) !important;
    transition: all 0.3s ease;
    cursor: pointer;
    border: none !important;
    padding: 0 !important;
    overflow: visible !important;
  }

  body:not(.dark-only) .notification-box, 
  body:not(.dark-only) .mode-toggle {
    background-color: #f1f5f9 !important;
  }

  .nav-menus li {
    background: transparent !important;
    background-color: transparent !important;
    border: none !important;
    padding: 0 !important;
    margin: 0 8px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 50% !important;
  }

  .mode-toggle.mode {
      box-shadow: none !important;
      border-radius: 50% !important;
      width: 40px;
      height: 40px;
  }

  .notification-box i, .mode-toggle i {
    width: 20px;
    height: 20px;
    display: flex !important;
    align-items: center;
    justify-content: center;
  }

  .notification-box .badge {
    position: absolute;
    margin-top: -2px;
    margin-right: -2px;
    padding: 5px !important;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px !important;
    border-radius: 50% !important;
    line-height: 1;
    z-index: 10;
  }

  .notification-dropdown {
    min-width: 350px !important;
  }

  .notification-dropdown ul li {
    padding: 10px !important;
    border-bottom: 1px solid #f4f4f4;
    transition: transform 0.4s ease, background-color 0.4s ease !important;
  }

  .notification-dropdown ul li:last-child {
    border-bottom: none;
  }

  .notification-dropdown ul li:hover {
    transform: translateX(3px) !important;
    /* Very slow slight movement */
  }

  /* Dark mode fixes */
  body.dark-only .profile-dropdown li:hover a span, 
  body.dark-only .profile-dropdown li:hover a i {
      color: var(--bs-primary) !important;
  }
  body.dark-only .profile-dropdown li form button.logout-btn {
      color: #fff !important;
      background-color: var(--bs-primary) !important;
  }
  .profile-dropdown li a {
      display: flex !important;
      align-items: center !important;
      white-space: nowrap !important;
      width: 100% !important;
  }
  .profile-dropdown li a i {
      margin-right: 10px !important;
      margin-bottom: 0 !important;
  }
  .profile-dropdown li a span {
      white-space: nowrap !important;
  }
</style>
<div class="page-header">
  @if(!request()->is('api.admin.login'))
    <div class="header-wrapper row m-0">
      <form class="form-inline search-full col" action="#" method="get">
        <div class="form-group w-100">
          <div class="Typeahead Typeahead--twitterUsers">
            <div class="u-posRelative">
              <input class="demo-input Typeahead-input form-control-plaintext w-100" type="text"
                placeholder="Search Riho .." name="q" title="" autofocus>
              <div class="spinner-border Typeahead-spinner" role="status"><span class="sr-only">Loading... </span></div><i
                class="close-search" data-feather="x"></i>
            </div>
            <div class="Typeahead-menu"> </div>
          </div>
        </div>
      </form>
      <div class="header-logo-wrapper col-auto p-0">
        <div class="logo-wrapper">
          <a href="{{ route('dashboard') }}">
            <img class="img-fluid for-light" src="{{ asset('admin/assets/images/logo/logo_dark.png') }}" alt="logo-light">
            <img class="img-fluid for-dark" src="{{ asset('admin/assets/images/logo/logo.png') }}" alt="logo-dark">
          </a>
        </div>
        <div class="toggle-sidebar"> <i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i></div>
      </div>
      <div class="left-header col-xxl-5 col-xl-6 col-lg-5 col-md-4 col-sm-3 p-0">
        <div> <a class="toggle-sidebar" href="#"> <i class="iconly-Category icli"> </i></a>
          <div class="d-flex align-items-center gap-2 ">
            <?php
    $loggedInRole = null;
    if (Auth::guard('web')->check()) {
      $loggedInRole = Auth::guard('web')->user()->getRoleNames()->first();
    }
                                                                    ?>

            <?php  if (Auth::guard('web')->check()): ?>
            <h4 class="fs-4">Welcome <?php    echo e(Auth::guard('web')->user()->name); ?></h4><img class="mt-0"
              src="<?php    echo e(asset('admin/assets/images/hand.gif')); ?>" alt="hand-gif">
            <?php  endif; ?>
          </div>
        </div>
      </div>
      <div class="nav-right col-xxl-7 col-xl-6 col-md-7 col-8 pull-right right-header p-0 ms-auto">
        <ul class="nav-menus">
          <li class="d-md-block d-none">
            {{-- <div class="form search-form mb-0">
              <div class="input-group"><span class="input-icon">
                  <svg>
                    <use href="../../admin/assets/svg/icon-sprite.svg#search-header"></use>
                  </svg>
                  <input class="w-100" type="search" placeholder="Search"></span></div>
            </div> --}}
          </li>


          <li>
            <div class="mode-toggle mode"><i class="moon" data-feather="moon"></i></div>
          </li>

          @if(Auth::guard('web')->check() && Auth::guard('web')->user()->hasRole('retailer') && Auth::guard('web')->user()->retailer)
            <li class="onhover-dropdown loyalty-header-item">
              <a href="{{ route('admin.loyalty-points.index') }}">
                <div class="premium-loyalty-badge" title="My Loyalty Points">
                  <div class="badge-icon">
                    <i data-feather="star" style="width: 14px; height: 14px;"></i>
                  </div>
                  <div class="badge-content">
                    <span class="pts-label">LOYALTY</span>
                    <span class="pts-value">{{ number_format(Auth::guard('web')->user()->retailer->loyalty_points, 0) }}</span>
                  </div>
                </div>
              </a>
            </li>
            <style>
              .premium-loyalty-badge {
                display: flex;
                align-items: center;
                background: rgba(255, 255, 255, 0.05);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 215, 0, 0.3);
                border-radius: 50px;
                padding: 4px 12px 4px 6px;
                gap: 8px;
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                position: relative;
                overflow: hidden;
              }

              body:not(.dark-only) .premium-loyalty-badge {
                background: rgba(0, 73, 122, 0.05);
                border-color: rgba(218, 165, 32, 0.4);
              }

              .premium-loyalty-badge::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(
                  90deg,
                  transparent,
                  rgba(255, 215, 0, 0.2),
                  transparent
                );
                transition: 0.5s;
              }

              .premium-loyalty-badge:hover::before {
                left: 100%;
              }

              .premium-loyalty-badge:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(255, 215, 0, 0.15);
                border-color: rgba(255, 215, 0, 0.6);
              }

              .badge-icon {
                width: 28px;
                height: 28px;
                background: linear-gradient(135deg, #ffd700 0%, #daa520 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-size: 14px;
                box-shadow: 0 2px 10px rgba(218, 165, 32, 0.4);
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
              }

              .badge-content {
                display: flex;
                flex-direction: column;
                line-height: 1;
              }

              .pts-label {
                font-size: 8px;
                font-weight: 800;
                color: #daa520;
                letter-spacing: 1px;
                margin-bottom: 1px;
              }

              .pts-value {
                font-size: 14px;
                font-weight: 800;
                color: var(--med-text-main);
                letter-spacing: -0.5px;
              }
            </style>
          @endif

          <li class="onhover-dropdown">
            <div class="notification-box">
              <i data-feather="bell" style="animation: none !important; transform: none !important;"></i>
              @php 
                $notificationsList = Auth::user()->notifications;
                foreach ($notificationsList as $notification) {
                  $data = $notification->data;
                  if (isset($data['order_code'])) {
                    $needsAction = \App\Http\Controllers\NotificationController::checkActionStatus($notification);
                    $orderExists = isset($data['order_code']) && (
                        \App\Models\RetailerOrder::where('order_code', $data['order_code'])->exists() || 
                        \App\Models\DistributorOrder::where('order_code', $data['order_code'])->exists() ||
                        \App\Models\ReturnRequest::where('return_code', $data['order_code'])->exists()
                    );

                    if (!$orderExists && isset($data['order_code'])) {
                      $notification->delete(); // Clean up deleted orders
                    } elseif (!$needsAction && $notification->unread()) {
                      $notification->markAsRead(); // Mark as read
                    }
                  }
                }
                $unreadCount = Auth::user()->unreadNotifications()->count(); 
              @endphp
              @if($unreadCount > 0)
                <span class="badge rounded-pill badge-primary text-white pulse-badge">{{ $unreadCount }}</span>
              @endif
            </div>
            <style>
                .pulse-badge {
                animation: shake-pump 2s infinite ease-in-out;
                display: inline-block;
              }
              @keyframes shake-pump {
                0% {
                  transform: scale(1) rotate(0deg);
                }
              10% {
                transform: scale(1.2) rotate(-10deg);
              }

                               20% {
                transform: scale(1.2) rotate(10deg);
            }
              30% {
              transform: scale(1.2) rotate(-10deg);
            }
              40% {
                transform: scale(1.1) rotate(5deg);
              }
              50% {
                transform: scale(1) rotate(0deg);
              }
                100% {
                transform: scale(1) rotate(0deg);
              }
            }
            </style>
              <div class="onhover-show-div notification-dropdown">
              <h6 class="f-18 mb-0 dropdown-title">Notifications</h6>
              <ul>
                @forelse(Auth::user()->notifications()->latest()->take(5)->get() as $notification)
                  @php 
                    $is_pending = \App\Http\Controllers\NotificationController::checkActionStatus($notification); 
                    $is_unread = $notification->unread();
                    $actionUrl = $notification->data['action_url'] ?? $notification->data['url'] ?? '#';
                    $orderCode = $notification->data['order_code'] ?? '';
                    if ($actionUrl !== '#' && !empty($orderCode)) {
                        $separator = parse_url($actionUrl, PHP_URL_QUERY) ? '&' : '?';
                        $actionUrl .= $separator . 'highlight=' . urlencode($orderCode);
                    }
                  @endphp
                  <li class="{{ $is_unread ? ($is_pending ? 'b-l-primary border-4' : 'b-l-secondary border-4') : 'notification-read' }}"
                    style="{{ !$is_unread ? 'opacity: 0.6;' : '' }}"
                    data-id="{{ $notification->id }}">
                    <a href="{{ $actionUrl }}" style="display: block; width: 100%; color: inherit; cursor: pointer; text-decoration: none;">
                      <p class="mb-1 {{ $is_unread ? 'fw-bold' : 'text-muted' }}"
                        style="font-size: 0.8rem;">
                      {{ $notification->data['message'] ?? 'Notification' }}
                    </p>
                    <span class="{{ $is_unread ? ($is_pending ? 'font-danger' : 'text-primary') : 'text-muted' }}"
                      style="font-size: 0.70rem;">
                      <i class="fa fa-clock-o"></i> {{ $notification->created_at->diffForHumans() }}
                      </span>
                    </a>
                  </li>
                @empty
                    <li>
                      <p class="text-center text-muted my-2">No notifications found</p>
                    </li>
                  @endforelse
                <li class="p-2 text-center" style="border-bottom: none !important;">
                  <a class="f-w-700 btn btn-primary btn-sm w-100" href="{{ route('notifications.index') }}">
                    View all notifications <i class="fa fa-arrow-right ms-1"></i>
                  </a>
                </li>
              </ul>
            </div>
          </li>
            <li class="profile-nav onhover-dropdown">
              <div class="media profile-media">
                <img class="rounded-circle" src="{{ Auth::guard('web')->user()->avatar_url }}" width="43" height="43"
                  alt="Profile Picture">
                <div class="media-body d-xxl-block d-none box-col-none">
                  @if(Auth::guard('web')->check())
                      <div class="d-flex align-items-center justify-content-between gap-2 pt-1">
                      <span style="color: var(--med-text-main);">{{ Auth::guard('web')->user()->name }}</span>
                      <i class="middle fa fa-angle-down" style="color: var(--med-text-main);"></i>
                    </div>
                      <p class="mb-0 font-roboto"><?php    echo e($loggedInRole); ?></p>
                  @endif
                </div>
              </div>

              <ul class="profile-dropdown onhover-show-div">
                <li><a href="{{ route('profile.index') }}"><i data-feather="user"></i><span>Edit Profile</span></a></li>
                {{-- <li> <a href="edit-profile.html"> <i data-feather="settings"></i><span>Settings</span></a></li> --}}
                <li>
                  <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-pill btn-primary btn-sm w-100 logout-btn">Logout</button>
                  </form>
                </li>
              </ul>
            </li>
          </ul>
        </div>
        <script class="result-template" type="text/x-handlebars-template">
                    <div class="ProfileCard u-cf">                        
                        <div class="ProfileCard-avatar"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-airplay m-0"><path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"></path><polygon points="12 15 17 21 7 21 12 15"></polygon></svg></div>
                        <div class="ProfileCard-details"> 
                        <div class="ProfileCard-realName">name</div>
                        </div> 
                    </div>
                  </script>
        <script class="empty-template"
          type="text/x-handlebars-template"><div class="EmptyMessage">Your search turned up 0 results. This most likely means the backend is down, yikes!</div></script>
      </div>
  @endif
</div>
<!-- Page Header Ends-->
<script>
  setTimeout(function () {
    if (typeof feather !== 'undefined') {
      feather.replace();
    }
  }, 500);
</script>