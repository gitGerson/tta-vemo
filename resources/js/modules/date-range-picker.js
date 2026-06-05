const momentUrl = 'https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js';
const dateRangePickerUrl = 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js';

let pickerScriptPromise = null;

function loadScript(src) {
    const existing = document.querySelector(`script[data-dynamic-src="${src}"]`);

    if (existing?.dataset.loaded === '1') {
        return Promise.resolve();
    }

    if (existing) {
        return new Promise((resolve, reject) => {
            existing.addEventListener('load', resolve, { once: true });
            existing.addEventListener('error', reject, { once: true });
        });
    }

    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src;
        script.dataset.dynamicSrc = src;
        script.onload = () => {
            script.dataset.loaded = '1';
            resolve();
        };
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

async function ensureDateRangePicker() {
    if (typeof window.jQuery === 'undefined') {
        return false;
    }

    if (typeof window.jQuery.fn.daterangepicker !== 'undefined') {
        return true;
    }

    pickerScriptPromise ??= (async () => {
        if (typeof window.moment === 'undefined') {
            await loadScript(momentUrl);
        }

        await loadScript(dateRangePickerUrl);
    })();

    await pickerScriptPromise;

    return typeof window.jQuery.fn.daterangepicker !== 'undefined';
}

async function initDateRangePickers() {
    if (! await ensureDateRangePicker()) {
        return;
    }

    window.jQuery('input[data-date-mode="daterangepicker"]').each(function () {
        const $input = window.jQuery(this);

        if ($input.data('daterangepickerInitialized') === 1) {
            return;
        }

        const format = this.dataset.dateFormat || 'YYYY-MM-DD';
        const options = {
            autoUpdateInput: false,
            singleDatePicker: this.dataset.singleDatePicker === '1',
            autoApply: this.dataset.autoApply === '1',
            opens: this.dataset.opens || 'right',
            drops: this.dataset.drops || 'auto',
            locale: {
                format,
                cancelLabel: 'Clear',
            },
        };
        const $modal = $input.closest('.modal');

        if ($modal.length) {
            options.parentEl = $modal;
        }

        if (this.dataset.minDate) {
            options.minDate = this.dataset.minDate;
        }

        if (this.dataset.maxDate) {
            options.maxDate = this.dataset.maxDate;
        }

        if (this.value) {
            if (options.singleDatePicker) {
                options.startDate = window.moment(this.value, format, true).isValid()
                    ? window.moment(this.value, format)
                    : this.value;
            } else {
                const parts = this.value.split(' - ');
                if (parts.length === 2) {
                    options.startDate = window.moment(parts[0], format, true).isValid()
                        ? window.moment(parts[0], format)
                        : parts[0];
                    options.endDate = window.moment(parts[1], format, true).isValid()
                        ? window.moment(parts[1], format)
                        : parts[1];
                }
            }
        }

        $input.daterangepicker(options);

        $input.on('apply.daterangepicker', function (event, picker) {
            const value = picker.singleDatePicker
                ? picker.startDate.format(format)
                : `${picker.startDate.format(format)} - ${picker.endDate.format(format)}`;

            $input.val(value).trigger('change');
        });

        $input.on('cancel.daterangepicker', function () {
            $input.val('').trigger('change');
        });

        $input.data('daterangepickerInitialized', 1);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDateRangePickers);
} else {
    initDateRangePickers();
}

document.addEventListener('form:enhance', initDateRangePickers);
