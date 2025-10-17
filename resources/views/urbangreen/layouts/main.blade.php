<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>UrbanGreen</title>
  <link rel="icon" href="{{ asset('urbangreen/img/core-img/favicon.ico') }}">
  <link rel="stylesheet" href="{{ asset('urbangreen/style.css') }}">
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  @stack('styles')
</head>
<body>
  <!-- Header -->
  <header class="header-area">
    <div class="top-header-area">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="top-header-content d-flex align-items-center justify-content-between">
              <div class="top-header-meta"></div>
              <div class="top-header-meta d-flex">
                @auth
                  <div class="dropdown mr-3">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fa fa-heart" aria-hidden="true"></i> <span>My Account</span>
                    </a>
                    <div class="dropdown-menu">
                      <a class="dropdown-item" href="{{ route('front.cart') }}">
                        <i class="fa fa-shopping-cart mr-2"></i>My Cart
                      </a>
                      <a class="dropdown-item" href="{{ route('front.orders.index') }}">
                        <i class="fa fa-list mr-2"></i>My Orders
                      </a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item" href="{{ route('front.favorites.index') }}">
                        <i class="fa fa-heart mr-2"></i>My Favorites
                      </a>
                      <a class="dropdown-item" href="{{ route('front.notifications.index') }}">
                        <i class="fa fa-bell mr-2"></i>My Notifications
                      </a>
                      <div class="dropdown-divider"></div>

                      <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fa fa-sign-out mr-2"></i>Logout
                      </a>
                      <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                      </form>
                    </div>
                  </div>
                  <div class="user-welcome">
                    <span>Hello, {{ auth()->user()->name }}!</span>
                  </div>
                @else
                  <div class="login">
                    <a href="{{ route('login', [], false) }}"><i class="fa fa-user" aria-hidden="true"></i> <span>Login</span></a>
                  </div>
                @endauth
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="alazea-main-menu">
      <div class="classy-nav-container breakpoint-off">
        <div class="container">
          <nav class="classy-navbar justify-content-between" id="alazeaNav">
            <a href="{{ route('front.home', [], false) }}" class="nav-brand"><span class="brand-text"><span class="brand-urban">URBAN</span><span class="brand-green">GREEN</span></span></a>
            <div class="classy-navbar-toggler">
              <span class="navbarToggler"><span></span><span></span><span></span></span>
            </div>
            <div class="classy-menu">
              <div class="classycloseIcon">
                <div class="cross-wrap"><span class="top"></span><span class="bottom"></span></div>
              </div>
              <div class="classynav">
                <ul>
                  <li><a href="{{ route('front.home', [], false) }}">Home</a></li>
                  <li><a href="{{ route('front.event', [], false) }}">Event</a></li>
                  <li><a href="{{ route('front.shop', [], false) }}">Shop</a></li>
                  <li><a href="{{ route('front.blog', [], false) }}">Blog</a></li>
                  <li><a href="{{ route('front.maintenance', [], false) }}">Maintenance</a></li>
                </ul>
                <div id="searchIcon"><i class="fa fa-search" aria-hidden="true"></i></div>
              </div>
            </div>
          </nav>
          <div class="search-form">
            <form action="#" method="get">
              <input type="search" name="search" id="search" placeholder="Type keywords &amp; press enter...">
              <button type="submit" class="d-none"></button>
            </form>
            <div class="closeIcon"><i class="fa fa-times" aria-hidden="true"></i></div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Page content -->
  @yield('content')

  <!-- Footer -->
  <footer class="footer-area bg-img" style="background-image: url({{ asset('urbangreen/img/bg-img/3.jpg') }});">
    <div class="main-footer-area">
      <div class="container">
        <div class="row">
          <div class="col-12 col-sm-6 col-lg-4">
            <div class="single-footer-widget">
              <div class="footer-logo mb-30">
                <span class="brand-text footer-brand"><span class="brand-urban">URBAN</span><span class="brand-green">GREEN</span></span>
              </div>
              <p>UrbanGreen encourage la végétalisation des espaces urbains pour améliorer la qualité de vie. Le projet fédère citoyens et associations afin de rendre les villes plus vertes, respirables et conviviales.</p>
              <div class="social-info">
                <a href="#"><i class="fa fa-facebook" aria-hidden="true"></i></a>
                <a href="#"><i class="fa fa-twitter" aria-hidden="true"></i></a>
                <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-lg-4">
            <div class="single-footer-widget">
              <div class="widget-title"><h5>QUICK LINK</h5></div>
              <nav class="widget-nav"><ul>
                <li><a href="{{ route('front.home', [], false) }}">Home</a></li>
                <li><a href="{{ route('front.event', [], false) }}">Event</a></li>
                <li><a href="{{ route('front.shop', [], false) }}">Shop</a></li>
                <li><a href="{{ route('front.blog', [], false) }}">Blog</a></li>
                <li><a href="{{ route('front.maintenance', [], false) }}">Maintenance</a></li>
              </ul></nav>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-lg-4">
            <div class="single-footer-widget">
              <div class="widget-title"><h5>BEST SELLER</h5></div>
              <div class="single-best-seller-product d-flex align-items-center">
                <div class="product-thumbnail"><img src="{{ asset('urbangreen/img/bg-img/4.jpg') }}" alt=""></div>
                <div class="product-info"><a href="#">Cactus Flower</a><p>$10.99</p></div>
              </div>
              <div class="single-best-seller-product d-flex align-items-center">
                <div class="product-thumbnail"><img src="{{ asset('urbangreen/img/bg-img/5.jpg') }}" alt=""></div>
                <div class="product-info"><a href="#">Tulip Flower</a><p>$11.99</p></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="footer-bottom-area">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="border-line"></div>
          </div>
          <div class="col-12 col-md-6">
            <div class="copywrite-text"><p>© {{ date('Y') }} Webcore — UrbanGreen. All rights reserved.</p></div>
          </div>
          <div class="col-12 col-md-6">
            <div class="footer-nav"><nav><ul>
              <li><a href="{{ route('front.home', [], false) }}">Home</a></li>
              <li><a href="{{ route('front.event', [], false) }}">Event</a></li>
              <li><a href="{{ route('front.shop', [], false) }}">Shop</a></li>
              <li><a href="{{ route('front.blog', [], false) }}">Blog</a></li>
              <li><a href="{{ route('front.maintenance', [], false) }}">Maintenance</a></li>
            </ul></nav></div>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="{{ asset('urbangreen/js/jquery/jquery-2.2.4.min.js') }}"></script>
  <script src="{{ asset('urbangreen/js/bootstrap/popper.min.js') }}"></script>
  <script src="{{ asset('urbangreen/js/bootstrap/bootstrap.min.js') }}"></script>
  <script src="{{ asset('urbangreen/js/plugins/plugins.js') }}"></script>
  <script src="{{ asset('urbangreen/js/active.js') }}"></script>
  <script src="https://unpkg.com/alpinejs@3.13.10/dist/cdn.min.js" defer></script>
  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  @stack('scripts')
  <style>
    .brand-text{font-family:"Dosis",sans-serif;font-weight:700;letter-spacing:1px;color:#fff;font-size:28px;line-height:1}
    .brand-text .brand-green{color:#70c745}
    .footer-brand{font-size:26px}
    /* Ensure account dropdown items are readable */
    .header-area .top-header-area .top-header-content .top-header-meta .dropdown-menu {
      background: #ffffff;
      border: 1px solid rgba(0,0,0,.15);
    }
    .header-area .top-header-area .top-header-content .top-header-meta .dropdown-menu .dropdown-item{
      color:#111 !important;
    }
    .header-area .top-header-area .top-header-content .top-header-meta .dropdown-menu .dropdown-item i{
      color:#70c745;
      margin-right:8px;
    }
  </style>
</body>
</html>
