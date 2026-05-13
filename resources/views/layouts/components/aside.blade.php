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
        @foreach ($sidebarMenuSections ?? [] as $section)
            @if (filled($section['label']))
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">{{ $section['label'] }}</span>
                </li>
            @endif

            @foreach ($section['items'] as $item)
                <li class="menu-item {{ $item['is_active'] ? 'active' : '' }}">
                    <a href="{{ $item['url'] }}" class="menu-link">
                        <i class="menu-icon tf-icons bx {{ $item['icon'] }}"></i>
                        <div class="text-truncate" data-i18n="{{ $item['title'] }}">{{ $item['title'] }}</div>
                    </a>
                </li>
            @endforeach
        @endforeach
    </ul>
</aside>
