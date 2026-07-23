const rupiahFormatter = new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
});

function enhanceRecordFuelPricing(form) {
    if (form.dataset.recordFuelPricingEnhanced === '1') {
        return;
    }

    form.dataset.recordFuelPricingEnhanced = '1';

    const fuelType = form.querySelector('[name="fuel_type_id"]');
    const liters = form.querySelector('[name="bbm"]');
    const unitPrice = form.querySelector('[data-fuel-unit-price]');
    const totalPrice = form.querySelector('[data-fuel-total-price]');
    const prices = JSON.parse(form.dataset.fuelTypePrices || '{}');

    function recalculateTotal() {
        const unitPriceValue = unitPrice?.value ?? '';
        const litersValue = liters?.value ?? '';
        const numericPrice = Number(unitPriceValue);
        const numericLiters = Number(litersValue);

        totalPrice.value = unitPriceValue !== ''
            && litersValue !== ''
            && Number.isFinite(numericPrice)
            && Number.isFinite(numericLiters)
            ? rupiahFormatter.format(numericPrice * numericLiters)
            : '';
    }

    function prefillUnitPrice(force = false) {
        const selectedPrice = prices[fuelType?.value];
        const hasSelectedPrice = selectedPrice !== undefined
            && Number.isFinite(Number(selectedPrice));

        if (unitPrice && (force || unitPrice.value === '')) {
            unitPrice.value = hasSelectedPrice ? selectedPrice : '';
        }

        recalculateTotal();
    }

    function handleFuelTypeChange() {
        prefillUnitPrice(true);
    }

    if (window.jQuery && fuelType) {
        window.jQuery(fuelType).on('change.recordFuelPricing', handleFuelTypeChange);
    } else {
        fuelType?.addEventListener('change', handleFuelTypeChange);
    }
    liters?.addEventListener('input', recalculateTotal);
    unitPrice?.addEventListener('input', recalculateTotal);
    form.closest('.modal')?.addEventListener('shown.bs.modal', () => queueMicrotask(() => prefillUnitPrice()));
    prefillUnitPrice();
}

function initRecordFuelPricing() {
    document.querySelectorAll('[data-record-fuel-pricing-form]').forEach(enhanceRecordFuelPricing);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRecordFuelPricing);
} else {
    initRecordFuelPricing();
}
