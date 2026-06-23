const root = document.documentElement;

try {
    localStorage.setItem('sneat-theme', 'light');
} catch {}

root.setAttribute('data-bs-theme', 'light');
root.classList.remove('theme-dark');
