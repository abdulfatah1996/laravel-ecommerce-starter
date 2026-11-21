<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    @include('layouts.head', [
        'primary' => $store->primary_color, 'secondary' => $store->secondary_color, 'accent' => $store->accent_color,
        'onPrimary' => $store->onPrimary(), 'onAccent' => $store->onAccent(), 'themeColor' => $store->primary_color,
    ])
    <title>@hasSection('title')@yield('title') | @endif{{ $store->name }}</title>
    <meta name="description" content="@yield('description', $store->description)">
</head>
@php
    $nav = [
        ['الرئيسية', route('home'), request()->routeIs('home')],
        ['المنتجات', route('products.index'), request()->routeIs('products.*')],
        ['من نحن', route('pages.show', 'about'), request()->is('about')],
        ['اتصل بنا', route('pages.show', 'contact'), request()->is('contact')],
    ];
    $inlineFieldKeys = ['name', 'phone', 'email', 'city', 'address', 'notes', 'payment_method'];
    $generalErrors = $errors->getBag('default')->messages();
    if (View::hasSection('inline_errors')) {
        $generalErrors = collect($generalErrors)->except($inlineFieldKeys)->all();
    }
    $generalErrors = collect($generalErrors)->flatten()->unique()->values();
@endphp
<body class="min-h-screen @yield('body_class')">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:start-3 focus:top-3 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:shadow-lg">تخطَّ إلى المحتوى</a>

    <header class="sticky top-0 z-40 border-b bg-white/95 backdrop-blur" style="border-color: var(--line)">
        <div class="mx-auto flex h-16 max-w-6xl items-center gap-2 px-4 md:gap-6 md:px-6">
            <a href="{{ route('home') }}" class="flex min-h-11 min-w-0 items-center gap-2.5 text-primary" aria-label="{{ $store->name }} — الرئيسية">
                @if ($store->logo_url)
                    <img src="{{ $store->logo_url }}" alt="" class="h-9 w-auto max-w-28 object-contain">
                @endif
                <span class="truncate text-xl font-bold">{{ $store->name }}</span>
            </a>

            <nav class="hidden items-center gap-1 md:flex" aria-label="التنقل الرئيسي">
                @foreach ($nav as [$label, $url, $active])
                    <a href="{{ $url }}" @if ($active) aria-current="page" @endif class="inline-flex min-h-11 items-center rounded-lg px-3 text-[0.95rem] font-medium hover:bg-primary-soft {{ $active ? 'text-primary font-bold' : '' }}">{{ $label }}</a>
                @endforeach
            </nav>

            <form action="{{ route('products.index') }}" method="get" role="search" class="relative ms-auto hidden w-full max-w-xs md:block">
                <label for="header-search" class="sr-only">ابحث عن منتج</label>
                <x-ui-icon name="search" class="pointer-events-none absolute start-3.5 top-1/2 size-5 -translate-y-1/2 text-stone-400" />
                <input id="header-search" type="search" name="q" value="{{ request('q') }}" placeholder="ابحث عن منتج…" class="input !min-h-11 !ps-11">
            </form>

            <div class="ms-auto flex items-center gap-1 md:ms-0">
                <a href="{{ route('cart.index') }}" class="relative inline-flex size-11 items-center justify-center rounded-xl hover:bg-primary-soft" aria-label="سلة المشتريات{{ $cartCount > 0 ? ' — '.$cartCount.' منتج' : '' }}">
                    <x-ui-icon name="cart" class="size-6" />
                    @if ($cartCount > 0)
                        <span class="absolute -end-0.5 -top-0.5 inline-flex min-w-5 items-center justify-center rounded-full bg-accent px-1 text-[0.7rem] font-bold leading-5 text-on-accent" data-cart-count>{{ $cartCount }}</span>
                    @endif
                </a>
                <button type="button" class="inline-flex size-11 items-center justify-center rounded-xl hover:bg-primary-soft md:hidden" data-menu-toggle aria-expanded="false" aria-controls="mobile-menu" aria-label="القائمة">
                    <x-ui-icon name="menu" class="size-6" data-icon-open />
                    <x-ui-icon name="close" class="hidden size-6" data-icon-close />
                </button>
            </div>
        </div>

        <div id="mobile-menu" data-menu class="hidden border-t bg-white px-4 pb-4 pt-2 md:hidden" style="border-color: var(--line)">
            <nav class="flex flex-col" aria-label="قائمة الهاتف">
                @foreach ($nav as [$label, $url, $active])
                    <a href="{{ $url }}" @if ($active) aria-current="page" @endif class="flex min-h-12 items-center justify-between border-b border-stone-100 text-base font-medium last:border-0 {{ $active ? 'text-primary font-bold' : '' }}">{{ $label }} <x-ui-icon name="chevron-left" class="size-4 text-stone-400" /></a>
                @endforeach
            </nav>
        </div>
    </header>

    {{-- بحث الهاتف: ظاهر دائمًا تحت الشريط (لا يلتصق عند التمرير) --}}
    <div class="mx-auto max-w-6xl px-4 pt-3 md:hidden">
        <form action="{{ route('products.index') }}" method="get" role="search" class="relative">
            <label for="mobile-search" class="sr-only">ابحث عن منتج</label>
            <x-ui-icon name="search" class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-stone-400" />
            <input id="mobile-search" type="search" name="q" value="{{ request('q') }}" placeholder="ابحث عن منتج…" enterkeyhint="search" class="input !ps-12">
        </form>
    </div>

    <main id="main" class="mx-auto max-w-6xl px-4 pb-10 pt-5 md:px-6 md:pt-8">
        @if (session('status'))
            <x-alert type="ok" class="mb-5">{{ session('status') }}</x-alert>
        @endif
        @if (session('error'))
            <x-alert type="error" class="mb-5">{{ session('error') }}</x-alert>
        @endif
        @if ($errors->any() && ($generalErrors->isNotEmpty() || View::hasSection('inline_errors')))
            <x-alert type="error" class="mb-5">
                @if (View::hasSection('inline_errors'))
                    <p class="font-bold">يرجى مراجعة الحقول المعلَّمة بالأحمر أدناه.</p>
                @endif
                @foreach ($generalErrors as $error)<p>{{ $error }}</p>@endforeach
            </x-alert>
        @endif

        @yield('content')
    </main>

    <footer class="mt-8 border-t" style="border-color: var(--line); background: var(--tint)">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 text-sm md:grid-cols-3 md:px-6">
            <div>
                <p class="mb-2 text-lg font-bold text-primary">{{ $store->name }}</p>
                @if ($store->description)
                    <p class="t-muted max-w-sm leading-7">{{ $store->description }}</p>
                @endif
            </div>
            <div>
                <p class="mb-3 font-bold">تواصل معنا</p>
                <ul class="t-muted space-y-2">
                    @if ($store->phone)<li><a href="tel:{{ preg_replace('/[^0-9+]/', '', $store->phone) }}" class="inline-flex min-h-11 items-center gap-2 md:min-h-0"><x-ui-icon name="phone" class="size-4" /> <bdi dir="ltr">{{ $store->phone }}</bdi></a></li>@endif
                    @if ($store->email)<li><bdi dir="ltr">{{ $store->email }}</bdi></li>@endif
                    @if ($store->fullAddress())<li>{{ $store->fullAddress() }}</li>@endif
                </ul>
            </div>
            <div>
                <p class="mb-3 font-bold">معلومات</p>
                <ul class="t-muted">
                    <li><a class="inline-flex min-h-11 items-center hover:text-primary md:min-h-9" href="{{ route('pages.show', 'about') }}">من نحن</a></li>
                    <li><a class="inline-flex min-h-11 items-center hover:text-primary md:min-h-9" href="{{ route('pages.show', 'privacy') }}">سياسة الخصوصية</a></li>
                    <li><a class="inline-flex min-h-11 items-center hover:text-primary md:min-h-9" href="{{ route('pages.show', 'returns') }}">سياسة الاستبدال والاسترجاع</a></li>
                </ul>
            </div>
        </div>
        <p class="border-t py-4 text-center text-xs t-muted" style="border-color: var(--line)">© {{ date('Y') }} {{ $store->name }}</p>
    </footer>
    @yield('after_footer')
</body>
</html>
