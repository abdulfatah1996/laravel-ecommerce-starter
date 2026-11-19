// قائمة الهاتف
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-menu-toggle]');
    const menu = document.querySelector('[data-menu]');

    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            const open = menu.classList.toggle('hidden') === false;
            toggle.setAttribute('aria-expanded', String(open));
        });
    }

    // معرض صور المنتج: الضغط على صورة مصغّرة يبدّل الصورة الكبيرة
    const main = document.querySelector('[data-gallery-main]');
    document.querySelectorAll('[data-gallery-thumb]').forEach((thumb) => {
        thumb.addEventListener('click', () => {
            if (main) {
                main.src = thumb.dataset.galleryThumb;
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
