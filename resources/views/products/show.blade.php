@extends('layouts.app')

@section('title', $product->name)
@section('description', \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 150))

@section('content')
    @php($images = $product->imageUrls())

    <nav class="mb-4 text-sm text-stone-500" aria-label="مسار التنقل">
        <a href="{{ route('home') }}" class="hover:text-primary">الرئيسية</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('products.index') }}" class="hover:text-primary">المنتجات</a>
        @if ($product->category)
            <span aria-hidden="true">/</span>
            <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="hover:text-primary">{{ $product->category->name }}</a>
        @endif
    </nav>

    <div class="grid gap-8 md:grid-cols-2">
        <div>
            <div class="card aspect-square overflow-hidden bg-secondary/40">
                @if ($images)
                    <img data-gallery-main src="{{ $images[0] }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full items-center justify-center text-stone-400">لا توجد صورة</div>
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

        <div>
            <h1 class="text-2xl font-extrabold">{{ $product->name }}</h1>
            <div class="mt-3 flex items-baseline gap-3">
                <span class="text-2xl font-bold text-primary">{{ $store->money($product->price) }}</span>
                @if ($product->hasDiscount())
                    <span class="text-stone-400 line-through">{{ $store->money($product->compare_at_price) }}</span>
                @endif
            </div>

            <p class="mt-3 text-sm">
                @if ($product->isInStock())
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">متوفر</span>
                    @if ($product->stock <= config('store.low_stock_threshold'))
                        <span class="ms-2 text-amber-700">الكمية محدودة ({{ $product->stock }})</span>
                    @endif
                @else
                    <span class="rounded-full bg-stone-100 px-3 py-1 text-stone-600">غير متوفر حاليًا</span>
                @endif
            </p>

            @if ($product->sku)
                <p class="mt-2 text-xs text-stone-500">رمز المنتج: {{ $product->sku }}</p>
            @endif

            @if ($product->description)
                <div class="prose-ar mt-5">{!! nl2br(e($product->description)) !!}</div>
            @endif

            @if ($product->isInStock())
                <form action="{{ route('cart.add', $product->id) }}" method="post" class="mt-6 flex items-end gap-3">
                    @csrf
                    <div class="w-24">
                        <label for="quantity" class="label">الكمية</label>
                        <input id="quantity" type="number" name="quantity" value="1" min="1" max="{{ min($product->stock, config('store.max_cart_quantity')) }}" class="input">
                    </div>
                    <button type="submit" class="btn btn-primary flex-1">أضف إلى السلة</button>
                </form>
            @endif
        </div>
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
