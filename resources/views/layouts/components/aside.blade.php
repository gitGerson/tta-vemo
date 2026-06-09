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

            @foreach ($section['nodes'] ?? $section['items'] as $node)
                @if (($node['type'] ?? 'item') === 'group')
                    <li class="menu-item {{ $node['is_active'] ? 'active open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx {{ $node['icon'] }}"></i>
                            <div class="text-truncate" data-i18n="{{ $node['label'] }}">{{ $node['label'] }}</div>
                        </a>
                        <ul class="menu-sub">
                            @foreach ($node['items'] as $child)
                                <li class="menu-item {{ $child['is_active'] ? 'active' : '' }}">
                                    <a href="{{ $child['url'] }}" class="menu-link">
                                        <div class="text-truncate" data-i18n="{{ $child['sidebar_label'] }}">{{ $child['sidebar_label'] }}</div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @else
                    <li class="menu-item {{ $node['is_active'] ? 'active' : '' }}">
                        <a href="{{ $node['url'] }}" class="menu-link">
                            <i class="menu-icon tf-icons bx {{ $node['icon'] }}"></i>
                            <div class="text-truncate" data-i18n="{{ $node['title'] }}">{{ $node['title'] }}</div>
                        </a>
                    </li>
                @endif
            @endforeach
        @endforeach
    </ul>
</aside>
