@extends('layouts.app')

@section('title', 'المنتجات')

@section('content')
    @php
        $activeFilters = collect($filters)->filter(fn ($v) => filled($v) && $v !== 'latest');
        $hasFilters = $activeFilters->isNotEmpty();
    @endphp
    <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-xl font-bold">{{ ($filters['collection'] ?? null) === 'featured' ? 'المجموعة المختارة' : 'المنتجات' }}</h1>
        <p class="text-sm text-stone-600"><bdi>{{ $products->total() }}</bdi> منتج</p>
    </div>

    <div class="grid gap-5 md:grid-cols-4">
        <details class="card h-fit md:col-span-1 md:open:block" @if($hasFilters) open @endif data-filters>
            <summary class="flex min-h-12 cursor-pointer list-none items-center justify-between px-4 text-sm font-semibold md:pointer-events-none">
                <span>بحث وتصفية @if($hasFilters)<span class="badge-sale ms-1">{{ $activeFilters->count() }}</span>@endif</span>
                <span aria-hidden="true" class="md:hidden">▾</span>
            </summary>
            <form action="{{ route('products.index') }}" method="get" class="space-y-4 border-t border-stone-200 p-4">
                <div>
                    <label for="q" class="label">بحث</label>
                    <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="input" placeholder="اسم المنتج أو الرمز">
                </div>
                <div>
                    <label for="category" class="label">التصنيف</label>
                    <select id="category" name="category" class="input">
                        <option value="">كل التصنيفات</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label for="min_price" class="label">من سعر</label>
                        <input id="min_price" type="number" min="0" step="0.01" inputmode="decimal" name="min_price" value="{{ $filters['min_price'] ?? '' }}" class="input">
                    </div>
                    <div>
                        <label for="max_price" class="label">إلى سعر</label>
                        <input id="max_price" type="number" min="0" step="0.01" inputmode="decimal" name="max_price" value="{{ $filters['max_price'] ?? '' }}" class="input">
                    </div>
                </div>
                <label class="flex min-h-11 items-center gap-2 text-sm">
                    <input type="checkbox" name="availability" value="in_stock" class="size-5" @checked(($filters['availability'] ?? '') === 'in_stock')>
                    المتوفر فقط
                </label>
                @if (($filters['collection'] ?? null) === 'featured')
                    <input type="hidden" name="collection" value="featured">
                @endif
                <div>
                    <label for="sort" class="label">الترتيب</label>
                    <select id="sort" name="sort" class="input">
                        @foreach (['latest' => 'الأحدث', 'price_asc' => 'السعر: من الأقل', 'price_desc' => 'السعر: من الأعلى', 'name' => 'الاسم'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'latest') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary flex-1">تطبيق</button>
                    @if ($hasFilters)
                        <a href="{{ route('products.index') }}" class="btn btn-outline">مسح الفلاتر</a>
                    @endif
                </div>
            </form>
        </details>

        <section class="md:col-span-3" aria-label="نتائج البحث">
            @if ($products->isEmpty())
                <div class="card p-8 text-center">
                    <p class="font-semibold">لا توجد منتجات مطابقة.</p>
                    <p class="mt-1 text-sm text-stone-600">جرّب كلمة أخرى أو وسّع نطاق السعر.</p>
                    @if ($hasFilters)
                        <a href="{{ route('products.index') }}" class="btn btn-primary mt-4">مسح الفلاتر</a>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-2 gap-3 lg:grid-cols-3">
                    @foreach ($products as $product)
                        @include('partials.product-card', ['product' => $product])
                    @endforeach
                </div>
                <div class="mt-6">{{ $products->links() }}</div>
            @endif
        </section>
    </div>
@endsection
