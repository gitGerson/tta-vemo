import { TabulatorFull as Tabulator } from 'tabulator-tables';
import 'tabulator-tables/dist/css/tabulator_bootstrap5.min.css';

/**
 * Trigger a native Tabulator download. CSV / JSON / HTML are built in; XLSX and
 * PDF need external libraries, which are lazy-loaded (separate chunks) only when
 * those formats are actually requested so the main bundle stays small.
 */
async function downloadTable(table, type, id) {
    try {
        if (type === 'xlsx') {
            window.XLSX = await import('xlsx');
            table.download('xlsx', `${id}.xlsx`, { sheetName: id });
        } else if (type === 'pdf') {
            const { jsPDF } = await import('jspdf');
            const { applyPlugin } = await import('jspdf-autotable');
            applyPlugin(jsPDF);
            window.jspdf = { jsPDF };
            table.download('pdf', `${id}.pdf`, { orientation: 'landscape' });
        } else {
            table.download(type, `${id}.${type}`);
        }
    } catch (error) {
        window.alert(`Export gagal: ${error.message}`);
    }
}

/**
 * Initialize every Tabulator table on the page.
 *
 * Each <x-table engine="tabulator"> renders a container div plus a sibling
 * JSON config script. The config carries the AJAX URL, columns, sort, and
 * clickable row settings; row data stays server-side.
 */
function initTabulators() {
    document.querySelectorAll('script[data-tabulator-config]').forEach((configEl) => {
        const id = configEl.getAttribute('data-tabulator-config');
        const el = document.getElementById(id);

        if (!el || el.dataset.tabulatorInitialized === '1') {
            return;
        }

        let config;
        try {
            config = JSON.parse(configEl.textContent);
        } catch (error) {
            return;
        }

        const editableFields = new Set((config.columns ?? []).filter((column) => column.editor).map((column) => column.field));
        const columns = config.selectableRows
            ? [
                  {
                      formatter: 'rowSelection',
                      titleFormatter: 'rowSelection',
                      titleFormatterParams: {
                          rowRange: 'active',
                      },
                      hozAlign: 'center',
                      headerHozAlign: 'center',
                      headerSort: false,
                      width: 46,
                      minWidth: 46,
                      resizable: false,
                      frozen: true,
                      download: false,
                      responsive: 0,
                  },
                  ...(config.columns ?? []),
              ]
            : (config.columns ?? []);

        const table = new Tabulator(el, {
            columns,
            layout: config.layout ?? 'fitColumns',
            responsiveLayout: config.responsiveLayout ?? false,
            selectableRows: config.selectableRows ? true : 'highlight',
            selectableRowsPersistence: false,
            pagination: true,
            paginationMode: 'remote',
            paginationSize: config.paginationSize ?? 10,
            paginationSizeSelector: config.paginationSizeSelector ?? [10, 25, 50, 100],
            sortMode: 'remote',
            filterMode: 'remote',
            persistence: { columns: ['visible'] },
            persistenceID: id,
            persistenceMode: 'local',
            ajaxURL: config.ajaxUrl,
            ajaxParams: {
                engine: 'tabulator',
            },
            ...(config.initialSort?.length ? { initialSort: config.initialSort } : {}),
        });

        el.tabulatorInstance = table;
        el.dataset.tabulatorInitialized = '1';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const dirtyRows = new Map();

        const requestJson = async (url, options = {}) => {
            const response = await fetch(url, {
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    ...(options.headers ?? {}),
                },
            });

            if (!response.ok) {
                throw new Error((await response.json().catch(() => null))?.message ?? 'Request failed');
            }

            return response.json();
        };

        const reloadData = () => {
            if (typeof table.reloadData === 'function') {
                table.reloadData();
            } else {
                table.setData();
            }
        };

        const addButton = document.getElementById(`${id}-add`);
        const deleteButton = document.getElementById(`${id}-delete`);
        const saveButton = document.getElementById(`${id}-save`);

        table.on('cellEdited', (cell) => {
            const field = cell.getField();

            if (!editableFields.has(field)) {
                return;
            }

            const row = cell.getRow();
            const rowData = row.getData();
            const rowId = rowData.id;

            if (!rowId) {
                return;
            }

            const changes = dirtyRows.get(rowId) ?? { id: rowId };
            changes[field] = cell.getValue();
            dirtyRows.set(rowId, changes);
            row.getElement().classList.add('table-tabulator-row-dirty');

            if (saveButton) {
                saveButton.disabled = false;
            }
        });

        table.on('rowSelectionChanged', () => {
            if (deleteButton) {
                deleteButton.disabled = table.getSelectedRows().length === 0;
            }
        });

        if (addButton && config.createUrl) {
            addButton.addEventListener('click', async () => {
                addButton.disabled = true;

                try {
                    const payload = await requestJson(config.createUrl, { method: 'POST', body: '{}' });
                    await table.addRow(payload.row, true);
                } catch (error) {
                    window.alert(error.message);
                } finally {
                    addButton.disabled = false;
                }
            });
        }

        if (deleteButton && config.deleteUrl) {
            deleteButton.addEventListener('click', async () => {
                const rows = table.getSelectedRows();

                if (rows.length === 0 || !window.confirm(`Delete ${rows.length} selected row(s)?`)) {
                    return;
                }

                deleteButton.disabled = true;

                try {
                    await requestJson(config.deleteUrl, {
                        method: 'DELETE',
                        body: JSON.stringify({ ids: rows.map((row) => row.getData().id).filter(Boolean) }),
                    });

                    rows.forEach((row) => {
                        dirtyRows.delete(row.getData().id);
                        row.delete();
                    });

                    if (saveButton) {
                        saveButton.disabled = dirtyRows.size === 0;
                    }
                } catch (error) {
                    window.alert(error.message);
                } finally {
                    deleteButton.disabled = true;
                }
            });
        }

        if (saveButton && config.updateUrl) {
            saveButton.addEventListener('click', async () => {
                const rows = Array.from(dirtyRows.values());

                if (rows.length === 0) {
                    return;
                }

                saveButton.disabled = true;

                try {
                    await requestJson(config.updateUrl, {
                        method: 'PATCH',
                        body: JSON.stringify({ rows }),
                    });

                    dirtyRows.clear();
                    document.querySelectorAll(`#${id} .table-tabulator-row-dirty`).forEach((rowEl) => {
                        rowEl.classList.remove('table-tabulator-row-dirty');
                    });
                    reloadData();
                } catch (error) {
                    window.alert(error.message);
                    saveButton.disabled = false;
                }
            });
        }

        const searchInput = document.getElementById(`${id}-search`);
        if (searchInput) {
            let searchTimer;

            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimer);

                searchTimer = setTimeout(() => {
                    const value = searchInput.value.trim();

                    if (value) {
                        table.setFilter('__global', 'like', value);
                    } else {
                        table.clearFilter();
                    }
                }, 250);
            });
        }

        // Toggle manually: this app loads two Bootstrap bundles, so the
        // data-api would fire twice and immediately re-close the menu.
        const setupDropdownToggle = (button, menu) => {
            const container = button.closest('.dropdown');

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

            return close;
        };

        const exportButton = document.getElementById(`${id}-export`);
        const exportMenu = document.getElementById(`${id}-export-menu`);
        if (exportButton && exportMenu) {
            const closeExport = setupDropdownToggle(exportButton, exportMenu);

            exportMenu.querySelectorAll('[data-export]').forEach((item) => {
                item.addEventListener('click', async (event) => {
                    event.preventDefault();
                    closeExport();
                    await downloadTable(table, item.dataset.export, id);
                });
            });
        }

        const colvisButton = document.getElementById(`${id}-colvis`);
        const colvisMenu = document.getElementById(`${id}-colvis-menu`);
        if (colvisButton && colvisMenu) {
            setupDropdownToggle(colvisButton, colvisMenu);

            table.on('tableBuilt', () => {
                table.getColumns().forEach((column) => {
                    const def = column.getDefinition();

                    if (!def.title) {
                        return;
                    }

                    const item = document.createElement('label');
                    item.className = 'dropdown-item d-flex align-items-center gap-2 mb-0';

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.className = 'form-check-input m-0';
                    checkbox.checked = column.isVisible();
                    checkbox.addEventListener('change', () => (checkbox.checked ? column.show() : column.hide()));

                    item.append(checkbox, document.createTextNode(def.title));
                    colvisMenu.append(item);
                });
            });
        }

        const detailModalEl = config.detailModal ? document.getElementById(config.detailModal) : null;

        const openDetailModal = (url) => {
            if (!detailModalEl || !url) {
                return;
            }

            const body = detailModalEl.querySelector('.modal-body');
            body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';
            window.bootstrap.Modal.getOrCreateInstance(detailModalEl).show();

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' } })
                .then((response) => (response.ok ? response.text() : Promise.reject(response.status)))
                .then((html) => {
                    body.innerHTML = html;
                })
                .catch(() => {
                    body.innerHTML = '<div class="alert alert-danger m-3 mb-0">Gagal memuat detail.</div>';
                });
        };

        if (detailModalEl) {
            el.addEventListener('click', (event) => {
                const trigger = event.target.closest('.btn-detail-modal');

                if (!trigger) {
                    return;
                }

                event.preventDefault();
                openDetailModal(trigger.dataset.url);
            });
        }

        if (config.clickable) {
            el.classList.add('table-clickable');
            table.on('rowClick', (event, row) => {
                if (event.target.closest('a, button, form, input, select, textarea, label')) {
                    return;
                }

                if (event.target.closest('.tabulator-cell')?.querySelector('input[type="checkbox"]')) {
                    return;
                }

                const cell = event.target.closest('.tabulator-cell');
                const field = cell?.getAttribute('tabulator-field');

                if (field && editableFields.has(field)) {
                    return;
                }

                const url = row.getData()[config.rowUrlField];

                if (!url) {
                    return;
                }

                if (detailModalEl) {
                    openDetailModal(url);
                } else {
                    window.location.href = url;
                }
            });
        }
    });
}

document.addEventListener('shown.bs.tab', (event) => {
    const targetSelector = event.target?.getAttribute('data-bs-target');

    if (!targetSelector) {
        return;
    }

    document.querySelector(targetSelector)?.querySelectorAll('.table-tabulator').forEach((tableEl) => {
        tableEl.tabulatorInstance?.redraw(true);
    });
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTabulators);
} else {
    initTabulators();
}
