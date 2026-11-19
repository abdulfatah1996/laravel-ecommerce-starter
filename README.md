# قالب متجر إلكتروني عربي (Laravel + Filament)

> ⚠️ **حالة الاختبار — اقرأ هذا أولًا**
> كُتب هذا القالب في بيئة لا تستطيع الوصول إلى Packagist، فلم يُنفَّذ `composer install` ولا `php artisan` ولا اختبارات PHPUnit ولا `npm run build`.
> الكود **غير مختبر تشغيليًا**. الذي فُحص فقط: صياغة PHP (`php -l`) وصحة JSON وJavaScript وفحص عدم وجود أسرار.
> تفاصيل ما اختُبر وما لم يُختبر في [`docs/TESTING_STATUS.md`](docs/TESTING_STATUS.md). **شغّل الخطوات في القسم 3 واجعل الاختبارات تمرّ قبل تجهيز أي عميل.**

## 1. فكرة المشروع ومميزاته

قالب متجر إلكتروني عام تنسخه وتجهّزه لكل عميل، ثم تنشره على Hostinger أو أي استضافة تدعم Laravel.

- واجهة عربية **RTL** متجاوبة (هاتف/كمبيوتر) بألوان يحددها العميل.
- **تثبيت أول آمن** عبر `/install`: بيانات المتجر، الشعار، 3 ألوان مع معاينة فورية، وحساب المدير الأول. يعمل **مرة واحدة** ثم يُغلق (404).
- لوحة إدارة **Filament**: المنتجات والصور والمخزون، التصنيفات، الطلبات وحالاتها، العملاء، الصفحات الثابتة، المستخدمون والأدوار، إعدادات المتجر، وملخص مبيعات.
- سلة وطلب **دون بوابة دفع**: الدفع عند الاستلام أو دفع يدوي (تعليماته من الإعدادات). **لا يوجد دفع إلكتروني حقيقي** في هذا القالب.
- بنية جاهزة لإضافة بوابة دفع لاحقًا دون إعادة كتابة الطلبات (`App\Payments\PaymentMethod`).
- أمان: الأسعار والكميات تُحسب من الخادم، منع تكرار الطلب، حد معدل الطلبات، مصيدة روبوتات، رفع صور مقيّد النوع والحجم، عدم رؤية طلبات الآخرين.
- لا أسماء عملاء ولا بيانات حقيقية في الكود؛ بيانات تجريبية اختيارية وقابلة للحذف بأمر واحد.

## 2. المتطلبات والإصدارات

| المكوّن | الإصدار المستهدف | ملاحظة |
|---|---|---|
| PHP | **8.3 أو أحدث** | Laravel 13 يتطلب 8.3 كحد أدنى |
| Laravel | `^13.0` | |
| Filament | `^5.0` | يتطلب PHP 8.2+ وLaravel 11.28+؛ **توافقه مع Laravel 13 لم يُتحقق منه بعد** (يظهر عند أول `composer install`) |
| Node.js | 20 أو أحدث | لبناء الواجهة فقط (Vite 7 + Tailwind 4) |
| قاعدة البيانات | MySQL 8 / MariaDB 10.6+ | SQLite للاختبارات |

إضافات PHP: `ctype curl dom fileinfo filter hash intl mbstring openssl pdo_mysql session tokenizer xml zip gd`.

> **ما الذي تحقّقتُ منه فعلًا؟** البيئة التي كُتب فيها القالب فيها PHP 8.3.6 وNode 22 فقط. إصدارات Laravel 13 / Filament 5 / PHPUnit 12 / Vite 7 مأخوذة من الوثائق الرسمية والمصادر العامة، ولم تُثبَّت فعليًا. إن فشل `composer install` بسبب تعارض إصدارات، عدّل القيود في `composer.json` وفق رسالة Composer.
> إن كانت خطة الاستضافة محصورة بـ PHP 8.2 فاستخدم Laravel 12 + Filament 4 (غيّر القيود في `composer.json` وراجع الفروق).

## 3. تشغيل المشروع محليًا

```bash
git clone <رابط-المستودع> my-store && cd my-store
git checkout feature/ecommerce-starter   # أو الفرع الذي تعمل عليه

composer install
cp .env.example .env
php artisan key:generate

# عدّل .env: قاعدة البيانات (انظر 4 و5)
php artisan migrate
php artisan storage:link

npm install
npm run build        # أو: npm run dev أثناء التطوير

php artisan serve    # ثم افتح http://127.0.0.1:8000 → سيحوّلك إلى /install
```

اختياري — بيانات تجريبية:

```bash
php artisan db:seed --class=DemoDataSeeder   # تجريبية فقط (موسومة is_demo)
php artisan store:demo-clean                  # تحذفها قبل التسليم
```

تشغيل الاختبارات:

```bash
php artisan test
```

## 4. إنشاء قاعدة البيانات وربطها

1. أنشئ قاعدة بيانات ومستخدمًا لها من لوحة الاستضافة (Hostinger: **Databases ← Management**) أو من MySQL محليًا.
2. امنح المستخدم كل صلاحيات تلك القاعدة فقط.
3. ضع البيانات في ملف **`.env` على الخادم** (القسم 5). **لا تضعها في Git ولا في أي نموذج داخل الموقع.**

## 5. إعداد ملف `.env` خطوة بخطوة

```bash
cp .env.example .env
php artisan key:generate
```

| المتغيّر | القيمة |
|---|---|
| `APP_NAME` | اسم مبدئي (الاسم الفعلي يُحفظ في إعدادات المتجر) |
| `APP_ENV` | `local` محليًا، `production` على الخادم |
| `APP_DEBUG` | `false` على الخادم دائمًا |
| `APP_URL` | رابط الموقع الكامل مع `https://` |
| `APP_TIMEZONE` | المنطقة الزمنية (اضبطها مرة واحدة) |
| `DB_HOST` `DB_PORT` `DB_DATABASE` `DB_USERNAME` `DB_PASSWORD` | بيانات قاعدة البيانات (على Hostinger غالبًا `DB_HOST=localhost`) |
| `INSTALL_TOKEN` | رمز عشوائي للتثبيت الأول — **إلزامي في production**: `php -r "echo bin2hex(random_bytes(16));"` |
| `MAIL_*` | بيانات SMTP لمزوّد البريد |

الجلسات والكاش على الملفات افتراضيًا (لا جداول إضافية، مناسب للاستضافة المشتركة).

## 6. أوامر Composer وNode والواجهة

```bash
composer install --no-dev --optimize-autoloader   # على الخادم (إنتاج)
composer install                                  # محليًا (مع أدوات التطوير)
npm install
npm run build        # ينتج public/build (غير مرفوع إلى Git)
```

> `public/build` غير مرفوع إلى Git. إن لم يتوفر Node على استضافتك فابنِ الواجهة محليًا أو عبر GitHub Actions وارفع المجلد (انظر `DEPLOYMENT.md`).

## 7. migrations وأول مدير

```bash
php artisan migrate --force
```

ثم **إحدى طريقتين** لإنشاء المدير الأول:

- **الموصى بها:** افتح `https://موقعك/install` وأكمل النموذج (يتطلب `INSTALL_TOKEN` في production). ينشئ المتجر والمدير والصفحات الافتراضية ثم يُغلق.
- **عبر SSH:** `php artisan store:create-admin` (كلمة المرور تُكتب بشكل مخفي).

## 8. النشر على Hostinger

الخطوات المفصلة في **[`DEPLOYMENT.md`](DEPLOYMENT.md)**. باختصار: SSH → `git clone` → `composer install --no-dev` → `.env` → `migrate` → جذر الموقع على `public/` → SSL → `/install`.

## 9. الدومين وSSL

انظر القسم المخصص في `DEPLOYMENT.md` (توجيه DNS، تفعيل SSL، تحويل HTTPS، ضبط `APP_URL`).

## 10. جذر الموقع ومجلد `public`

**يجب أن يشير جذر الموقع إلى `public/` فقط.** لا تجعل جذر الموقع هو جذر المشروع، وإلا قد يُكشف `.env`.
يوجد `.htaccess` في الجذر كشبكة أمان تمنع الملفات الحساسة، لكنه ليس بديلًا عن ضبط الجذر. الطرق البديلة عند عدم القدرة على تغيير الجذر في `DEPLOYMENT.md`.

## 11. بدائل الاستضافة

المتطلبات تختلف حسب الخطة والمزوّد: Hostinger (مشتركة/Cloud/VPS)، cPanel، VPS عام. قبل الشراء تأكد من: PHP 8.3+، SSH، Composer، إمكانية ضبط الجذر، وسماحية `symlink`. التفاصيل في `DEPLOYMENT.md`.

## 12. النسخ الاحتياطي والاستعادة

**ما يجب نسخه:** (1) قاعدة البيانات، (2) `storage/app/public` (صور المنتجات والشعار)، (3) ملف `.env`.

```bash
# قاعدة البيانات (ضع كلمة المرور في ملف ~/.my.cnf بدل كتابتها في الأمر)
mysqldump --single-transaction -u USER DBNAME | gzip > backup-$(date +%F).sql.gz

# الصور
tar czf storage-$(date +%F).tar.gz storage/app/public
```

- احتفظ بالنسخ **خارج** مجلد الموقع وخارج Git، وبنسخة خارج الخادم (تخزين سحابي).
- التكرار المقترح: يوميًا للقاعدة، أسبوعيًا للصور، **وقبل كل تحديث**.
- **اختبر الاستعادة** على نسخة تجريبية مرة على الأقل.
- الاستعادة: `gunzip < backup.sql.gz | mysql -u USER DBNAME` ثم فك ضغط الصور في `storage/app/public`.
- لوحة Hostinger توفر نسخًا احتياطية تلقائية في بعض الخطط — لا تعتمد عليها وحدها.

## 13. تحديث نسخة العميل بأمان

```bash
# 1) نسخة احتياطية كاملة (القاعدة + الصور) أولًا
php artisan down
git pull origin <الفرع>
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear && php artisan optimize
php artisan up
```

افحص المتجر ولوحة الإدارة بعد التحديث. عند فشل شيء: `php artisan up`، استعد النسخة الاحتياطية، وارجع إلى الإصدار السابق (`git checkout <commit>`).

## 14. حل الأخطاء الشائعة

| العَرَض | الحل |
|---|---|
| صفحة بيضاء / 500 | `storage/logs/laravel.log`؛ تأكد من صلاحيات الكتابة على `storage` و`bootstrap/cache` (775) |
| `Vite manifest not found` | لم تُبنَ الواجهة: `npm run build` ثم ارفع `public/build` |
| `No application encryption key` | `php artisan key:generate` ثم `php artisan config:clear` |
| صور لا تظهر | `php artisan storage:link`؛ وإن منعت الاستضافة الروابط الرمزية فراجع `DEPLOYMENT.md` |
| `/install` يظهر "غير جاهز" | قاعدة البيانات غير متصلة أو لم تُنفَّذ migrations، أو `INSTALL_TOKEN` غير مضبوط |
| `/install` يعطي 404 | التثبيت اكتمل (أو يوجد `storage/app/installed.lock`). على نسخة جديدة فارغة احذف الملف |
| تغيّرت `.env` ولم يتأثر شيء | `php artisan config:clear` |
| `composer install` يشكو من إصدار PHP | سطر أوامر SSH قد يستخدم PHP أقدم من إعداد hPanel؛ استخدم مسار PHP الأحدث (مثل `/opt/alt/php83/usr/bin/php`) |
| لوحة Filament بلا تنسيق | `php artisan filament:upgrade` ثم `php artisan optimize:clear` |
| 403 على `/admin` | حساب غير مفعّل أو دور لا يملك الصلاحية |

## 15. قائمة مراجعة قبل تسليم المتجر

انظر الجزء الأخير من [`CLIENT_HANDOFF.md`](CLIENT_HANDOFF.md).

---

## هيكل التوثيق

- [`DEPLOYMENT.md`](DEPLOYMENT.md) — النشر بالتفصيل
- [`CLIENT_HANDOFF.md`](CLIENT_HANDOFF.md) — نموذج بيانات العميل + قائمة التسليم
- [`docs/DATABASE.md`](docs/DATABASE.md) — الجداول والعلاقات
- [`docs/CLIENT_INSTANCES.md`](docs/CLIENT_INSTANCES.md) — نسخة مستقلة لكل عميل
- [`docs/TESTING_STATUS.md`](docs/TESTING_STATUS.md) — ما اختُبر وما لم يُختبر

## الأدوار

| الدور | الصلاحيات |
|---|---|
| مدير النظام | كل شيء بما فيه المستخدمون وحذف الطلبات والعملاء |
| مدير المتجر | المنتجات والتصنيفات والصفحات والإعدادات وتعديل العملاء |
| موظف | عرض/تعديل المنتجات (المخزون) والطلبات، عرض العملاء |

## إضافة بوابة دفع مستقبلًا

1. أنشئ صنفًا يطبّق `App\Payments\PaymentMethod` (له مفتاح فريد، ويُرجع تحويلًا في `afterOrderPlaced`).
2. سجّله في `config/store.php` ضمن `payment_methods`.
3. أضف webhook من المزوّد يحدّث `orders.payment_status`.
4. **اختبره مع بيئة اختبار المزوّد قبل التفعيل.** لا تصف المتجر بأنه يدعم دفعًا إلكترونيًا قبل ذلك.
