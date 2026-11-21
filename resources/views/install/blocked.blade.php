<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    @include('layouts.head', ['primary' => config('store.default_colors.primary'), 'secondary' => config('store.default_colors.secondary'), 'accent' => config('store.default_colors.accent'), 'onPrimary' => '#ffffff', 'onAccent' => '#1c1917', 'themeColor' => config('store.default_colors.primary')])
    <meta name="robots" content="noindex, nofollow">
    <title>المتجر غير جاهز بعد</title>
</head>
<body class="min-h-screen py-10">
<main class="mx-auto max-w-xl px-4">
    <div class="card card-pad space-y-4">
        <span class="flex size-12 items-center justify-center rounded-full bg-primary-soft text-primary"><x-ui-icon name="alert" class="size-6" /></span>
        <h1 class="t-title">المتجر غير جاهز بعد</h1>

        @if ($reason === 'database')
            <p class="leading-7">لا يمكن بدء التثبيت لأن قاعدة البيانات غير متصلة أو لم تُجهَّز جداولها.</p>
            <ol class="list-decimal space-y-2 ps-6 text-[0.95rem] leading-7">
                <li>أنشئ قاعدة بيانات من لوحة الاستضافة.</li>
                <li>ضع بياناتها في ملف <code dir="ltr">.env</code> على الخادم (<code dir="ltr">DB_*</code>) — وليس في هذه الصفحة.</li>
                <li>نفّذ الأمر <code dir="ltr">php artisan migrate --force</code> عبر SSH.</li>
                <li>حدّث هذه الصفحة.</li>
            </ol>
        @else
            <p class="leading-7">لأسباب أمنية لا يعمل التثبيت في بيئة الإنتاج دون رمز تثبيت.</p>
            <ol class="list-decimal space-y-2 ps-6 text-[0.95rem] leading-7">
                <li>أنشئ رمزًا عشوائيًا: <code dir="ltr">php -r "echo bin2hex(random_bytes(16));"</code></li>
                <li>ضعه في ملف <code dir="ltr">.env</code> على الخادم: <code dir="ltr">INSTALL_TOKEN=...</code></li>
                <li>إن كنت تستخدم <code dir="ltr">config:cache</code> فنفّذ <code dir="ltr">php artisan config:clear</code> ثم حدّث الصفحة.</li>
            </ol>
        @endif
    </div>
</main>
</body>
</html>
