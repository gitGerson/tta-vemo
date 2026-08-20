/**
 * Generic manual dropdown toggle for `[data-dropdown-toggle]` buttons.
 *
 * This app loads two Bootstrap bundles (Sneat vendor + npm), so the native
 * dropdown data-api fires twice and immediately re-closes the menu. Page-level
 * dropdowns therefore opt into this hand-rolled toggle instead (same behavior as
 * the Tabulator toolbar's Export/Kolom menus): the button toggles `.show` on its
 * sibling `.dropdown-menu`, and an outside click or Escape closes it.
 */
function wireDropdown(button) {
    if (button.dataset.dropdownReady === '1') {
        return;
    }

    button.dataset.dropdownReady = '1';

    const container = button.closest('.dropdown');
    const menu = container?.querySelector('.dropdown-menu');

    if (!container || !menu) {
        return;
    }

    const close = () => {
        menu.classList.remove('show');
        button.setAttribute('aria-expanded', 'false');
    };

    button.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        const isOpen = menu.classList.toggle('show');
        button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    document.addEventListener('click', (event) => {
        if (!container.contains(event.target)) {
            close();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
        }
    });
}

function initManualDropdowns() {
    document.querySelectorAll('[data-dropdown-toggle]').forEach(wireDropdown);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initManualDropdowns);
} else {
    initManualDropdowns();
}
