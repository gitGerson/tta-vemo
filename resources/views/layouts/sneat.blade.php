<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="layout-menu-fixed layout-compact" data-bs-theme="light"
    data-assets-path="../assets/"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    @vite(['resources/sass/app.scss', 'resources/js/modules/sneat-theme.js'])

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} | @yield('title', 'Dashboard')</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('sneat/assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/fonts/iconify-icons.css') }}" />

    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css  -->

    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('sneat/assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/custom-styles.css') }}">

    <!-- Vendors CSS -->

    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <!-- endbuild -->

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="{{ asset('sneat/assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->

    <script src="{{ asset('sneat/assets/js/config.js') }}"></script>

    <!-- DataTables CSS -->
    <link href="{{ asset('datatables/datatables.min.css') }}" rel="stylesheet">

    @vite(['resources/sass/sneat.scss', 'resources/js/app.js'])

    @stack('styles')


</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            @include('layouts.components.aside')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                @include('layouts.components.navbar')
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">

                    @include('layouts.components.breadcrumbs')

                    @yield('content')
                    <!-- / Content -->

                    <!-- Footer -->
                    @include('layouts.components.footer')
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    @php
        $host = request()->getHost();
        $environmentLabel = match (true) {
            str_ends_with($host, '.test') => 'local',
            str_starts_with($host, 'dev-') => 'Development',
            str_starts_with($host, 'stg-') => 'Staging',
            default => null,
        };
    @endphp

    @if ($environmentLabel)
        <div class="buy-now">
            <a href="#" class="btn btn-danger btn-buy-now">{{ $environmentLabel }}</a>
        </div>
    @endif

    <!-- Core JS -->

    <script src="{{ asset('sneat/assets/vendor/libs/jquery/jquery.js') }}"></script>

    <script src="{{ asset('sneat/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('sneat/assets/vendor/js/bootstrap.js') }}"></script>

    <script src="{{ asset('sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

    <script src="{{ asset('sneat/assets/vendor/js/menu.js') }}"></script>

    <!-- endbuild -->

    <!-- Main JS -->

    <script src="{{ asset('sneat/assets/js/main.js') }}"></script>

    {{-- Datatables --}}
    <script src="{{ asset('datatables/datatables.min.js') }}"></script>

    <script>
        window.sneatSearchMenuItems = {{ \Illuminate\Support\Js::from($searchMenuItems ?? []) }};
    </script>

    @stack('scripts')
</body>

</html>
