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

function initNavbarNotifications() {
    const dropdown = document.querySelector('[data-notifications-dropdown]');
    const list = dropdown?.querySelector('[data-notifications-list]');
    const loadMore = dropdown?.querySelector('[data-notifications-load-more]');

    if (!dropdown || !list) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    list.addEventListener('click', async (event) => {
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
            await fetch(dropdown.dataset.notificationsReadUrl, {
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

    loadMore?.addEventListener('click', async (event) => {
        event.preventDefault();
        event.stopPropagation();

        if (loadMore.disabled) {
            return;
        }

        loadMore.disabled = true;
        loadMore.textContent = 'Loading...';

        const url = new URL(dropdown.dataset.notificationsUrl, window.location.origin);
        url.searchParams.set('offset', dropdown.dataset.notificationsNextOffset || '0');

        try {
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('Unable to load notifications.');
            }

            const data = await response.json();
            const emptyState = list.querySelector('[data-notifications-empty]');
            emptyState?.remove();

            data.items.forEach((notification) => {
                list.appendChild(createNotificationItem(notification));
            });

            dropdown.dataset.notificationsNextOffset = String(data.next_offset);
            dropdown.dataset.notificationsHasMore = data.has_more ? '1' : '0';

            if (!data.has_more) {
                loadMore.closest('.dropdown-menu-footer')?.remove();
                return;
            }

            loadMore.disabled = false;
            loadMore.textContent = 'View more';
        } catch (error) {
            loadMore.disabled = false;
            loadMore.textContent = 'Try again';
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNavbarNotifications);
} else {
    initNavbarNotifications();
}
