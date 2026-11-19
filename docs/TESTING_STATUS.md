# حالة الاختبار — ما اختُبر وما لم يُختبر

آخر تحديث: عند إنشاء الفرع `feature/ecommerce-starter`.

## السياق

كُتب القالب في بيئة محظور فيها `repo.packagist.org` و`api.github.com` و`codeload.github.com`، لذلك **لم يُنزَّل أي حزمة** (لا Composer ولا مرايا ولا تنزيل يدوي). هذا يعني أن Laravel وFilament لم يكونا موجودين وقت الكتابة.

## ما تم فحصه فعليًا (لا يحتاج تنزيلًا)

| الفحص | النتيجة |
|---|---|
| `php -l` على كل ملفات PHP | انظر ملخص الالتزام (commit) |
| صحة `composer.json` و`package.json` كـ JSON | ✔︎ |
| `node --check` على `resources/js/app.js` و`vite.config.js` | ✔︎ |
| فحص أسرار (كلمات مرور/مفاتيح/.env) داخل الملفات المتتبَّعة | ✔︎ |
| اختبار منطق الألوان والـ slug بسكربت PHP مستقل | ✔︎ (انظر `tests/Unit/SupportTest.php` — نفس الحالات) |

## ما **لم** يُختبر

| البند | السبب | ما يجب فعله |
|---|---|---|
| `composer install` وحل الإصدارات | Packagist محظور | شغّله؛ قد يلزم تعديل القيود (خاصة Filament 5 مع Laravel 13) |
| `php artisan migrate`، الإقلاع، الـ service providers | لا إطار | شغّل `php artisan migrate` و`php artisan about` |
| اختبارات PHPUnit (`tests/`) | لا إطار | `php artisan test`؛ **كُتبت دون تشغيل وقد تحتاج تصحيحًا** |
| واجهات Blade (تجميعها وعرضها) | لا إطار | افتح كل صفحة يدويًا |
| لوحة Filament (الموارد، الصفحة، الودجات) | لا Filament | واجهات Filament 4/5 اعتمدتُها من الوثائق دون تجربة؛ توقّع تعديلات بسيطة في الأصناف/الأسماء |
| `npm install` / `npm run build` / Tailwind 4 | لم يُنفَّذ بقرار (لا تنزيل) | `npm install && npm run build`؛ تحقق من إصدارات `laravel-vite-plugin` و`vite` |
| RTL والألوان على الهاتف/الكمبيوتر | لا متصفح | افحص بصريًا على الهاتف والكمبيوتر |
| رفع الصور وعرضها (`storage:link`) | لا إطار | جرّب رفع شعار وصورة منتج |
| النشر على Hostinger | لا وصول | جرّب على نسخة تجريبية؛ الخطوات منقولة من وثائق ومصادر عامة |
| إعادة المخزون عند الإلغاء، منع التكرار، سباق التثبيت | لا إطار | تغطيها اختبارات مكتوبة لكن غير مشغّلة |

## نقاط مخاطرة معروفة (راجعها أولًا إن ظهرت أخطاء)

1. **Filament 5 + Laravel 13:** التوافق لم يُتحقق منه. إن فشل Composer جرّب `filament/filament:^4.5`.
2. **أسماء أصناف Filament 4/5:** `Filament\Schemas\Components\Section`، `Filament\Schemas\Components\Utilities\Set`، `recordActions()` / `toolbarActions()`، `Filament\Actions\*`، `Filament\Widgets\StatsOverviewWidget\Stat`، وصفحة الإعدادات (`HasSchemas` + `$this->form`).
3. **Middleware الـ CSRF في Laravel 13:** استُعمل `Illuminate\Foundation\Http\Middleware\VerifyCsrfToken` في `AdminPanelProvider`؛ إن أعيدت تسميته فاستخدم الاسم الجديد.
4. **`$this->form` في صفحة الإعدادات** يعتمد على دالة `form(Schema $schema)`؛ تحقق من وثائق Filament للإصدار المثبّت.
5. **`phpunit/phpunit ^12.0` و`laravel/tinker ^3.0`** افتراضات من مصادر عامة.
6. اختبارات Filament تعتمد على أن slug صفحة الإعدادات `store-settings` (مضبوط يدويًا في الصفحة).
7. `ApplyStoreSettings` يغيّر المنطقة الزمنية لكل طلب؛ لم يُختبر تأثيره على عرض التواريخ في Filament.
