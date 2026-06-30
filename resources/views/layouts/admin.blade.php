<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Reso Admin | @yield('title')</title>

    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="{{ asset('assets/admin/vendors/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="{{ asset('assets/admin/vendors/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <!-- NProgress -->
    <link href="{{ asset('assets/admin/vendors/nprogress/nprogress.css') }}" rel="stylesheet">
    <!-- Custom Theme Style -->
    <link href="{{ asset('assets/admin/build/css/custom.min.css') }}" rel="stylesheet">
    @stack('styles')
    
    <style>
        /* Modern Responsive Scaling to fit various monitor sizes */
        html {
            font-size: 16px !important;
        }
        @media (max-width: 1600px) {
            html { font-size: 15px !important; }
        }
        @media (max-width: 1366px) {
            html { font-size: 14.5px !important; }
        }
        @media (max-width: 1200px) {
            html { font-size: 14px !important; }
        }

        body, .container.body .right_col {
            background: #f1f5f9 !important;
            font-family: 'Inter', 'Figtree', sans-serif;
            color: #1a202c !important;
            font-size: 15px !important;
            line-height: 1.65 !important;
        }
        
        /* Modern Sidebar Width Scaling (Expanded / nav-md) */
        .nav-md .container.body .col-md-3.left_col {
            width: 280px !important;
        }
        .nav-md .container.body .right_col {
            margin-left: 280px !important;
        }
        .nav-md .nav_title {
            width: 280px !important;
        }
        .nav-md .main_container .top_nav {
            margin-left: 280px !important;
        }
        .nav-md .sidebar-footer {
            width: 280px !important;
        }
        @media (min-width: 992px) {
            .nav-md footer {
                margin-left: 280px !important;
            }
        }

        /* Narrower sidebar on smaller monitors to preserve content canvas space */
        @media (max-width: 1440px) {
            .nav-md .container.body .col-md-3.left_col {
                width: 240px !important;
            }
            .nav-md .container.body .right_col {
                margin-left: 240px !important;
            }
            .nav-md .nav_title {
                width: 240px !important;
            }
            .nav-md .main_container .top_nav {
                margin-left: 240px !important;
            }
            .nav-md .sidebar-footer {
                width: 240px !important;
            }
            @media (min-width: 992px) {
                .nav-md footer {
                    margin-left: 240px !important;
                }
            }
        }

        /* Collapsed Sidebar (nav-sm) Width Scaling */
        .nav-sm .container.body .col-md-3.left_col {
            width: 70px !important;
        }
        .nav-sm .container.body .right_col {
            margin-left: 70px !important;
        }
        .nav-sm .nav_title {
            width: 70px !important;
        }
        .nav-sm .main_container .top_nav {
            margin-left: 70px !important;
        }
        .nav-sm .sidebar-footer {
            display: none !important;
        }
        @media (min-width: 992px) {
            .nav-sm footer {
                margin-left: 70px !important;
            }
        }

        /* Sidebar container styling */
        .left_col {
            background: #ffffff !important; /* White sidebar */
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
            border-right: 1px solid #e2e8f0;
        }
        
        .nav_title {
            background: #ffffff !important; /* White logo area */
            border-bottom: 1px solid #e2e8f0;
            color: #1a202c !important;
            box-shadow: none !important;
            height: 72px !important;
            display: flex;
            align-items: center;
        }
        
        .nav-md .site_title {
            color: #1a202c !important;
            font-weight: 700;
            font-size: 20px !important;
            height: auto !important;
            line-height: 1 !important;
            display: flex;
            align-items: center;
            padding-left: 24px !important;
        }

        .nav-md .site_title img {
            height: 36px !important;
            margin-right: 12px !important;
        }

        /* Collapsed Sidebar Logo overrides */
        .nav-sm .site_title {
            padding-left: 0 !important;
            justify-content: center !important;
        }
        .nav-sm .site_title span {
            display: none !important;
        }
        .nav-sm .site_title img {
            margin-right: 0 !important;
            height: 28px !important;
        }
        
        /* Sidebar Links & Spacing (Expanded / nav-md) */
        .nav-md .nav.side-menu > li > a, .nav-md .nav.child_menu > li > a {
            color: #4a5568 !important; /* Dark grey text */
            font-weight: 600;
            font-size: 16.5px !important;
            padding: 16px 20px 16px 28px !important;
        }
        
        .nav.side-menu > li > a:hover, .nav.side-menu > li.current-page, .nav.side-menu > li.active > a {
            background: #ebf8ff !important; /* Light blue hover/active */
            color: #3182ce !important; /* Reso Blue text */
            text-shadow: none !important;
        }

        /* Override the default green right-border on active items */
        .nav-md .nav.side-menu > li.current-page, .nav-md .nav.side-menu > li.active {
            border-right: 6px solid #3182ce !important; /* Reso Blue border */
        }
        
        /* Sidebar Icons (Expanded / nav-md) */
        .nav-md .nav.side-menu > li > a > i {
            color: #718096 !important;
            font-size: 18px !important;
            margin-right: 14px !important;
            width: 22px !important;
            text-align: center !important;
        }
        .nav.side-menu > li > a:hover > i, .nav.side-menu > li.active > a > i {
            color: #3182ce !important; /* Blue icons on active */
        }

        /* Sidebar Section Headers (Expanded / nav-md) */
        .nav-md .menu_section h3 {
            color: #1a202c !important; /* Black text for section headers */
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 30px !important;
            margin-bottom: 10px !important;
            padding-left: 28px !important;
            font-size: 13px !important;
            font-weight: 800;
        }

        /* Collapsed Sidebar (nav-sm) Link, Icon, and Header Styling */
        .nav-sm .nav.side-menu > li > a {
            text-align: center !important;
            padding: 12px 5px !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            color: #4a5568 !important;
        }
        .nav-sm .nav.side-menu > li > a > i {
            color: #718096 !important;
            font-size: 20px !important;
            margin-right: 0 !important;
            margin-bottom: 6px !important;
            display: block !important;
            width: 100% !important;
            text-align: center !important;
        }
        .nav-sm .menu_section h3 {
            display: none !important;
        }
        .nav-sm .nav.side-menu > li.current-page, .nav-sm .nav.side-menu > li.active {
            border-right: 4px solid #3182ce !important;
        }

        /* Top Navigation Bar Resizing */
        .top_nav .nav_menu {
            background: #ebf8ff !important; /* Light blue top bar */
            border-bottom: 1px solid #bee3f8;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
            height: 72px !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px !important;
        }
        
        .toggle {
            float: none !important;
            margin: 0 !important;
            padding: 0 !important;
            display: flex;
            align-items: center;
        }

        .toggle a {
            padding: 12px !important;
            margin: 0 !important;
        }

        .toggle a i {
            color: #1a202c !important;
            font-size: 20px !important;
        }

        .top_nav nav {
            height: 100%;
            display: flex;
            align-items: center;
        }

        .nav.navbar-nav {
            margin: 0 !important;
            display: flex !important;
            align-items: center !important;
            height: 100% !important;
            list-style: none !important;
            padding: 0 !important;
        }

        .nav.navbar-nav > li {
            display: flex !important;
            align-items: center !important;
            height: 100% !important;
            float: none !important;
            position: relative !important;
        }

        .nav.navbar-nav > li > a {
            color: #1a202c !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            padding: 0 20px !important;
            height: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            position: relative !important;
        }
        
        .user-profile {
            color: #1a202c !important;
        }

        /* Adjust the notification badge position inside the flex link */
        .info-number .badge {
            right: 6px !important;
            top: 14px !important;
        }

        /* Spacing for Main Page Content Area */
        .right_col {
            padding: 40px 50px !important;
        }
        @media (max-width: 1440px) {
            .right_col {
                padding: 30px 40px !important;
            }
        }
        @media (max-width: 1200px) {
            .right_col {
                padding: 24px 24px !important;
            }
        }
        @media (max-width: 768px) {
            .right_col {
                padding: 16px 16px !important;
            }
        }

        /* Standardize Buttons globally */
        .btn {
            font-size: 15px !important;
            padding: 10px 20px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
        }

        /* Standardize Forms & Form Inputs globally */
        .form-control, select, input {
            font-size: 15.5px !important;
            padding: 11px 15px !important;
            border-radius: 8px !important;
            height: auto !important;
        }

        label {
            font-size: 15px !important;
            font-weight: 600 !important;
            margin-bottom: 8px !important;
            color: #374151 !important;
        }

        /* Footer styling */
        footer {
            background: #f7f9fc !important;
            color: #718096 !important;
            padding: 22px 28px !important;
            font-size: 14.5px !important;
        }
    </style>
  </head>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <div class="col-md-3 left_col">
          <div class="left_col scroll-view">
            <div class="navbar nav_title" style="border: 0;">
              <a href="{{ route('admin.dashboard') }}" class="site_title">
                <img src="{{ asset('icons/reso.png') }}" alt="Reso" style="height: 30px; margin-right: 10px;">
                <span>Reso Admin</span>
              </a>
            </div>

            <div class="clearfix"></div>

            <!-- menu profile quick info (Removed as requested) -->
            <!-- /menu profile quick info -->

            <br />

            <!-- sidebar menu -->
            <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
              <div class="menu_section">
                <h3>General</h3>
                <ul class="nav side-menu">
                  <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}"><i class="fa fa-home"></i> Dashboard</a>
                  </li>
                  <li class="{{ request()->routeIs('admin.users') ? 'active' : '' }}">
                    <a href="{{ route('admin.users') }}"><i class="fa fa-users"></i> Users</a>
                  </li>
                  <li class="{{ request()->routeIs('admin.songs') || request()->routeIs('admin.songs.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.songs') }}"><i class="fa fa-music"></i> Songs</a>
                  </li>
                  <li class="{{ request()->routeIs('admin.moderation') ? 'active' : '' }}">
                    <a href="{{ route('admin.moderation') }}"><i class="fa fa-gavel"></i> Moderation</a>
                  </li>
                </ul>
              </div>
              <div class="menu_section">
                <h3>System</h3>
                <ul class="nav side-menu">
                    <li class="{{ request()->routeIs('admin.admins.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.admins.index') }}">
                            <i class="fa fa-shield"></i> Admins
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin.retrain.page') ? 'active' : '' }}">
                        <a href="{{ route('admin.retrain.page') }}">
                            <i class="fa fa-eye"></i> Algo Rec Preview
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin.profile') ? 'active' : '' }}">
                        <a href="{{ route('admin.profile') }}">
                            <i class="fa fa-user"></i> My Profile
                        </a>
                    </li>
                </ul>
              </div>
              <div class="menu_section">
                <h3>Audit Report</h3>
                <ul class="nav side-menu">
                    <li class="{{ request()->routeIs('admin.algo-test-suite') ? 'active' : '' }}">
                        <a href="{{ route('admin.algo-test-suite') }}">
                            <i class="fa fa-flask"></i> Accuracy Result
                        </a>
                    </li>
                </ul>
              </div>
            </div>
            <!-- /sidebar menu -->
          </div>
        </div>

        <!-- top navigation -->
        <div class="top_nav">
            <div class="nav_menu">
                <div class="nav toggle">
                  <a id="menu_toggle"><i class="fa fa-bars"></i></a>
                </div>
                <nav>
                <ul class="nav navbar-nav navbar-right">
                  <li class="">
                    <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                      {{ Auth::guard('admin')->user()->name ?? 'Admin' }}
                      <span class=" fa fa-angle-down"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-usermenu pull-right">
                      <li><a href="{{ route('admin.profile') }}"> Profile</a></li>
                      <li>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fa fa-sign-out pull-right"></i> Log Out
                        </a>
                      </li>
                      <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                        @csrf
                      </form>
                    </ul>
                  </li>

                  {{-- Admin Notifications Dropdown --}}
                  <li role="presentation" class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle info-number" data-toggle="dropdown" aria-expanded="false">
                      <i class="fa fa-envelope-o"></i>
                      @if(isset($adminUnreadCount) && $adminUnreadCount > 0)
                        <span class="badge bg-green">{{ $adminUnreadCount }}</span>
                      @endif
                    </a>
                    <ul id="menu1" class="dropdown-menu list-unstyled msg_list" role="menu">
                      @if(isset($adminNotifications) && $adminNotifications->count() > 0)
                          @foreach($adminNotifications as $notification)
                          <li>
                            <a href="{{ $notification->data['link'] ?? '#' }}">
                              <span>
                                <span><b>{{ $notification->data['title'] ?? 'System Alert' }}</b></span>
                                <span class="time text-xs ml-2">{{ $notification->created_at->diffForHumans() }}</span>
                              </span>
                              <span class="message" style="display: block; margin-top: 5px;">
                                {{ $notification->data['message'] ?? '' }}
                              </span>
                            </a>
                          </li>
                          @endforeach
                          <li>
                            <div class="text-center">
                              <form action="{{ route('admin.notifications.markAllRead') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-link" style="color: #3182ce; font-weight: 600;">
                                  Mark all as read
                                  <i class="fa fa-angle-right"></i>
                                </button>
                              </form>
                            </div>
                          </li>
                      @else
                          <li>
                            <div class="text-center" style="padding: 10px;">
                              <span class="text-gray-500">No new notifications</span>
                            </div>
                          </li>
                      @endif
                    </ul>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            
            @yield('content')
        </div>
        <!-- /page content -->

        <!-- footer content -->
        <footer>
          <div class="pull-right">
            Reso Admin Panel
          </div>
          <div class="clearfix"></div>
        </footer>
        <!-- /footer content -->
      </div>
    </div>

    <!-- jQuery -->
    <script src="{{ asset('assets/admin/vendors/jquery/dist/jquery.min.js') }}"></script>
    <!-- Bootstrap -->
    <script src="{{ asset('assets/admin/vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <!-- FastClick -->
    <script src="{{ asset('assets/admin/vendors/fastclick/lib/fastclick.js') }}"></script>
    <!-- NProgress -->
    <script src="{{ asset('assets/admin/vendors/nprogress/nprogress.js') }}"></script>
    <!-- Custom Theme Scripts -->
    <script src="{{ asset('assets/admin/build/js/custom.min.js') }}"></script>
    @stack('scripts')
  </body>
</html>
