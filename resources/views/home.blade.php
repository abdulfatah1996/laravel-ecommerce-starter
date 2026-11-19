@extends('layouts.app')

@section('content')
    <section class="mb-8 rounded-2xl bg-primary px-6 py-10 text-on-primary md:px-12 md:py-16">
        <h1 class="text-2xl font-extrabold md:text-4xl">{{ $store->name }}</h1>
        @if ($store->description)
            <p class="mt-3 max-w-2xl leading-8 opacity-90">{{ $store->description }}</p>
        @endif
        <a href="{{ route('products.index') }}" class="btn btn-accent mt-6">تصفّح المنتجات</a>
    </section>

    @if ($categories->isNotEmpty())
        <section class="mb-10" aria-labelledby="cats">
            <h2 id="cats" class="mb-4 text-lg font-bold">التصنيفات</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                @foreach ($categories as $category)
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="card px-4 py-5 text-center text-sm font-semibold hover:border-primary hover:text-primary">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($featured->isNotEmpty())
        <section class="mb-10" aria-labelledby="featured">
            <h2 id="featured" class="mb-4 text-lg font-bold">منتجات مميزة</h2>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                @foreach ($featured as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif

    <section aria-labelledby="latest">
        <h2 id="latest" class="mb-4 text-lg font-bold">وصل حديثًا</h2>
        @if ($latest->isEmpty())
            <p class="card p-6 text-center text-stone-500">لا توجد منتجات بعد. أضف منتجاتك من لوحة الإدارة.</p>
        @else
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                @foreach ($latest as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @endif
    </section>
@endsection
