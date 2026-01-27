<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Reso Admin | @yield('title')</title>

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
        /* Reso Light Theme Overrides */
        body, .container.body .right_col {
            background: #f7f9fc !important; /* Very light blue-grey background */
            font-family: 'Figtree', sans-serif;
            color: #1a202c !important; /* Black text */
        }
        
        /* Sidebar */
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
        }
        
        .site_title {
            color: #1a202c !important;
            font-weight: 700;
        }
        
        /* Sidebar Links */
        .nav.side-menu > li > a, .nav.child_menu > li > a {
            color: #4a5568 !important; /* Dark grey text */
            font-weight: 500;
        }
        
        .nav.side-menu > li > a:hover, .nav.side-menu > li.current-page, .nav.side-menu > li.active > a {
            background: #ebf8ff !important; /* Light blue hover/active */
            color: #3182ce !important; /* Reso Blue text */
            text-shadow: none !important;
        }

        /* Override the default green right-border on active items */
        .nav.side-menu > li.current-page, .nav.side-menu > li.active {
            border-right: 5px solid #3182ce !important; /* Reso Blue border */
        }
        
        /* Icons */
        .nav.side-menu > li > a > i {
            color: #718096 !important;
        }
        .nav.side-menu > li > a:hover > i, .nav.side-menu > li.active > a > i {
            color: #3182ce !important; /* Blue icons on active */
        }

        /* Sidebar Headers (General, System) */
        .menu_section h3 {
            color: #1a202c !important; /* Black text for section headers */
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 10px;
            margin-bottom: 0; /* Align with screenshot */
            padding-left: 23px; /* Align with items */
            font-size: 13px; /* Slightly larger */
            font-weight: 700;
        }

        /* Top Navigation */
        .top_nav .nav_menu {
            background: #ebf8ff !important; /* Light blue top bar */
            border-bottom: 1px solid #bee3f8;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        
        .toggle a i {
            color: #1a202c !important;
        }
        
        .user-profile {
            color: #1a202c !important;
        }

        /* Footer */
        footer {
            background: #f7f9fc !important;
            color: #718096 !important;
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
                  <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-home"></i> Dashboard</a></li>
                  <li><a href="{{ route('admin.users') }}"><i class="fa fa-users"></i> Users</a></li>
                  <li><a href="{{ route('admin.moderation') }}"><i class="fa fa-gavel"></i> Moderation</a></li>
                </ul>
              </div>
              <div class="menu_section">
                <h3>System</h3>
                <ul class="nav side-menu">
                    <li>
                        <a href="{{ route('admin.admins.index') }}">
                            <i class="fa fa-users"></i> Admins
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.retrain.page') }}">
                            <i class="fa fa-eye"></i> AI Recs
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.profile') }}">
                            <i class="fa fa-user"></i> Profile
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
