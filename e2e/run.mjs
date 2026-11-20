// سيناريو شراء كامل في متصفح حقيقي (Chromium) على نسخة تعمل فعليًا بقاعدة MariaDB منفصلة للاختبار.
// الاستخدام: BASE_URL=http://127.0.0.1:8000 ADMIN_EMAIL=.. ADMIN_PASSWORD=.. node e2e/run.mjs
import { chromium } from '@playwright/test';
import fs from 'node:fs';

const BASE = process.env.BASE_URL || 'http://127.0.0.1:8000';
const EMAIL = process.env.ADMIN_EMAIL;
const PASS = process.env.ADMIN_PASSWORD;
const OUT = process.env.SHOTS_DIR || 'e2e-output';
fs.mkdirSync(OUT, { recursive: true });

const results = [];
const check = (name, ok, detail = '') => {
    results.push({ name, ok: !!ok, detail: String(detail) });
    console.log(`${ok ? 'PASS' : 'FAIL'}  ${name}${detail ? '  → ' + detail : ''}`);
};
const step = async (name, fn) => {
    try { await fn(); } catch (e) { check(name, false, e.message.split('\n')[0]); }
};

const browser = await chromium.launch({ args: ['--use-gl=swiftshader', '--enable-unsafe-swiftshader', '--ignore-gpu-blocklist'] });
const phone = await browser.newContext({ viewport: { width: 360, height: 740 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true, locale: 'ar' });
const desk = await browser.newContext({ viewport: { width: 1440, height: 900 }, locale: 'ar' });
const adminCtx = await browser.newContext({ viewport: { width: 1280, height: 800 }, locale: 'ar' });
const m = await phone.newPage();
const d = await desk.newPage();
const a = await adminCtx.newPage();
for (const p of [m, d, a]) p.setDefaultTimeout(20000);

const shot = (p, name, full = false) => p.screenshot({ path: `${OUT}/${name}.png`, fullPage: full });
const noOverflow = async (p, label) => {
    const w = await p.evaluate(() => [document.documentElement.scrollWidth, window.innerWidth]);
    check(`${label}: لا تمرير أفقي`, w[0] <= w[1], `${w[0]} / ${w[1]}`);
};
const money = (n) => `${Number(n).toFixed(2)}`;

// ---------- 1) المدير يغيّر سعر منتج من فيلامنت ----------
const NEW_PRICE = 199;
await step('admin-login', async () => {
    await a.goto(`${BASE}/admin/login`);
    await a.locator('input[type="email"]').fill(EMAIL);
    await a.locator('input[type="password"]').fill(PASS);
    await a.locator('form button[type="submit"]').first().click();
    await a.waitForURL(/\/admin(?!\/login)/);
    await shot(a, '10-admin-dashboard');
    check('المدير يدخل لوحة فيلامنت', true);
});
await step('admin-edit-price', async () => {
    await a.goto(`${BASE}/admin/products/2/edit`);
    const price = a.locator('[wire\\:model="data.price"]');
    check('المنتج 2 قبل التعديل سعره 249', (await price.inputValue()).startsWith('249'), await price.inputValue());
    await price.fill(String(NEW_PRICE));
    await a.locator('form button[type="submit"]').first().click();
    await a.waitForTimeout(2500);
    await shot(a, '11-admin-product-saved');
    await a.reload();
    check('السعر الجديد محفوظ في اللوحة', (await a.locator('[wire\\:model="data.price"]').inputValue()).startsWith(String(NEW_PRICE)));
});

// ---------- 2) الهاتف 360px: بحث ← سعر جديد ← سلة ← كمية ← طلب كضيف ----------
let orderNumber = '';
await step('mobile-home', async () => {
    await m.goto(`${BASE}/`);
    await m.waitForLoadState('networkidle');
    await shot(m, '01-mobile-home');
    check('الرئيسية: زر «تسوّق المجموعة» ظاهر', await m.getByRole('link', { name: 'تسوّق المجموعة' }).isVisible());
    await noOverflow(m, 'الرئيسية 360');
    await shot(m, '01b-mobile-home-full', true);
    await m.getByRole('link', { name: 'تسوّق المجموعة' }).click();
    await m.waitForURL(/collection=featured/);
    await shot(m, '02-mobile-collection');
    const cols = await m.evaluate(() => {
        const cards = [...document.querySelectorAll('article.card')];
        return new Set(cards.map((c) => Math.round(c.getBoundingClientRect().top))).size && cards.filter((c) => Math.abs(c.getBoundingClientRect().top - cards[0].getBoundingClientRect().top) < 4).length;
    });
    check('الكتالوج: بطاقتان في الصف على 360px', cols === 2, `cards in first row: ${cols}`);
});
await step('mobile-search', async () => {
    await m.goto(`${BASE}/products`);
    await m.locator('summary').first().click();
    await m.locator('#q').fill('قنديل');
    await m.getByRole('button', { name: 'تطبيق' }).click();
    await m.waitForURL(/q=/);
    await shot(m, '03-mobile-search');
    const card = m.locator('article.card', { hasText: 'قنديل' });
    check('البحث يعرض المنتج بالسعر الجديد من فيلامنت', (await card.innerText()).includes(money(NEW_PRICE)), (await card.innerText()).replace(/\s+/g, ' '));
    await card.getByRole('link').first().click();
    await m.waitForURL(/riwaq-2/);
    await shot(m, '04-mobile-product');
    check('صفحة المنتج تعرض السعر الجديد', (await m.locator('[data-price]').innerText()).includes(money(NEW_PRICE)));
    await noOverflow(m, 'صفحة المنتج 360');
    const bar = await m.locator('[data-buy-bar]').boundingBox();
    check('شريط الشراء ثابت أسفل الشاشة (قابل للوصول بالإبهام)', bar && bar.y + bar.height >= 730, JSON.stringify(bar));
    await m.locator('[data-add]').click();
    await m.waitForURL(/riwaq-2/);
});
await step('mobile-cart-edit', async () => {
    await m.goto(`${BASE}/cart`);
    await m.locator('input[name="quantity"]').first().fill('2');
    await m.locator('input[name="quantity"]').first().dispatchEvent('change');
    await m.waitForLoadState('networkidle');
    await m.waitForTimeout(800);
    await shot(m, '05-mobile-cart');
    const text = await m.locator('[data-cart-line]').first().innerText();
    check('تعديل الكمية يحدّث إجمالي السطر', text.includes(money(NEW_PRICE * 2)), text.replace(/\s+/g, ' '));
    await noOverflow(m, 'السلة 360');
});
await step('mobile-checkout-guest', async () => {
    await m.goto(`${BASE}/checkout`);
    await m.locator('#name').fill('زبون تجريبي');
    await m.locator('#phone').fill('0599123456');
    await m.locator('#city').fill('رام الله');
    await m.locator('#address').fill('شارع الإرسال 5');
    const zone = m.locator('select[data-zone]');
    await zone.selectOption({ index: 0 });
    const feeText = await m.locator('[data-shipping]').innerText();
    const totalText = await m.locator('[data-total]').innerText();
    check('الشحن والإجمالي ظاهران قبل التأكيد', feeText.includes('20.00') && totalText.includes(money(NEW_PRICE * 2 + 20)), `${feeText} | ${totalText}`);
    await shot(m, '06-mobile-checkout', true);
    // ضغط مزدوج سريع على زر التأكيد
    await m.getByRole('button', { name: /تأكيد الطلب/ }).dblclick();
    await m.waitForURL(/order\/success/);
    orderNumber = (await m.locator('[data-order-number]').innerText()).trim();
    await shot(m, '07-mobile-success');
    check('صفحة النجاح تعرض رقم الطلب', /^ORD-\d{6}-[A-Z0-9]{5}$/.test(orderNumber), orderNumber);
    check('إجمالي الطلب صحيح (2×199 + 20)', (await m.locator('[data-order-total]').innerText()).includes(money(NEW_PRICE * 2 + 20)));
});

// ---------- 3) خيارات المنتج والنفاد على الهاتف ----------
await step('variants', async () => {
    await m.goto(`${BASE}/products/riwaq-1`);
    await m.locator('label.chip', { hasText: 'كبير' }).click();
    const big = await m.locator('[data-price]').innerText();
    await m.locator('label.chip', { hasText: 'صغير' }).click();
    const small = await m.locator('[data-price]').innerText();
    check('تغيير المقاس يغيّر السعر (129 ↔ 89)', big.includes('129.00') && small.includes('89.00'), `${big} | ${small}`);
    await shot(m, '08-mobile-variants');
});
await step('out-of-stock', async () => {
    await m.goto(`${BASE}/products/riwaq-8`);
    check('منتج نافد: الزر معطّل ويقول غير متوفر', await m.locator('[data-add]').isDisabled() && (await m.locator('[data-add-label]').innerText()).includes('غير متوفر'));
    await shot(m, '09-mobile-out-of-stock');
    await m.goto(`${BASE}/products/riwaq-5`);
    const disabled = await m.locator('label.chip', { hasText: 'عاجي' }).locator('input').isDisabled();
    check('خيار نافد (عاجي) معطّل', disabled);
    // محاولة إضافة منتج نافد مباشرة عبر الخادم
    const resp = await m.request.post(`${BASE}/cart/add/8`, { form: { quantity: 1 }, failOnStatusCode: false });
    check('الخادم لا يقبل نافدًا (لا 500)', resp.status() < 500, resp.status());
});

// ---------- 4) اللوحة: الطلب والإجمالي وخصم المخزون مرة واحدة ----------
await step('admin-verify-order', async () => {
    await a.goto(`${BASE}/admin/orders`);
    await a.waitForLoadState('networkidle');
    await shot(a, '12-admin-orders');
    const rows = await a.locator('table tbody tr').count();
    const rowText = await a.locator('table tbody tr', { hasText: orderNumber }).first().innerText();
    check('الطلب ظاهر في اللوحة برقمه', rowText.includes(orderNumber));
    check('إجمالي الطلب في اللوحة صحيح', rowText.includes(money(NEW_PRICE * 2 + 20)), rowText.replace(/\s+/g, ' '));
    check('طلب واحد فقط رغم الضغط المزدوج', rows === 1, `rows=${rows}`);
    await a.goto(`${BASE}/admin/products/2/edit`);
    const stock = await a.locator('[wire\\:model="data.stock"]').inputValue();
    check('المخزون خُصم مرة واحدة (7 − 2 = 5)', stock === '5', stock);
});

// ---------- 5) سطح المكتب ----------
await step('desktop', async () => {
    await d.goto(`${BASE}/`);
    await d.waitForLoadState('networkidle');
    await shot(d, '20-desktop-home');
    await d.goto(`${BASE}/products/riwaq-1`);
    await d.waitForLoadState('networkidle');
    await shot(d, '21-desktop-product');
    await noOverflow(d, 'صفحة المنتج 1440');
    const btn = d.getByRole('button', { name: '3D' }).or(d.locator('[data-viewer-toggle]'));
    await btn.click();
    await d.waitForSelector('[data-viewer-stage] canvas', { timeout: 20000 });
    await d.waitForTimeout(1200);
    await shot(d, '22-desktop-3d');
    check('العرض ثلاثي الأبعاد يُحمَّل عند الضغط', true);
    await d.goto(`${BASE}/products/riwaq-4`);
    await d.locator('[data-add]').click();
    await d.goto(`${BASE}/cart`);
    await shot(d, '23-desktop-cart');
    await noOverflow(d, 'السلة 1440');
});

await browser.close();
fs.writeFileSync(`${OUT}/results.json`, JSON.stringify(results, null, 2));
const failed = results.filter((r) => !r.ok);
console.log(`\n${results.length - failed.length}/${results.length} فحصًا نجح`);
process.exit(failed.length ? 1 : 0);
