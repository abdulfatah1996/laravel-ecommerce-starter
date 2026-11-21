<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    @php
        // نقبل القيم القديمة فقط إن كانت لونًا صحيحًا (حماية من حقن CSS داخل <style>)
        $pick = fn (string $key) => \App\Support\Colors::isHex(old($key.'_color')) ? old($key.'_color') : $defaults[$key];
    @endphp
    @include('layouts.head', ['primary' => $pick('primary'), 'secondary' => $pick('secondary'), 'accent' => $pick('accent'), 'onPrimary' => '#ffffff', 'onAccent' => '#1c1917', 'themeColor' => $pick('primary')])
    <meta name="robots" content="noindex, nofollow">
    <title>تجهيز المتجر</title>
</head>
<body class="min-h-screen pb-28 pt-8 md:pb-12">
<main class="mx-auto max-w-3xl px-4">
    <header class="mb-6">
        <p class="mb-1 text-sm font-bold text-primary">الخطوة الأخيرة قبل الافتتاح</p>
        <h1 class="t-title">تجهيز المتجر</h1>
        <p class="t-muted mt-2 text-[0.95rem] leading-7">
            هذه الصفحة تعمل <strong>مرة واحدة فقط</strong> وتُغلق تلقائيًا بعد الحفظ. بيانات قاعدة البيانات لا تُدخل هنا
            بل في ملف <code dir="ltr">.env</code> على الخادم.
        </p>
    </header>

    @if ($errors->any())
        <x-alert type="error" class="mb-5">
            <p class="font-bold">يرجى مراجعة الحقول المعلَّمة بالأحمر.</p>
            @foreach ($errors->get('install') as $message)<p>{{ $message }}</p>@endforeach
        </x-alert>
    @endif

    <form action="{{ route('install.store') }}" method="post" enctype="multipart/form-data" class="space-y-5" data-once>
        @csrf

        @if ($tokenRequired)
            <section class="card card-pad space-y-3">
                <h2 class="t-heading">رمز التثبيت</h2>
                <x-field name="install_token" label="القيمة الموجودة في INSTALL_TOKEN داخل ملف .env" required>
                    <input id="install_token" name="install_token" type="password" required autocomplete="off" dir="ltr" class="input text-start" @error('install_token') aria-invalid="true" @enderror>
                </x-field>
            </section>
        @endif

        <section class="card card-pad space-y-4">
            <h2 class="t-heading">بيانات المتجر</h2>
            <x-field name="store_name" label="اسم المتجر" required>
                <input id="store_name" name="store_name" value="{{ old('store_name') }}" required maxlength="100" class="input" @error('store_name') aria-invalid="true" @enderror>
            </x-field>
            <x-field name="description" label="وصف المتجر" hint="جملة قصيرة تظهر في أول شاشة من الرئيسية: ماذا تبيع؟">
                <textarea id="description" name="description" rows="3" maxlength="500" class="input" @error('description') aria-invalid="true" @enderror>{{ old('description') }}</textarea>
            </x-field>
            <x-field name="logo" label="الشعار (PNG / JPG / WebP، حتى 2MB)">
                <input id="logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="input !py-2.5 file:me-3 file:rounded-lg file:border-0 file:bg-primary-soft file:px-3 file:py-1.5 file:font-bold file:text-primary" @error('logo') aria-invalid="true" @enderror>
            </x-field>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field name="email" label="بريد المتجر">
                    <input id="email" type="email" name="email" value="{{ old('email') }}" dir="ltr" class="input text-start" @error('email') aria-invalid="true" @enderror>
                </x-field>
                <x-field name="phone" label="هاتف المتجر">
                    <input id="phone" name="phone" value="{{ old('phone') }}" dir="ltr" class="input text-start" @error('phone') aria-invalid="true" @enderror>
                </x-field>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <x-field name="country" label="الدولة"><input id="country" name="country" value="{{ old('country') }}" class="input"></x-field>
                <x-field name="city" label="المدينة"><input id="city" name="city" value="{{ old('city') }}" class="input"></x-field>
                <x-field name="address" label="العنوان"><input id="address" name="address" value="{{ old('address') }}" class="input"></x-field>
            </div>
        </section>

        <section class="card card-pad space-y-4">
            <h2 class="t-heading">العملة والمنطقة الزمنية واللغة</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <x-field name="currency_code" label="العملة" required>
                    <select id="currency_code" name="currency_code" required class="input">
                        @foreach ($currencies as $code => $currency)
                            <option value="{{ $code }}" @selected(old('currency_code', 'USD') === $code)>{{ $currency['name'] }} ({{ $currency['symbol'] }})</option>
                        @endforeach
                    </select>
                </x-field>
                <x-field name="timezone" label="المنطقة الزمنية" required>
                    <select id="timezone" name="timezone" required class="input" dir="ltr">
                        @foreach ($timezones as $tz)
                            <option value="{{ $tz }}" @selected(old('timezone', config('app.timezone')) === $tz)>{{ $tz }}</option>
                        @endforeach
                    </select>
                </x-field>
                <x-field name="locale" label="اللغة" required>
                    <select id="locale" name="locale" required class="input"><option value="ar" selected>العربية (RTL)</option></select>
                </x-field>
            </div>
            <p class="hint">يُنصح باختيار المنطقة الزمنية الصحيحة الآن وعدم تغييرها لاحقًا بعد بدء الطلبات.</p>
        </section>

        <section class="card card-pad space-y-4">
            <h2 class="t-heading">ألوان المتجر</h2>
            <p class="hint !mt-0">تُطبَّق على المتجر كله، ويمكنك تغييرها لاحقًا من لوحة الإدارة.</p>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach (['primary_color' => ['اللون الأساسي (الأزرار والروابط)', 'primary'], 'secondary_color' => ['اللون المساعد (الخلفيات الهادئة)', 'secondary'], 'accent_color' => ['لون الإبراز (الشارات)', 'accent']] as $field => [$label, $key])
                    <x-field :name="$field" :label="$label">
                        <input id="{{ $field }}" type="color" name="{{ $field }}" value="{{ old($field, $defaults[$key]) }}" data-color-input="{{ $key }}" class="h-12 w-full cursor-pointer rounded-xl border bg-white p-1" style="border-color: var(--line)">
                    </x-field>
                @endforeach
            </div>

            {{-- معاينة فورية للألوان قبل الحفظ --}}
            <div class="rounded-2xl border p-4" style="border-color: var(--line); background: var(--tint)" aria-label="معاينة الألوان">
                <p class="t-small t-muted mb-3">معاينة فورية:</p>
                <div class="rounded-xl px-4 py-3" id="preview-bar" style="background: var(--store-primary); color: var(--store-on-primary)">
                    <span class="font-bold" id="preview-name">اسم متجرك</span>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <button type="button" class="btn btn-primary btn-sm">زر أساسي</button>
                    <span class="badge badge-sale">شارة خصم</span>
                    <span class="badge" style="background: var(--tint); box-shadow: inset 0 0 0 1px var(--line)">لون مساعد</span>
                </div>
            </div>
        </section>

        <section class="card card-pad space-y-4">
            <h2 class="t-heading">حساب مدير المتجر الأول</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field name="owner_name" label="الاسم" required>
                    <input id="owner_name" name="owner_name" value="{{ old('owner_name') }}" required maxlength="120" autocomplete="name" class="input" @error('owner_name') aria-invalid="true" @enderror>
                </x-field>
                <x-field name="owner_email" label="البريد الإلكتروني" required>
                    <input id="owner_email" type="email" name="owner_email" value="{{ old('owner_email') }}" required dir="ltr" autocomplete="email" class="input text-start" @error('owner_email') aria-invalid="true" @enderror>
                </x-field>
                <x-field name="owner_password" label="كلمة المرور" hint="10 أحرف على الأقل، حروف وأرقام" required>
                    <input id="owner_password" type="password" name="owner_password" required minlength="10" autocomplete="new-password" dir="ltr" class="input text-start" @error('owner_password') aria-invalid="true" @enderror>
                </x-field>
                <x-field name="owner_password_confirmation" label="تأكيد كلمة المرور" required>
                    <input id="owner_password_confirmation" type="password" name="owner_password_confirmation" required minlength="10" autocomplete="new-password" dir="ltr" class="input text-start">
                </x-field>
            </div>
        </section>

        <div class="sticky-bar md:static md:border-0 md:bg-transparent md:p-0 md:backdrop-blur-none">
            <x-button variant="primary" block class="md:!min-h-14 md:text-lg">حفظ وتجهيز المتجر</x-button>
        </div>
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
