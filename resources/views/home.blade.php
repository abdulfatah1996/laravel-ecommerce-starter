@extends('layouts.app')

@section('content')
    @php($heroImage = $hero?->primary_image_url)
    <section class="-mx-4 -mt-5 mb-8 md:mx-0 md:mt-0 md:overflow-hidden md:rounded-3xl" aria-label="المجموعة المميزة">
        <div class="relative isolate flex min-h-[26rem] items-end bg-primary md:min-h-[30rem]">
            @if ($heroImage)
                <img src="{{ $heroImage }}" alt="{{ $hero->name }}" fetchpriority="high" class="absolute inset-0 -z-10 h-full w-full object-cover">
                <div class="absolute inset-0 -z-10 bg-gradient-to-t from-stone-950/80 via-stone-950/35 to-transparent"></div>
            @endif
            <div class="w-full p-6 pb-8 md:max-w-xl md:p-12 {{ $heroImage ? 'text-white' : 'text-on-primary' }}">
                <h1 class="text-3xl font-bold leading-tight md:text-5xl">{{ $store->tagline ?: $store->name }}</h1>
                @if ($store->description)
                    <p class="mt-3 line-clamp-2 max-w-md text-base leading-7 opacity-95">{{ $store->description }}</p>
                @endif
                <a href="{{ route('products.index', ['collection' => 'featured']) }}" class="btn btn-lg btn-accent mt-6 w-full sm:w-auto">تسوّق المجموعة</a>
            </div>
        </div>
    </section>

    @if ($categories->isNotEmpty())
        <section class="mb-10" aria-labelledby="cats">
            <h2 id="cats" class="mb-4 text-xl font-bold">تسوّق حسب التصنيف</h2>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                @foreach ($categories as $category)
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="group card overflow-hidden hover:border-primary">
                        <span class="block aspect-[4/3] bg-secondary/50">
                            @if ($category->image_url)
                                <img src="{{ $category->image_url }}" alt="" loading="lazy" class="h-full w-full object-cover">
                            @endif
                        </span>
                        <span class="block px-3 py-3 text-center text-sm font-semibold group-hover:text-primary">{{ $category->name }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($featured->isNotEmpty())
        <section class="mb-10" aria-labelledby="featured">
            <div class="mb-4 flex items-baseline justify-between">
                <h2 id="featured" class="text-xl font-bold">مجموعة مختارة</h2>
                <a href="{{ route('products.index', ['collection' => 'featured']) }}" class="text-sm font-semibold text-primary">عرض الكل</a>
            </div>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                @foreach ($featured as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif

    <section class="mb-10 grid gap-3 text-sm sm:grid-cols-3" aria-label="خدمة المتجر">
        <p class="card p-4"><strong class="block">الدفع عند الاستلام</strong><span class="text-stone-600">ادفع حين يصلك الطلب.</span></p>
        <p class="card p-4"><strong class="block">التوصيل</strong><span class="text-stone-600">{{ $store->delivery_info ?: 'نوصّل طلبك إلى باب بيتك.' }}</span></p>
        <p class="card p-4"><strong class="block">استبدال واسترجاع</strong><span class="text-stone-600"><a class="underline" href="{{ route('pages.show', 'returns') }}">اقرأ السياسة</a></span></p>
    </section>

    <section aria-labelledby="latest">
        <h2 id="latest" class="mb-4 text-xl font-bold">وصل حديثًا</h2>
        @if ($latest->isEmpty())
            <p class="card p-6 text-center text-stone-600">لا توجد منتجات بعد. أضف منتجاتك من لوحة الإدارة.</p>
        @else
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                @foreach ($latest as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @endif
    </section>
@endsection
