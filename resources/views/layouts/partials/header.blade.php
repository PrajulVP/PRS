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
  }

  .notification-dropdown ul li:last-child {
    border-bottom: none;
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
        <div class="logo-wrapper"> <a href="index.html"><img class="img-fluid for-light"
              src="../../admin/assets/images/logo/logo_dark.png" alt="logo-light"><img class="img-fluid for-dark"
              src="../../admin/assets/images/logo/logo.png" alt="logo-dark"></a></div>
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

          <li class="onhover-dropdown">
            <div class="notification-box">
              <i data-feather="bell"></i>
              @php $unreadCount = Auth::user()->unreadNotifications->count(); @endphp
              @if($unreadCount > 0)
                <span class="badge rounded-pill badge-primary text-white">{{ $unreadCount }}</span>
              @endif
            </div>
            <div class="onhover-show-div notification-dropdown">
              <h6 class="f-18 mb-0 dropdown-title">Notifications</h6>
              <ul>
                @forelse(Auth::user()->unreadNotifications->take(5) as $notification)
                  <li class="b-l-primary border-4" data-id="{{ $notification->id }}">
                    <div style="display: block; width: 100%; color: inherit; cursor: default;">
                      <p class="mb-0">{{ $notification->data['message'] }} </p>
                      <span class="font-danger">( {{ $notification->created_at->diffForHumans() }} )</span>
                    </div>
                  </li>
                @empty
                  <li>
                    <p class="text-center">No new notifications</p>
                  </li>
                @endforelse
                {{-- @if($unreadCount > 0)
                <li>
                  <a class="f-w-700 text-center" href="#">Check all notifications</a>
                </li>
                @endif --}}
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
                    <span>{{ Auth::guard('web')->user()->name }}</span>
                    <i class="middle fa fa-angle-down"></i>
                  </div>
                  <p class="mb-0 font-roboto"><?php    echo e($loggedInRole); ?></p>
                @endif
              </div>
            </div>

            <ul class="profile-dropdown onhover-show-div">
              <li><a href="{{ route('profile.index') }}"><i data-feather="user"></i><span>My Profile</span></a></li>
              <li> <a href="edit-profile.html"> <i data-feather="settings"></i><span>Settings</span></a></li>
              <li>
                <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                  @csrf
                  <button type="submit" class="btn btn-pill btn-outline-primary btn-sm">Log Out</button>
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