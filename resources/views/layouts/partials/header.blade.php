<!-- Page Header Start-->
<style>
  .notification-box {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .notification-box i {
    width: 20px;
    height: 20px;
  }

  .notification-box .badge {
    position: absolute;
    margin-top: -4px;
    margin-right: -5px;
    padding: 7px !important;
    width: 10px;
    height: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px !important;
    border-radius: 50% !important;
    line-height: 1;
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
          <li class="d-md-none d-block">
            <div class="form search-form mb-0">
              <div class="input-group"> <span class="input-show">
                  <svg id="searchIcon">
                    <use href="../../admin/assets/svg/icon-sprite.svg#search-header"></use>
                  </svg>
                  <div id="searchInput">
                    <input type="search" placeholder="Search">
                  </div>
                </span></div>
            </div>
          </li>

          <li>
            <div class="mode"><i class="moon" data-feather="moon"> </i></div>
          </li>

          @if(Auth::guard('web')->check() && Auth::guard('web')->user()->hasRole('retailer') && Auth::guard('web')->user()->retailer)
            <li class="onhover-dropdown loyalty-header-item">
              <a href="{{ route('admin.loyalty-points.index') }}">
                <div class="loyalty-coin-wrapper" title="My Loyalty Points">
                  <div class="big-gold-coin">
                      <div class="coin-inner">
                        <i class="fa fa-star"></i>
                      </div>
                  </div>
                  <span class="pts-value">{{ number_format(Auth::guard('web')->user()->retailer->loyalty_points, 0) }}</span>
                </div>
              </a>
            </li>
            <style>
              .loyalty-coin-wrapper {
                display: flex;
                align-items: center;
                gap: 5px;
                transition: transform 0.3s;
              }

              .loyalty-coin-wrapper:hover {
                transform: scale(1.05);
              }

              .big-gold-coin {
                width: 25px;
                height: 25px;
                background: radial-gradient(ellipse at center, #ffd700 0%, #fdb931 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 
                  inset 0 0 0 2px #d4af37,
                  0 2px 5px rgba(0,0,0,0.1);
                animation: coin-flip-spin 3s infinite linear;
                position: relative;
                transform-style: preserve-3d;
                flex-shrink: 0;
              }

              .big-gold-coin::before {
                content: '';
                position: absolute;
                inset: 3px;
                border: 1px dashed rgba(184, 134, 11, 0.5);
                border-radius: 50%;
              }

              .coin-inner {
                font-size: 15px;
                color: #ffffff;
                text-shadow: 1px 1px 2px rgba(184, 134, 11, 0.8);
                transform: translateZ(1px);
              }

              .pts-value {
                font-weight: 700;
                font-size: 16px;
                color: var(--med-text-main);
                letter-spacing: 0.5px;
              }

              @keyframes coin-flip-spin {
                0% { transform: rotateY(0deg); filter: drop-shadow(0 0 3px rgba(255, 215, 0, 0.5)); }
                50% { transform: rotateY(180deg) scale(1.1); filter: drop-shadow(0 0 8px rgba(255, 215, 0, 0.8)); }
                100% { transform: rotateY(360deg); filter: drop-shadow(0 0 3px rgba(255, 215, 0, 0.5)); }
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
                    $orderExists = isset($data['order_code']) && (\App\Models\RetailerOrder::where('order_code', $data['order_code'])->exists() || \App\Models\DistributorOrder::where('order_code', $data['order_code'])->exists());

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
                    $actionUrl = $notification->data['action_url'] ?? '#';
                    $orderCode = $notification->data['order_code'] ?? '';
                    if ($actionUrl !== '#' && !empty($orderCode)) {
                        $separator = parse_url($actionUrl, PHP_URL_QUERY) ? '&' : '?';
                        $actionUrl .= $separator . 'highlight=' . urlencode($orderCode);
                    }
                  @endphp
                  <li class="{{ $is_unread ? ($is_pending ? 'b-l-primary border-4' : 'b-l-secondary border-4') : 'notification-read' }}"
                    style="{{ !$is_unread ? 'opacity: 0.6; background-color: #f8f9fa;' : '' }}"
                    data-id="{{ $notification->id }}">
                    <a href="{{ $actionUrl }}" style="display: block; width: 100%; color: inherit; cursor: pointer; text-decoration: none;">
                      <p class="mb-1 {{ $is_unread ? 'fw-bold text-dark' : 'text-muted' }}"
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
                    <button type="submit" class="btn btn-pill btn-outline-primary btn-sm">Logout</button>
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