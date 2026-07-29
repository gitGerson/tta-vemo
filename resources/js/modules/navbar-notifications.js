// Markup must stay identical to the @forelse branch in
// resources/views/layouts/components/notifications-offcanvas.blade.php — the
// server renders the 'Semua' pane, this builds every appended and lazily-loaded
// item.
function createNotificationItem(notification) {
    const item = document.createElement('li');
    item.className = 'list-group-item list-group-item-action dropdown-notifications-item p-0';

    const link = document.createElement('a');
    link.href = notification.link;
    link.className = 'd-flex align-items-center text-body p-3';
    link.dataset.notificationKey = notification.key;

    const avatar = document.createElement('div');
    avatar.className = 'avatar flex-shrink-0 me-3';

    const avatarInitial = document.createElement('span');
    avatarInitial.classList.add('avatar-initial', 'rounded', `bg-label-${notification.color}`);

    const icon = document.createElement('i');
    icon.classList.add('icon-base', 'bx', notification.icon);

    const body = document.createElement('div');
    body.className = 'flex-grow-1';

    const title = document.createElement('h6');
    title.className = 'mb-0';
    title.textContent = notification.title;

    const subtitle = document.createElement('small');
    subtitle.className = 'text-body-secondary';
    subtitle.textContent = notification.subtitle;

    avatarInitial.appendChild(icon);
    avatar.appendChild(avatarInitial);
    body.append(title, subtitle);
    link.append(avatar, body);
    item.appendChild(link);

    return item;
}

function createEmptyState() {
    const empty = document.createElement('li');
    empty.className = 'list-group-item text-center text-body-secondary py-4';
    empty.dataset.notificationsEmpty = '';

    const icon = document.createElement('i');
    icon.className = 'icon-base bx bx-bell-off d-block mb-2';
    icon.style.fontSize = '1.75rem';

    empty.append(icon, document.createTextNode('Tidak ada notifikasi'));

    return empty;
}

function createLoadMoreFooter() {
    const footer = document.createElement('div');
    footer.className = 'dropdown-menu-footer border-top';

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-sm btn-link w-100 py-2 text-decoration-none';
    button.dataset.notificationsLoadMore = '';
    button.textContent = 'Lihat lainnya';

    footer.appendChild(button);

    return footer;
}

function initNavbarNotifications() {
    const panel = document.querySelector('[data-notifications-dropdown]');
    const panes = panel?.querySelector('[data-notifications-panes]');
    const tabs = panel?.querySelector('[data-notifications-tabs]');

    if (!panel || !panes || panel.dataset.notificationsInitialized === '1') {
        return;
    }

    panel.dataset.notificationsInitialized = '1';

    // Driven from here rather than with data-bs-toggle="offcanvas": both loaded
    // Bootstrap bundles register that data-api and each keeps its own instance
    // map, so a declarative trigger opens two offcanvases — two backdrops, two
    // scroll locks — and dismissing unwinds only one. Creating the instance
    // ourselves means exactly one exists, and its backdrop/Escape handling works.
    const offcanvas = () => window.bootstrap?.Offcanvas.getOrCreateInstance(panel);

    document.querySelector('[data-notifications-toggle]')?.addEventListener('click', (event) => {
        event.preventDefault();
        offcanvas()?.toggle();
    });

    panel.querySelector('[data-notifications-close]')?.addEventListener('click', (event) => {
        event.preventDefault();
        offcanvas()?.hide();
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const paneFor = (group) => panes.querySelector(`[data-notifications-pane="${group}"]`);

    // Fetch one page for a pane. offset comes from the pane itself, so each tab
    // pages through its own group independently of the others.
    const fetchPage = async (pane) => {
        const url = new URL(panel.dataset.notificationsUrl, window.location.origin);
        url.searchParams.set('offset', pane.dataset.notificationsNextOffset || '0');

        if (pane.dataset.notificationsPane) {
            url.searchParams.set('group', pane.dataset.notificationsPane);
        }

        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('Unable to load notifications.');
        }

        return response.json();
    };

    const appendPage = (pane, data) => {
        const list = pane.querySelector('[data-notifications-list]');

        pane.querySelector('[data-notifications-empty]')?.remove();

        data.items.forEach((notification) => {
            list.appendChild(createNotificationItem(notification));
        });

        pane.dataset.notificationsNextOffset = String(data.next_offset);
        pane.dataset.notificationsHasMore = data.has_more ? '1' : '0';

        if (!list.children.length) {
            list.appendChild(createEmptyState());
        }

        const footer = pane.querySelector('.dropdown-menu-footer');

        if (data.has_more && !footer) {
            pane.appendChild(createLoadMoreFooter());
        } else if (!data.has_more) {
            footer?.remove();
        }
    };

    // Hand-rolled rather than data-bs-toggle="tab" for the same double-bundle
    // reason as the offcanvas above. Clicks inside the panel don't dismiss it, so
    // no stopPropagation is needed.
    tabs?.addEventListener('click', async (event) => {
        const trigger = event.target instanceof Element
            ? event.target.closest('[data-notifications-tab]')
            : null;

        if (!trigger) {
            return;
        }

        event.preventDefault();

        const group = trigger.dataset.notificationsTab;
        const pane = paneFor(group);

        if (!pane) {
            return;
        }

        tabs.querySelectorAll('[data-notifications-tab]').forEach((tab) => {
            const isActive = tab === trigger;
            tab.classList.toggle('active', isActive);
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        panes.querySelectorAll('[data-notifications-pane]').forEach((item) => {
            item.classList.toggle('show', item === pane);
            item.classList.toggle('active', item === pane);
        });

        if (pane.dataset.notificationsLoaded === '1') {
            return;
        }

        // Marked loaded up front so a double click can't fire two requests; reset
        // on failure so the next activation retries.
        pane.dataset.notificationsLoaded = '1';

        try {
            appendPage(pane, await fetchPage(pane));
        } catch (error) {
            pane.dataset.notificationsLoaded = '0';
        }
    });

    // Delegated from the pane container so it covers every pane, including ones
    // rendered after a lazy load.
    panes.addEventListener('click', async (event) => {
        const link = event.target instanceof Element
            ? event.target.closest('[data-notification-key]')
            : null;

        if (
            !link
            || event.button !== 0
            || event.ctrlKey
            || event.metaKey
            || event.shiftKey
            || event.altKey
        ) {
            return;
        }

        event.preventDefault();

        try {
            await fetch(panel.dataset.notificationsReadUrl, {
                method: 'POST',
                keepalive: true,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ key: link.dataset.notificationKey }),
            });
        } finally {
            window.location.assign(link.href);
        }
    });

    panes.addEventListener('click', async (event) => {
        const loadMore = event.target instanceof Element
            ? event.target.closest('[data-notifications-load-more]')
            : null;

        if (!loadMore || loadMore.disabled) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const pane = loadMore.closest('[data-notifications-pane]');

        if (!pane) {
            return;
        }

        loadMore.disabled = true;
        loadMore.textContent = 'Memuat...';

        try {
            appendPage(pane, await fetchPage(pane));

            // appendPage removes the footer when the group is exhausted, so only
            // restore the label if this button is still in the document.
            if (loadMore.isConnected) {
                loadMore.disabled = false;
                loadMore.textContent = 'Lihat lainnya';
            }
        } catch (error) {
            loadMore.disabled = false;
            loadMore.textContent = 'Coba lagi';
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNavbarNotifications);
} else {
    initNavbarNotifications();
}
