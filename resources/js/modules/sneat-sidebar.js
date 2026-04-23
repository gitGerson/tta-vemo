function initSneatSidebar() {
    const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
    const layoutContainer = document.querySelector('.layout-container');

    if (!sidebarToggleBtn || !layoutContainer || sidebarToggleBtn.dataset.sidebarInitialized === 'true') {
        return;
    }

    sidebarToggleBtn.dataset.sidebarInitialized = 'true';

    const updateToggleIcon = (isHidden) => {
        const icon = sidebarToggleBtn.querySelector('i');

        if (!icon) {
            return;
        }

        icon.className = isHidden ? 'icon-base bx bx-menu-alt-right icon-md' : 'icon-base bx bx-menu icon-md';
        sidebarToggleBtn.title = isHidden ? 'Show Sidebar' : 'Hide Sidebar';
    };

    const savedSidebarState = localStorage.getItem('sidebarHidden');

    if (savedSidebarState === 'true') {
        layoutContainer.classList.add('sidebar-hidden');
        updateToggleIcon(true);
    }

    sidebarToggleBtn.addEventListener('click', (event) => {
        event.preventDefault();

        const shouldHideSidebar = !layoutContainer.classList.contains('sidebar-hidden');

        layoutContainer.classList.toggle('sidebar-hidden', shouldHideSidebar);
        localStorage.setItem('sidebarHidden', shouldHideSidebar ? 'true' : 'false');
        updateToggleIcon(shouldHideSidebar);
    });

    document.addEventListener('keydown', (event) => {
        if (event.ctrlKey && event.key.toLowerCase() === 'b') {
            event.preventDefault();
            sidebarToggleBtn.click();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSneatSidebar);
} else {
    initSneatSidebar();
}
