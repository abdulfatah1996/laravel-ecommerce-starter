document.addEventListener('DOMContentLoaded', () => {
    // قائمة الهاتف
    const toggle = document.querySelector('[data-menu-toggle]');
    const menu = document.querySelector('[data-menu]');

    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            const open = menu.classList.toggle('hidden') === false;
            toggle.setAttribute('aria-expanded', String(open));
            toggle.querySelector('[data-icon-open]')?.classList.toggle('hidden', open);
            toggle.querySelector('[data-icon-close]')?.classList.toggle('hidden', !open);
        });
    }

    // معرض صور المنتج: الضغط على صورة مصغّرة يبدّل الصورة الكبيرة
    const main = document.querySelector('[data-gallery-main]');
    const thumbs = document.querySelectorAll('[data-gallery-thumb]');
    thumbs.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            if (main) {
                main.src = thumb.dataset.galleryThumb;
            }
            thumbs.forEach((t) => t.classList.toggle('!border-primary', t === thumb));
        });
    });
    thumbs[0]?.classList.add('!border-primary');

    // عدّاد الكمية (+/−). في السلة (data-qty-autosubmit) يُرسل النموذج تلقائيًا بعد توقف الضغط.
    document.querySelectorAll('[data-qty]').forEach((box) => {
        const input = box.querySelector('input');
        const form = input.form;
        let timer;
        const clamp = (v) => {
            const min = Number(input.min) || 1;
            const max = Number(input.max) || 999;
            return Math.min(max, Math.max(min, Number.isFinite(v) ? v : min));
        };
        const change = (delta) => {
            const next = clamp((parseInt(input.value, 10) || 1) + delta);
            if (next === Number(input.value)) {
                return;
            }
            input.value = next;
            if (box.hasAttribute('data-qty-autosubmit') && form) {
                clearTimeout(timer);
                timer = setTimeout(() => form.requestSubmit(), 500);
            }
        };
        box.querySelector('[data-qty-minus]')?.addEventListener('click', () => change(-1));
        box.querySelector('[data-qty-plus]')?.addEventListener('click', () => change(1));
        input.addEventListener('change', () => {
            input.value = clamp(parseInt(input.value, 10));
            if (box.hasAttribute('data-qty-autosubmit') && form) {
                clearTimeout(timer);
                timer = setTimeout(() => form.requestSubmit(), 300);
            }
        });
    });

    // منع الضغط المزدوج على أزرار الإرسال الحساسة (إتمام الطلب)
    document.querySelectorAll('form[data-once]').forEach((form) => {
        form.addEventListener('submit', () => {
            form.querySelectorAll('button[type="submit"]').forEach((btn) => {
                btn.disabled = true;
            });
        });
    });
});
