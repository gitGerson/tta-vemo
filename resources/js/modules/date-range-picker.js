function initDateRangePickers() {
    if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.daterangepicker === 'undefined') {
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

        if (this.dataset.minDate) {
            options.minDate = this.dataset.minDate;
        }

        if (this.dataset.maxDate) {
            options.maxDate = this.dataset.maxDate;
        }

        if (this.value) {
            if (options.singleDatePicker) {
                options.startDate = this.value;
            } else {
                const parts = this.value.split(' - ');
                if (parts.length === 2) {
                    options.startDate = parts[0];
                    options.endDate = parts[1];
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
