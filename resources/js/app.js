document.addEventListener('DOMContentLoaded', () => {
    // قائمة الهاتف
    const toggle = document.querySelector('[data-menu-toggle]');
    const menu = document.querySelector('[data-menu]');
    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            const open = menu.classList.toggle('hidden') === false;
            toggle.setAttribute('aria-expanded', String(open));
        });
    }

    // معرض صور المنتج
    const main = document.querySelector('[data-gallery-main]');
    document.querySelectorAll('[data-gallery-thumb]').forEach((thumb) => {
        thumb.addEventListener('click', () => {
            if (main) main.src = thumb.dataset.galleryThumb;
        });
    });

    // تصفية الكتالوج: مفتوحة دائمًا على الشاشات الكبيرة
    const filters = document.querySelector('[data-filters]');
    if (filters && window.matchMedia('(min-width: 768px)').matches) filters.open = true;

    // منع الضغط المزدوج على إتمام الطلب (الخادم يمنع التكرار أيضًا برمز الطلب)
    document.querySelectorAll('form[data-once]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.submitting === '1') {
                event.preventDefault();
                return;
            }
            form.dataset.submitting = '1';
            form.querySelectorAll('button[type="submit"]').forEach((btn) => {
                btn.setAttribute('aria-disabled', 'true');
                btn.classList.add('opacity-60', 'pointer-events-none');
                if (btn.dataset.submitLabel) btn.textContent = btn.dataset.submitLabel;
            });
        });
    });

    // السلة: حفظ الكمية فور تغييرها
    document.querySelectorAll('form[data-autosubmit] input[name="quantity"]').forEach((input) => {
        input.addEventListener('change', () => input.form.requestSubmit());
    });

    // الدفع: تحديث الشحن والإجمالي عند تغيير المنطقة (عرض فقط؛ الحساب النهائي على الخادم)
    const checkout = document.querySelector('[data-checkout]');
    if (checkout) {
        const zone = checkout.querySelector('[data-zone]');
        const symbol = checkout.dataset.symbol || '';
        const subtotal = parseFloat(checkout.dataset.subtotal);
        const fmt = (n) => `${n.toFixed(2)} ${symbol}`;
        const update = () => {
            const fee = zone ? parseFloat(zone.selectedOptions[0].dataset.fee) : parseFloat(checkout.dataset.baseShipping);
            checkout.querySelector('[data-shipping]').innerHTML = `<bdi>${fee > 0 ? fmt(fee) : 'مجاني'}</bdi>`;
            checkout.querySelector('[data-total]').innerHTML = `<bdi>${fmt(subtotal + fee)}</bdi>`;
        };
        zone?.addEventListener('change', update);
    }

    // صفحة المنتج: الخيارات والكمية
    const form = document.querySelector('[data-product-form]');
    if (form) {
        const variants = JSON.parse(form.dataset.variants || '{}');
        const hasVariants = form.dataset.hasVariants === '1';
        const max = parseInt(form.dataset.max, 10);
        const low = parseInt(form.dataset.low, 10);
        const priceEl = form.querySelector('[data-price]');
        const barPrice = form.querySelector('[data-bar-price]');
        const note = form.querySelector('[data-stock-note]');
        const qty = form.querySelector('[data-qty]');
        const addBtn = form.querySelector('[data-add]');
        const addLabel = form.querySelector('[data-add-label]');

        const render = () => {
            let stock = parseInt(form.dataset.baseStock, 10);
            if (hasVariants) {
                const picked = form.querySelector('input[name="variant_id"]:checked');
                const v = picked ? variants[picked.value] : null;
                stock = v ? v.stock : 0;
                if (v) {
                    priceEl.innerHTML = `<bdi>${v.priceText}</bdi>`;
                    if (barPrice) barPrice.innerHTML = `<bdi>${v.priceText}</bdi>`;
                }
            }
            const limit = Math.max(1, Math.min(stock, max));
            qty.max = String(limit);
            if (parseInt(qty.value, 10) > limit) qty.value = String(limit);
            note.innerHTML = stock > 0
                ? `<span class="rounded-full bg-emerald-50 px-3 py-1 font-semibold text-emerald-800">متوفر</span>${stock <= low ? ` <span class="ms-2 text-amber-800">الكمية محدودة (<bdi>${stock}</bdi>)</span>` : ''}`
                : '<span class="rounded-full bg-stone-100 px-3 py-1 text-stone-700">غير متوفر حاليًا</span>';
            addBtn.disabled = stock < 1;
            addLabel.textContent = stock < 1 ? 'غير متوفر' : 'أضف إلى السلة';
        };

        form.querySelectorAll('input[name="variant_id"]').forEach((r) => r.addEventListener('change', render));
        form.querySelectorAll('[data-qty-step]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const next = (parseInt(qty.value, 10) || 1) + parseInt(btn.dataset.qtyStep, 10);
                qty.value = String(Math.min(Math.max(1, next), parseInt(qty.max, 10) || 1));
            });
        });
        render();
    }

    // العرض ثلاثي الأبعاد: يُحمَّل عند الضغط فقط، وصورة المنتج تظهر فورًا
    const viewerBtn = document.querySelector('[data-viewer-toggle]');
    if (viewerBtn) {
        const stage = document.querySelector('[data-viewer-stage]');
        let dispose = null;
        viewerBtn.addEventListener('click', async () => {
            if (dispose) {
                dispose();
                dispose = null;
                stage.classList.add('hidden');
                viewerBtn.textContent = 'عرض ثلاثي الأبعاد';
                return;
            }
            viewerBtn.disabled = true;
            viewerBtn.textContent = 'جارٍ التحميل…';
            try {
                const { mountViewer } = await import('./viewer3d.js');
                stage.classList.remove('hidden');
                dispose = mountViewer(stage, viewerBtn.dataset.viewerType, viewerBtn.dataset.viewerColor);
                viewerBtn.textContent = 'العودة إلى الصورة';
            } catch (e) {
                viewerBtn.textContent = 'تعذّر تحميل العرض';
            } finally {
                viewerBtn.disabled = false;
            }
        });
    }
});
