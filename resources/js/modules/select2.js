function initSelect2() {
    if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined') {
        return;
    }

    window.jQuery('select[data-select2-enabled="1"]').each(function () {
        const $el = window.jQuery(this);

        if ($el.hasClass('select2-hidden-accessible')) {
            return;
        }

        const options = {
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: this.dataset.select2AllowClear === '1',
        };

        if (this.dataset.select2Placeholder) {
            options.placeholder = this.dataset.select2Placeholder;
        }

        const $modal = $el.closest('.modal');
        if ($modal.length) {
            options.dropdownParent = $modal;
        }

        $el.select2(options);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSelect2);
} else {
    initSelect2();
}

document.addEventListener('form:enhance', initSelect2);
