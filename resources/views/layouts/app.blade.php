<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@hasSection('title')@yield('title') | @endif{{ $store->name }}</title>
    <meta name="description" content="@yield('description', $store->description)">
    <style>
        :root {
            --store-primary: {{ $store->primary_color }};
            --store-secondary: {{ $store->secondary_color }};
            --store-accent: {{ $store->accent_color }};
            --store-on-primary: {{ $store->onPrimary() }};
            --store-on-accent: {{ $store->onAccent() }};
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-800 antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-3">تخطَّ إلى المحتوى</a>
    <header class="sticky top-0 z-30 border-b border-stone-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center gap-3 px-4 py-3">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-bold text-primary">
                @if ($store->logo_url)
                    <img src="{{ $store->logo_url }}" alt="{{ $store->name }}" class="h-9 w-auto">
                @endif
                <span>{{ $store->name }}</span>
            </a>

            <nav class="ms-6 hidden items-center gap-5 text-sm md:flex" aria-label="التنقل الرئيسي">
                <a href="{{ route('home') }}" class="hover:text-primary">الرئيسية</a>
                <a href="{{ route('products.index') }}" class="hover:text-primary">المنتجات</a>
                <a href="{{ route('pages.show', 'about') }}" class="hover:text-primary">من نحن</a>
                <a href="{{ route('pages.show', 'contact') }}" class="hover:text-primary">اتصل بنا</a>
            </nav>

            <form action="{{ route('products.index') }}" method="get" class="ms-auto hidden flex-1 md:block md:max-w-xs">
                <label for="header-search" class="sr-only">بحث</label>
                <input id="header-search" type="search" name="q" value="{{ request('q') }}" placeholder="ابحث عن منتج…" class="input">
            </form>

            <a href="{{ auth()->check() ? route('account.orders') : route('login') }}" class="btn btn-outline ms-auto hidden md:inline-flex">{{ auth()->check() ? 'حسابي' : 'دخول' }}</a>

            <a href="{{ route('cart.index') }}" class="btn btn-outline relative ms-auto md:ms-0" aria-label="سلة المشتريات{{ $cartCount > 0 ? ' ('.$cartCount.' منتج)' : '' }}">
                <span>السلة</span>
                @if ($cartCount > 0)
                    <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-accent px-1.5 text-xs font-bold text-on-accent">{{ $cartCount }}</span>
                @endif
            </a>

            <button type="button" class="btn btn-outline md:hidden" data-menu-toggle aria-expanded="false" aria-controls="mobile-menu" aria-label="القائمة">☰</button>
        </div>

        <div id="mobile-menu" data-menu class="hidden border-t border-stone-200 bg-white px-4 py-3 md:hidden">
            <form action="{{ route('products.index') }}" method="get" class="mb-3">
                <label for="mobile-search" class="sr-only">بحث</label>
                <input id="mobile-search" type="search" name="q" value="{{ request('q') }}" placeholder="ابحث عن منتج…" class="input">
            </form>
            <nav class="flex flex-col gap-2 text-sm" aria-label="قائمة الهاتف">
                <a href="{{ route('home') }}" class="py-1">الرئيسية</a>
                <a href="{{ route('products.index') }}" class="py-1">المنتجات</a>
                <a href="{{ route('pages.show', 'about') }}" class="py-1">من نحن</a>
                <a href="{{ route('pages.show', 'contact') }}" class="py-1">اتصل بنا</a>
                <a href="{{ route('pages.show', 'returns') }}" class="py-1">الاستبدال والاسترجاع</a>
                <a href="{{ auth()->check() ? route('account.orders') : route('login') }}" class="py-1 font-semibold">{{ auth()->check() ? 'طلباتي' : 'تسجيل الدخول / حساب جديد' }}</a>
            </nav>
        </div>
    </header>

    <main id="main" class="mx-auto max-w-6xl px-4 py-5 md:py-6 @yield('main_class')">
        @if (session('status'))
            <div role="status" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="mt-10 border-t border-stone-200 bg-white">
        <div class="mx-auto grid max-w-6xl gap-6 px-4 py-8 text-sm md:grid-cols-3">
            <div>
                <p class="mb-2 font-bold text-primary">{{ $store->name }}</p>
                @if ($store->description)
                    <p class="leading-7 text-stone-600">{{ $store->description }}</p>
                @endif
            </div>
            <div>
                <p class="mb-2 font-bold">تواصل معنا</p>
                <ul class="space-y-1 text-stone-600">
                    @if ($store->whatsappUrl())<li><a class="font-semibold text-primary" href="{{ $store->whatsappUrl() }}" rel="noopener" target="_blank">واتساب</a></li>@endif
                    @if ($store->phone)<li dir="ltr" class="text-start">{{ $store->phone }}</li>@endif
                    @if ($store->email)<li>{{ $store->email }}</li>@endif
                    @if ($store->fullAddress())<li>{{ $store->fullAddress() }}</li>@endif
                </ul>
            </div>
            <div>
                <p class="mb-2 font-bold">روابط</p>
                <ul class="space-y-1 text-stone-600">
                    <li><a class="hover:text-primary" href="{{ route('pages.show', 'about') }}">من نحن</a></li>
                    <li><a class="hover:text-primary" href="{{ route('pages.show', 'contact') }}">اتصل بنا</a></li>
                    <li><a class="hover:text-primary" href="{{ route('pages.show', 'shipping') }}">الشحن والتوصيل</a></li>
                    <li><a class="hover:text-primary" href="{{ route('pages.show', 'privacy') }}">سياسة الخصوصية</a></li>
                    <li><a class="hover:text-primary" href="{{ route('pages.show', 'returns') }}">سياسة الاستبدال والاسترجاع</a></li>
                </ul>
            </div>
        </div>
        <p class="border-t border-stone-100 py-4 text-center text-xs text-stone-500">© {{ date('Y') }} {{ $store->name }}</p>
    </footer>

    @if ($store->whatsappUrl() && ! trim($__env->yieldContent('hide_wa')))
        <a href="{{ $store->whatsappUrl('مرحبًا، أريد الاستفسار عن منتج') }}" target="_blank" rel="noopener" aria-label="تواصل عبر واتساب"
           class="fixed bottom-4 start-4 z-20 hidden h-12 items-center md:inline-flex gap-2 rounded-full bg-[#1b7f4b] px-4 text-sm font-semibold text-white shadow-lg">واتساب</a>
    @endif
</body>
</html>
