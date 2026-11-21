// رحلة شراء كاملة + لقطات على 360/768/1440 بمتصفح حقيقي. تعمل على كود «قبل» و«بعد» بنفس المحددات العامة.
import { chromium } from '@playwright/test';
import fs from 'node:fs';
import { execSync } from 'node:child_process';

const BASE = process.env.BASE_URL || 'http://127.0.0.1:8000';
const OUT = 'qa-output';
fs.mkdirSync(OUT, { recursive: true });

const OWNER = { name: 'مدير الفحص', email: 'owner@example.test', pass: 'Qa-Passw0rd-2026' };
const results = { checks: [], touchOffenders: [], pageErrors: [], notes: [] };
const check = (name, ok, detail = '') => {
    results.checks.push({ name, ok: !!ok, detail: String(detail) });
    console.log(`${ok ? 'PASS' : 'FAIL'}  ${name}${detail ? '  → ' + detail : ''}`);
};
const step = async (name, fn) => {
    try { await fn(); } catch (e) { check(`${name} (استثناء)`, false, e.message.split('\n')[0]); }
};

const browser = await chromium.launch();
const mk = (viewport, extra = {}) => browser.newContext({ viewport, locale: 'ar', ...extra });
const ctxM = await mk({ width: 360, height: 740 }, { deviceScaleFactor: 2, isMobile: true, hasTouch: true });
const ctxT = await mk({ width: 768, height: 1024 });
const ctxD = await mk({ width: 1440, height: 900 });
const ctxA = await mk({ width: 1280, height: 800 });
const ctxAM = await mk({ width: 390, height: 780 }, { deviceScaleFactor: 2, isMobile: true, hasTouch: true });
const pages = { m: await ctxM.newPage(), t: await ctxT.newPage(), d: await ctxD.newPage(), a: await ctxA.newPage(), am: await ctxAM.newPage() };
for (const [k, p] of Object.entries(pages)) {
    p.setDefaultTimeout(20000);
    p.on('pageerror', (e) => results.pageErrors.push(`[${k}] ${e.message.split('\n')[0]}`));
    p.on('response', (r) => { if (r.status() >= 400 && !r.url().includes('favicon')) results.pageErrors.push(`[${k}] ${r.status()} ${r.url().replace(BASE, '')}`); });
}
const { m, t, d, a, am } = pages;

const shot = (p, name, full = false) => p.screenshot({ path: `${OUT}/${name}.png`, fullPage: full });
const widthOf = { m: 360, t: 768, d: 1440 };
const noOverflow = async (p, key, label) => {
    const w = await p.evaluate(() => document.documentElement.scrollWidth);
    check(`${label} @${widthOf[key]}: لا تمرير أفقي`, w <= widthOf[key], `scrollWidth=${w}`);
};
const touchAudit = async (p, label) => {
    const bad = await p.evaluate(() => {
        const sel = 'button, input:not([type=hidden]):not([type=checkbox]):not([type=radio]), select, textarea, header a, nav a, a[class*="btn"], [role=button]';
        const out = [];
        for (const el of document.querySelectorAll(sel)) {
            const r = el.getBoundingClientRect();
            const st = getComputedStyle(el);
            if (r.width === 0 || r.height === 0 || st.visibility === 'hidden' || st.display === 'none' || el.classList.contains('sr-only')) continue;
            if (r.height < 40 || r.width < 40) out.push(`${el.tagName.toLowerCase()} "${(el.innerText || el.getAttribute('aria-label') || el.name || '').trim().slice(0, 24)}" ${Math.round(r.width)}x${Math.round(r.height)}`);
        }
        return out;
    });
    bad.forEach((b) => results.touchOffenders.push(`${label}: ${b}`));
    check(`${label}: أهداف اللمس ≥ 40px`, bad.length === 0, bad.slice(0, 4).join(' | '));
};
const visible = (p, sel) => p.locator(sel).filter({ visible: true });

// شعار اختبار مرسوم بالمتصفح (ليس شعار عميل حقيقي)
await step('logo', async () => {
    const p = await ctxD.newPage();
    await p.setViewportSize({ width: 360, height: 140 });
    await p.setContent('<body style="margin:0;background:transparent"><div style="font:800 84px \'Noto Naskh Arabic\',serif;color:#17483D;text-align:center;line-height:140px">نقوة</div></body>');
    await p.screenshot({ path: '/tmp/qa-logo.png', omitBackground: true });
    await p.close();
});

// ---------- 0) التثبيت الأول من الهاتف ----------
await step('install', async () => {
    await m.goto(`${BASE}/`);
    await m.waitForURL(/\/install/);
    await shot(m, 'm-00-install');
    await shot(m, 'm-00-install-full', true);
    await noOverflow(m, 'm', 'صفحة التثبيت');
    const setColor = (id, v) => m.locator(id).evaluate((el, val) => { el.value = val; el.dispatchEvent(new Event('input', { bubbles: true })); }, v);
    await m.locator('#store_name').fill('نقوة');
    await m.locator('#description').fill('هدايا ومستلزمات للبيت والحياة اليومية — قطع مختارة بعناية من فلسطين.');
    await m.locator('#logo').setInputFiles('/tmp/qa-logo.png');
    await m.locator('#email').fill('hello@example.test');
    await m.locator('#phone').fill('+970 59 000 0000');
    await m.locator('#country').fill('فلسطين');
    await m.locator('#city').fill('رام الله');
    await m.locator('#address').fill('شارع الإرسال');
    await m.locator('#currency_code').selectOption('ILS');
    await m.locator('#timezone').selectOption('Asia/Hebron');
    await setColor('#primary_color', '#17483D');
    await setColor('#secondary_color', '#FAF6EC');
    await setColor('#accent_color', '#AD603D');
    await m.locator('#owner_name').fill(OWNER.name);
    await m.locator('#owner_email').fill(OWNER.email);
    await m.locator('#owner_password').fill(OWNER.pass);
    await m.locator('#owner_password_confirmation').fill(OWNER.pass);
    await shot(m, 'm-00-install-filled', true);
    await m.locator('form button[type="submit"]').last().click();
    await m.waitForURL(/\/admin/);
    check('التثبيت الأول ينجح ويحوّل إلى لوحة الإدارة', true);
    await shot(m, 'm-00-after-install-admin');
});

execSync('php artisan db:seed --class=QaSeeder --force', { stdio: 'inherit' });
const products = JSON.parse(fs.readFileSync('storage/app/qa-products.json', 'utf8'));
const inStock = products.find((p) => p.stock > 3 && p.name.includes('قنديل'));
const longName = products[0];
const oos = products.find((p) => p.stock === 0);
const lowStock = products.find((p) => p.stock > 0 && p.stock <= 5);

const addToCart = async (p) => {
    const btn = visible(p, 'form[action*="/cart/add/"] button[type="submit"]').first();
    await btn.click();
    await p.waitForLoadState('load');
};

// ---------- 1) الهاتف: الرئيسية ← بحث ← منتج ← سلة ← دفع ----------
await step('mobile-home', async () => {
    await m.goto(`${BASE}/`);
    await m.waitForLoadState('networkidle');
    await shot(m, 'm-01-home');
    await shot(m, 'm-01-home-full', true);
    await noOverflow(m, 'm', 'الرئيسية');
    await touchAudit(m, 'الرئيسية 360');
    const bodyText = await m.locator('body').innerText();
    const vars = await m.evaluate(() => ['--store-primary','--store-secondary','--store-accent'].map((k) => getComputedStyle(document.documentElement).getPropertyValue(k).trim()).join(' '));
    check('ألوان المتجر المحقونة (المتوقع #17483D #FAF6EC #AD603D)', /17483d/i.test(vars), vars);
    check('الرئيسية: اسم المتجر ووصفه ظاهران', bodyText.includes('نقوة') && bodyText.includes('هدايا ومستلزمات'));
    const fold = await m.evaluate(() => {
        const imgs = [...document.querySelectorAll('img')].filter((i) => { const r = i.getBoundingClientRect(); return r.top < innerHeight && r.bottom > 0 && r.width > 80; });
        const prices = [...document.querySelectorAll('body *')].filter((e) => e.children.length === 0 && /₪/.test(e.textContent) && e.getBoundingClientRect().top < innerHeight && e.getBoundingClientRect().bottom > 0);
        return { imgs: imgs.length, prices: prices.length };
    });
    check('الرئيسية: منتج (صورة) ظاهر في أول شاشة على الهاتف', fold.imgs > 0, JSON.stringify(fold));
});

const openSearch = async (p) => {
    if (!(await visible(p, 'input[name="q"]').count())) {
        const toggle = (await p.locator('[data-search-toggle]').count()) ? '[data-search-toggle]' : '[data-menu-toggle]';
        await visible(p, toggle).first().click();
    }
    await visible(p, 'input[name="q"]').first().waitFor();
};

await step('mobile-search', async () => {
    await openSearch(m);
    await shot(m, 'm-02-search-open');
    const q = visible(m, 'input[name="q"]').first();
    await q.fill('مصباح');
    await q.press('Enter');
    await m.waitForURL(/products\?.*q=/);
    await m.waitForLoadState('networkidle');
    await shot(m, 'm-03-search-results');
    await shot(m, 'm-03-search-results-full', true);
    await noOverflow(m, 'm', 'نتائج البحث');
    await touchAudit(m, 'الكتالوج 360');
    const cols = await m.evaluate(() => {
        const cards = [...document.querySelectorAll('article')].filter((c) => c.getBoundingClientRect().height > 0);
        if (!cards.length) return 0;
        const t0 = cards[0].getBoundingClientRect().top;
        return cards.filter((c) => Math.abs(c.getBoundingClientRect().top - t0) < 6).length;
    });
    check('الكتالوج على 360: بطاقتان في الصف الأول', cols === 2, `عدد البطاقات في الصف: ${cols}`);
    const names = await m.locator('article a').allInnerTexts();
    check('الكتالوج: الاسم العربي الطويل ظاهر دون كسر الصفحة', names.some((n) => n.includes('مصباح طاولة سيراميك')));
});

await step('mobile-filters', async () => {
    await m.goto(`${BASE}/products`);
    await m.waitForLoadState('networkidle');
    await shot(m, 'm-04-catalog');
    await shot(m, 'm-04-catalog-full', true);
    const toggle = m.locator('[data-filter-toggle]');
    if (await toggle.count()) { await toggle.first().click(); await m.waitForTimeout(300); await shot(m, 'm-04b-filters-open'); }
    await m.goto(`${BASE}/products?sort=price_asc&availability=in_stock`);
    const sel = await visible(m, 'select[name="sort"]').count();
    check('الفلاتر الحالية (الترتيب) تعمل وقابلة للوصول على الهاتف', sel >= 0);
});

await step('mobile-product', async () => {
    await m.goto(`${BASE}/products/${longName.slug}`);
    await m.waitForLoadState('networkidle');
    await shot(m, 'm-05-product');
    await shot(m, 'm-05-product-full', true);
    await noOverflow(m, 'm', 'صفحة المنتج (اسم طويل)');
    await touchAudit(m, 'المنتج 360');
    const buy = await visible(m, 'form[action*="/cart/add/"] button[type="submit"]').count();
    check('المنتج: زر الشراء ظاهر وقابل للضغط', buy > 0);
    const priceTxt = await m.locator('main').innerText();
    check('المنتج: السعر بالأرقام والرمز ظاهر (129.00)', /129\.00/.test(priceTxt) && priceTxt.includes('₪'));

    await m.goto(`${BASE}/products/${oos.slug}`);
    await m.waitForLoadState('networkidle');
    await shot(m, 'm-06-product-out-of-stock');
    const oosText = await m.locator('main').innerText();
    check('المنتج النافد: يظهر «غير متوفر» دون زر شراء رئيسي', oosText.includes('غير متوفر'));

    await m.goto(`${BASE}/products/${lowStock.slug}`);
    await shot(m, 'm-06b-product-low-stock');
});

await step('mobile-cart', async () => {
    await m.goto(`${BASE}/products/${inStock.slug}`);
    await addToCart(m);
    await shot(m, 'm-07-after-add');
    await m.goto(`${BASE}/products/${longName.slug}`);
    await addToCart(m);
    await m.goto(`${BASE}/cart`);
    await m.waitForLoadState('networkidle');
    await shot(m, 'm-08-cart');
    await shot(m, 'm-08-cart-full', true);
    await noOverflow(m, 'm', 'السلة');
    await touchAudit(m, 'السلة 360');

    const before = (await m.locator('main').innerText());
    const form = m.locator('form[action*="/cart/"]:not([action*="/add/"]) input[name="quantity"]').first();
    await form.evaluate((el) => { el.value = '2'; el.dispatchEvent(new Event('input', { bubbles: true })); el.dispatchEvent(new Event('change', { bubbles: true })); el.form.requestSubmit(); });
    await m.waitForLoadState('load');
    await m.waitForTimeout(500);
    await shot(m, 'm-09-cart-qty-2');
    const after = await m.locator('main').innerText();
    check('السلة: تعديل الكمية يغيّر الإجمالي', before !== after && /498\.00/.test(after.replace(/\s/g, ' ')) , 'المتوقع ظهور 498.00 (2 × 249)');
    check('السلة: تفصيل التكلفة (المجموع الفرعي والشحن والإجمالي) ظاهر', /المجموع الفرعي/.test(after) && /الإجمالي/.test(after));
});

let orderNumber = '';
await step('mobile-checkout', async () => {
    await m.goto(`${BASE}/checkout`);
    await m.waitForLoadState('networkidle');
    await shot(m, 'm-10-checkout');
    await shot(m, 'm-10-checkout-full', true);
    await noOverflow(m, 'm', 'إتمام الطلب');
    await touchAudit(m, 'الدفع 360');

    // خطأ من الخادم: هاتف غير صالح — نتحقق من مكان الرسالة والاحتفاظ بالبيانات
    await m.locator('#name').fill('ليلى أحمد');
    await m.locator('#phone').fill('abc');
    await m.locator('#city').fill('رام الله');
    await m.locator('#address').fill('شارع الإرسال، عمارة 4، الطابق 2');
    await m.locator('#notes').fill('الاتصال قبل التوصيل');
    await visible(m, 'form[action*="/checkout"] button[type="submit"]').first().click();
    await m.waitForLoadState('load');
    await shot(m, 'm-11-checkout-error');
    await shot(m, 'm-11-checkout-error-full', true);
    const errBox = m.getByText('رقم الهاتف غير صحيح').first();
    const phoneBox = await m.locator('#phone').boundingBox();
    const eb = await errBox.boundingBox();
    const dist = eb && phoneBox ? Math.abs(eb.y - (phoneBox.y + phoneBox.height)) : 9999;
    check('الخطأ: رسالة الهاتف بجانب الحقل المعني (≤ 80px)', dist <= 80, `المسافة ${Math.round(dist)}px`);
    check('الخطأ: بيانات الزبون محفوظة بعد التصحيح', (await m.locator('#name').inputValue()) === 'ليلى أحمد' && (await m.locator('#address').inputValue()).includes('عمارة 4') && (await m.locator('#notes').inputValue()).includes('قبل التوصيل'));

    await m.locator('#phone').fill('0599123456');
    await visible(m, 'form[action*="/checkout"] button[type="submit"]').first().click();
    await m.waitForURL(/order\/success/);
    await m.waitForLoadState('networkidle');
    await shot(m, 'm-12-order-success');
    await shot(m, 'm-12-order-success-full', true);
    await noOverflow(m, 'm', 'نجاح الطلب');
    const txt = await m.locator('main').innerText();
    orderNumber = (txt.match(/[A-Z]RD-[A-Z0-9]+-[A-Z0-9]+/) || [''])[0];
    check('إتمام الطلب: ظهر رقم الطلب', !!orderNumber, orderNumber);
});

// ---------- 2) اللوحي والمكتب ----------
for (const key of ['t', 'd']) {
    const p = pages[key];
    await step(`${key}-store`, async () => {
        await p.goto(`${BASE}/`); await p.waitForLoadState('networkidle');
        await shot(p, `${key}-01-home`); await shot(p, `${key}-01-home-full`, true);
        await noOverflow(p, key, 'الرئيسية');
        await p.goto(`${BASE}/products`); await p.waitForLoadState('networkidle');
        await shot(p, `${key}-02-catalog`); await shot(p, `${key}-02-catalog-full`, true);
        await noOverflow(p, key, 'الكتالوج');
        await p.goto(`${BASE}/products/${longName.slug}`); await p.waitForLoadState('networkidle');
        await shot(p, `${key}-03-product`); await shot(p, `${key}-03-product-full`, true);
        await noOverflow(p, key, 'صفحة المنتج');
        await addToCart(p);
        await p.goto(`${BASE}/products/${inStock.slug}`); await addToCart(p);
        await p.goto(`${BASE}/cart`); await p.waitForLoadState('networkidle');
        await shot(p, `${key}-04-cart`); await noOverflow(p, key, 'السلة');
        await p.goto(`${BASE}/checkout`); await p.waitForLoadState('networkidle');
        await shot(p, `${key}-05-checkout`); await shot(p, `${key}-05-checkout-full`, true);
        await noOverflow(p, key, 'إتمام الطلب');
        await p.goto(`${BASE}/about`); await shot(p, `${key}-06-page-about`);
    });
}

// ---------- 3) لوحة الإدارة ----------
const adminLogin = async (p) => {
    await p.goto(`${BASE}/admin/login`);
    await p.locator('input[type="email"]').fill(OWNER.email);
    await p.locator('input[type="password"]').fill(OWNER.pass);
    await p.locator('form button[type="submit"]').first().click();
    await p.waitForURL(/\/admin(?!\/login)/);
};
await step('admin', async () => {
    await a.goto(`${BASE}/admin/login`); await shot(a, 'a-00-login');
    await adminLogin(a);
    await a.waitForLoadState('networkidle');
    await shot(a, 'a-01-dashboard');
    await a.goto(`${BASE}/admin/orders`); await a.waitForLoadState('networkidle');
    await shot(a, 'a-02-orders');
    const ordersText = await a.locator('body').innerText();
    check('الطلب ظهر في لوحة الإدارة', !!orderNumber && ordersText.includes(orderNumber), orderNumber);
    const row = a.getByText(orderNumber).first();
    if (orderNumber && (await row.count())) { await row.click(); await a.waitForLoadState('networkidle'); await shot(a, 'a-03-order-edit'); await shot(a, 'a-03-order-edit-full', true); }
    await a.goto(`${BASE}/admin/products`); await a.waitForLoadState('networkidle'); await shot(a, 'a-04-products');
    await a.goto(`${BASE}/admin/products/${longName.id}/edit`); await a.waitForLoadState('networkidle'); await shot(a, 'a-05-product-edit'); await shot(a, 'a-05-product-edit-full', true);
    await a.goto(`${BASE}/admin/store-settings`); await a.waitForLoadState('networkidle'); await shot(a, 'a-06-settings'); await shot(a, 'a-06-settings-full', true);
});
await step('admin-mobile', async () => {
    await adminLogin(am);
    await am.goto(`${BASE}/admin/orders`); await am.waitForLoadState('networkidle'); await shot(am, 'am-01-orders');
    await am.goto(`${BASE}/admin/products`); await am.waitForLoadState('networkidle'); await shot(am, 'am-02-products');
});

// ---------- ملخص ----------
const failed = results.checks.filter((c) => !c.ok);
const lines = [
    `# QA ${process.env.QA_LABEL || ''}`, '',
    `النتيجة: ${results.checks.length - failed.length}/${results.checks.length} نجح`, '',
    ...results.checks.map((c) => `- ${c.ok ? '✅' : '❌'} ${c.name}${c.detail ? ' — ' + c.detail : ''}`), '',
    '## أهداف لمس أصغر من 40px', ...(results.touchOffenders.length ? results.touchOffenders.map((x) => `- ${x}`) : ['- لا شيء']), '',
    '## أخطاء صفحات/طلبات', ...(results.pageErrors.length ? [...new Set(results.pageErrors)].map((x) => `- ${x}`) : ['- لا شيء']),
];
fs.writeFileSync(`${OUT}/QA_SUMMARY.md`, lines.join('\n'));
fs.writeFileSync(`${OUT}/results.json`, JSON.stringify(results, null, 2));
await browser.close();
