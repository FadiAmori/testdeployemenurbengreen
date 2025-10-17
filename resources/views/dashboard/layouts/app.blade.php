<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ $title ?? 'Dashboard' }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/nucleo-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/nucleo-svg.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons+Round">
    <link rel="stylesheet" href="{{ asset('assets/css/material-dashboard.css?v=3.0.0') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
</head>
<body class="{{ $bodyClass ?? 'dark-version' }}">
    @include('dashboard.components.navbars.sidebar', ['activePage' => $activePage ?? ''])

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        @include('dashboard.components.navbars.navs.auth', ['titlePage' => $titlePage ?? 'Dashboard'])
        @yield('content')
    </main>

    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/material-dashboard.min.js?v=3.0.0') }}"></script>
    @stack('js')
</body>
</html>
