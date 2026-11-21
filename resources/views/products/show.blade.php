@extends('layouts.app')

@section('title', $product->name)
@section('description', \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 150))
@section('body_class', 'pb-28 md:pb-0')

@php
    $images = $product->imageUrls();
    $inStock = $product->isInStock();
    $maxQty = max(1, min((int) $product->stock, (int) config('store.max_cart_quantity')));
    $lowStock = $inStock && $product->stock <= config('store.low_stock_threshold');
@endphp

@section('content')
    <nav class="mb-4 flex flex-wrap items-center gap-x-1 text-sm t-muted" aria-label="مسار التنقل">
        <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center hover:text-primary md:min-h-8">الرئيسية</a>
        <x-icon name="chevron-left" class="size-3.5" />
        <a href="{{ route('products.index') }}" class="inline-flex min-h-11 items-center hover:text-primary md:min-h-8">المنتجات</a>
        @if ($product->category)
            <x-icon name="chevron-left" class="size-3.5" />
            <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="inline-flex min-h-11 items-center hover:text-primary md:min-h-8">{{ $product->category->name }}</a>
        @endif
    </nav>

    <div class="grid gap-6 md:grid-cols-2 md:gap-12">
        {{-- الصور --}}
        <div class="min-w-0">
            <div class="relative aspect-square overflow-hidden rounded-3xl" style="background: var(--tint)">
                @if ($images)
                    <img data-gallery-main src="{{ $images[0] }}" alt="{{ $product->name }}" class="size-full object-cover" fetchpriority="high">
                @else
                    <div class="flex size-full flex-col items-center justify-center gap-2 text-primary/40"><x-icon name="image" class="size-14" /><span class="text-sm">لا توجد صورة</span></div>
                @endif
                @if ($product->hasDiscount() && $inStock)
                    <span class="badge badge-sale absolute start-3 top-3 !px-3 !py-1 !text-sm">خصم <bdi dir="ltr">{{ (int) round((1 - (float) $product->price / (float) $product->compare_at_price) * 100) }}%</bdi></span>
                @endif
            </div>
            @if (count($images) > 1)
                <div class="mt-3 grid grid-cols-5 gap-2.5">
                    @foreach ($images as $image)
                        <button type="button" data-gallery-thumb="{{ $image }}" class="aspect-square overflow-hidden rounded-xl border-2 border-transparent bg-white transition hover:border-primary focus-visible:border-primary" aria-label="عرض الصورة {{ $loop->iteration }}">
                            <img src="{{ $image }}" alt="" loading="lazy" class="size-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- المعلومات: بالترتيب الذي يحتاجه الزبون ليقرر --}}
        <div class="min-w-0">
            @if ($product->category)
                <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="mb-1 inline-block text-sm font-bold text-primary">{{ $product->category->name }}</a>
            @endif
            <h1 class="t-title !leading-[1.5]">{{ $product->name }}</h1>

            <div class="mt-4 flex flex-wrap items-center gap-3">
                <x-price :amount="$product->price" :compare="$product->compare_at_price" size="lg" :badge="false" />
                @if ($inStock)
                    <span class="badge badge-ok">متوفر</span>
                    @if ($lowStock)<span class="badge badge-warn">الكمية محدودة (<bdi dir="ltr">{{ $product->stock }}</bdi>)</span>@endif
                @else
                    <span class="badge badge-muted">غير متوفر حاليًا</span>
                @endif
            </div>

            @if ($product->description)
                <div class="prose-ar mt-5 border-t pt-5" style="border-color: var(--line)">{!! nl2br(e($product->description)) !!}</div>
            @endif

            {{-- الشراء --}}
            @if ($inStock)
                <div class="mt-6 flex items-center gap-4">
                    <label for="quantity" class="label !mb-0">الكمية</label>
                    <x-qty id="quantity" form="buy-form" :max="$maxQty" />
                </div>
                <form id="buy-form" action="{{ route('cart.add', $product->id) }}" method="post" class="sticky-bar md:static md:z-auto md:mt-5 md:border-0 md:bg-transparent md:p-0 md:backdrop-blur-none">
                    @csrf
                    <div class="mx-auto flex max-w-6xl items-center gap-4">
                        <div class="md:hidden">
                            <p class="text-xs t-muted">السعر</p>
                            <x-price :amount="$product->price" size="md" />
                        </div>
                        <x-button class="flex-1 md:!min-h-14 md:text-lg">
                            <x-icon name="cart" class="size-5" /> أضف إلى السلة
                        </x-button>
                    </div>
                </form>
            @else
                <div class="mt-6 rounded-2xl p-4" style="background: var(--tint)">
                    <p class="font-bold">هذا المنتج غير متوفر حاليًا</p>
                    <p class="t-small t-muted mt-1">تصفّح منتجات مشابهة أو تواصل مع المتجر لمعرفة موعد توفره.</p>
                    <x-button :href="$product->category ? route('products.index', ['category' => $product->category->slug]) : route('products.index')" variant="outline" size="sm" class="mt-3">تصفّح المزيد</x-button>
                </div>
            @endif

            {{-- تفاصيل قابلة للطي (على نمط IKEA): معلومات حقيقية من المنتج والمتجر فقط --}}
            <div class="mt-6 divide-y border-y" style="border-color: var(--line); --tw-divide-color: var(--line)">
                <details class="fold">
                    <summary>تفاصيل المنتج <x-icon name="chevron-down" class="fold-icon size-5" /></summary>
                    <dl class="t-small grid grid-cols-[auto_1fr] gap-x-6 gap-y-2 pb-4">
                        @if ($product->sku)<dt class="t-muted">رمز المنتج</dt><dd><bdi dir="ltr">{{ $product->sku }}</bdi></dd>@endif
                        @if ($product->category)<dt class="t-muted">التصنيف</dt><dd>{{ $product->category->name }}</dd>@endif
                        <dt class="t-muted">التوفر</dt><dd>{{ $inStock ? 'متوفر' : 'غير متوفر حاليًا' }}</dd>
                    </dl>
                </details>
                <details class="fold">
                    <summary>الشحن والدفع <x-icon name="chevron-down" class="fold-icon size-5" /></summary>
                    <ul class="t-small space-y-1.5 pb-4">
                        <li>{{ (float) $store->shipping_fee > 0 ? 'رسوم الشحن: '.$store->money($store->shipping_fee).' لكل طلب.' : 'الشحن مجاني.' }}</li>
                        @if ($store->cod_enabled)<li>الدفع عند الاستلام متاح.</li>@endif
                        @if ($store->manual_payment_enabled)<li>{{ $store->manual_payment_label ?: 'الدفع اليدوي' }} متاح.</li>@endif
                        <li><a href="{{ route('pages.show', 'returns') }}" class="font-bold text-primary underline underline-offset-4">سياسة الاستبدال والاسترجاع</a></li>
                    </ul>
                </details>
            </div>
        </div>
    </div>

    @if ($related->isNotEmpty())
        <section class="section" aria-labelledby="related">
            <x-section-heading id="related" title="منتجات مشابهة" :href="$product->category ? route('products.index', ['category' => $product->category->slug]) : route('products.index')" />
            <div class="grid grid-cols-2 gap-x-3 gap-y-7 md:grid-cols-4 md:gap-x-5">
                @foreach ($related as $item)
                    <x-product-card :product="$item" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
