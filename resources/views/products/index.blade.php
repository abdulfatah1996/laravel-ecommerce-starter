@extends('layouts.app')

@section('title', 'المنتجات')

@section('content')
    <h1 class="mb-4 text-xl font-bold">المنتجات</h1>

    <div class="grid gap-6 md:grid-cols-4">
        <form action="{{ route('products.index') }}" method="get" class="card h-fit space-y-4 p-4 md:col-span-1">
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
                    <input id="min_price" type="number" min="0" step="0.01" name="min_price" value="{{ $filters['min_price'] ?? '' }}" class="input">
                </div>
                <div>
                    <label for="max_price" class="label">إلى سعر</label>
                    <input id="max_price" type="number" min="0" step="0.01" name="max_price" value="{{ $filters['max_price'] ?? '' }}" class="input">
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="availability" value="in_stock" @checked(($filters['availability'] ?? '') === 'in_stock')>
                المتوفر فقط
            </label>

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
                <a href="{{ route('products.index') }}" class="btn btn-outline">مسح</a>
            </div>
        </form>

        <section class="md:col-span-3" aria-label="نتائج البحث">
            @if ($products->isEmpty())
                <p class="card p-8 text-center text-stone-500">لا توجد منتجات مطابقة.</p>
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
