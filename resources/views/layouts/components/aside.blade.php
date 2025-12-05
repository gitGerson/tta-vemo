<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('home') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <span class="text-primary">
                    <img src="{{ asset('assets/logo.png') }}" alt="" class="img-fluid w-50">
                </span>
            </span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
        </a>
    </div>

    <div class="menu-divider mt-0"></div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}">
            <a href="{{ route('home') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-smile"></i>
                <div class="text-truncate" data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('record.*') ? 'active' : '' }}">
            <a href="{{ route('record.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-gas-pump"></i>
                <div class="text-truncate">Pelacakan BBM</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('maintenances.*') ? 'active' : '' }}">
            <a href="{{ route('maintenances.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-wrench"></i>
                <div class="text-truncate">Pemeliharaan</div>
            </a>
        </li>

        <!-- Master Data -->
        @if (auth()->user()->hasPermission('master.categories.view') || auth()->user()->hasPermission('master.products.view'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Master Data</span>
            </li>
        @endif

        <!-- Perusahaan -->
        @if (auth()->user()->hasPermission('master.categories.view'))
            <li class="menu-item {{ request()->routeIs('company.*') ? 'active' : '' }}">
                <a href="{{ route('company.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-category"></i>
                    <div class="text-truncate" data-i18n="Perusahaan">Perusahaan</div>
                </a>
            </li>
        @endif

        @if (auth()->user()->hasPermission('master.categories.view'))
            <li class="menu-item {{ request()->routeIs('driver.*') ? 'active' : '' }}">
                <a href="{{ route('driver.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-id-card"></i>
                    <div class="text-truncate">Driver</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('vehicle.*') ? 'active' : '' }}">
                <a href="{{ route('vehicle.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-car"></i>
                    <div class="text-truncate">Kendaraan</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('destination.*') ? 'active' : '' }}">
                <a href="{{ route('destination.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-map"></i>
                    <div class="text-truncate">Tujuan</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('type-maintenance.*') ? 'active' : '' }}">
                <a href="{{ route('type-maintenance.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-wrench"></i>
                    <div class="text-truncate">Tipe Maintenance</div>
                </a>
            </li>
        @endif

        @if (auth()->user()->hasPermission('master.categories.view'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Rekap Laporan</span>
            </li>
            <li class="menu-item {{ request()->routeIs('report.daily') ? 'active' : '' }}">
                <a href="{{ route('report.daily') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                    <div class="text-truncate">Laporan Harian</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('report.monthly') ? 'active' : '' }}">
                <a href="{{ route('report.monthly') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar-event"></i>
                    <div class="text-truncate">Laporan Bulanan</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('report.maintenances.daily') ? 'active' : '' }}">
                <a href="{{ route('report.maintenances.daily') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-cog"></i>
                    <div class="text-truncate">MTC Harian</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('report.maintenances.year') ? 'active' : '' }}">
                <a href="{{ route('report.maintenances.year') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar"></i>
                    <div class="text-truncate">MTC Tahunan</div>
                </a>
            </li>
        @endif

        <!-- Management -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Management</span>
        </li>

        <!-- Users -->
        @if (auth()->user()->hasPermission('management.users.view'))
            <li class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <a href="{{ route('users.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user"></i>
                    <div class="text-truncate" data-i18n="Users">Users</div>
                </a>
            </li>
        @endif

        <!-- Roles (RBAC) -->
        @if (auth()->user()->hasPermission('management.roles.view'))
            <li class="menu-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <a href="{{ route('roles.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-shield"></i>
                    <div class="text-truncate" data-i18n="Roles">Role Templates</div>
                </a>
            </li>
        @endif
    </ul>
</aside>