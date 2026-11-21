@extends('layouts.app')

@php
    // صور المنتجات المميزة (الفعلية) تُستخدم في غلاف الصفحة؛ بدونها يظهر غلاف لوني بسيط
    $heroProducts = $featured->concat($latest)->unique('id')->filter(fn ($p) => $p->primary_image_url)->take(3)->values();
@endphp

@section('content')
    {{-- 1) أول شاشة: ماذا يبيع المتجر + طريق مباشر للبضاعة --}}
    <section class="relative overflow-hidden rounded-3xl px-5 py-7 md:px-12 md:py-14" style="background: var(--tint)" aria-labelledby="hero-title">
        <div class="relative z-10 grid items-center gap-6 md:grid-cols-[1.1fr_1fr] md:gap-10">
            <div>
                <p class="mb-2 inline-flex items-center gap-2 text-sm font-bold text-primary">
                    <span class="inline-block h-0.5 w-6 rounded bg-accent"></span>{{ $store->name }}
                </p>
                <h1 id="hero-title" class="t-display">{{ $store->description ?: 'تسوّق منتجاتنا المختارة' }}</h1>
                <div class="mt-5 flex flex-wrap gap-3">
                    <x-button :href="route('products.index')" variant="primary" class="min-w-40">تسوّق الآن</x-button>
                    @if ($categories->isNotEmpty())
                        <x-button href="#categories" variant="outline">التصنيفات</x-button>
                    @endif
                </div>
            </div>

            @if ($heroProducts->isNotEmpty())
                <ul class="grid grid-cols-3 gap-2.5 md:gap-4" aria-label="من منتجاتنا">
                    @foreach ($heroProducts as $i => $hp)
                        <li @class(['md:translate-y-6' => $i === 1])>
                            <a href="{{ route('products.show', $hp->slug) }}" class="block aspect-[4/5] overflow-hidden rounded-2xl bg-white shadow-sm" aria-label="{{ $hp->name }}">
                                <img src="{{ $hp->primary_image_url }}" alt="{{ $hp->name }}" class="size-full object-cover" fetchpriority="high">
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>

    {{-- 2) الوصول السريع للتصنيفات --}}
    @if ($categories->isNotEmpty())
        <section id="categories" class="section" aria-labelledby="cats">
            <x-section-heading id="cats" title="تسوّق حسب التصنيف" :href="route('products.index')" />
            <div class="-mx-4 flex gap-2.5 overflow-x-auto px-4 pb-1 no-scrollbar md:mx-0 md:flex-wrap md:overflow-visible md:px-0">
                @foreach ($categories as $category)
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="chip md:!min-h-12 md:px-6 md:text-base">{{ $category->name }}</a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- 3) المنتجات --}}
    @if ($featured->isNotEmpty())
        <section class="section" aria-labelledby="featured">
            <x-section-heading id="featured" title="منتجات مميزة" :href="route('products.index')" />
            <div class="grid grid-cols-2 gap-x-3 gap-y-7 md:grid-cols-4 md:gap-x-5">
                @foreach ($featured as $product)
                    <x-product-card :product="$product" :priority="$loop->index < 2" />
                @endforeach
            </div>
        </section>
    @endif

    <section class="section" aria-labelledby="latest">
        <x-section-heading id="latest" title="وصل حديثًا" :href="route('products.index')" />
        @if ($latest->isEmpty())
            <x-empty-state title="لا توجد منتجات بعد" text="أضف منتجاتك من لوحة الإدارة وستظهر هنا." />
        @else
            <div class="grid grid-cols-2 gap-x-3 gap-y-7 md:grid-cols-4 md:gap-x-5">
                @foreach ($latest as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- 4) معلومات الشراء من إعدادات المتجر الفعلية فقط (لا ادّعاءات) --}}
    @php
        $perks = [];
        if ($store->cod_enabled) { $perks[] = ['cash', 'الدفع عند الاستلام', 'تدفع نقدًا عند وصول طلبك.']; }
        $perks[] = ['truck', 'التوصيل', (float) $store->shipping_fee > 0 ? 'رسوم الشحن '.$store->money($store->shipping_fee).' لكل طلب.' : 'الشحن مجاني.'];
        if ($store->phone) { $perks[] = ['phone', 'تواصل معنا', $store->phone]; }
    @endphp
    <section class="section grid gap-3 {{ [1 => 'md:grid-cols-1', 2 => 'md:grid-cols-2'][count($perks)] ?? 'md:grid-cols-3' }}" aria-label="معلومات الشراء">
        @foreach ($perks as [$icon, $title, $text])
            <div class="card flex items-start gap-3 p-4">
                <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary"><x-icon :name="$icon" class="size-5" /></span>
                <div class="min-w-0">
                    <p class="font-bold">{{ $title }}</p>
                    <p class="t-small t-muted">{!! $icon === 'phone' ? '<bdi dir="ltr">'.e($text).'</bdi>' : e($text) !!}</p>
                </div>
            </div>
        @endforeach
    </section>
@endsection
