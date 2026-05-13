const root = document.documentElement;

try {
    const storedTheme = localStorage.getItem('sneat-theme');
    const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)').matches;
    const theme = storedTheme || (prefersDark ? 'dark' : 'light');

    root.setAttribute('data-bs-theme', theme);
    root.classList.toggle('theme-dark', theme === 'dark');
} catch (error) {
    root.setAttribute('data-bs-theme', 'light');
}
