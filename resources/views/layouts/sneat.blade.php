<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="layout-menu-fixed layout-compact" data-bs-theme="light"
    data-assets-path="../assets/"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    @vite(['resources/sass/app.scss', 'resources/js/modules/sneat-theme.js', 'resources/js/app.js'])

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

    <style>
        /* Jarak antar dropdown, search box, dan export buttons */
        .dt-container .dt-length,
        .dt-container .dt-search,
        .dt-container .dt-buttons {
            margin-bottom: 1rem;
        }

        /* Responsive spacing untuk kolom atas */
        @media (min-width: 768px) {
            .dt-container {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                align-items: center;
            }

            .dt-length,
            .dt-buttons,
            .dt-search {
                margin-bottom: 0.5rem;
            }
        }

        /* Sidebar Toggle Styles */
        .sidebar-hidden #layout-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .sidebar-hidden .layout-page {
            padding-inline-start: 0 !important;
            -webkit-padding-start: 0 !important;
            padding-left: 0 !important;
            margin-left: 0 !important;
            transition: padding-inline-start 0.3s ease, margin-left 0.3s ease;
        }

        .sidebar-hidden .layout-navbar {
            left: 0 !important;
            width: 100% !important;
            margin-left: 0 !important;
            transition: left 0.3s ease, width 0.3s ease, margin-left 0.3s ease;
        }

        /* Ensure navbar is full width when sidebar is hidden */
        .sidebar-hidden .layout-navbar.navbar-detached {
            width: calc(100% - 3rem) !important;
            left: 1.5rem !important;
        }

        /* Fullscreen container when sidebar is hidden */
        .sidebar-hidden .container-xxl {
            max-width: none !important;
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
        }

        /* Make content truly fullscreen when sidebar is hidden */
        .sidebar-hidden .content-wrapper {
            margin-left: 0 !important;
        }

        /* Ensure tables and DataTables use full width */
        .sidebar-hidden .table-responsive {
            width: 100% !important;
        }

        .sidebar-hidden .dt-container {
            width: 100% !important;
        }

        /* Smooth transitions for sidebar toggle */
        #layout-menu {
            transition: transform 0.3s ease;
        }

        .layout-page {
            transition: padding-inline-start 0.3s ease, margin-left 0.3s ease, padding-left 0.3s ease;
        }

        .layout-navbar {
            transition: left 0.3s ease, width 0.3s ease;
        }

        /* Toggle button hover effect */
        #sidebar-toggle-btn:hover {
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 0.375rem;
        }

        /* Active state for toggle button when sidebar is hidden */
        .sidebar-hidden #sidebar-toggle-btn {
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            border-radius: 0.375rem;
        }

        /* Responsive behavior - hide toggle on small screens */
        @media (max-width: 1199.98px) {
            .sidebar-toggle-desktop {
                display: none !important;
            }

            /* Ensure sidebar toggle doesn't affect mobile layout */
            .sidebar-hidden .layout-page {
                padding-inline-start: var(--bs-menu-width) !important;
                -webkit-padding-start: var(--bs-menu-width) !important;
            }
        }

        /* Desktop specific behavior */
        @media (min-width: 1200px) {

            /* Override default sidebar behavior only on desktop */
            .sidebar-hidden.layout-menu-fixed .layout-page,
            .sidebar-hidden.layout-menu-fixed-offcanvas .layout-page {
                padding-inline-start: 0 !important;
                -webkit-padding-start: 0 !important;
                padding-left: 0 !important;
            }
        }

        /* Menu Search Styles */
        .search-dropdown {
            position: absolute !important;
            top: 100% !important;
            left: 0 !important;
            z-index: 1050 !important;
            display: block !important;
            min-width: 300px;
            padding: 0.5rem 0;
            margin: 0.125rem 0 0;
            font-size: 0.875rem;
            color: var(--bs-body-color);
            text-align: left;
            background-color: var(--bs-dropdown-bg);
            background-clip: padding-box;
            border: var(--bs-dropdown-border-width) solid var(--bs-dropdown-border-color);
            border-radius: var(--bs-dropdown-border-radius);
            box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);
        }

        .search-dropdown.d-none {
            display: none !important;
        }

        .search-result-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            color: var(--bs-dropdown-link-color);
            text-decoration: none;
            transition: background-color 0.15s ease-in-out;
            border: none;
            width: 100%;
            background: none;
            text-align: left;
        }

        .search-result-item:hover,
        .search-result-item:focus {
            background-color: var(--bs-dropdown-link-hover-bg);
            color: var(--bs-dropdown-link-hover-color);
        }

        .search-result-item .result-icon {
            width: 1.5rem;
            height: 1.5rem;
            margin-right: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            border-radius: 0.375rem;
            color: var(--bs-primary);
        }

        .search-result-item .result-content {
            flex: 1;
        }

        .search-result-item .result-title {
            font-weight: 500;
            margin: 0;
            font-size: 0.875rem;
        }

        .search-result-item .result-description {
            font-size: 0.75rem;
            color: var(--bs-secondary-color);
            margin: 0;
        }

        /* Search input focus state */
        #menu-search:focus {
            box-shadow: none;
            border-color: transparent;
        }

        /* Keyboard navigation highlight */
        .search-result-item.keyboard-focus {
            background-color: var(--bs-dropdown-link-hover-bg);
            color: var(--bs-dropdown-link-hover-color);
        }

        /* Keyboard shortcut styling */
        .search-dropdown kbd {
            font-size: 0.75rem;
            padding: 0.125rem 0.375rem;
            color: var(--bs-gray-700);
            background-color: var(--bs-gray-100);
            border: 1px solid var(--bs-gray-300);
            border-radius: 0.25rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        /* Search dropdown header improvements */
        .search-dropdown .dropdown-header {
            padding: 0.75rem 1rem 0.5rem;
            margin-bottom: 0;
            font-size: 0.8rem;
            color: var(--bs-secondary-color);
            white-space: nowrap;
        }

        /* Responsive adjustments for search dropdown */
        @media (max-width: 576px) {
            .search-dropdown {
                width: 280px !important;
            }

            .search-dropdown .dropdown-header small span {
                display: none !important;
            }
        }
    </style>

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
