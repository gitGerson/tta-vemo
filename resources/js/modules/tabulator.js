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

// ── Client-side adapter registry ────────────────────────────────────────────
// Editors and formatters that a DataTable can request by string name (via
// Column::editable('select2', …) / Column::formatter('moneyProduct', …)), so
// PHP never has to ship a JS closure. Built-in Tabulator names (money, html,
// number, input, …) are NOT in these maps and pass through untouched.

function formatMoney(value, params = {}) {
    const symbol = params.symbol ?? '';
    const thousand = params.thousand ?? '.';
    const decimal = params.decimal ?? ',';
    const precision = params.precision ?? 0;
    const number = Number(value) || 0;
    const fixed = number.toFixed(precision);
    let [intPart, decPart] = fixed.split('.');
    intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousand);
    return symbol + intPart + (decPart ? decimal + decPart : '');
}

/** Build a Tabulator editor that mounts a select2 dropdown inside the cell. */
function makeSelect2Editor(editorParams = {}) {
    const options = editorParams.options ?? [];
    const placeholder = editorParams.placeholder ?? '- Pilih -';

    return function select2Editor(cell, onRendered, success, cancel) {
        const editor = document.createElement('select');
        editor.style.width = '100%';

        const blank = document.createElement('option');
        blank.value = '';
        blank.text = placeholder;
        editor.appendChild(blank);

        options.forEach((name) => {
            const opt = document.createElement('option');
            opt.value = name;
            opt.text = name;
            if (name === cell.getValue()) {
                opt.selected = true;
            }
            editor.appendChild(opt);
        });

        // Stop Tabulator cancelling the edit when focus moves to the select2
        // dropdown (rendered on <body>, i.e. outside the cell).
        editor.addEventListener('blur', (event) => event.stopImmediatePropagation(), true);

        const $ = window.jQuery || window.$;

        // Graceful fallback when select2/jQuery isn't on the page.
        if (!$ || typeof $.fn?.select2 === 'undefined') {
            editor.addEventListener('change', () => success(editor.value || ''));
            onRendered(() => editor.focus());
            return editor;
        }

        let done = false;
        const commit = () => {
            if (done) {
                return;
            }
            done = true;
            success($(editor).val() || '');
        };

        onRendered(() => {
            const $editor = $(editor);
            $editor.select2({ width: '100%', dropdownParent: $('body'), placeholder });
            $editor.on('select2:close', commit);
            $editor.select2('open');
        });

        return editor;
    };
}

const EDITORS = {
    select2: makeSelect2Editor,
};

// Column calculations a DataTable can request by string name (config is JSON, so
// it cannot ship a JS function). staticLabel renders a fixed footer label like
// "JUMLAH" / "TOTAL" in a non-numeric column's bottomCalc cell.
const CALCS = {
    staticLabel: (params = {}) => params.label ?? '',
};

const FORMATTERS = {
    nameOrPlaceholder: (cell, params) =>
        cell.getValue() || `<span class="text-muted">${params.placeholder ?? ''}</span>`,
    moneyProduct: (cell, params) => {
        const data = cell.getRow().getData();
        const product = (params.fields ?? []).reduce(
            (acc, field) => acc * (Number(data[field]) || 0),
            1
        );
        return formatMoney(product, params);
    },
    rowDelete: () =>
        '<button type="button" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bx bx-trash"></i></button>',
};

/**
 * Resolve registry editor/formatter strings into real functions before building
 * the table. Only rewrites names the registry owns — built-in Tabulator strings
 * pass through, so remote tables (which use no registry names) are untouched.
 */
function resolveColumnDefs(columns) {
    return (columns ?? []).map((column) => {
        const def = { ...column };

        // Grouped (nested) columns: resolve their children too.
        if (Array.isArray(def.columns)) {
            def.columns = resolveColumnDefs(def.columns);
        }

        if (typeof def.editor === 'string' && EDITORS[def.editor]) {
            def.editor = EDITORS[def.editor](def.editorParams ?? {});
            delete def.editorParams;
        }

        if (typeof def.formatter === 'string' && FORMATTERS[def.formatter]) {
            if (def.formatter === 'rowDelete') {
                def.__rowDelete = true;
            }
            const fn = FORMATTERS[def.formatter];
            const params = def.formatterParams ?? {};
            def.formatter = (cell) => fn(cell, params);
        }

        if (typeof def.bottomCalc === 'string' && CALCS[def.bottomCalc]) {
            const calc = CALCS[def.bottomCalc];
            const params = def.bottomCalcParams ?? {};
            def.bottomCalc = () => calc(params);
        }

        return def;
    });
}

/**
 * Initialize every Tabulator table on the page.
 *
 * Each <x-table engine="tabulator"> renders a container div plus a sibling
 * JSON config script. The config carries the AJAX URL, columns, sort, and
 * clickable row settings; row data stays server-side (remote mode) — or inline
 * for local mode (in-form editable sub-grids).
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

        if ((config.mode ?? 'remote') === 'local') {
            initLocalTable(el, id, config);
        } else {
            initRemoteTable(el, id, config);
        }
    });
}

/**
 * Server-side (remote) table: paginate / sort / filter against an ajaxURL, with
 * optional inline-edit / add / delete wired to REST endpoints. This is the
 * original initTabulators body, unchanged — every existing table uses this path.
 */
function initRemoteTable(el, id, config) {
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
}

/**
 * Client-side (local) table: rows come inline from config.data, edits stay in
 * memory, and on parent-form submit they serialize into formField[i][…] hidden
 * inputs. No ajaxURL / remote modes / REST calls. Powers in-form editable grids.
 */
function initLocalTable(el, id, config) {
    const columns = resolveColumnDefs(config.columns).map((def) => {
        // Bind the row-delete action button (rendered by the rowDelete formatter).
        if (def.__rowDelete) {
            def.cellClick = (event, cell) => cell.getRow().delete();
            delete def.__rowDelete;
        }
        return def;
    });

    const table = new Tabulator(el, {
        // Clone each seed row so rows never share an object reference (shared refs
        // make edits on one row bleed into the others).
        columns,
        layout: config.layout ?? 'fitColumns',
        data: (config.data ?? []).map((row) => ({ ...row })),
        pagination: false,
        placeholder: config.placeholder ?? 'Belum ada item perbaikan',
    });

    el.tabulatorInstance = table;
    el.dataset.tabulatorInitialized = '1';

    const notifyChanged = () => el.dispatchEvent(new CustomEvent('tabulator:local-changed', { bubbles: true }));

    table.on('cellEdited', (cell) => {
        // Re-run the row's formatters so computed columns (e.g. Total) refresh.
        cell.getRow().reformat();
        notifyChanged();
    });
    table.on('rowAdded', notifyChanged);
    table.on('rowDeleted', notifyChanged);

    // Same toolbar as remote tables — search / column visibility / export, all local.
    wireLocalToolbar(table, id);

    const addButton = document.getElementById(`${id}-add`);
    if (addButton && config.editable) {
        addButton.addEventListener('click', () => {
            // Spread into a fresh object so every new row gets its own data store
            // (passing the shared addRowDefaults reference makes rows duplicate).
            table.addRow({ ...(config.addRowDefaults ?? {}) }, false).then((row) => {
                if (config.addRowFocusField) {
                    row.getCell(config.addRowFocusField)?.edit();
                }
            });
        });
    }

    if (config.editable && config.formTarget && config.formField) {
        bindFormSerialization(table, config);
    }
}

/**
 * On parent-form submit, serialize the table rows into hidden inputs named
 * formField[i][key] = row[field] (per config.formFieldMap). Rows whose first
 * mapped field is empty are skipped. Re-runs cleanly on every submit.
 */
function bindFormSerialization(table, config) {
    const form = document.querySelector(config.formTarget);
    if (!form) {
        return;
    }

    const field = config.formField;
    const map = config.formFieldMap ?? {};
    const keys = Object.keys(map);
    if (keys.length === 0) {
        return;
    }

    const requiredField = map[keys[0]];
    const marker = `${field}-serialized`;

    form.addEventListener('submit', () => {
        form.querySelectorAll(`input[data-serialized="${marker}"]`).forEach((node) => node.remove());

        let index = 0;
        table.getData().forEach((row) => {
            if (!row[requiredField]) {
                return;
            }

            keys.forEach((key) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `${field}[${index}][${key}]`;
                input.value = row[map[key]] ?? '';
                input.setAttribute('data-serialized', marker);
                form.appendChild(input);
            });

            index++;
        });
    });
}

// Toggle a Bootstrap dropdown manually (this app loads two Bootstrap bundles, so
// the data-api would fire twice and immediately re-close the menu).
function setupTableDropdown(button, menu) {
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
}

/**
 * Wire the standard table toolbar (search / column-visibility / export) for a
 * LOCAL table so it matches the remote tables. Search filters client-side; the
 * filter only hides rows, getData() still returns every row for serialization.
 */
function wireLocalToolbar(table, id) {
    const searchInput = document.getElementById(`${id}-search`);
    if (searchInput) {
        let searchTimer;
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                const value = searchInput.value.trim().toLowerCase();
                if (value) {
                    table.setFilter((data) =>
                        Object.values(data).some((cell) => String(cell ?? '').toLowerCase().includes(value))
                    );
                } else {
                    table.clearFilter();
                }
            }, 250);
        });
    }

    const exportButton = document.getElementById(`${id}-export`);
    const exportMenu = document.getElementById(`${id}-export-menu`);
    if (exportButton && exportMenu) {
        const closeExport = setupTableDropdown(exportButton, exportMenu);
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
        setupTableDropdown(colvisButton, colvisMenu);
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
