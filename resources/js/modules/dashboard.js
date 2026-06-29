window.dispatchEvent(new CustomEvent('sneat:dashboard-scripts-loaded'));

// Resolve chart text / line / surface colors from the active Sneat theme so the
// charts follow light/dark mode. Read at render time from the CSS custom
// properties on <html> (which dark mode swaps), with light-mode fallbacks.
function chartTheme() {
    const styles = getComputedStyle(document.documentElement);
    const read = (name, fallback) => styles.getPropertyValue(name).trim() || fallback;
    return {
        foreColor: read('--bs-body-color', '#697a8d'),
        gridBorder: read('--bs-border-color', '#e7e7e8'),
        surface: read('--bs-paper-bg', '#fff'),
    };
}

// Live ApexCharts instances, tracked so they can be destroyed before the
// dashboard content is swapped out and re-rendered on a filter change.
let chartInstances = [];

// Shared helpers for the analytics charts.
function readChartData(id) {
    const el = document.querySelector(id);
    if (!el) {
        return null;
    }
    try {
        return JSON.parse(el.textContent);
    } catch (e) {
        return null;
    }
}

function formatRupiahShort(value) {
    if (value >= 1.0e9) {
        return 'Rp ' + (value / 1.0e9).toFixed(1) + ' M';
    }
    if (value >= 1.0e6) {
        return 'Rp ' + (value / 1.0e6).toFixed(1) + ' jt';
    }
    if (value >= 1.0e3) {
        return 'Rp ' + Math.round(value / 1.0e3) + ' rb';
    }
    return 'Rp ' + value;
}

// Shared donut renderer for dashboard cards.
// Both feed an array of { label, count, hex } slices.
function renderDonut(elSelector, items) {
    const el = document.querySelector(elSelector);

    if (!el || !Array.isArray(items) || items.length === 0 || typeof ApexCharts === 'undefined') {
        return;
    }

    const theme = chartTheme();
    const chart = new ApexCharts(el, {
        chart: { height: 260, width: 260, type: 'donut', foreColor: theme.foreColor },
        labels: items.map((i) => i.label),
        series: items.map((i) => i.count),
        colors: items.map((i) => i.hex),
        stroke: { width: 5, colors: [theme.surface] },
        dataLabels: { enabled: false },
        legend: { position: 'bottom' },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total',
                            formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0),
                        },
                    },
                },
            },
        },
    });

    chart.render();
    chartInstances.push(chart);
}

function renderSpendTrendChart() {
    const el = document.querySelector('#spendTrendChart');
    const data = readChartData('#spendTrendData');

    if (!el || !data || typeof ApexCharts === 'undefined') {
        return;
    }

    const theme = chartTheme();
    const chart = new ApexCharts(el, {
        chart: { height: 260, type: 'area', toolbar: { show: false }, foreColor: theme.foreColor },
        series: [
            { name: 'Pemeliharaan', data: data.maintenance || [] },
            { name: 'BBM', data: data.fuel || [] },
        ],
        colors: ['#ff9f43', '#00cfe8'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
        xaxis: { categories: data.categories || [] },
        yaxis: { labels: { formatter: (v) => formatRupiahShort(v) } },
        tooltip: { y: { formatter: (v) => formatRupiahShort(v) } },
        legend: { position: 'top', horizontalAlign: 'left' },
        grid: { borderColor: theme.gridBorder },
    });

    chart.render();
    chartInstances.push(chart);
}

function renderMaintenanceVolumeChart() {
    const el = document.querySelector('#maintenanceVolumeChart');
    const data = readChartData('#maintenanceVolumeData');

    if (!el || !data || typeof ApexCharts === 'undefined') {
        return;
    }

    const theme = chartTheme();
    const chart = new ApexCharts(el, {
        chart: { height: 260, type: 'bar', stacked: true, toolbar: { show: false }, foreColor: theme.foreColor },
        series: [
            { name: 'Rutin', data: data.routine || [] },
            { name: 'Non-rutin', data: data.non_routine || [] },
        ],
        colors: ['#28c76f', '#ea5455'],
        dataLabels: { enabled: false },
        plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
        xaxis: { categories: data.categories || [] },
        yaxis: { labels: { formatter: (v) => Math.round(v) } },
        legend: { position: 'top', horizontalAlign: 'left' },
        grid: { borderColor: theme.gridBorder },
    });

    chart.render();
    chartInstances.push(chart);
}

// (Re)render every dashboard chart from the inline JSON data tags currently in
// the DOM. Destroys any previous instances first so an AJAX content swap does
// not leave orphaned canvases behind.
function renderDashboardCharts() {
    chartInstances.forEach((chart) => {
        try {
            chart.destroy();
        } catch (e) {
            // ignore charts whose element was already removed from the DOM
        }
    });
    chartInstances = [];

    const condition = readChartData('#vehicleConditionData');
    renderDonut('#vehicleConditionChart', condition ? condition.items || [] : []);
    renderDonut('#fleetCompositionChart', readChartData('#fleetCompositionData'));
    renderSpendTrendChart();
    renderMaintenanceVolumeChart();
}

// Bootstrap tooltips inside the dashboard content (e.g. the "KM Ditempuh" info
// icon), re-initialized after an AJAX swap inserts fresh markup.
function initDashboardTooltips() {
    if (typeof window.bootstrap === 'undefined' || !window.bootstrap.Tooltip) {
        return;
    }

    document.querySelectorAll('#dashboard-content [data-bs-toggle="tooltip"]').forEach((el) => {
        window.bootstrap.Tooltip.getOrCreateInstance(el);
    });
}

// --- Top progress bar -------------------------------------------------------

const progressBar = {
    el: () => document.getElementById('dashboard-progress'),
    timer: null,
    start() {
        const bar = this.el();
        if (!bar) {
            return;
        }
        clearTimeout(this.timer);
        bar.style.opacity = '1';
        bar.style.width = '0';
        // Force reflow so the transition runs from 0 on a fresh start.
        void bar.offsetWidth;
        bar.style.width = '90%';
    },
    done() {
        const bar = this.el();
        if (!bar) {
            return;
        }
        bar.style.width = '100%';
        this.timer = setTimeout(() => {
            bar.style.opacity = '0';
            this.timer = setTimeout(() => {
                bar.style.width = '0';
            }, 300);
        }, 200);
    },
};

// --- AJAX filter refresh ----------------------------------------------------

let isRefreshing = false;

// Fetch the dashboard content partial for the given URL and swap it in place,
// then re-initialize tables, charts and tooltips for the new markup.
function refreshDashboard(url) {
    const container = document.getElementById('dashboard-content');
    if (!container || isRefreshing) {
        return;
    }

    isRefreshing = true;
    progressBar.start();
    container.classList.add('is-loading');

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' } })
        .then((response) => (response.ok ? response.text() : Promise.reject(response.status)))
        .then((html) => {
            container.innerHTML = html;
            window.initTabulators?.();
            renderDashboardCharts();
            initDashboardTooltips();
        })
        .catch(() => {
            // Leave the existing content in place on failure.
        })
        .finally(() => {
            container.classList.remove('is-loading');
            progressBar.done();
            isRefreshing = false;
        });
}

// Build the /home URL from the current filter form values.
function dashboardFilterUrl() {
    const form = document.getElementById('dashboard-filter');
    const params = new URLSearchParams(new FormData(form));
    return form.getAttribute('action') + '?' + params.toString();
}

function initDashboardFilter() {
    const form = document.getElementById('dashboard-filter');
    if (!form) {
        return;
    }

    // Suppresses apply() while we sync controls programmatically (e.g. restoring
    // state on popstate), so updating the datepicker doesn't trigger a refetch.
    let suppress = false;

    const apply = () => {
        if (suppress) {
            return;
        }
        const url = dashboardFilterUrl();
        window.history.pushState({ dashboard: true }, '', url);
        refreshDashboard(url);
    };

    const $ = window.jQuery;

    // Company dropdown: jQuery 'change' so Select2's change event is caught.
    if (typeof $ !== 'undefined') {
        $('#dashboard-company').on('change', apply);
    } else {
        document.getElementById('dashboard-company')?.addEventListener('change', apply);
    }

    // Month: a month-only bootstrap-datepicker that auto-applies on selection.
    const monthInput = document.getElementById('dashboard-month');
    const $monthPicker = $ && $.fn.datepicker ? $('#dashboard-month-picker') : null;
    const monthValueToDate = (value) => {
        const match = /^(\d{4})-(\d{2})$/.exec(value);

        return match ? new Date(Number(match[1]), Number(match[2]) - 1, 1) : null;
    };
    const dateToMonthValue = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');

        return `${year}-${month}`;
    };

    if ($monthPicker) {
        $monthPicker.datepicker({
            format: 'MM yyyy',
            language: 'id',
            minViewMode: 'months',
            startView: 'months',
            autoclose: true,
            todayHighlight: true,
            orientation: 'bottom right',
        });
        $monthPicker.datepicker('setDate', monthValueToDate(monthInput?.value));

        // changeDate fires once per selection; refresh the dashboard from it.
        $monthPicker.on('changeDate', (event) => {
            if (monthInput && event.date) {
                monthInput.value = dateToMonthValue(event.date);
            }

            apply();
        });
    } else {
        monthInput?.addEventListener('change', apply);
    }

    // Never full-submit the form (e.g. Enter key) once JS is active.
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        apply();
    });

    // On browser back/forward, sync the filter controls to the restored URL and
    // refresh the content. Update select2's display without firing 'change' so we
    // don't trigger a second fetch.
    window.addEventListener('popstate', () => {
        const params = new URLSearchParams(window.location.search);
        const company = document.getElementById('dashboard-company');

        suppress = true;

        if (company) {
            company.value = params.get('company') || '';
            $?.(company).trigger('change.select2');
        }

        if ($monthPicker) {
            // 'update' re-syncs the picker display; guarded by `suppress`.
            const restoredMonth = params.get('month') || '';

            if (monthInput) {
                monthInput.value = restoredMonth;
            }

            $monthPicker.datepicker('setDate', monthValueToDate(restoredMonth));
        }

        suppress = false;

        refreshDashboard(window.location.href);
    });
}

renderDashboardCharts();
initDashboardTooltips();
initDashboardFilter();
