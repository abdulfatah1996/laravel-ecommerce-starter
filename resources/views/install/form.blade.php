<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>تجهيز المتجر</title>
    @php
        // نقبل القيم القديمة فقط إن كانت لونًا صحيحًا (حماية من حقن CSS داخل <style>)
        $pick = fn (string $key) => \App\Support\Colors::isHex(old($key.'_color')) ? old($key.'_color') : $defaults[$key];
    @endphp
    <style>
        :root {
            --store-primary: {{ $pick('primary') }};
            --store-secondary: {{ $pick('secondary') }};
            --store-accent: {{ $pick('accent') }};
            --store-on-primary: #ffffff;
            --store-on-accent: #1c1917;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 py-8 text-stone-800 antialiased">
<main class="mx-auto max-w-3xl px-4">
    <h1 class="mb-1 text-2xl font-extrabold">تجهيز المتجر</h1>
    <p class="mb-6 text-sm text-stone-600">
        هذه الصفحة تعمل <strong>مرة واحدة فقط</strong> وتُغلق تلقائيًا بعد الحفظ. بيانات قاعدة البيانات لا تُدخل هنا
        بل في ملف <code dir="ltr">.env</code> على الخادم.
    </p>

    @if ($errors->any())
        <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc ps-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('install.store') }}" method="post" enctype="multipart/form-data" class="space-y-6" data-once>
        @csrf

        @if ($tokenRequired)
            <section class="card space-y-3 p-4">
                <h2 class="font-bold">رمز التثبيت</h2>
                <p class="text-xs text-stone-500">القيمة الموجودة في <code dir="ltr">INSTALL_TOKEN</code> داخل ملف <code dir="ltr">.env</code>.</p>
                <input name="install_token" type="password" required autocomplete="off" dir="ltr" class="input text-start" aria-label="رمز التثبيت">
            </section>
        @endif

        <section class="card space-y-4 p-4">
            <h2 class="font-bold">بيانات المتجر</h2>
            <div>
                <label for="store_name" class="label">اسم المتجر *</label>
                <input id="store_name" name="store_name" value="{{ old('store_name') }}" required maxlength="100" class="input">
            </div>
            <div>
                <label for="description" class="label">وصف المتجر</label>
                <textarea id="description" name="description" rows="3" maxlength="500" class="input">{{ old('description') }}</textarea>
            </div>
            <div>
                <label for="logo" class="label">الشعار (PNG / JPG / WebP، حتى 2MB)</label>
                <input id="logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="input">
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="email" class="label">بريد المتجر</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" dir="ltr" class="input text-start">
                </div>
                <div>
                    <label for="phone" class="label">هاتف المتجر</label>
                    <input id="phone" name="phone" value="{{ old('phone') }}" dir="ltr" class="input text-start">
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="country" class="label">الدولة</label>
                    <input id="country" name="country" value="{{ old('country') }}" class="input">
                </div>
                <div>
                    <label for="city" class="label">المدينة</label>
                    <input id="city" name="city" value="{{ old('city') }}" class="input">
                </div>
                <div>
                    <label for="address" class="label">العنوان</label>
                    <input id="address" name="address" value="{{ old('address') }}" class="input">
                </div>
            </div>
        </section>

        <section class="card space-y-4 p-4">
            <h2 class="font-bold">العملة والمنطقة الزمنية واللغة</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="currency_code" class="label">العملة *</label>
                    <select id="currency_code" name="currency_code" required class="input">
                        @foreach ($currencies as $code => $currency)
                            <option value="{{ $code }}" @selected(old('currency_code', 'USD') === $code)>{{ $currency['name'] }} ({{ $currency['symbol'] }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="timezone" class="label">المنطقة الزمنية *</label>
                    <select id="timezone" name="timezone" required class="input" dir="ltr">
                        @foreach ($timezones as $tz)
                            <option value="{{ $tz }}" @selected(old('timezone', config('app.timezone')) === $tz)>{{ $tz }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="locale" class="label">اللغة *</label>
                    <select id="locale" name="locale" required class="input">
                        <option value="ar" selected>العربية (RTL)</option>
                    </select>
                </div>
            </div>
            <p class="text-xs text-stone-500">يُنصح باختيار المنطقة الزمنية الصحيحة الآن وعدم تغييرها لاحقًا بعد بدء الطلبات.</p>
        </section>

        <section class="card space-y-4 p-4">
            <h2 class="font-bold">ألوان المتجر</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach (['primary_color' => ['اللون الأساسي', 'primary'], 'secondary_color' => ['اللون المساعد', 'secondary'], 'accent_color' => ['لون الإبراز', 'accent']] as $field => [$label, $key])
                    <div>
                        <label for="{{ $field }}" class="label">{{ $label }}</label>
                        <input id="{{ $field }}" type="color" name="{{ $field }}" value="{{ old($field, $defaults[$key]) }}" data-color-input="{{ $key }}" class="h-11 w-full cursor-pointer rounded-lg border border-stone-300 bg-white p-1">
                    </div>
                @endforeach
            </div>

            {{-- معاينة فورية للألوان قبل الحفظ --}}
            <div class="rounded-xl border border-stone-200 p-4" aria-label="معاينة الألوان">
                <p class="mb-3 text-xs text-stone-500">معاينة فورية:</p>
                <div class="rounded-lg px-4 py-3" id="preview-bar" style="background: var(--store-primary); color: var(--store-on-primary)">
                    <span class="font-bold" id="preview-name">اسم متجرك</span>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <button type="button" class="btn btn-primary">زر أساسي</button>
                    <button type="button" class="btn btn-accent">زر إبراز</button>
                    <span class="rounded-full px-3 py-1 text-xs" style="background: var(--store-secondary)">لون مساعد</span>
                </div>
            </div>
        </section>

        <section class="card space-y-4 p-4">
            <h2 class="font-bold">حساب مدير المتجر الأول</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="owner_name" class="label">الاسم *</label>
                    <input id="owner_name" name="owner_name" value="{{ old('owner_name') }}" required maxlength="120" autocomplete="name" class="input">
                </div>
                <div>
                    <label for="owner_email" class="label">البريد الإلكتروني *</label>
                    <input id="owner_email" type="email" name="owner_email" value="{{ old('owner_email') }}" required dir="ltr" autocomplete="email" class="input text-start">
                </div>
                <div>
                    <label for="owner_password" class="label">كلمة المرور * (10 أحرف على الأقل، حروف وأرقام)</label>
                    <input id="owner_password" type="password" name="owner_password" required minlength="10" autocomplete="new-password" dir="ltr" class="input text-start">
                </div>
                <div>
                    <label for="owner_password_confirmation" class="label">تأكيد كلمة المرور *</label>
                    <input id="owner_password_confirmation" type="password" name="owner_password_confirmation" required minlength="10" autocomplete="new-password" dir="ltr" class="input text-start">
                </div>
            </div>
        </section>

        <button type="submit" class="btn btn-primary w-full">حفظ وتجهيز المتجر</button>
    </form>
</main>

<script>
    (function () {
        var root = document.documentElement;
        var nameInput = document.getElementById('store_name');
        var previewName = document.getElementById('preview-name');

        // يختار لون نص مقروء فوق لون الخلفية (نفس منطق الخادم)
        function onColor(hex) {
            var c = [1, 3, 5].map(function (i) {
                var v = parseInt(hex.substr(i, 2), 16) / 255;
                return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
            });
            return (0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2]) > 0.4 ? '#1c1917' : '#ffffff';
        }

        document.querySelectorAll('[data-color-input]').forEach(function (input) {
            function apply() {
                var key = input.dataset.colorInput;
                root.style.setProperty('--store-' + key, input.value);
                if (key === 'primary') { root.style.setProperty('--store-on-primary', onColor(input.value)); }
                if (key === 'accent') { root.style.setProperty('--store-on-accent', onColor(input.value)); }
            }
            input.addEventListener('input', apply);
            apply();
        });

        if (nameInput && previewName) {
            nameInput.addEventListener('input', function () {
                previewName.textContent = nameInput.value || 'اسم متجرك';
            });
        }
    })();
</script>
</body>
</html>
