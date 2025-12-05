<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#000000">

    <title>{{ config('app.name', 'Laravel') }} | @yield('title', 'Dashboard')</title>

    <meta name="description" content="Tong Tji - Mobile UI Kit Template" />
    <meta name="keywords" content="bootstrap 5, mobile template, cordova, phonegap, mobile, html" />
    <link rel="icon" type="image/png" href="{{ asset('mobilekit/assets/img/favicon.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('mobilekit/assets/img/icon/192x192.png') }}">
    <link rel="stylesheet" href="{{ asset('mobilekit/assets/css/style.css') }}">
    <link rel="manifest" href="{{ asset('mobilekit/__manifest.json') }}">

    @stack('styles')

</head>

<body>

    <!-- loader -->
    @include('layouts.components.mobile.loader')
    <!-- * loader -->

    <!-- App Header -->
    @include('layouts.components.mobile.header')
    <!-- * App Header -->

    <!-- Search Component -->
    @include('layouts.components.mobile.search')
    <!-- * Search Component -->

    <!-- App Capsule -->
    <div id="appCapsule">

        @yield('content')


        <!-- app footer -->
        @include('layouts.components.mobile.footer')
        <!-- * app footer -->

    </div>
    <!-- * App Capsule -->


    <!-- App Bottom Menu -->
    @include('layouts.components.mobile.bottom-menu')
    <!-- * App Bottom Menu -->

    <!-- App Sidebar -->
    @include('layouts.components.mobile.sidebar')
    <!-- * App Sidebar -->

    <!-- welcome notification  -->
    @include('layouts.components.mobile.notification')
    <!-- * welcome notification -->

    <!-- ============== Js Files ==============  -->
    <!-- Bootstrap -->
    <script src="{{ asset('mobilekit/assets/js/lib/bootstrap.min.js') }}"></script>
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <!-- Splide -->
    <script src="{{ asset('mobilekit/assets/js/plugins/splide/splide.min.js') }}"></script>
    <!-- ProgressBar js -->
    <script src="{{ asset('mobilekit/assets/js/plugins/progressbar-js/progressbar.min.js') }}"></script>
    <!-- Base Js File -->
    <script src="{{ asset('mobilekit/assets/js/base.js') }}"></script>

    @stack('scripts')
</body>

</html>
