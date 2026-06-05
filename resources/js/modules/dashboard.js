window.dispatchEvent(new CustomEvent('sneat:dashboard-scripts-loaded'));

(function renderVehicleConditionChart() {
    const el = document.querySelector('#vehicleConditionChart');
    const dataEl = document.querySelector('#vehicleConditionData');

    if (!el || !dataEl || typeof ApexCharts === 'undefined') {
        return;
    }

    let summary;
    try {
        summary = JSON.parse(dataEl.textContent);
    } catch (e) {
        return;
    }

    const items = summary.items || [];
    const cardColor = '#fff';

    const chart = new ApexCharts(el, {
        chart: {
            height: 165,
            width: 136,
            type: 'donut',
            offsetX: 15,
        },
        labels: items.map((i) => i.label),
        series: items.map((i) => i.count),
        colors: items.map((i) => i.hex),
        stroke: {
            width: 5,
            colors: [cardColor],
        },
        dataLabels: {
            enabled: false,
        },
        legend: {
            show: false,
        },
        grid: {
            padding: { top: 0, bottom: 0, right: 15 },
        },
        states: {
            hover: { filter: { type: 'none' } },
            active: { filter: { type: 'none' } },
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '75%',
                    labels: {
                        show: true,
                        value: {
                            fontSize: '1.125rem',
                            fontWeight: 500,
                            offsetY: -17,
                        },
                        name: {
                            offsetY: 17,
                        },
                        total: {
                            show: true,
                            fontSize: '13px',
                            label: 'Total',
                            formatter: () => summary.total,
                        },
                    },
                },
            },
        },
    });

    chart.render();
})();
