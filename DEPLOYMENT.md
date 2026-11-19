# دليل النشر (Hostinger وغيره)

> هذا الدليل لم يُجرَّب على استضافة فعلية. المتطلبات وخيارات اللوحة تختلف حسب الخطة وقد تتغير؛ راجع وثائق الاستضافة الرسمية وتحقق من كل خطوة على نسخة تجريبية قبل عميل حقيقي.

## 0. ما تحتاجه من الاستضافة

| المتطلب | لماذا |
|---|---|
| PHP **8.3+** مع الإضافات (intl, mbstring, pdo_mysql, zip, gd, xml…) | Laravel 13 |
| وصول **SSH** | تشغيل Composer وartisan (الأسهل بكثير) |
| Composer (أو إمكانية تنزيل `composer.phar`) | الاعتماديات |
| MySQL/MariaDB | قاعدة البيانات |
| إمكانية جعل **جذر الموقع = `public/`** أو إنشاء رابط رمزي | الأمان |
| SSL مجاني/مدفوع | HTTPS |
| Node.js (اختياري) | بناء الواجهة؛ وإلا ابنِها محليًا/CI |

> الاستضافة المشتركة قد لا تسمح بتغيير جذر الموقع، وقد يختلف PHP في SSH عن المضبوط في اللوحة (انظر 3).

## 1. تجهيز قاعدة البيانات

1. hPanel ← **Databases** ← أنشئ قاعدة ومستخدمًا وكلمة مرور قوية.
2. سجّل: اسم القاعدة، المستخدم، كلمة المرور، المضيف (غالبًا `localhost`).

## 2. رفع المشروع

**الطريقة أ — SSH + Git (موصى بها):**

```bash
ssh -p PORT USER@HOST            # بيانات SSH من hPanel ← Advanced ← SSH Access
cd ~
git clone https://github.com/OWNER/REPO.git store-app    # للمستودع الخاص: مفتاح نشر (Deploy key) للقراءة فقط
cd store-app
git checkout <الفرع-أو-الوسم>
```

> ميزة Git المدمجة في hPanel تنسخ الملفات إلى `public_html` فقط، وهذا **غير مناسب** لـ Laravel لأنه يضع كل المشروع (ومنه `.env` لو وُجد) داخل الجذر العام. فضّل SSH.

**الطريقة ب — رفع حزمة جاهزة:** ابنِ حزمة (انظر `.github/workflows/release.yml`) تحتوي `vendor/` و`public/build/`، ارفعها عبر مدير الملفات/SFTP وفُكّها **خارج** `public_html`.

## 3. إصدار PHP في SSH

```bash
php -v
```

إن ظهر إصدار أقدم من 8.3 رغم ضبط 8.3 في hPanel، فسطر الأوامر يستخدم PHP مستقلًا. جرّب مسار الإصدار المطلوب (على Hostinger غالبًا تحت `/opt/alt/phpXX/usr/bin/php`، تحقق بـ `ls /opt/alt/`):

```bash
alias php='/opt/alt/php83/usr/bin/php'     # ضعه في ~/.bashrc ليدوم
php -v
```

> لا تستخدم `--ignore-platform-reqs` لتجاوز هذه المشكلة؛ هذا يخفي الخطأ ولا يحلّه.

## 4. Composer

```bash
composer --version || {
  php -r "copy('https://getcomposer.org/installer','composer-setup.php');"
  php composer-setup.php --install-dir=$HOME --filename=composer.phar
  rm composer-setup.php
}
# استخدم: php ~/composer.phar بدل composer
composer install --no-dev --optimize-autoloader
```

## 5. ملف `.env` (الأسرار تُكتب هنا فقط)

```bash
cp .env.example .env
nano .env        # أو عدّله عبر مدير الملفات في hPanel
php artisan key:generate
chmod 600 .env
```

اضبط: `APP_ENV=production`، `APP_DEBUG=false`، `APP_URL=https://دومينك`، `DB_*`، `INSTALL_TOKEN` (عشوائي طويل)، `MAIL_*`.

> بيانات قاعدة البيانات توضع **في هذا الملف على الخادم** أو في متغيرات البيئة التي توفرها الاستضافة. لا تضعها في Git، ولا ترسلها عبر واتساب/بريد غير مشفّر، ولا تُدخلها في أي صفحة من الموقع (القالب لا يطلبها أصلًا).

## 6. جذر الموقع (أهم خطوة أمنية)

**الخيار 1 — تغيير الجذر (الأفضل):** إن سمحت الخطة (VPS/Cloud أو خيار Document Root/Website root) فاجعل الجذر `…/store-app/public`.

**الخيار 2 — رابط رمزي (إن سمحت الاستضافة بـ symlink):**

```bash
cd ~/domains/دومينك          # مسار الموقع قد يختلف؛ تحقق من hPanel
mv public_html public_html.bak
ln -s ~/store-app/public public_html
```

**الخيار 3 — نسخ محتوى `public/` إلى `public_html`:**

```bash
cp -r ~/store-app/public/. ~/domains/دومينك/public_html/
```

ثم عدّل `public_html/index.php` ليشير إلى المشروع خارج الجذر العام:

```php
if (file_exists($maintenance = __DIR__.'/../store-app/storage/framework/maintenance.php')) { require $maintenance; }
require __DIR__.'/../store-app/vendor/autoload.php';
(require_once __DIR__.'/../store-app/bootstrap/app.php')->handleRequest(Request::capture());
```

(عدّل `../store-app` بحسب موقع المشروع الفعلي.) وبعد **كل تحديث** أعد نسخ `public/build` (وأي تغيير في `public/`).

**الخيار 4 (ملاذ أخير) — جذر الموقع = جذر المشروع:** يوجد `.htaccess` في الجذر يمنع الملفات الحساسة ويحوّل إلى `public/`. هذا أضعف الخيارات؛ **تحقق يدويًا** بعد التفعيل أن هذه الروابط تعطي 403/404:

- `https://دومينك/.env`
- `https://دومينك/composer.json`
- `https://دومينك/storage/logs/laravel.log`
- `https://دومينك/vendor/autoload.php`

## 7. الواجهة (Vite/Tailwind)

- **Node متوفر على الخادم:** `npm ci && npm run build`.
- **غير متوفر:** ابنِها محليًا (`npm ci && npm run build`) أو من GitHub Actions، ثم ارفع مجلد `public/build/` إلى `public/` على الخادم.

بدون `public/build/manifest.json` ستظهر أخطاء Vite manifest.

## 8. قاعدة البيانات والملفات

```bash
php artisan migrate --force
php artisan storage:link
chmod -R ug+rwX storage bootstrap/cache
```

إن فشل `storage:link` (الاستضافة تمنع الروابط الرمزية): أنشئ الرابط يدويًا من لوحة الاستضافة إن توفر، أو انسخ/ازامن `storage/app/public` إلى `public/storage` (وكرّر ذلك عند رفع صور جديدة) — حل مؤقت أقل راحة، فالأفضل خطة تدعم الروابط الرمزية.

## 9. الدومين وSSL

1. **DNS:** وجّه سجل `A` (للجذر) و`CNAME`/`A` لـ `www` إلى IP الاستضافة، من مزوّد الدومين، أو غيّر nameservers إلى استضافتك. الانتشار قد يستغرق من دقائق إلى 24 ساعة.
2. أضف الدومين إلى الموقع في hPanel.
3. فعّل **SSL** (hPanel ← Security ← SSL)، وفعّل **Force HTTPS**.
4. اضبط `APP_URL=https://…` في `.env` ثم:

```bash
php artisan config:clear
```

5. تحقق أن `http://` يحوّل إلى `https://` وأن الروابط والصور تُحمَّل بـ HTTPS (بلا Mixed Content).
6. **تغيير الدومين لاحقًا:** حدّث `APP_URL`، ثم `config:clear`، وتحقق من رابط الصور (`storage:link`).

## 10. تشغيل المتجر لأول مرة

1. افتح `https://دومينك/install` (يتطلب `INSTALL_TOKEN` في production).
2. املأ بيانات المتجر والألوان (مع معاينة فورية) وحساب المدير.
3. بعد الحفظ تُغلق الصفحة تلقائيًا (404) ويُنشأ `storage/app/installed.lock`.
4. سجّل الدخول إلى `/admin`.
5. احذف سطر `INSTALL_TOKEN` من `.env` (اختياري) ثم `php artisan config:clear`.

بديل عبر SSH: `php artisan store:create-admin` (لكن إعدادات المتجر ستحتاج ضبطها من لوحة الإدارة ← إعدادات المتجر، ويلزم صف الإعدادات؛ الأسهل استخدام `/install`).

## 11. التحسين للإنتاج

```bash
php artisan optimize        # config/route/view cache
```

بعد أي تعديل على `.env`: `php artisan config:clear && php artisan optimize`.

> لا تشغّل `config:cache` قبل ضبط `.env` نهائيًا.

## 12. البريد

اضبط `MAIL_*` من بيانات مزوّد البريد (SMTP). القالب لا يرسل بريدًا للزبائن افتراضيًا؛ الإعداد جاهز لإضافة ذلك لاحقًا.

## 13. النسخ الاحتياطي (مختصر)

انظر README القسم 12. لا تُجرِ تحديثًا دون نسخة احتياطية للقاعدة والصور.

## 14. فحوص ما بعد النشر

- [ ] `https://دومينك` يفتح المتجر بالألوان الصحيحة وRTL.
- [ ] `/install` يعطي 404.
- [ ] `/.env` و`/composer.json` و`/storage/logs/laravel.log` غير متاحة.
- [ ] تسجيل الدخول إلى `/admin` يعمل، ورفع صورة منتج يعمل وتظهر في المتجر.
- [ ] طلب تجريبي كامل (سلة ← إتمام ← نجاح) يظهر في لوحة الإدارة، وينقص المخزون.
- [ ] `APP_DEBUG=false`.

## 15. بدائل الاستضافة

| النوع | الاختلاف |
|---|---|
| Hostinger مشتركة | غالبًا SSH متاح في بعض الخطط، جذر الموقع `public_html` (استخدم الخيارات 2/3) |
| Hostinger Cloud/VPS | تحكم أكبر بالجذر وPHP وNode |
| cPanel عام | "Domains ← Document Root" لتغيير الجذر؛ MultiPHP لاختيار الإصدار؛ Terminal إن وُجد |
| VPS (Nginx/Apache) | اضبط الجذر على `public/` وإعادة التوجيه إلى `index.php` (إعداد Laravel القياسي) |

> المتطلبات تختلف حسب الخطة والمزوّد ومن وقت لآخر؛ تأكد قبل الشراء.
