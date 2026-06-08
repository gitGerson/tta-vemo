import { bootstrap } from './bootstrap';
import './modules/sneat-sidebar';
import './modules/sneat-menu-search';
import './modules/navbar-user-dropdown';
import './modules/navbar-notifications';
import './modules/manual-dropdown';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach((toggle) => {
        bootstrap.Dropdown.getOrCreateInstance(toggle);
    });
});
