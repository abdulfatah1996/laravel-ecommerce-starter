@extends('layouts.app')

@section('title', $product->name)
@section('description', \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 150))
@section('hide_wa', '1')
@section('main_class', 'pb-28 md:pb-6')

@section('content')
    @php
        $images = $product->imageUrls();
        $variants = $product->variants->where('is_active', true)->values();
        $hasVariants = $variants->isNotEmpty();
        $firstAvailable = $variants->first(fn ($v) => $v->stock > 0);
        $startVariant = $firstAvailable ?? $variants->first();
        $startPrice = $hasVariants ? $startVariant->effectivePrice() : (float) $product->price;
        $startStock = $hasVariants ? $startVariant->stock : $product->stock;
        $maxQty = (int) config('store.max_cart_quantity');
        $variantData = $variants->mapWithKeys(fn ($v) => [$v->id => ['price' => $v->effectivePrice(), 'priceText' => $store->money($v->effectivePrice()), 'stock' => $v->stock]]);
    @endphp

    <nav class="mb-3 text-sm text-stone-600" aria-label="مسار التنقل">
        <a href="{{ route('home') }}" class="hover:text-primary">الرئيسية</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('products.index') }}" class="hover:text-primary">المنتجات</a>
        @if ($product->category)
            <span aria-hidden="true">/</span>
            <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="hover:text-primary">{{ $product->category->name }}</a>
        @endif
    </nav>

    <div class="grid gap-6 md:grid-cols-2 md:gap-10">
        <div>
            <div class="relative card aspect-square overflow-hidden bg-secondary/40" data-gallery-frame>
                @if ($images)
                    <img data-gallery-main src="{{ $images[0] }}" alt="{{ $product->name }}" fetchpriority="high" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full items-center justify-center text-stone-600">لا توجد صورة</div>
                @endif
                @if ($product->preview_3d)
                    <div data-viewer-stage class="absolute inset-0 hidden bg-secondary/40"></div>
                    <button type="button" data-viewer-toggle data-viewer-type="{{ $product->preview_3d }}" data-viewer-color="{{ $store->primary_color }}"
                        class="btn btn-outline absolute bottom-3 start-3 shadow">عرض ثلاثي الأبعاد</button>
                @endif
            </div>
            @if (count($images) > 1)
                <div class="mt-3 grid grid-cols-5 gap-2">
                    @foreach ($images as $image)
                        <button type="button" data-gallery-thumb="{{ $image }}" class="card aspect-square overflow-hidden" aria-label="عرض الصورة {{ $loop->iteration }}">
                            <img src="{{ $image }}" alt="" loading="lazy" class="h-full w-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <form action="{{ route('cart.add', $product->id) }}" method="post" data-product-form
              data-variants='@json($variantData)' data-max="{{ $maxQty }}" data-has-variants="{{ $hasVariants ? 1 : 0 }}" data-base-stock="{{ $product->stock }}"
              data-low="{{ config('store.low_stock_threshold') }}">
            @csrf
            <h1 class="text-2xl font-bold leading-snug">{{ $product->name }}</h1>

            <div class="mt-3 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                <span class="text-3xl font-bold text-primary" data-price><bdi>{{ $store->money($startPrice) }}</bdi></span>
                @if ($product->hasDiscount() && ! $hasVariants)
                    <span class="text-stone-600 line-through"><bdi>{{ $store->money($product->compare_at_price) }}</bdi></span>
                    <span class="badge-sale">وفّر <bdi>{{ $store->money((float) $product->compare_at_price - (float) $product->price) }}</bdi></span>
                @endif
            </div>

            <p class="mt-3 text-sm" aria-live="polite" data-stock-note>
                @if ($startStock > 0)
                    <span class="rounded-full bg-emerald-50 px-3 py-1 font-semibold text-emerald-800">متوفر</span>
                    @if ($startStock <= config('store.low_stock_threshold'))
                        <span class="ms-2 text-amber-800">الكمية محدودة (<bdi>{{ $startStock }}</bdi>)</span>
                    @endif
                @else
                    <span class="rounded-full bg-stone-100 px-3 py-1 text-stone-700">غير متوفر حاليًا</span>
                @endif
            </p>

            @if ($hasVariants)
                <fieldset class="mt-5">
                    <legend class="label">{{ $product->option_name ?: 'الخيار' }}</legend>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($variants as $variant)
                            <label class="chip">
                                <input type="radio" name="variant_id" value="{{ $variant->id }}" class="sr-only" required
                                    @checked($startVariant->id === $variant->id) @disabled($variant->stock < 1)>
                                {{ $variant->label }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endif

            @if ($product->description)
                <div class="prose-ar mt-5">{!! nl2br(e($product->description)) !!}</div>
            @endif

            <ul class="mt-5 space-y-1 text-sm text-stone-700">
                <li>{{ $product->delivery_note ?: ($store->delivery_info ?: 'التوصيل إلى باب البيت، والدفع عند الاستلام.') }}</li>
                <li>استبدال واسترجاع حسب <a class="font-semibold text-primary underline" href="{{ route('pages.show', 'returns') }}">السياسة</a>.</li>
                @if ($product->sku)<li class="text-stone-600">رمز المنتج: <bdi>{{ $product->sku }}</bdi></li>@endif
                @if ($store->whatsappUrl())
                    <li><a class="font-semibold text-primary underline" target="_blank" rel="noopener" href="{{ $store->whatsappUrl('أريد الاستفسار عن: '.$product->name) }}">اسأل عن هذا المنتج على واتساب</a></li>
                @endif
            </ul>

            <div class="buy-bar mt-6" data-buy-bar>
                <div class="mx-auto flex max-w-6xl items-center gap-3 md:max-w-none">
                    <div class="flex items-center rounded-xl border border-stone-300 bg-white" role="group" aria-label="الكمية">
                        <button type="button" data-qty-step="-1" class="size-11 text-lg" aria-label="إنقاص الكمية">−</button>
                        <input type="number" name="quantity" value="1" min="1" max="{{ max(1, min($startStock, $maxQty)) }}" inputmode="numeric" data-qty aria-label="الكمية" class="w-10 border-0 bg-transparent p-0 text-center text-base font-semibold focus:outline-none">
                        <button type="button" data-qty-step="1" class="size-11 text-lg" aria-label="زيادة الكمية">+</button>
                    </div>
                    <button type="submit" data-add class="btn btn-primary btn-lg flex-1" @disabled($startStock < 1)>
                        <span data-add-label>{{ $startStock < 1 ? 'غير متوفر' : 'أضف إلى السلة' }}</span>
                        <span class="font-bold md:hidden" data-bar-price><bdi>{{ $store->money($startPrice) }}</bdi></span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if ($related->isNotEmpty())
        <section class="mt-12" aria-labelledby="related">
            <h2 id="related" class="mb-4 text-lg font-bold">منتجات مشابهة</h2>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                @foreach ($related as $item)
                    @include('partials.product-card', ['product' => $item])
                @endforeach
            </div>
        </section>
    @endif
@endsection
