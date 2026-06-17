window.dispatchEvent(new CustomEvent('sneat:dashboard-scripts-loaded'));

// Shared donut renderer for the Vehicle Condition and Fleet Composition cards.
// Both feed an array of { label, count, hex } slices.
function renderDonut(elSelector, items) {
    const el = document.querySelector(elSelector);

    if (!el || !Array.isArray(items) || items.length === 0 || typeof ApexCharts === 'undefined') {
        return;
    }

    const chart = new ApexCharts(el, {
        chart: { height: 260, width: 260, type: 'donut' },
        labels: items.map((i) => i.label),
        series: items.map((i) => i.count),
        colors: items.map((i) => i.hex),
        stroke: { width: 5, colors: ['#fff'] },
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
}

(function renderDonutCharts() {
    const condition = readChartData('#vehicleConditionData');
    renderDonut('#vehicleConditionChart', condition ? condition.items || [] : []);
    renderDonut('#fleetCompositionChart', readChartData('#fleetCompositionData'));
})();

// Shared helpers for the analytics charts below.
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

(function renderSpendTrendChart() {
    const el = document.querySelector('#spendTrendChart');
    const data = readChartData('#spendTrendData');

    if (!el || !data || typeof ApexCharts === 'undefined') {
        return;
    }

    const chart = new ApexCharts(el, {
        chart: { height: 260, type: 'area', toolbar: { show: false } },
        series: [
            { name: 'Maintenance', data: data.maintenance || [] },
            { name: 'Fuel', data: data.fuel || [] },
        ],
        colors: ['#ff9f43', '#00cfe8'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
        xaxis: { categories: data.categories || [] },
        yaxis: { labels: { formatter: (v) => formatRupiahShort(v) } },
        tooltip: { y: { formatter: (v) => formatRupiahShort(v) } },
        legend: { position: 'top', horizontalAlign: 'left' },
        grid: { borderColor: '#e7e7e8' },
    });

    chart.render();
})();

(function renderMaintenanceVolumeChart() {
    const el = document.querySelector('#maintenanceVolumeChart');
    const data = readChartData('#maintenanceVolumeData');

    if (!el || !data || typeof ApexCharts === 'undefined') {
        return;
    }

    const chart = new ApexCharts(el, {
        chart: { height: 260, type: 'bar', stacked: true, toolbar: { show: false } },
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
        grid: { borderColor: '#e7e7e8' },
    });

    chart.render();
})();
