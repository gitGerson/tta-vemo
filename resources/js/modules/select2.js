export function select2Options(element, overrides = {}) {
    const $ = window.jQuery || window.$;
    const options = {
        theme: 'bootstrap-5',
        width: '100%',
        allowClear: element?.dataset.select2AllowClear === '1',
    };

    if (element?.dataset.select2Placeholder) {
        options.placeholder = element.dataset.select2Placeholder;
    }

    const $modal = element && $ ? $(element).closest('.modal') : null;
    if ($modal?.length) {
        options.dropdownParent = $modal;
    }

    return { ...options, ...overrides };
}

export function initSelect2() {
    const $ = window.jQuery || window.$;

    if (!$ || typeof $.fn?.select2 === 'undefined') {
        return;
    }

    $('select[data-select2-enabled="1"]').each(function () {
        const $el = $(this);

        if ($el.hasClass('select2-hidden-accessible')) {
            return;
        }

        $el.select2(select2Options(this));
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSelect2);
} else {
    initSelect2();
}

document.addEventListener('form:enhance', initSelect2);
