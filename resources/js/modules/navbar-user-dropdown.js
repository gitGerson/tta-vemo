document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('navbar-user-dropdown');
    const menu = document.getElementById('navbar-user-menu');
    const container = toggle?.closest('.dropdown');

    if (!toggle || !menu || !container) {
        return;
    }

    const closeMenu = () => {
        toggle.setAttribute('aria-expanded', 'false');
        toggle.classList.remove('show');
        menu.classList.remove('show');
        container.classList.remove('show');
    };

    const openMenu = () => {
        toggle.setAttribute('aria-expanded', 'true');
        toggle.classList.add('show');
        menu.classList.add('show');
        container.classList.add('show');
    };

    toggle.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();

        if (menu.classList.contains('show')) {
            closeMenu();
            return;
        }

        openMenu();
    });

    document.addEventListener('click', (event) => {
        if (!container.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });
});
