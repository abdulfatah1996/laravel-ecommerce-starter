# واجهة الشراء (فرع feature/storefront-ux)

- **الجديد:** خيارات المنتج (مقاس/لون بسعر ومخزون لكل خيار)، مناطق شحن بأسعار، حساب زبون (تسجيل/دخول/«طلباتي»)، واتساب، عنوان الواجهة، عرض ثلاثي الأبعاد لمصباح، DESIGN.md.
- **بيانات المعاينة:** `php artisan db:seed --class=RiwaqDemoSeeder` (متجر «رِواق»، صور مرسومة بالكود وليست صور منتجات حقيقية؛ احذفها بـ `php artisan store:demo-clean`).
- **الاختبار:** `php artisan test` + سيناريو المتصفح `node e2e/run.mjs` (يعمل تلقائيًا في GitHub Actions: E2E، ويحفظ اللقطات في الفرع ci-screenshots).

## تحديث متجر حي على Hostinger (يحافظ على البيانات)
```bash
cd ~/domains/YOUR_DOMAIN/store-app            # مسار مجلد المشروع
git fetch origin && git checkout feature/storefront-ux && git pull --ff-only
/opt/alt/php83/usr/bin/php composer.phar install --no-dev --optimize-autoloader   # أو composer install --no-dev
npm ci && npm run build                        # أو ارفع public/build من جهازك
/opt/alt/php83/usr/bin/php artisan config:clear && /opt/alt/php83/usr/bin/php artisan view:clear
```
**خطوة منفصلة بعد التأكد من نسخة احتياطية لقاعدة البيانات** (الترحيل إضافي فقط ولا يحذف بيانات):
```bash
/opt/alt/php83/usr/bin/php artisan migrate --force
```
لا تشغّل `db:seed` ولا `migrate:fresh` على المتجر الحي.
