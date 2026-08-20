@php
    // 'Semua' is server-rendered from the composer payload; the per-group panes are
    // empty shells the JS fills on first activation, so the initial page render
    // costs no more than a single-list dropdown did.
    $notificationGroups = \App\Services\NotificationService::GROUPS;
    $notificationGroupCounts = $navbarNotifications['groups'] ?? [];
@endphp

{{--
    Deliberately rendered as a sibling of the layout wrapper rather than inside
    <nav>: a position:fixed panel nested in .navbar-detached can inherit a
    stacking/overflow context.

    No data-bs-toggle / data-bs-dismiss anywhere. The app loads two Bootstrap
    bundles (Sneat vendor + npm) and both register the offcanvas data-api, so a
    declarative trigger builds two instances, two backdrops and two scroll locks —
    dismissing then only unwinds one and leaves the page stuck behind a grey
    overlay. navbar-notifications.js drives a single instance instead.
--}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="notifications-offcanvas"
    aria-labelledby="notifications-offcanvas-title" data-notifications-dropdown
    data-notifications-url="{{ route('notifications.index') }}"
    data-notifications-read-url="{{ route('notifications.read') }}">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title mb-0 me-auto" id="notifications-offcanvas-title">Notifikasi</h5>
        @if ($navbarNotifications['count'] > 0)
            <span class="badge bg-label-primary me-2">{{ $navbarNotifications['count'] }} Baru</span>
        @endif
        <button type="button" class="btn-close" aria-label="Tutup" data-notifications-close></button>
    </div>

    <div class="offcanvas-body p-0 d-flex flex-column">
        {{-- Outside the scroller below, so the strip stays pinned while a pane scrolls. --}}
        <ul class="nav nav-tabs nav-fill flex-nowrap border-bottom" role="tablist" data-notifications-tabs>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link active px-2 py-2 small border-0" id="notifications-tab-all"
                    data-notifications-tab="" role="tab" aria-controls="notifications-pane-all" aria-selected="true">
                    Semua
                    @if ($navbarNotifications['count'] > 0)
                        <span class="badge bg-label-primary ms-1">{{ $navbarNotifications['count'] }}</span>
                    @endif
                </button>
            </li>
            @foreach ($notificationGroups as $groupKey => $group)
                <li class="nav-item" role="presentation">
                    <button type="button" class="nav-link px-2 py-2 small border-0"
                        id="notifications-tab-{{ $groupKey }}" data-notifications-tab="{{ $groupKey }}" role="tab"
                        aria-controls="notifications-pane-{{ $groupKey }}" aria-selected="false"
                        title="{{ $group['label'] }}">
                        <i class="icon-base bx {{ $group['icon'] }}"></i>
                        <span class="visually-hidden">{{ $group['label'] }}</span>
                        @if (($notificationGroupCounts[$groupKey] ?? 0) > 0)
                            <span class="badge bg-label-primary ms-1">{{ $notificationGroupCounts[$groupKey] }}</span>
                        @endif
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content p-0 flex-grow-1 overflow-auto" data-notifications-panes>
            <div class="tab-pane fade show active" id="notifications-pane-all" role="tabpanel"
                aria-labelledby="notifications-tab-all" tabindex="0" data-notifications-pane=""
                data-notifications-loaded="1"
                data-notifications-next-offset="{{ $navbarNotifications['next_offset'] ?? count($navbarNotifications['items']) }}"
                data-notifications-has-more="{{ ($navbarNotifications['has_more'] ?? false) ? '1' : '0' }}">
                <ul class="list-group list-group-flush" data-notifications-list>
                    @forelse ($navbarNotifications['items'] as $notification)
                        <li class="list-group-item list-group-item-action dropdown-notifications-item p-0">
                            <a href="{{ $notification['link'] }}" class="d-flex align-items-center text-body p-3"
                                data-notification-key="{{ $notification['key'] }}">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-{{ $notification['color'] }}"><i
                                            class="icon-base bx {{ $notification['icon'] }}"></i></span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $notification['title'] }}</h6>
                                    <small class="text-body-secondary">{{ $notification['subtitle'] }}</small>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-body-secondary py-4" data-notifications-empty>
                            <i class="icon-base bx bx-bell-off d-block mb-2" style="font-size: 1.75rem;"></i>
                            Tidak ada notifikasi
                        </li>
                    @endforelse
                </ul>
                @if ($navbarNotifications['has_more'] ?? false)
                    <div class="dropdown-menu-footer border-top">
                        <button type="button" class="btn btn-sm btn-link w-100 py-2 text-decoration-none"
                            data-notifications-load-more>
                            Lihat lainnya
                        </button>
                    </div>
                @endif
            </div>
            @foreach ($notificationGroups as $groupKey => $group)
                <div class="tab-pane fade" id="notifications-pane-{{ $groupKey }}" role="tabpanel"
                    aria-labelledby="notifications-tab-{{ $groupKey }}" tabindex="0"
                    data-notifications-pane="{{ $groupKey }}" data-notifications-loaded="0"
                    data-notifications-next-offset="0" data-notifications-has-more="0">
                    <ul class="list-group list-group-flush" data-notifications-list></ul>
                </div>
            @endforeach
        </div>
    </div>
</div>
