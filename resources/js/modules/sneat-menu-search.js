import Fuse from 'fuse.js';
import axios from 'axios';

const RECORD_DEBOUNCE_MS = 250;

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

function createGroupHeader(label) {
    const header = document.createElement('div');
    header.className = 'search-group-header';
    header.textContent = label;
    return header;
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
    const fuse = new Fuse(menuItems, {
        keys: [
            { name: 'title', weight: 0.5 },
            { name: 'keywords', weight: 0.3 },
            { name: 'description', weight: 0.2 },
        ],
        threshold: 0.4,
        ignoreLocation: true,
        minMatchCharLength: 2,
    });

    let currentFocusIndex = -1;
    let recordResults = [];
    let recordsLoading = false;
    let recordAbort = null;
    let recordDebounceTimer = null;

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

    const onItemClick = () => {
        hideSearchResults();
        menuSearch.value = '';
    };

    const render = (pages) => {
        searchResultsContent.innerHTML = '';
        currentFocusIndex = -1;

        const hasPages = pages.length > 0;
        const hasRecords = recordResults.length > 0;

        if (!hasPages && !hasRecords && !recordsLoading) {
            noResults.classList.remove('d-none');
            return;
        }

        noResults.classList.add('d-none');

        if (hasPages) {
            searchResultsContent.appendChild(createGroupHeader('Pages'));
            pages.forEach((item) => {
                searchResultsContent.appendChild(createResultItem(item, onItemClick));
            });
        }

        if (hasRecords || recordsLoading) {
            searchResultsContent.appendChild(createGroupHeader('Records'));

            recordResults.forEach((item) => {
                searchResultsContent.appendChild(createResultItem(item, onItemClick));
            });

            if (recordsLoading && !hasRecords) {
                const loading = document.createElement('div');
                loading.className = 'search-group-loading';
                loading.textContent = 'Searching records…';
                searchResultsContent.appendChild(loading);
            }
        }
    };

    const searchPages = (query) => fuse.search(query).map((result) => result.item);

    const fetchRecords = (query) => {
        if (recordAbort) {
            recordAbort.abort();
        }
        recordAbort = new AbortController();
        recordsLoading = true;

        axios
            .get('/search', {
                params: { q: query },
                signal: recordAbort.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then(({ data }) => {
                // Ignore stale responses for an input that has since changed.
                if (menuSearch.value.trim() !== query) {
                    return;
                }
                recordResults = Array.isArray(data.results) ? data.results : [];
                recordsLoading = false;
                render(searchPages(query));
            })
            .catch((error) => {
                if (axios.isCancel?.(error) || error.name === 'CanceledError' || error.name === 'AbortError') {
                    return;
                }
                if (menuSearch.value.trim() !== query) {
                    return;
                }
                recordResults = [];
                recordsLoading = false;
                render(searchPages(query));
            });
    };

    const performSearch = (query) => {
        recordResults = [];
        render(searchPages(query));

        clearTimeout(recordDebounceTimer);
        recordDebounceTimer = setTimeout(() => fetchRecords(query), RECORD_DEBOUNCE_MS);
    };

    menuSearch.addEventListener('input', (event) => {
        const query = event.target.value.trim();

        if (query.length >= 2) {
            performSearch(query);
            showSearchResults();
            return;
        }

        clearTimeout(recordDebounceTimer);
        if (recordAbort) {
            recordAbort.abort();
        }
        recordResults = [];
        recordsLoading = false;
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
