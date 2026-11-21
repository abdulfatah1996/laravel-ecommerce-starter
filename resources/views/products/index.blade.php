@extends('layouts.app')

@section('title', 'المنتجات')

@php
    $activeCategory = $categories->firstWhere('slug', $filters['category'] ?? null);
    // شرائح الفلاتر النشطة: كل شريحة تزيل فلترًا واحدًا
    $chips = array_filter([
        'q' => filled($filters['q'] ?? null) ? 'بحث: '.$filters['q'] : null,
        'category' => $activeCategory?->name,
        'min_price' => filled($filters['min_price'] ?? null) ? 'من '.$filters['min_price'] : null,
        'max_price' => filled($filters['max_price'] ?? null) ? 'إلى '.$filters['max_price'] : null,
        'availability' => ($filters['availability'] ?? '') === 'in_stock' ? 'المتوفر فقط' : null,
    ]);
    $activeCount = count($chips) + (($filters['sort'] ?? 'latest') !== 'latest' ? 1 : 0);
@endphp

@section('content')
    <div class="mb-4 flex items-end justify-between gap-3 md:mb-6">
        <div>
            <h1 class="t-title">{{ $activeCategory?->name ?? 'المنتجات' }}</h1>
            <p class="t-small t-muted"><bdi dir="ltr">{{ $products->total() }}</bdi> منتج</p>
        </div>
    </div>

    {{-- تصنيفات سريعة (تمرير أفقي داخل الشريط فقط) --}}
    @if ($categories->isNotEmpty())
        <nav class="-mx-4 mb-4 flex gap-2.5 overflow-x-auto px-4 pb-1 no-scrollbar md:mx-0 md:flex-wrap md:overflow-visible md:px-0" aria-label="التصنيفات">
            <a href="{{ route('products.index', array_filter(['q' => $filters['q'] ?? null])) }}" class="chip" @if (! $activeCategory) aria-current="true" @endif>الكل</a>
            @foreach ($categories as $category)
                <a href="{{ route('products.index', array_filter(['category' => $category->slug, 'q' => $filters['q'] ?? null])) }}" class="chip" @if ($activeCategory?->id === $category->id) aria-current="true" @endif>{{ $category->name }}</a>
            @endforeach
        </nav>
    @endif

    <div class="grid gap-6 md:grid-cols-[17rem_1fr] md:gap-8">
        {{-- الفلاتر: مطوية على الهاتف، ظاهرة كعمود جانبي على الشاشات الكبيرة --}}
        <details class="fold card self-start md:sticky md:top-24" data-filters open>
            <summary class="px-4 md:cursor-default md:pointer-events-none" data-filter-toggle>
                <span class="inline-flex items-center gap-2"><x-icon name="filter" class="size-5" /> تصفية وترتيب @if ($activeCount > 0)<span class="badge badge-sale" dir="ltr">{{ $activeCount }}</span>@endif</span>
                <x-icon name="chevron-down" class="fold-icon size-5 md:hidden" />
            </summary>
            <script>if (window.matchMedia('(max-width: 767px)').matches) document.currentScript.closest('details').removeAttribute('open');</script>
            <form action="{{ route('products.index') }}" method="get" class="space-y-4 border-t px-4 pb-4 pt-4" style="border-color: var(--line)">
                <x-field name="q" label="بحث">
                    <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="input" placeholder="اسم المنتج أو الرمز">
                </x-field>
                <x-field name="category" label="التصنيف">
                    <select id="category" name="category" class="input">
                        <option value="">كل التصنيفات</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </x-field>
                <div class="grid grid-cols-2 gap-3">
                    <x-field name="min_price" label="من سعر">
                        <input id="min_price" type="number" min="0" step="0.01" inputmode="decimal" name="min_price" value="{{ $filters['min_price'] ?? '' }}" class="input" dir="ltr" @error('min_price') aria-invalid="true" @enderror>
                    </x-field>
                    <x-field name="max_price" label="إلى سعر">
                        <input id="max_price" type="number" min="0" step="0.01" inputmode="decimal" name="max_price" value="{{ $filters['max_price'] ?? '' }}" class="input" dir="ltr" @error('max_price') aria-invalid="true" @enderror>
                    </x-field>
                </div>
                <label class="check"><input type="checkbox" name="availability" value="in_stock" @checked(($filters['availability'] ?? '') === 'in_stock')> المتوفر فقط</label>
                <x-field name="sort" label="الترتيب">
                    <select id="sort" name="sort" class="input">
                        @foreach (['latest' => 'الأحدث', 'price_asc' => 'السعر: من الأقل', 'price_desc' => 'السعر: من الأعلى', 'name' => 'الاسم'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'latest') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-field>
                <div class="flex gap-2 pt-1">
                    <x-button class="flex-1">تطبيق</x-button>
                    <x-button :href="route('products.index')" variant="outline">مسح</x-button>
                </div>
            </form>
        </details>

        <section aria-label="نتائج المنتجات" class="min-w-0">
            @if ($chips)
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    @foreach ($chips as $key => $label)
                        <a href="{{ request()->fullUrlWithoutQuery([$key, 'page']) }}" class="chip !min-h-10 gap-2 !px-3 text-sm" aria-label="إزالة الفلتر: {{ $label }}">{{ $label }} <x-icon name="close" class="size-4" /></a>
                    @endforeach
                    <a href="{{ route('products.index') }}" class="inline-flex min-h-10 items-center px-2 text-sm font-bold text-primary">مسح الكل</a>
                </div>
            @endif

            @if ($products->isEmpty())
                <x-empty-state icon="search" title="لا توجد منتجات مطابقة" text="جرّب كلمة بحث أخرى أو أزل بعض الفلاتر.">
                    <x-button :href="route('products.index')" variant="primary">عرض كل المنتجات</x-button>
                </x-empty-state>
            @else
                <div class="grid grid-cols-2 gap-x-3 gap-y-7 md:gap-x-5 lg:grid-cols-3">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" :priority="$loop->index < 2" />
                    @endforeach
                </div>
                @if ($products->hasPages())
                    <div class="mt-10">{{ $products->links('partials.pagination') }}</div>
                @endif
            @endif
        </section>
    </div>
@endsection
