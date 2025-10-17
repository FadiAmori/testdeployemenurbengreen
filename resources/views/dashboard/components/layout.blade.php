@props(['bodyClass', 'titlePage' => '', 'activePage' => '', 'showSidebar' => false])
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets') }}/img/apple-icon.png">
    <link rel="icon" type="image/png" href="{{ asset('assets') }}/img/favicon.png">
    <title>{{ $titlePage ? $titlePage.' | ' : '' }}Webcore Admin</title>

    <!-- Fonts and icons -->
    <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
    <link href="{{ asset('assets') }}/css/nucleo-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets') }}/css/nucleo-svg.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link id="pagestyle" href="{{ asset('assets') }}/css/material-dashboard.css?v=3.0.0" rel="stylesheet" />
    <link href="{{ asset('assets') }}/css/custom.css" rel="stylesheet" />
    <link href="{{ asset('assets') }}/css/admin-theme.css" rel="stylesheet" />
    @stack('styles')
</head>
<body class="{{ trim(($bodyClass ?? '') . ' admin-theme') }}">

    {{-- Sidebar only on admin/dashboard pages --}}
    @if($showSidebar)
        @include('dashboard.components.navbars.sidebar', ['activePage' => $activePage])
    @endif

    {{-- Main content --}}
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        {{ $slot }}
    </main>

    <!-- Scripts -->
    <script src="{{ asset('assets') }}/js/core/popper.min.js"></script>
    <script src="{{ asset('assets') }}/js/core/bootstrap.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="https://unpkg.com/alpinejs@3.13.10/dist/cdn.min.js" defer></script>
    @stack('scripts')
    @stack('js')
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = { damping: '0.5' }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="{{ asset('assets') }}/js/material-dashboard.min.js?v=3.0.0"></script>
</body>
</html>
