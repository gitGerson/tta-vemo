function normalizeText(value) {
    return String(value || '').toLowerCase();
}

function createResultItem(item, onClick) {
    const resultItem = document.createElement('a');
    resultItem.href = item.url || '#';
    resultItem.className = 'search-result-item';

    const iconWrapper = document.createElement('div');
    iconWrapper.className = 'result-icon';

    const icon = document.createElement('i');
    icon.className = `bx ${item.icon || 'bx-link'}`;
    iconWrapper.appendChild(icon);

    const content = document.createElement('div');
    content.className = 'result-content';

    const title = document.createElement('div');
    title.className = 'result-title';
    title.textContent = item.title || '';

    const description = document.createElement('div');
    description.className = 'result-description';
    description.textContent = item.description || '';

    content.append(title, description);
    resultItem.append(iconWrapper, content);
    resultItem.addEventListener('click', onClick);

    return resultItem;
}

function initSneatMenuSearch() {
    const menuSearch = document.getElementById('menu-search');
    const searchResults = document.getElementById('search-results');
    const searchResultsContent = document.getElementById('search-results-content');
    const noResults = document.getElementById('no-results');

    if (!menuSearch || !searchResults || !searchResultsContent || !noResults || menuSearch.dataset.searchInitialized === 'true') {
        return;
    }

    menuSearch.dataset.searchInitialized = 'true';

    const menuItems = Array.isArray(window.sneatSearchMenuItems) ? window.sneatSearchMenuItems : [];
    let currentFocusIndex = -1;

    const showSearchResults = () => {
        searchResults.classList.remove('d-none');
    };

    const hideSearchResults = () => {
        searchResults.classList.add('d-none');
        currentFocusIndex = -1;
    };

    const updateFocus = (items) => {
        items.forEach((item, index) => {
            item.classList.toggle('keyboard-focus', index === currentFocusIndex);
        });
    };

    const renderResults = (results) => {
        searchResultsContent.innerHTML = '';

        if (results.length === 0) {
            noResults.classList.remove('d-none');
            return;
        }

        noResults.classList.add('d-none');

        results.forEach((item) => {
            searchResultsContent.appendChild(createResultItem(item, () => {
                hideSearchResults();
                menuSearch.value = '';
            }));
        });
    };

    const performSearch = (query) => {
        const searchTerms = query.toLowerCase().split(' ').filter(Boolean);
        const results = menuItems.filter((item) => {
            const keywords = Array.isArray(item.keywords) ? item.keywords : [];

            return searchTerms.every((term) => (
                keywords.some((keyword) => normalizeText(keyword).includes(term))
                || normalizeText(item.title).includes(term)
                || normalizeText(item.description).includes(term)
            ));
        });

        renderResults(results);
    };

    menuSearch.addEventListener('input', (event) => {
        const query = event.target.value.trim();

        if (query.length >= 2) {
            performSearch(query);
            showSearchResults();
            return;
        }

        hideSearchResults();
    });

    menuSearch.addEventListener('focus', (event) => {
        if (event.target.value.trim().length >= 2) {
            showSearchResults();
        }
    });

    menuSearch.addEventListener('keydown', (event) => {
        const items = searchResultsContent.querySelectorAll('.search-result-item');

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            currentFocusIndex = Math.min(currentFocusIndex + 1, items.length - 1);
            updateFocus(items);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            currentFocusIndex = Math.max(currentFocusIndex - 1, -1);
            updateFocus(items);
        } else if (event.key === 'Enter') {
            event.preventDefault();

            if (currentFocusIndex >= 0 && items[currentFocusIndex]) {
                items[currentFocusIndex].click();
            }
        } else if (event.key === 'Escape') {
            hideSearchResults();
            menuSearch.blur();
        }
    });

    document.addEventListener('click', (event) => {
        if (!menuSearch.contains(event.target) && !searchResults.contains(event.target)) {
            hideSearchResults();
        }
    });

    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            menuSearch.focus();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSneatMenuSearch);
} else {
    initSneatMenuSearch();
}
