<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>المتجر غير جاهز بعد</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 py-10 text-stone-800 antialiased">
<main class="mx-auto max-w-xl px-4">
    <div class="card space-y-3 p-6">
        <h1 class="text-xl font-extrabold">المتجر غير جاهز بعد</h1>

        @if ($reason === 'database')
            <p class="leading-7">لا يمكن بدء التثبيت لأن قاعدة البيانات غير متصلة أو لم تُجهَّز جداولها.</p>
            <ol class="list-decimal space-y-1 ps-6 text-sm leading-7 text-stone-700">
                <li>أنشئ قاعدة بيانات من لوحة الاستضافة.</li>
                <li>ضع بياناتها في ملف <code dir="ltr">.env</code> على الخادم (<code dir="ltr">DB_*</code>) — وليس في هذه الصفحة.</li>
                <li>نفّذ الأمر <code dir="ltr">php artisan migrate --force</code> عبر SSH.</li>
                <li>حدّث هذه الصفحة.</li>
            </ol>
        @else
            <p class="leading-7">لأسباب أمنية لا يعمل التثبيت في بيئة الإنتاج دون رمز تثبيت.</p>
            <ol class="list-decimal space-y-1 ps-6 text-sm leading-7 text-stone-700">
                <li>أنشئ رمزًا عشوائيًا: <code dir="ltr">php -r "echo bin2hex(random_bytes(16));"</code></li>
                <li>ضعه في ملف <code dir="ltr">.env</code> على الخادم: <code dir="ltr">INSTALL_TOKEN=...</code></li>
                <li>إن كنت تستخدم <code dir="ltr">config:cache</code> فنفّذ <code dir="ltr">php artisan config:clear</code> ثم حدّث الصفحة.</li>
            </ol>
        @endif
    </div>
</main>
</body>
</html>
