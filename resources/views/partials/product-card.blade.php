@php($hasVariants = $product->hasVariants())
<article class="card flex flex-col overflow-hidden">
    <a href="{{ route('products.show', $product->slug) }}" class="relative block aspect-square bg-secondary/40">
        @if ($product->primary_image_url)
            <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover">
        @endif
        @if ($product->hasDiscount() && ! $hasVariants)
            <span class="badge-sale absolute start-2 top-2">خصم {{ round((1 - (float) $product->price / (float) $product->compare_at_price) * 100) }}%</span>
        @endif
    </a>
    <div class="flex flex-1 flex-col gap-1.5 p-3">
        <a href="{{ route('products.show', $product->slug) }}" class="line-clamp-2 min-h-10 text-sm font-semibold leading-5 hover:text-primary">{{ $product->name }}</a>
        <div class="flex flex-wrap items-baseline gap-x-2">
            @if ($hasVariants && $product->minPrice() !== (float) $product->variants->where('is_active', true)->map(fn ($v) => $v->price !== null ? (float) $v->price : (float) $product->price)->max())
                <span class="text-xs text-stone-600">ابتداءً من</span>
            @endif
            <span class="text-base font-bold text-primary"><bdi>{{ $store->money($product->minPrice()) }}</bdi></span>
            @if ($product->hasDiscount() && ! $hasVariants)
                <span class="text-xs text-stone-500 line-through"><bdi>{{ $store->money($product->compare_at_price) }}</bdi></span>
            @endif
        </div>
        @if (! $product->isInStock())
            <span class="mt-auto rounded-xl bg-stone-100 py-2.5 text-center text-sm text-stone-600">غير متوفر</span>
        @elseif ($hasVariants)
            <a href="{{ route('products.show', $product->slug) }}" class="btn btn-outline mt-auto w-full">اختر {{ $product->option_name ?: 'الخيار' }}</a>
        @else
            <form action="{{ route('cart.add', $product->id) }}" method="post" class="mt-auto">
                @csrf
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-primary w-full">أضف إلى السلة</button>
            </form>
        @endif
    </div>
</article>
