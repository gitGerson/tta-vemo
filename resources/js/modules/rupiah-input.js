function formatRupiah(value) {
    const cleaned = String(value ?? '').replace(/[^\d.]/g, '');

    if (!cleaned) {
        return '';
    }

    const [whole, decimal] = cleaned.split('.');
    const formattedWhole = Number(whole || 0).toLocaleString('en-US');

    return decimal !== undefined ? `${formattedWhole}.${decimal.slice(0, 2)}` : formattedWhole;
}

function syncRawValue(input) {
    const targetId = input.dataset.rupiahTarget;

    if (!targetId) {
        return;
    }

    const target = document.getElementById(targetId);

    if (target) {
        target.value = String(input.value).replace(/[^\d.]/g, '');
    }
}

function enhanceRupiahInput(input) {
    if (input.dataset.rupiahEnhanced === '1') {
        return;
    }

    input.dataset.rupiahEnhanced = '1';
    input.value = formatRupiah(input.value);
    syncRawValue(input);

    input.addEventListener('input', function () {
        const cursorAtEnd = input.selectionStart === input.value.length;
        input.value = formatRupiah(input.value);

        if (cursorAtEnd) {
            input.setSelectionRange(input.value.length, input.value.length);
        }

        syncRawValue(input);
    });
}

function initRupiahInputs() {
    document.querySelectorAll('[data-rupiah-input]').forEach(enhanceRupiahInput);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRupiahInputs);
} else {
    initRupiahInputs();
}

document.addEventListener('form:enhance', initRupiahInputs);
