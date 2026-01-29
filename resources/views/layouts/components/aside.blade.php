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

    @php
        $menu = config('menu');
        $user = auth()->user();
        $canView = function ($permission) use ($user) {
            return !$permission || $user?->hasPermission($permission);
        };
        $isVisible = function ($item) use ($canView) {
            return $canView($item['permission'] ?? null);
        };
        $hasVisibleItem = function ($items) use ($isVisible) {
            foreach ($items as $item) {
                if ($isVisible($item)) {
                    return true;
                }
            }
            return false;
        };
        $isActive = function ($item) {
            $pattern = $item['routePattern'] ?? $item['route'];
            return request()->routeIs($pattern);
        };
    @endphp

    <ul class="menu-inner py-1">
        @foreach ($menu['main'] as $item)
            @if ($isVisible($item))
                <li class="menu-item {{ $isActive($item) ? 'active' : '' }}">
                    <a href="{{ route($item['route']) }}" class="menu-link">
                        <i class="menu-icon tf-icons bx {{ $item['icon'] }}"></i>
                        <div class="text-truncate">{{ $item['title'] }}</div>
                    </a>
                </li>
            @endif
        @endforeach

        @foreach ($menu['sections'] as $section)
            @if ($hasVisibleItem($section['items']))
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">{{ $section['header'] }}</span>
                </li>

                @foreach ($section['items'] as $item)
                    @if ($isVisible($item))
                        <li class="menu-item {{ $isActive($item) ? 'active' : '' }}">
                            <a href="{{ route($item['route']) }}" class="menu-link">
                                <i class="menu-icon tf-icons bx {{ $item['icon'] }}"></i>
                                <div class="text-truncate">{{ $item['title'] }}</div>
                            </a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach
    </ul>
</aside>
