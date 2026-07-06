/**
 * Format input nominal Rupiah: tampilan 65.000, submit angka 65000.
 */
(function (global) {
    function parseAmount(value) {
        const digits = String(value ?? '').replace(/\D/g, '');
        return digits ? parseInt(digits, 10) : 0;
    }

    function formatAmount(num) {
        const n = Math.max(0, Math.round(Number(num) || 0));
        return n.toLocaleString('id-ID');
    }

    function setValue(input, amount) {
        if (!input) return;
        const el = typeof input === 'string' ? document.getElementById(input) : input;
        if (!el) return;
        const max = parseFloat(el.dataset.maxAmount);
        let v = Math.max(0, Math.round(Number(amount) || 0));
        if (Number.isFinite(max) && max > 0) {
            v = Math.min(v, Math.round(max));
        }
        el.value = formatAmount(v);
    }

    function bind(input, onChange) {
        if (!input) return;
        const applyFormat = (allowEmpty) => {
            const digits = String(input.value ?? '').replace(/\D/g, '');
            if (allowEmpty && digits === '') {
                input.value = '';
                if (typeof onChange === 'function') onChange();
                return;
            }
            let v = digits ? parseInt(digits, 10) : 0;
            const max = parseFloat(input.dataset.maxAmount);
            if (Number.isFinite(max) && max > 0 && v > max) {
                v = Math.round(max);
            }
            input.value = formatAmount(v);
            if (typeof onChange === 'function') onChange();
        };
        input.addEventListener('input', () => applyFormat(true));
        input.addEventListener('blur', () => applyFormat(false));
    }

    function prepareFormSubmit(form) {
        if (!form) return;
        form.addEventListener('submit', () => {
            form.querySelectorAll('[data-amount-input]').forEach((input) => {
                input.value = String(parseAmount(input.value));
            });
        });
    }

    function init(root) {
        const scope = root || document;
        scope.querySelectorAll('[data-amount-input]').forEach((input) => bind(input));
        scope.querySelectorAll('form').forEach((form) => {
            if (form.querySelector('[data-amount-input]')) {
                prepareFormSubmit(form);
            }
        });
    }

    global.AmountInput = {
        parse: parseAmount,
        format: formatAmount,
        setValue,
        bind,
        init,
        prepareFormSubmit,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => init());
    } else {
        init();
    }
})(window);
